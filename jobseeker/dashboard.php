<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('jobseeker');

$user  = getCurrentUser();
$jspId = $user['profile_id'];

try {
    $pdo = getDB();

    $statsStmt = $pdo->prepare(
        "SELECT
           (SELECT COUNT(*) FROM applications WHERE jobseeker_id = ?) AS total_apps,
           (SELECT COUNT(*) FROM applications WHERE jobseeker_id = ? AND status = 'pending') AS pending,
           (SELECT COUNT(*) FROM applications WHERE jobseeker_id = ? AND status = 'approved') AS approved,
           (SELECT COUNT(*) FROM saved_jobs WHERE jobseeker_id = ?) AS saved,
           (SELECT COUNT(*) FROM followed_companies WHERE jobseeker_id = ?) AS followed"
    );
    $statsStmt->execute([$jspId, $jspId, $jspId, $jspId, $jspId]);
    $stats = $statsStmt->fetch();

    // Recent applications
    $appStmt = $pdo->prepare(
        'SELECT a.id, a.status, a.applied_at,
                jp.id AS job_id, jp.title AS job_title,
                ep.company_name
         FROM applications a
         JOIN job_posts jp ON jp.id = a.job_id
         JOIN employer_profiles ep ON ep.id = jp.employer_id
         WHERE a.jobseeker_id = ?
         ORDER BY a.applied_at DESC LIMIT 5'
    );
    $appStmt->execute([$jspId]);
    $recentApps = $appStmt->fetchAll();

    // Recommended / latest open jobs
    $recStmt = $pdo->query(
        "SELECT jp.id, jp.title, jp.location, jp.salary, ep.company_name
         FROM job_posts jp
         JOIN employer_profiles ep ON ep.id = jp.employer_id
         WHERE jp.status = 'open'
         ORDER BY jp.created_at DESC LIMIT 4"
    );
    $recommended = $recStmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $stats      = ['total_apps' => 0, 'pending' => 0, 'approved' => 0, 'saved' => 0, 'followed' => 0];
    $recentApps = $recommended = [];
}

$badgeMap = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];

$pageTitle = 'My Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-0">
      <i class="bi bi-speedometer2 me-2 text-primary"></i>My Dashboard
    </h4>
    <p class="text-muted mb-0 small">
      Welcome, <strong><?= htmlspecialchars($user['fullname'] ?: $user['email']) ?></strong>
    </p>
  </div>
  <a href="/website/index.php" class="btn btn-primary">
    <i class="bi bi-search me-1"></i>Browse Jobs
  </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md">
    <div class="card border-0 bg-primary bg-opacity-10 text-center py-3">
      <div class="fs-2 fw-bold text-primary"><?= $stats['total_apps'] ?></div>
      <div class="small text-muted">Applied</div>
    </div>
  </div>
  <div class="col-6 col-md">
    <div class="card border-0 bg-warning bg-opacity-10 text-center py-3">
      <div class="fs-2 fw-bold text-warning"><?= $stats['pending'] ?></div>
      <div class="small text-muted">Pending</div>
    </div>
  </div>
  <div class="col-6 col-md">
    <div class="card border-0 bg-success bg-opacity-10 text-center py-3">
      <div class="fs-2 fw-bold text-success"><?= $stats['approved'] ?></div>
      <div class="small text-muted">Approved</div>
    </div>
  </div>
  <div class="col-6 col-md">
    <div class="card border-0 bg-info bg-opacity-10 text-center py-3">
      <div class="fs-2 fw-bold text-info"><?= $stats['saved'] ?></div>
      <div class="small text-muted">Saved</div>
    </div>
  </div>
  <div class="col-6 col-md">
    <div class="card border-0 bg-secondary bg-opacity-10 text-center py-3">
      <div class="fs-2 fw-bold text-secondary"><?= $stats['followed'] ?></div>
      <div class="small text-muted">Following</div>
    </div>
  </div>
</div>

<?php if (!$user['fullname'] || !$user['cv_path']): ?>
  <div class="alert alert-info">
    <i class="bi bi-info-circle me-1"></i>
    <?= !$user['fullname'] ? 'Complete your profile' : 'Upload your CV' ?>
    to increase your chances.
    <a href="/website/jobseeker/profile.php" class="alert-link">Update now</a>
  </div>
<?php endif; ?>

<div class="row g-4">
  <!-- Recent applications -->
  <div class="col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-file-earmark-text me-1"></i>Recent Applications</strong>
        <a href="/website/jobseeker/my_applications.php" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($recentApps)): ?>
          <p class="text-muted text-center py-4">No applications yet.</p>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($recentApps as $app): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <a href="/website/jobseeker/job_detail.php?id=<?= $app['job_id'] ?>"
                     class="fw-semibold text-decoration-none">
                    <?= htmlspecialchars($app['job_title']) ?>
                  </a>
                  <div class="small text-muted">
                    <?= htmlspecialchars($app['company_name']) ?>
                    · <?= date('M j', strtotime($app['applied_at'])) ?>
                  </div>
                </div>
                <span class="badge bg-<?= $badgeMap[$app['status']] ?>">
                  <?= $app['status'] ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Recommended jobs -->
  <div class="col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-stars me-1"></i>Latest Open Jobs</strong>
        <a href="/website/index.php" class="btn btn-sm btn-outline-primary">Browse All</a>
      </div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush">
          <?php foreach ($recommended as $job): ?>
            <a href="/website/jobseeker/job_detail.php?id=<?= $job['id'] ?>"
               class="list-group-item list-group-item-action">
              <div class="fw-semibold"><?= htmlspecialchars($job['title']) ?></div>
              <div class="small text-muted">
                <?= htmlspecialchars($job['company_name']) ?>
                <?php if ($job['location']): ?> · <?= htmlspecialchars($job['location']) ?><?php endif; ?>
                <?php if ($job['salary']): ?> · <?= htmlspecialchars($job['salary']) ?><?php endif; ?>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
