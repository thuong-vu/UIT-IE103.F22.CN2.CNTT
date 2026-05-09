<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('jobseeker');

$user  = getCurrentUser();
$jspId = $user['profile_id'];

// Unsave
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobId = (int)($_POST['job_id'] ?? 0);
    try {
        $pdo = getDB();
        $pdo->prepare('DELETE FROM saved_jobs WHERE jobseeker_id = ? AND job_id = ?')
            ->execute([$jspId, $jobId]);
        setFlash('success', 'Job removed from saved.');
    } catch (PDOException $e) {
        error_log($e->getMessage());
        setFlash('danger', 'Failed to remove job.');
    }
    header('Location: /website/jobseeker/saved_jobs.php');
    exit;
}

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "SELECT sj.id AS save_id, sj.saved_at,
                jp.id AS job_id, jp.title, jp.location, jp.salary, jp.status AS job_status,
                jp.created_at,
                ep.company_name, ep.logo_path
         FROM saved_jobs sj
         JOIN job_posts jp ON jp.id = sj.job_id
         JOIN employer_profiles ep ON ep.id = jp.employer_id
         WHERE sj.jobseeker_id = ?
         ORDER BY sj.saved_at DESC"
    );
    $stmt->execute([$jspId]);
    $savedJobs = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $savedJobs = [];
}

$pageTitle = 'Saved Jobs';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">
    <i class="bi bi-bookmark-fill me-2 text-primary"></i>Saved Jobs
    <small class="text-muted fs-6">(<?= count($savedJobs) ?>)</small>
  </h4>
  <a href="/website/index.php" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-search me-1"></i>Browse More
  </a>
</div>

<?php if (empty($savedJobs)): ?>
  <div class="text-center py-5 text-muted">
    <i class="bi bi-bookmark fs-1 d-block mb-3"></i>
    You haven't saved any jobs yet.
    <a href="/website/index.php">Browse jobs</a> and click Save Job.
  </div>
<?php else: ?>
  <div class="row row-cols-1 row-cols-md-2 g-4">
    <?php foreach ($savedJobs as $sj): ?>
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-2">
              <?php if ($sj['logo_path']): ?>
                <img src="/website/<?= htmlspecialchars($sj['logo_path']) ?>"
                     class="company-logo-sm rounded" alt="">
              <?php else: ?>
                <div class="company-logo-placeholder rounded">
                  <i class="bi bi-building"></i>
                </div>
              <?php endif; ?>
              <span class="text-muted small"><?= htmlspecialchars($sj['company_name']) ?></span>
            </div>

            <h6 class="card-title mb-1">
              <a href="/website/jobseeker/job_detail.php?id=<?= $sj['job_id'] ?>"
                 class="text-decoration-none">
                <?= htmlspecialchars($sj['title']) ?>
              </a>
            </h6>

            <?php if ($sj['job_status'] === 'closed'): ?>
              <span class="badge bg-secondary mb-2">Closed</span>
            <?php else: ?>
              <span class="badge bg-success mb-2">Open</span>
            <?php endif; ?>

            <div class="small text-muted">
              <?php if ($sj['location']): ?>
                <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($sj['location']) ?><br>
              <?php endif; ?>
              <?php if ($sj['salary']): ?>
                <i class="bi bi-cash me-1"></i><?= htmlspecialchars($sj['salary']) ?><br>
              <?php endif; ?>
              <i class="bi bi-bookmark me-1"></i>Saved <?= date('M j, Y', strtotime($sj['saved_at'])) ?>
            </div>
          </div>
          <div class="card-footer d-flex gap-2 bg-transparent">
            <a href="/website/jobseeker/job_detail.php?id=<?= $sj['job_id'] ?>"
               class="btn btn-sm btn-primary flex-grow-1">
              <i class="bi bi-eye me-1"></i>View
            </a>
            <form method="post">
              <input type="hidden" name="job_id" value="<?= $sj['job_id'] ?>">
              <button class="btn btn-sm btn-outline-danger" title="Remove">
                <i class="bi bi-bookmark-x"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
