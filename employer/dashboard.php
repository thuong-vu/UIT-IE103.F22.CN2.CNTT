<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('employer');

$user = getCurrentUser();

try {
    $pdo = getDB();

    // Stats
    $statsStmt = $pdo->prepare(
        "SELECT
           (SELECT COUNT(*) FROM job_posts WHERE employer_id = ? AND status = 'open') AS open_jobs,
           (SELECT COUNT(*) FROM job_posts WHERE employer_id = ?) AS total_jobs,
           (SELECT COUNT(*) FROM applications a
            JOIN job_posts jp ON jp.id = a.job_id
            WHERE jp.employer_id = ?) AS total_apps,
           (SELECT COUNT(*) FROM applications a
            JOIN job_posts jp ON jp.id = a.job_id
            WHERE jp.employer_id = ? AND a.status = 'pending') AS pending_apps"
    );
    $epId = $user['profile_id'];
    $statsStmt->execute([$epId, $epId, $epId, $epId]);
    $stats = $statsStmt->fetch();

    // Recent posts
    $recentStmt = $pdo->prepare(
        "SELECT jp.id, jp.title, jp.status, jp.created_at,
                (SELECT COUNT(*) FROM applications WHERE job_id = jp.id) AS app_count
         FROM job_posts jp
         WHERE jp.employer_id = ?
         ORDER BY jp.created_at DESC LIMIT 5"
    );
    $recentStmt->execute([$epId]);
    $recentPosts = $recentStmt->fetchAll();

    // Recent applicants
    $appStmt = $pdo->prepare(
        "SELECT a.id, a.status, a.applied_at,
                jp.title AS job_title, jp.id AS job_id,
                jsp.fullname, u.email
         FROM applications a
         JOIN job_posts jp ON jp.id = a.job_id
         JOIN jobseeker_profiles jsp ON jsp.id = a.jobseeker_id
         JOIN users u ON u.id = jsp.user_id
         WHERE jp.employer_id = ?
         ORDER BY a.applied_at DESC LIMIT 5"
    );
    $appStmt->execute([$epId]);
    $recentApps = $appStmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $stats = ['open_jobs' => 0, 'total_jobs' => 0, 'total_apps' => 0, 'pending_apps' => 0];
    $recentPosts = $recentApps = [];
}

$pageTitle = 'Employer Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-0">
      <i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard
    </h4>
    <p class="text-muted mb-0 small">
      Welcome, <strong><?= htmlspecialchars($user['company_name'] ?: $user['email']) ?></strong>
    </p>
  </div>
  <a href="/website/employer/post_job.php" class="btn btn-primary">
    <i class="bi bi-plus-circle me-1"></i>Post a Job
  </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 bg-primary bg-opacity-10 text-center py-3">
      <div class="fs-2 fw-bold text-primary"><?= $stats['open_jobs'] ?></div>
      <div class="small text-muted">Open Jobs</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 bg-secondary bg-opacity-10 text-center py-3">
      <div class="fs-2 fw-bold text-secondary"><?= $stats['total_jobs'] ?></div>
      <div class="small text-muted">Total Posts</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 bg-success bg-opacity-10 text-center py-3">
      <div class="fs-2 fw-bold text-success"><?= $stats['total_apps'] ?></div>
      <div class="small text-muted">Applications</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 bg-warning bg-opacity-10 text-center py-3">
      <div class="fs-2 fw-bold text-warning"><?= $stats['pending_apps'] ?></div>
      <div class="small text-muted">Pending</div>
    </div>
  </div>
</div>

<?php if (!$user['company_name']): ?>
  <div class="alert alert-info">
    <i class="bi bi-info-circle me-1"></i>
    Your company profile is incomplete.
    <a href="/website/employer/profile.php" class="alert-link">Complete it now</a>
    to attract more candidates.
  </div>
<?php endif; ?>

<div class="row g-4">
  <!-- Recent Posts -->
  <div class="col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-list-ul me-1"></i>Recent Job Posts</strong>
        <a href="/website/employer/manage_posts.php" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($recentPosts)): ?>
          <p class="text-muted text-center py-4">No posts yet.</p>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($recentPosts as $post): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <a href="/website/employer/edit_post.php?id=<?= $post['id'] ?>"
                     class="fw-semibold text-decoration-none">
                    <?= htmlspecialchars($post['title']) ?>
                  </a>
                  <div class="small text-muted">
                    <?= date('M j, Y', strtotime($post['created_at'])) ?>
                    · <?= $post['app_count'] ?> applicant<?= $post['app_count'] != 1 ? 's' : '' ?>
                  </div>
                </div>
                <span class="badge bg-<?= $post['status'] === 'open' ? 'success' : 'secondary' ?>">
                  <?= $post['status'] ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Recent Applicants -->
  <div class="col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-header">
        <strong><i class="bi bi-people me-1"></i>Recent Applicants</strong>
      </div>
      <div class="card-body p-0">
        <?php if (empty($recentApps)): ?>
          <p class="text-muted text-center py-4">No applications yet.</p>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($recentApps as $app): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <div class="fw-semibold"><?= htmlspecialchars($app['fullname'] ?: $app['email']) ?></div>
                  <div class="small text-muted">
                    <?= htmlspecialchars($app['job_title']) ?> ·
                    <?= date('M j', strtotime($app['applied_at'])) ?>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-<?= ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$app['status']] ?>">
                    <?= $app['status'] ?>
                  </span>
                  <a href="/website/employer/applicants.php?job_id=<?= $app['job_id'] ?>"
                     class="btn btn-sm btn-outline-secondary">View</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
