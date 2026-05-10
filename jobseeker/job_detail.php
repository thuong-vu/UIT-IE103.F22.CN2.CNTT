<?php
require_once __DIR__ . '/../includes/auth.php';

$jobId = (int)($_GET['id'] ?? 0);

$isJobseeker = isLoggedIn() && getCurrentRole() === 'jobseeker';
$jspId       = null;
$jspProfile  = null;

if ($isJobseeker) {
    $jspProfile = getCurrentUser();
    $jspId      = $jspProfile['profile_id'];
}

/**
 * Returns true if jobseeker already has a pending/approved application
 * for any job posted by the given employer.
 */
function checkDuplicateEmployerApplication(PDO $pdo, int $jobseekerId, int $employerId): bool
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM applications a
         JOIN job_posts jp ON jp.id = a.job_id
         WHERE a.jobseeker_id = ?
           AND jp.employer_id = ?
           AND a.status IN ('pending','approved')"
    );
    $stmt->execute([$jobseekerId, $employerId]);
    return (int)$stmt->fetchColumn() > 0;
}

// Handle POST actions (apply, save/unsave, follow/unfollow)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isJobseeker) {
    $action = $_POST['action'] ?? '';

    try {
        $pdo = getDB();

        if ($action === 'apply') {
            $coverLetter = trim($_POST['cover_letter'] ?? '');
            $epId        = (int)($_POST['employer_id_hidden'] ?? 0);

            // PHP-level check for same-employer active application
            if ($epId > 0 && checkDuplicateEmployerApplication($pdo, $jspId, $epId)) {
                setFlash('danger', 'You already have an active application with this employer.');
            } else {
                // Check not already applied for this exact job
                $chk = $pdo->prepare(
                    'SELECT id FROM applications WHERE job_id = ? AND jobseeker_id = ?'
                );
                $chk->execute([$jobId, $jspId]);
                if ($chk->fetch()) {
                    setFlash('warning', 'You have already applied for this job.');
                } else {
                    try {
                        $pdo->prepare(
                            'INSERT INTO applications (job_id, jobseeker_id, cover_letter) VALUES (?, ?, ?)'
                        )->execute([$jobId, $jspId, $coverLetter]);
                        setFlash('success', 'Application submitted!');
                    } catch (PDOException $insertEx) {
                        // Catch trigger SIGNAL SQLSTATE '45000'
                        if ($insertEx->getCode() === '45000') {
                            setFlash('danger', $insertEx->getMessage());
                        } else {
                            throw $insertEx;
                        }
                    }
                }
            }

        } elseif ($action === 'save') {
            $pdo->prepare(
                'INSERT IGNORE INTO saved_jobs (jobseeker_id, job_id) VALUES (?, ?)'
            )->execute([$jspId, $jobId]);
            setFlash('success', 'Job saved.');

        } elseif ($action === 'unsave') {
            $pdo->prepare(
                'DELETE FROM saved_jobs WHERE jobseeker_id = ? AND job_id = ?'
            )->execute([$jspId, $jobId]);
            setFlash('success', 'Job removed from saved.');

        } elseif ($action === 'follow') {
            $epId = (int)($_POST['employer_id'] ?? 0);
            $pdo->prepare(
                'INSERT IGNORE INTO followed_companies (jobseeker_id, employer_id) VALUES (?, ?)'
            )->execute([$jspId, $epId]);
            setFlash('success', 'Company followed.');

        } elseif ($action === 'unfollow') {
            $epId = (int)($_POST['employer_id'] ?? 0);
            $pdo->prepare(
                'DELETE FROM followed_companies WHERE jobseeker_id = ? AND employer_id = ?'
            )->execute([$jspId, $epId]);
            setFlash('success', 'Company unfollowed.');
        }

    } catch (PDOException $e) {
        error_log($e->getMessage());
        setFlash('danger', 'Action failed. Please try again.');
    }

    header('Location: /website/jobseeker/job_detail.php?id=' . $jobId);
    exit;
}

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'SELECT jp.*, ep.id AS employer_id, ep.company_name, ep.description AS company_desc,
                ep.logo_path, ep.website, ep.address
         FROM job_posts jp
         JOIN employer_profiles ep ON ep.id = jp.employer_id
         WHERE jp.id = ?'
    );
    $stmt->execute([$jobId]);
    $job = $stmt->fetch();

    if (!$job) {
        setFlash('danger', 'Job not found.');
        header('Location: /website/index.php');
        exit;
    }

    $alreadyApplied = false;
    $isSaved        = false;
    $isFollowing    = false;

    if ($isJobseeker) {
        $a = $pdo->prepare('SELECT id FROM applications WHERE job_id = ? AND jobseeker_id = ?');
        $a->execute([$jobId, $jspId]);
        $alreadyApplied = (bool)$a->fetch();

        $s = $pdo->prepare('SELECT id FROM saved_jobs WHERE job_id = ? AND jobseeker_id = ?');
        $s->execute([$jobId, $jspId]);
        $isSaved = (bool)$s->fetch();

        $f = $pdo->prepare('SELECT id FROM followed_companies WHERE employer_id = ? AND jobseeker_id = ?');
        $f->execute([$job['employer_id'], $jspId]);
        $isFollowing = (bool)$f->fetch();
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    setFlash('danger', 'Could not load job.');
    header('Location: /website/index.php');
    exit;
}

