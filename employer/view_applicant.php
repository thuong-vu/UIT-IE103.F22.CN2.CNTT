<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('employer');

$user  = getCurrentUser();
$epId  = $user['profile_id'];
$appId = (int)($_GET['app_id'] ?? 0);

// Handle approve / reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? '';
    if (!in_array($newStatus, ['approved', 'rejected', 'pending'], true)) {
        setFlash('danger', 'Invalid status.');
    } else {
        try {
            $pdo = getDB();
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
                    ->execute([$newStatus, $appId]);
                setFlash('success', 'Status updated to "' . $newStatus . '".');
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            setFlash('danger', 'Update failed.');
        }
    }
    header('Location: /website/employer/view_applicant.php?app_id=' . $appId);
    exit;
}

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'SELECT a.id AS app_id, a.status, a.applied_at, a.cover_letter,
                jp.id AS job_id, jp.title AS job_title,
                jsp.id AS profile_id, jsp.fullname, jsp.phone, jsp.bio, jsp.cv_path, jsp.avatar_path,
                u.email, u.created_at AS member_since
         FROM applications a
         JOIN job_posts jp ON jp.id = a.job_id
         JOIN jobseeker_profiles jsp ON jsp.id = a.jobseeker_id
         JOIN users u ON u.id = jsp.user_id
         WHERE a.id = ? AND jp.employer_id = ?'
    );
    $stmt->execute([$appId, $epId]);
    $app = $stmt->fetch();

    if (!$app) {
        setFlash('danger', 'Application not found or access denied.');
        header('Location: /website/employer/manage_posts.php');
        exit;
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    setFlash('danger', 'Could not load application.');
    header('Location: /website/employer/manage_posts.php');
    exit;
}

$badgeMap = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];

$pageTitle = 'View Applicant';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex align-items-center mb-4 gap-3">
  <a href="/website/employer/applicants.php?job_id=<?= $app['job_id'] ?>"
     class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="mb-0">
    <i class="bi bi-person-badge me-2 text-primary"></i>Applicant Profile
  </h4>
</div>

<div class="row g-4">
  <!-- Profile card -->
  <div class="col-md-4">
    <div class="card shadow-sm text-center">
      <div class="card-body py-4">
        <?php if ($app['avatar_path']): ?>
          <img src="/website/<?= htmlspecialchars($app['avatar_path']) ?>"
               class="rounded-circle mb-3" width="90" height="90" style="object-fit:cover;" alt="avatar">
        <?php else: ?>
          <div class="avatar-placeholder mx-auto mb-3">
            <i class="bi bi-person fs-1"></i>
          </div>
        <?php endif; ?>
        <h5 class="mb-1"><?= htmlspecialchars($app['fullname'] ?: 'Anonymous') ?></h5>
        <p class="text-muted small mb-3"><?= htmlspecialchars($app['email']) ?></p>
        <?php if ($app['phone']): ?>
          <p class="mb-1 small"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($app['phone']) ?></p>
        <?php endif; ?>
        <p class="small text-muted">Member since <?= date('M Y', strtotime($app['member_since'])) ?></p>
        <?php if ($app['cv_path']): ?>
          <a href="/website/<?= htmlspecialchars($app['cv_path']) ?>"
             class="btn btn-primary btn-sm w-100 mt-2" target="_blank">
            <i class="bi bi-file-earmark-pdf me-1"></i>Download CV
          </a>
        <?php else: ?>
          <span class="badge bg-secondary mt-2">No CV uploaded</span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Application details -->
  <div class="col-md-8">
    <!-- Application meta -->
    <div class="card shadow-sm mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Application for: <?= htmlspecialchars($app['job_title']) ?></strong>
        <span class="badge bg-<?= $badgeMap[$app['status']] ?> fs-6">
          <?= ucfirst($app['status']) ?>
        </span>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-3">
          <i class="bi bi-calendar3 me-1"></i>
          Applied: <?= date('M j, Y H:i', strtotime($app['applied_at'])) ?>
        </p>
        <h6>Cover Letter</h6>
        <?php if ($app['cover_letter']): ?>
          <div class="bg-light rounded p-3 text-pre-wrap">
            <?= nl2br(htmlspecialchars($app['cover_letter'])) ?>
          </div>
        <?php else: ?>
          <p class="text-muted fst-italic">No cover letter provided.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Bio -->
    <?php if ($app['bio']): ?>
      <div class="card shadow-sm mb-4">
        <div class="card-header"><strong>About the Applicant</strong></div>
        <div class="card-body">
          <?= nl2br(htmlspecialchars($app['bio'])) ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Decision buttons -->
    <div class="card shadow-sm">
      <div class="card-body d-flex gap-2">
        <?php if ($app['status'] !== 'approved'): ?>
          <form method="post">
            <input type="hidden" name="status" value="approved">
            <button class="btn btn-success">
              <i class="bi bi-check-circle me-1"></i>Approve
            </button>
          </form>
        <?php endif; ?>
        <?php if ($app['status'] !== 'rejected'): ?>
          <form method="post">
            <input type="hidden" name="status" value="rejected">
            <button class="btn btn-danger">
              <i class="bi bi-x-circle me-1"></i>Reject
            </button>
          </form>
        <?php endif; ?>
        <?php if ($app['status'] !== 'pending'): ?>
          <form method="post">
            <input type="hidden" name="status" value="pending">
            <button class="btn btn-outline-warning">
              <i class="bi bi-arrow-counterclockwise me-1"></i>Reset to Pending
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
