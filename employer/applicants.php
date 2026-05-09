<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('employer');

$user  = getCurrentUser();
$epId  = $user['profile_id'];
$jobId = (int)($_GET['job_id'] ?? 0);

// Handle approve/reject via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appId  = (int)($_POST['app_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if (!in_array($status, ['approved', 'rejected', 'pending'], true)) {
        setFlash('danger', 'Invalid status.');
    } else {
        try {
            $pdo = getDB();
            // Verify ownership
            $own = $pdo->prepare(
                'SELECT a.id FROM applications a
                 JOIN job_posts jp ON jp.id = a.job_id
                 WHERE a.id = ? AND jp.employer_id = ?'
            );
            $own->execute([$appId, $epId]);
            if (!$own->fetch()) {
                setFlash('danger', 'Application not found.');
            } else {
                $pdo->prepare('UPDATE applications SET status = ? WHERE id = ?')
                    ->execute([$status, $appId]);
                setFlash('success', 'Application status updated.');
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            setFlash('danger', 'Update failed.');
        }
    }
    header('Location: /website/employer/applicants.php?job_id=' . $jobId);
    exit;
}

try {
    $pdo = getDB();

    // Verify job belongs to this employer
    $jobStmt = $pdo->prepare(
        'SELECT jp.id, jp.title FROM job_posts jp WHERE jp.id = ? AND jp.employer_id = ?'
    );
    $jobStmt->execute([$jobId, $epId]);
    $job = $jobStmt->fetch();

    if (!$job) {
        setFlash('danger', 'Job not found or access denied.');
        header('Location: /website/employer/manage_posts.php');
        exit;
    }

    // Get all jobs for dropdown filter
    $allJobs = $pdo->prepare(
        'SELECT id, title FROM job_posts WHERE employer_id = ? ORDER BY created_at DESC'
    );
    $allJobs->execute([$epId]);
    $allJobs = $allJobs->fetchAll();

    $statusFilter = $_GET['status'] ?? '';
    $where  = ['a.job_id = ?'];
    $params = [$jobId];
    if (in_array($statusFilter, ['pending', 'approved', 'rejected'], true)) {
        $where[]  = 'a.status = ?';
        $params[] = $statusFilter;
    }
    $whereSQL = 'WHERE ' . implode(' AND ', $where);

    $appStmt = $pdo->prepare(
        'SELECT a.id AS app_id, a.status, a.applied_at, a.cover_letter,
                jsp.id AS profile_id, jsp.fullname, jsp.phone, jsp.cv_path,
                u.email
         FROM applications a
         JOIN jobseeker_profiles jsp ON jsp.id = a.jobseeker_id
         JOIN users u ON u.id = jsp.user_id
         ' . $whereSQL . '
         ORDER BY a.applied_at DESC'
    );
    $appStmt->execute($params);
    $applications = $appStmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    setFlash('danger', 'Could not load applications.');
    header('Location: /website/employer/manage_posts.php');
    exit;
}

$badgeMap = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];

$pageTitle = 'Applicants — ' . $job['title'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex align-items-center mb-3 gap-3">
  <a href="/website/employer/manage_posts.php" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="mb-0">
    <i class="bi bi-people me-2 text-primary"></i>Applicants
  </h4>
</div>

<!-- Job selector -->
<div class="row g-3 mb-4">
  <div class="col-md-5">
    <label class="form-label fw-semibold">Viewing job:</label>
    <form method="get" class="d-flex gap-2">
      <select name="job_id" class="form-select" onchange="this.form.submit()">
        <?php foreach ($allJobs as $j): ?>
          <option value="<?= $j['id'] ?>"
            <?= $j['id'] == $jobId ? 'selected' : '' ?>>
            <?= htmlspecialchars($j['title']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
  <div class="col-md-4">
    <label class="form-label fw-semibold">Filter by status:</label>
    <div class="btn-group w-100">
      <?php foreach ([''=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $val => $label): ?>
        <a href="?job_id=<?= $jobId ?>&status=<?= $val ?>"
           class="btn btn-sm btn-<?= $statusFilter === $val ? '' : 'outline-' ?>secondary">
          <?= $label ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<h5 class="mb-3">
  <?= htmlspecialchars($job['title']) ?>
  <small class="text-muted">(<?= count($applications) ?> applicant<?= count($applications) != 1 ? 's' : '' ?>)</small>
</h5>

<?php if (empty($applications)): ?>
  <div class="text-center py-5 text-muted">
    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
    No applications<?= $statusFilter ? ' with status "' . htmlspecialchars($statusFilter) . '"' : '' ?>.
  </div>
<?php else: ?>
  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Applicant</th>
            <th>Contact</th>
            <th>Applied</th>
            <th class="text-center">Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($applications as $app): ?>
            <tr>
              <td>
                <div class="fw-semibold">
                  <?= htmlspecialchars($app['fullname'] ?: 'Anonymous') ?>
                </div>
                <?php if ($app['cv_path']): ?>
                  <a href="/website/<?= htmlspecialchars($app['cv_path']) ?>"
                     target="_blank" class="small text-primary">
                    <i class="bi bi-file-earmark-pdf me-1"></i>Download CV
                  </a>
                <?php else: ?>
                  <span class="small text-muted">No CV uploaded</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="small"><?= htmlspecialchars($app['email']) ?></div>
                <?php if ($app['phone']): ?>
                  <div class="small text-muted"><?= htmlspecialchars($app['phone']) ?></div>
                <?php endif; ?>
              </td>
              <td class="text-nowrap small">
                <?= date('M j, Y H:i', strtotime($app['applied_at'])) ?>
              </td>
              <td class="text-center">
                <span class="badge bg-<?= $badgeMap[$app['status']] ?>">
                  <?= $app['status'] ?>
                </span>
              </td>
              <td class="text-end text-nowrap">
                <a href="/website/employer/view_applicant.php?app_id=<?= $app['app_id'] ?>"
                   class="btn btn-sm btn-outline-primary me-1">
                  <i class="bi bi-eye me-1"></i>View
                </a>
                <?php if ($app['status'] !== 'approved'): ?>
                  <form method="post" class="d-inline">
                    <input type="hidden" name="app_id" value="<?= $app['app_id'] ?>">
                    <input type="hidden" name="status" value="approved">
                    <button class="btn btn-sm btn-success me-1">
                      <i class="bi bi-check-lg"></i>
                    </button>
                  </form>
                <?php endif; ?>
                <?php if ($app['status'] !== 'rejected'): ?>
                  <form method="post" class="d-inline">
                    <input type="hidden" name="app_id" value="<?= $app['app_id'] ?>">
                    <input type="hidden" name="status" value="rejected">
                    <button class="btn btn-sm btn-danger">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
