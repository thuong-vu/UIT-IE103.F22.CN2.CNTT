<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('jobseeker');

$user  = getCurrentUser();
$jspId = $user['profile_id'];

// Unfollow
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employerId = (int)($_POST['employer_id'] ?? 0);
    try {
        $pdo = getDB();
        $pdo->prepare('DELETE FROM followed_companies WHERE jobseeker_id = ? AND employer_id = ?')
            ->execute([$jspId, $employerId]);
        setFlash('success', 'Company unfollowed.');
    } catch (PDOException $e) {
        error_log($e->getMessage());
        setFlash('danger', 'Failed to unfollow.');
    }
    header('Location: /website/jobseeker/followed_companies.php');
    exit;
}

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "SELECT fc.followed_at,
                ep.id AS employer_id, ep.company_name, ep.logo_path,
                ep.address, ep.website,
                (SELECT COUNT(*) FROM job_posts jp
                 WHERE jp.employer_id = ep.id AND jp.status = 'open') AS open_jobs
         FROM followed_companies fc
         JOIN employer_profiles ep ON ep.id = fc.employer_id
         WHERE fc.jobseeker_id = ?
         ORDER BY fc.followed_at DESC"
    );
    $stmt->execute([$jspId]);
    $companies = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $companies = [];
}

$pageTitle = 'Followed Companies';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">
    <i class="bi bi-buildings me-2 text-primary"></i>Followed Companies
    <small class="text-muted fs-6">(<?= count($companies) ?>)</small>
  </h4>
  <a href="/website/index.php" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-search me-1"></i>Browse Jobs
  </a>
</div>

<?php if (empty($companies)): ?>
  <div class="text-center py-5 text-muted">
    <i class="bi bi-buildings fs-1 d-block mb-3"></i>
    You're not following any companies yet.<br>
    Visit a job post and click <strong>Follow Company</strong>.
  </div>
<?php else: ?>
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    <?php foreach ($companies as $co): ?>
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-body text-center pt-4">
            <?php if ($co['logo_path']): ?>
              <img src="/website/<?= htmlspecialchars($co['logo_path']) ?>"
                   class="company-logo-lg rounded mb-2" alt="logo">
            <?php else: ?>
              <div class="company-logo-placeholder-lg mx-auto mb-2">
                <i class="bi bi-building fs-2"></i>
              </div>
            <?php endif; ?>

            <h6 class="card-title mb-1">
              <?= htmlspecialchars($co['company_name'] ?: 'Company') ?>
            </h6>

            <?php if ($co['address']): ?>
              <p class="text-muted small mb-1">
                <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($co['address']) ?>
              </p>
            <?php endif; ?>

            <p class="small mb-2">
              <span class="badge bg-success bg-opacity-75">
                <?= $co['open_jobs'] ?> open job<?= $co['open_jobs'] != 1 ? 's' : '' ?>
              </span>
            </p>

            <?php if ($co['website']): ?>
              <p class="small mb-1">
                <a href="<?= htmlspecialchars($co['website']) ?>"
                   target="_blank" rel="noopener" class="text-muted">
                  <i class="bi bi-globe me-1"></i>Website
                </a>
              </p>
            <?php endif; ?>

            <p class="text-muted small mb-0">
              <i class="bi bi-clock me-1"></i>
              Following since <?= date('M j, Y', strtotime($co['followed_at'])) ?>
            </p>
          </div>
          <div class="card-footer d-flex gap-2 bg-transparent">
            <a href="/website/index.php?keyword=<?= urlencode($co['company_name']) ?>"
               class="btn btn-sm btn-outline-primary flex-grow-1">
              <i class="bi bi-briefcase me-1"></i>View Jobs
            </a>
            <form method="post">
              <input type="hidden" name="employer_id" value="<?= $co['employer_id'] ?>">
              <button class="btn btn-sm btn-outline-danger" title="Unfollow"
                      onclick="return confirm('Unfollow this company?')">
                <i class="bi bi-person-dash"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
