<?php
require_once __DIR__ . '/includes/auth.php';

$keyword  = trim($_GET['keyword']  ?? '');
$location = trim($_GET['location'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 12;
$offset   = ($page - 1) * $perPage;

try {
    $pdo = getDB();

    $where  = ['jp.status = ?', '(jp.end_date IS NULL OR jp.end_date >= CURDATE())'];
    $params = ['open'];

    if ($keyword !== '') {
        $where[]  = '(jp.title LIKE ? OR jp.description LIKE ? OR ep.company_name LIKE ?)';
        $like     = '%' . $keyword . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    if ($location !== '') {
        $where[]  = 'jp.location LIKE ?';
        $params[] = '%' . $location . '%';
    }

    $whereSQL = 'WHERE ' . implode(' AND ', $where);

    $countStmt = $pdo->prepare(
        'SELECT COUNT(*) FROM job_posts jp
         JOIN employer_profiles ep ON ep.id = jp.employer_id ' . $whereSQL
    );
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();
    $pages = (int)ceil($total / $perPage);

    $params[] = $perPage;
    $params[] = $offset;
    $stmt = $pdo->prepare(
        'SELECT jp.id, jp.title, jp.location, jp.category, jp.created_at,
                jp.salary_min, jp.salary_max, jp.currency,
                jp.end_date, jp.recruit_type,
                ep.company_name, ep.logo_path
         FROM job_posts jp
         JOIN employer_profiles ep ON ep.id = jp.employer_id
         ' . $whereSQL . '
         ORDER BY jp.created_at DESC
         LIMIT ? OFFSET ?'
    );
    $stmt->execute($params);
    $jobs = $stmt->fetchAll();

    $statsStmt = $pdo->query(
        "SELECT
           (SELECT COUNT(*) FROM job_posts WHERE status = 'open') AS open_jobs,
           (SELECT COUNT(*) FROM users WHERE role = 'employer') AS employers,
           (SELECT COUNT(*) FROM users WHERE role = 'jobseeker') AS seekers"
    );
    $stats = $statsStmt->fetch();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $jobs  = [];
    $total = $pages = 0;
    $stats = ['open_jobs' => 0, 'employers' => 0, 'seekers' => 0];
}

$pageTitle = 'Browse Jobs';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero / Search -->
<div class="hero-banner rounded-3 p-5 mb-4 text-white"
     style="background: linear-gradient(135deg,#0d6efd,#6610f2);">
  <h1 class="display-6 fw-bold mb-1">Find Your Dream Job</h1>
  <p class="lead mb-4">Search thousands of open positions across the country.</p>

  <form method="get" class="row g-2">
    <div class="col-md-5">
      <div class="input-group">
        <span class="input-group-text bg-white border-0">
          <i class="bi bi-search text-muted"></i>
        </span>
        <input type="text" class="form-control border-0" name="keyword"
               placeholder="Job title, keyword, company…"
               value="<?= htmlspecialchars($keyword) ?>">
      </div>
    </div>
    <div class="col-md-4">
      <div class="input-group">
        <span class="input-group-text bg-white border-0">
          <i class="bi bi-geo-alt text-muted"></i>
        </span>
        <input type="text" class="form-control border-0" name="location"
               placeholder="City, province…"
               value="<?= htmlspecialchars($location) ?>">
      </div>
    </div>
    <div class="col-md-3">
      <button type="submit" class="btn btn-warning fw-semibold w-100">
        <i class="bi bi-search me-1"></i>Search Jobs
      </button>
    </div>
  </form>
</div>

<!-- Stats -->
<div class="row g-3 mb-4 text-center">
  <div class="col-4">
    <div class="card border-0 bg-primary bg-opacity-10 py-3">
      <div class="fs-3 fw-bold text-primary"><?= number_format($stats['open_jobs']) ?></div>
      <div class="small text-muted">Open Jobs</div>
    </div>
  </div>
  <div class="col-4">
    <div class="card border-0 bg-success bg-opacity-10 py-3">
      <div class="fs-3 fw-bold text-success"><?= number_format($stats['employers']) ?></div>
      <div class="small text-muted">Employers</div>
    </div>
  </div>
  <div class="col-4">
    <div class="card border-0 bg-info bg-opacity-10 py-3">
      <div class="fs-3 fw-bold text-info"><?= number_format($stats['seekers']) ?></div>
      <div class="small text-muted">Job Seekers</div>
    </div>
  </div>
</div>

<!-- Results header -->
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">
    <?php if ($keyword || $location): ?>
      <?= $total ?> result<?= $total !== 1 ? 's' : '' ?> found
      <?php if ($keyword): ?> for "<strong><?= htmlspecialchars($keyword) ?></strong>"<?php endif; ?>
      <?php if ($location): ?> in <strong><?= htmlspecialchars($location) ?></strong><?php endif; ?>
    <?php else: ?>
      Latest Job Openings
    <?php endif; ?>
  </h5>
  <?php if ($keyword || $location): ?>
    <a href="/website/index.php" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-x me-1"></i>Clear
    </a>
  <?php endif; ?>
</div>

<?php if (empty($jobs)): ?>
  <div class="text-center py-5 text-muted">
    <i class="bi bi-search fs-1 d-block mb-3"></i>
    No open jobs found. Try different keywords.
  </div>
<?php else: ?>
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
    <?php
    $rtMap = [
        'fulltime'  => ['primary',   'Fulltime'],
        'parttime'  => ['info',      'Parttime'],
        'online'    => ['success',   'Online'],
        'offline'   => ['secondary', 'Offline'],
    ];
    foreach ($jobs as $job):
        // Build salary range string
        $sMin = isset($job['salary_min']) && $job['salary_min'] !== '' ? number_format((int)$job['salary_min']) : null;
        $sMax = isset($job['salary_max']) && $job['salary_max'] !== '' ? number_format((int)$job['salary_max']) : null;
        $cur  = $job['currency'] ?? 'VND';
        if ($sMin && $sMax && $sMin !== $sMax) $salaryStr = "$sMin – $sMax $cur";
        elseif ($sMin)                         $salaryStr = "$sMin $cur";
        elseif ($sMax)                         $salaryStr = "$sMax $cur";
        else                                   $salaryStr = '';

        // Recruit type badge
        [$rtColor, $rtLabel] = $rtMap[$job['recruit_type'] ?? 'fulltime'] ?? ['secondary', '—'];

        // End-date badge
        $endBadge = '';
        if (!empty($job['end_date'])) {
            $diff = (int)ceil((strtotime($job['end_date']) - mktime(0,0,0)) / 86400);
            if ($diff <= 3 && $diff >= 0) {
                $endBadge = '<span class="badge bg-danger ms-1">Closing Soon</span>';
            }
        }
    ?>
      <div class="col">
        <a href="/website/jobseeker/job_detail.php?id=<?= $job['id'] ?>"
           class="text-decoration-none">
          <div class="card h-100 job-card shadow-sm">
            <div class="card-body">
              <div class="d-flex align-items-center mb-2 gap-2">
                <?php if ($job['logo_path']): ?>
                  <img src="/website/<?= htmlspecialchars($job['logo_path']) ?>"
                       alt="logo" class="company-logo-sm rounded">
                <?php else: ?>
                  <div class="company-logo-placeholder rounded">
                    <i class="bi bi-building"></i>
                  </div>
                <?php endif; ?>
                <span class="text-muted small"><?= htmlspecialchars($job['company_name'] ?: 'Company') ?></span>
              </div>

              <h6 class="card-title text-dark fw-semibold mb-1">
                <?= htmlspecialchars($job['title']) ?>
              </h6>

              <div class="mb-2">
                <span class="badge bg-<?= $rtColor ?>"><?= $rtLabel ?></span>
                <?= $endBadge ?>
                <?php if ($job['category']): ?>
                  <span class="badge bg-secondary bg-opacity-10 text-secondary border ms-1">
                    <?= htmlspecialchars($job['category']) ?>
                  </span>
                <?php endif; ?>
              </div>

              <div class="small text-muted">
                <?php if ($job['location']): ?>
                  <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['location']) ?><br>
                <?php endif; ?>
                <?php if ($salaryStr): ?>
                  <i class="bi bi-cash me-1"></i><?= htmlspecialchars($salaryStr) ?><br>
                <?php endif; ?>
                <i class="bi bi-calendar3 me-1"></i>
                <?= date('M j, Y', strtotime($job['created_at'])) ?>
                <?php if (!empty($job['end_date'])): ?>
                  · Deadline: <?= date('M j, Y', strtotime($job['end_date'])) ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($pages > 1): ?>
    <nav>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
