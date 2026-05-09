<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('jobseeker');

$user  = getCurrentUser();
$jspId = $user['profile_id'];

// Withdraw application
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appId = (int)($_POST['app_id'] ?? 0);
    try {
        $pdo = getDB();
        // Verify ownership and only pending can be withdrawn
        $own = $pdo->prepare(
            "SELECT id FROM applications WHERE id = ? AND jobseeker_id = ? AND status = 'pending'"
        );
        $own->execute([$appId, $jspId]);
        if (!$own->fetch()) {
            setFlash('warning', 'Application not found or cannot be withdrawn.');
        } else {
            $pdo->prepare('DELETE FROM applications WHERE id = ?')->execute([$appId]);
            setFlash('success', 'Application withdrawn.');
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        setFlash('danger', 'Withdrawal failed.');
    }
    header('Location: /website/jobseeker/my_applications.php');
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

try {
    $pdo    = getDB();
    $where  = ['a.jobseeker_id = ?'];
    $params = [$jspId];

    if (in_array($statusFilter, ['pending', 'approved', 'rejected'], true)) {
        $where[]  = 'a.status = ?';
        $params[] = $statusFilter;
    }
    $whereSQL = 'WHERE ' . implode(' AND ', $where);

    $countStmt = $pdo->prepare(
        'SELECT COUNT(*) FROM applications a ' . $whereSQL
    );
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();
    $pages = (int)ceil($total / $perPage);

    $params[] = $perPage;
    $params[] = $offset;

    $stmt = $pdo->prepare(
        'SELECT a.id AS app_id, a.status, a.applied_at,
                jp.id AS job_id, jp.title AS job_title, jp.location, jp.status AS job_status,
                ep.company_name, ep.logo_path
         FROM applications a
         JOIN job_posts jp ON jp.id = a.job_id
         JOIN employer_profiles ep ON ep.id = jp.employer_id
         ' . $whereSQL . '
         ORDER BY a.applied_at DESC
         LIMIT ? OFFSET ?'
    );
    $stmt->execute($params);
    $applications = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $applications = [];
    $total = $pages = 0;
}

$badgeMap = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];

$pageTitle = 'My Applications';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">
    <i class="bi bi-file-earmark-text me-2 text-primary"></i>My Applications
    <small class="text-muted fs-6">(<?= $total ?>)</small>
  </h4>
  <a href="/website/index.php" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-search me-1"></i>Browse More Jobs
  </a>
</div>

<!-- Status filter -->
<div class="btn-group mb-4">
  <?php foreach ([''=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $val => $label): ?>
    <a href="?status=<?= $val ?>"
       class="btn btn-sm btn-<?= $statusFilter === $val ? '' : 'outline-' ?>secondary">
      <?= $label ?>
    </a>
  <?php endforeach; ?>
</div>

<?php if (empty($applications)): ?>
  <div class="text-center py-5 text-muted">
    <i class="bi bi-file-earmark fs-1 d-block mb-3"></i>
    No applications found.
    <a href="/website/index.php">Browse jobs</a>
  </div>
<?php else: ?>
  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Job</th>
            <th>Company</th>
            <th>Location</th>
            <th>Applied</th>
            <th class="text-center">Status</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($applications as $app): ?>
            <tr>
              <td>
                <a href="/website/jobseeker/job_detail.php?id=<?= $app['job_id'] ?>"
                   class="fw-semibold text-decoration-none">
                  <?= htmlspecialchars($app['job_title']) ?>
                </a>
                <?php if ($app['job_status'] === 'closed'): ?>
                  <span class="badge bg-secondary ms-1 small">Closed</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <?php if ($app['logo_path']): ?>
                    <img src="/website/<?= htmlspecialchars($app['logo_path']) ?>"
                         class="company-logo-sm rounded" alt="">
                  <?php endif; ?>
                  <?= htmlspecialchars($app['company_name']) ?>
                </div>
              </td>
              <td class="small"><?= htmlspecialchars($app['location'] ?: '—') ?></td>
              <td class="text-nowrap small">
                <?= date('M j, Y', strtotime($app['applied_at'])) ?>
              </td>
              <td class="text-center">
                <span class="badge bg-<?= $badgeMap[$app['status']] ?>">
                  <?= $app['status'] ?>
                </span>
              </td>
              <td class="text-end">
                <?php if ($app['status'] === 'pending'): ?>
                  <form method="post"
                        onsubmit="return confirm('Withdraw this application?')">
                    <input type="hidden" name="app_id" value="<?= $app['app_id'] ?>">
                    <button class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-x-circle me-1"></i>Withdraw
                    </button>
                  </form>
                <?php else: ?>
                  <span class="text-muted small">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($pages > 1): ?>
    <nav class="mt-3">
      <ul class="pagination justify-content-center">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
          <li class="page-item <?= $p === $page ? 'active' : '' ?>">
            <a class="page-link"
               href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>">
              <?= $p ?>
            </a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