$pageTitle = $job['title'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row g-4">
  <!-- Main column -->
  <div class="col-lg-8">
    <!-- Job header -->
    <div class="card shadow-sm mb-4">
      <div class="card-body p-4">
        <div class="d-flex align-items-start gap-3 mb-3">
          <?php if ($job['logo_path']): ?>
            <img src="/website/<?= htmlspecialchars($job['logo_path']) ?>"
                 class="company-logo-lg rounded" alt="logo">
          <?php else: ?>
            <div class="company-logo-placeholder-lg rounded flex-shrink-0">
              <i class="bi bi-building fs-2"></i>
            </div>
          <?php endif; ?>
          <div>
            <h3 class="mb-1"><?= htmlspecialchars($job['title']) ?></h3>
            <p class="text-muted mb-1">
              <i class="bi bi-building me-1"></i>
              <?= htmlspecialchars($job['company_name'] ?: 'Company') ?>
            </p>
            <?php if ($job['status'] === 'closed'): ?>
              <span class="badge bg-danger">Closed</span>
            <?php else: ?>
              <span class="badge bg-success">Open</span>
            <?php endif; ?>
          </div>
        </div>

        <?php
        // Salary range label
        $sMin = isset($job['salary_min']) && $job['salary_min'] !== '' ? number_format((int)$job['salary_min']) : null;
        $sMax = isset($job['salary_max']) && $job['salary_max'] !== '' ? number_format((int)$job['salary_max']) : null;
        $cur  = $job['currency'] ?? 'VND';
        $salaryDisplay = '';
        if ($sMin && $sMax && $sMin !== $sMax) $salaryDisplay = "$sMin – $sMax $cur";
        elseif ($sMin)                         $salaryDisplay = "$sMin $cur";
        elseif ($sMax)                         $salaryDisplay = "$sMax $cur";

        // Recruit type badge
        $rtMap = ['fulltime'=>['primary','Fulltime'],'parttime'=>['info','Parttime'],
                  'online'=>['success','Online'],'offline'=>['secondary','Offline']];
        [$rtColor, $rtLabel] = $rtMap[$job['recruit_type'] ?? 'fulltime'] ?? ['secondary','—'];

        // End-date countdown
        $endDateBadge = '';
        if (!empty($job['end_date'])) {
            $diff = (int)ceil((strtotime($job['end_date']) - mktime(0,0,0)) / 86400);
            if ($diff < 0)
                $endDateBadge = '<span class="badge bg-secondary ms-1">Expired</span>';
            elseif ($diff <= 3)
                $endDateBadge = '<span class="badge bg-danger ms-1">Closing Soon ('
                              . $diff . ' day' . ($diff === 1 ? '' : 's') . ')</span>';
            else
                $endDateBadge = '<span class="badge bg-light text-muted border ms-1">Deadline: '
                              . date('M j, Y', strtotime($job['end_date'])) . '</span>';
        }
        ?>

        <div class="row g-2 text-muted small mb-3">
          <?php if ($job['location']): ?>
            <div class="col-auto">
              <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['location']) ?>
            </div>
          <?php endif; ?>
          <?php if ($salaryDisplay): ?>
            <div class="col-auto">
              <i class="bi bi-cash me-1"></i><?= htmlspecialchars($salaryDisplay) ?>
            </div>
          <?php endif; ?>
          <div class="col-auto">
            <span class="badge bg-<?= $rtColor ?>"><?= $rtLabel ?></span>
            <?= $endDateBadge ?>
          </div>
          <?php if ($job['category']): ?>
            <div class="col-auto">
              <i class="bi bi-tag me-1"></i><?= htmlspecialchars($job['category']) ?>
            </div>
          <?php endif; ?>
          <div class="col-auto">
            <i class="bi bi-calendar3 me-1"></i>
            Posted <?= date('M j, Y', strtotime($job['created_at'])) ?>
          </div>
        </div>

        <!-- Action buttons -->
        <?php if ($isJobseeker): ?>
          <div class="d-flex flex-wrap gap-2">
            <?php if ($job['status'] === 'open' && !$alreadyApplied): ?>
              <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyModal">
                <i class="bi bi-send me-1"></i>Apply Now
              </button>
            <?php elseif ($alreadyApplied): ?>
              <button class="btn btn-success" disabled>
                <i class="bi bi-check-circle me-1"></i>Applied
              </button>
            <?php else: ?>
              <button class="btn btn-secondary" disabled>
                <i class="bi bi-lock me-1"></i>Closed
              </button>
            <?php endif; ?>

            <form method="post">
              <input type="hidden" name="action" value="<?= $isSaved ? 'unsave' : 'save' ?>">
              <button class="btn <?= $isSaved ? 'btn-warning' : 'btn-outline-warning' ?>">
                <i class="bi bi-bookmark<?= $isSaved ? '-fill' : '' ?> me-1"></i>
                <?= $isSaved ? 'Saved' : 'Save Job' ?>
              </button>
            </form>

            <form method="post">
              <input type="hidden" name="action" value="<?= $isFollowing ? 'unfollow' : 'follow' ?>">
              <input type="hidden" name="employer_id" value="<?= $job['employer_id'] ?>">
              <button class="btn <?= $isFollowing ? 'btn-info' : 'btn-outline-info' ?>">
                <i class="bi bi-buildings<?= $isFollowing ? '-fill' : '' ?> me-1"></i>
                <?= $isFollowing ? 'Following' : 'Follow Company' ?>
              </button>
            </form>
          </div>
        <?php elseif (!isLoggedIn()): ?>
          <a href="/website/login.php" class="btn btn-primary">
            <i class="bi bi-box-arrow-in-right me-1"></i>Login to Apply
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Description -->
    <div class="card shadow-sm mb-4">
      <div class="card-header"><strong>Job Description</strong></div>
      <div class="card-body">
        <div class="text-pre-wrap"><?= nl2br(htmlspecialchars($job['description'])) ?></div>
      </div>
    </div>

    <!-- Requirements -->
    <?php if ($job['requirements']): ?>
      <div class="card shadow-sm mb-4">
        <div class="card-header"><strong>Requirements</strong></div>
        <div class="card-body">
          <div class="text-pre-wrap"><?= nl2br(htmlspecialchars($job['requirements'])) ?></div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Sidebar: Company info -->
  <div class="col-lg-4">
    <div class="card shadow-sm sticky-sidebar">
      <div class="card-header"><strong>About the Company</strong></div>
      <div class="card-body">
        <div class="text-center mb-3">
          <?php if ($job['logo_path']): ?>
            <img src="/website/<?= htmlspecialchars($job['logo_path']) ?>"
                 class="company-logo-lg rounded mb-2" alt="logo">
          <?php endif; ?>
          <h6 class="mb-0"><?= htmlspecialchars($job['company_name'] ?: 'Company') ?></h6>
        </div>
        <?php if ($job['company_desc']): ?>
          <p class="small text-muted">
            <?= nl2br(htmlspecialchars(mb_substr($job['company_desc'], 0, 200))) ?>…
          </p>
        <?php endif; ?>
        <?php if ($job['address']): ?>
          <p class="small mb-1">
            <i class="bi bi-geo-alt me-1 text-muted"></i><?= htmlspecialchars($job['address']) ?>
          </p>
        <?php endif; ?>
        <?php if ($job['website']): ?>
          <p class="small mb-0">
            <i class="bi bi-globe me-1 text-muted"></i>
            <a href="<?= htmlspecialchars($job['website']) ?>" target="_blank" rel="noopener">
              <?= htmlspecialchars($job['website']) ?>
            </a>
          </p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Apply Modal -->
<?php if ($isJobseeker && $job['status'] === 'open' && !$alreadyApplied): ?>
<div class="modal fade" id="applyModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="action" value="apply">
        <input type="hidden" name="employer_id_hidden" value="<?= $job['employer_id'] ?>">
        <div class="modal-header">
          <h5 class="modal-title">Apply for <?= htmlspecialchars($job['title']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php if (!$jspProfile['cv_path']): ?>
            <div class="alert alert-warning small">
              <i class="bi bi-exclamation-triangle me-1"></i>
              You haven't uploaded a CV.
              <a href="/website/jobseeker/profile.php">Upload one</a> to improve your chances.
            </div>
          <?php endif; ?>
          <div class="mb-3">
            <label for="cover_letter" class="form-label">Cover Letter</label>
            <textarea class="form-control" id="cover_letter" name="cover_letter"
                      rows="6"
                      placeholder="Introduce yourself and explain why you're a great fit…"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-send me-1"></i>Submit Application
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
