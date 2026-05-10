<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('employer');

$user  = getCurrentUser();
$epId  = $user['profile_id'];
$jobId = (int)($_GET['id'] ?? 0);

$errors = [];

$categories = [
    'Technology', 'Marketing', 'Sales', 'Finance', 'Human Resources',
    'Design', 'Customer Service', 'Engineering', 'Healthcare', 'Education', 'Other',
];

$recruitTypes  = ['fulltime' => 'Fulltime', 'parttime' => 'Parttime', 'online' => 'Online', 'offline' => 'Offline'];

try {
    $pdo = getDB();

    // Fetch post (verify ownership)
    $stmt = $pdo->prepare('SELECT * FROM job_posts WHERE id = ? AND employer_id = ?');
    $stmt->execute([$jobId, $epId]);
    $post = $stmt->fetch();

    if (!$post) {
        setFlash('danger', 'Job post not found or access denied.');
        header('Location: /website/employer/manage_posts.php');
        exit;
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    setFlash('danger', 'Could not load post.');
    header('Location: /website/employer/manage_posts.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title']        ?? '');
    $description  = trim($_POST['description']  ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $salaryMin    = trim($_POST['salary_min'] ?? '');
    $salaryMax    = trim($_POST['salary_max'] ?? '');
    $currency     = $_POST['currency']     ?? 'VND';
    $endDate      = trim($_POST['end_date']      ?? '');
    $recruitType  = $_POST['recruit_type'] ?? 'fulltime';
    $location     = trim($_POST['location']     ?? '');
    $category     = trim($_POST['category']     ?? '');
    $status       = in_array($_POST['status'] ?? '', ['open', 'closed']) ? $_POST['status'] : 'open';

    if ($title === '')       $errors[] = 'Job title is required.';
    if ($description === '') $errors[] = 'Job description is required.';
    if ($salaryMin !== '' && (!ctype_digit($salaryMin) || (int)$salaryMin < 0)) {
        $errors[] = 'Minimum salary must be a non-negative number.';
    }
    if ($salaryMax !== '' && (!ctype_digit($salaryMax) || (int)$salaryMax < 0)) {
        $errors[] = 'Maximum salary must be a non-negative number.';
    }
    if ($salaryMin !== '' && $salaryMax !== '' && (int)$salaryMax < (int)$salaryMin) {
        $errors[] = 'Maximum salary must be greater than or equal to minimum salary.';
    }
    if (!in_array($currency, ['VND', 'USD', 'EUR'], true))    $errors[] = 'Invalid currency.';
    if (!array_key_exists($recruitType, $recruitTypes))        $errors[] = 'Invalid recruit type.';
    if ($endDate !== '' && strtotime($endDate) < mktime(0,0,0)) $errors[] = 'End date cannot be in the past.';

    if (empty($errors)) {
        try {
            $pdo->prepare(
                'UPDATE job_posts
                 SET title = ?, description = ?, requirements = ?,
                     salary_min = ?, salary_max = ?, currency = ?,
                     end_date = ?, recruit_type = ?,
                     location = ?, category = ?, status = ?
                 WHERE id = ? AND employer_id = ?'
            )->execute([
                $title, $description, $requirements,
                $salaryMin !== '' ? (int)$salaryMin : null,
                $salaryMax !== '' ? (int)$salaryMax : null,
                $currency,
                $endDate ?: null, $recruitType,
                $location, $category, $status,
                $jobId, $epId,
            ]);

            setFlash('success', 'Job post updated.');
            header('Location: /website/employer/manage_posts.php');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'Update failed. Please try again.';
        }
    }

    // Re-populate from POST on error
    $post = array_merge($post, [
        'title' => $title, 'description' => $description, 'requirements' => $requirements,
        'salary_min' => $salaryMin, 'salary_max' => $salaryMax, 'currency' => $currency,
        'end_date' => $endDate, 'recruit_type' => $recruitType,
        'location' => $location, 'category' => $category, 'status' => $status,
    ]);
}

$pageTitle = 'Edit Job Post';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="d-flex align-items-center mb-4 gap-3">
      <a href="/website/employer/manage_posts.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
      </a>
      <h4 class="mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Job Post</h4>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="card shadow-sm">
      <div class="card-body p-4">
        <form method="post" novalidate>

          <div class="mb-3">
            <label for="title" class="form-label">Job Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="title" name="title"
                   value="<?= htmlspecialchars($post['title']) ?>" required>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label for="category" class="form-label">Category</label>
              <select class="form-select" id="category" name="category">
                <option value="">— Select —</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= htmlspecialchars($cat) ?>"
                    <?= $post['category'] === $cat ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label for="location" class="form-label">Location</label>
              <input type="text" class="form-control" id="location" name="location"
                     value="<?= htmlspecialchars($post['location']) ?>">
            </div>
            <div class="col-md-3">
              <label for="recruit_type" class="form-label">Job Type</label>
              <select class="form-select" id="recruit_type" name="recruit_type">
                <?php foreach ($recruitTypes as $val => $label): ?>
                  <option value="<?= $val ?>"
                    <?= ($post['recruit_type'] ?? 'fulltime') === $val ? 'selected' : '' ?>>
                    <?= $label ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label for="status" class="form-label">Status</label>
              <select class="form-select" id="status" name="status">
                <option value="open"   <?= $post['status'] === 'open'   ? 'selected' : '' ?>>Open</option>
                <option value="closed" <?= $post['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
              </select>
            </div>
          </div>

          <!-- Salary range -->
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label for="salary_min" class="form-label">Minimum Salary</label>
              <input type="number" class="form-control" id="salary_min" name="salary_min"
                     min="0" step="1" placeholder="e.g. 10000000"
                     value="<?= htmlspecialchars($post['salary_min'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label for="salary_max" class="form-label">Maximum Salary</label>
              <input type="number" class="form-control" id="salary_max" name="salary_max"
                     min="0" step="1" placeholder="e.g. 20000000"
                     value="<?= htmlspecialchars($post['salary_max'] ?? '') ?>">
            </div>
            <div class="col-md-2">
              <label for="currency" class="form-label">Currency</label>
              <select class="form-select" id="currency" name="currency">
                <?php foreach (['VND', 'USD', 'EUR'] as $cur): ?>
                  <option value="<?= $cur ?>"
                    <?= ($post['currency'] ?? 'VND') === $cur ? 'selected' : '' ?>>
                    <?= $cur ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label for="end_date" class="form-label">Deadline</label>
              <input type="date" class="form-control" id="end_date" name="end_date"
                     min="<?= date('Y-m-d') ?>"
                     value="<?= htmlspecialchars($post['end_date'] ?? '') ?>">
            </div>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">
              Job Description <span class="text-danger">*</span>
            </label>
            <textarea class="form-control" id="description" name="description"
                      rows="7" required><?= htmlspecialchars($post['description']) ?></textarea>
          </div>

          <div class="mb-4">
            <label for="requirements" class="form-label">Requirements</label>
            <textarea class="form-control" id="requirements" name="requirements"
                      rows="5"><?= htmlspecialchars($post['requirements']) ?></textarea>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-1"></i>Save Changes
            </button>
            <a href="/website/employer/manage_posts.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
