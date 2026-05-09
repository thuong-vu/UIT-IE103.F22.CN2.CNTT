<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('employer');

$user   = getCurrentUser();
$errors = [];
$old    = [
    'title' => '', 'description' => '', 'requirements' => '',
    'salary_min' => '', 'salary_max' => '', 'currency' => 'VND',
    'end_date' => '', 'recruit_type' => 'fulltime',
    'location' => '', 'category' => '',
];

$categories = [
    'Technology', 'Marketing', 'Sales', 'Finance', 'Human Resources',
    'Design', 'Customer Service', 'Engineering', 'Healthcare', 'Education', 'Other',
];

$salaryOptions = [
    ''          => '— Select salary range —',
    'below_10M' => 'Under 10M',
    '10M_15M'   => '10M – 15M',
    '15M_20M'   => '15M – 20M',
    '20M_30M'   => '20M – 30M',
    '30M_50M'   => '30M – 50M',
    'above_50M' => 'Above 50M',
];
$salaryOrder   = ['below_10M', '10M_15M', '15M_20M', '20M_30M', '30M_50M', 'above_50M'];
$recruitTypes  = ['fulltime' => 'Fulltime', 'parttime' => 'Parttime', 'online' => 'Online', 'offline' => 'Offline'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title']        ?? '');
    $description  = trim($_POST['description']  ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $salaryMin    = $_POST['salary_min']   ?? '';
    $salaryMax    = $_POST['salary_max']   ?? '';
    $currency     = $_POST['currency']     ?? 'VND';
    $endDate      = trim($_POST['end_date']      ?? '');
    $recruitType  = $_POST['recruit_type'] ?? 'fulltime';
    $location     = trim($_POST['location']     ?? '');
    $category     = trim($_POST['category']     ?? '');

    if ($title === '')       $errors[] = 'Job title is required.';
    if ($description === '') $errors[] = 'Job description is required.';
    if (!in_array($category, $categories, true) && $category !== '') {
        $errors[] = 'Invalid category.';
    }
    if (!array_key_exists($salaryMin, $salaryOptions) || !array_key_exists($salaryMax, $salaryOptions)) {
        $errors[] = 'Invalid salary range selection.';
    } elseif ($salaryMin !== '' && $salaryMax !== '') {
        if (array_search($salaryMax, $salaryOrder) < array_search($salaryMin, $salaryOrder)) {
            $errors[] = 'Maximum salary must be greater than or equal to minimum salary.';
        }
    }
    if (!in_array($currency, ['VND', 'USD', 'EUR'], true)) {
        $errors[] = 'Invalid currency.';
    }
    if (!array_key_exists($recruitType, $recruitTypes)) {
        $errors[] = 'Invalid recruit type.';
    }
    if ($endDate !== '' && strtotime($endDate) < mktime(0, 0, 0)) {
        $errors[] = 'End date cannot be in the past.';
    }

    if (empty($errors)) {
        try {
            $pdo = getDB();
            $pdo->prepare(
                "INSERT INTO job_posts
                 (employer_id, title, description, requirements,
                  salary_min, salary_max, currency, end_date, recruit_type,
                  location, category, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'open')"
            )->execute([
                $user['profile_id'], $title, $description, $requirements,
                $salaryMin ?: null, $salaryMax ?: null, $currency,
                $endDate ?: null, $recruitType,
                $location, $category,
            ]);

            setFlash('success', 'Job posted successfully!');
            header('Location: /website/employer/manage_posts.php');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'Failed to post job. Please try again.';
        }
    }

    $old = compact('title', 'description', 'requirements',
                   'salaryMin', 'salaryMax', 'currency', 'endDate', 'recruitType',
                   'location', 'category');
    $old['salary_min']   = $salaryMin;
    $old['salary_max']   = $salaryMax;
    $old['end_date']     = $endDate;
    $old['recruit_type'] = $recruitType;
}

$pageTitle = 'Post a Job';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="d-flex align-items-center mb-4 gap-3">
      <a href="/website/employer/manage_posts.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
      </a>
      <h4 class="mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i>Post a New Job</h4>
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
                   value="<?= htmlspecialchars($old['title']) ?>" required>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label for="category" class="form-label">Category</label>
              <select class="form-select" id="category" name="category">
                <option value="">— Select —</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= htmlspecialchars($cat) ?>"
                    <?= $old['category'] === $cat ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label for="location" class="form-label">Location</label>
              <input type="text" class="form-control" id="location" name="location"
                     placeholder="e.g. Ho Chi Minh City"
                     value="<?= htmlspecialchars($old['location']) ?>">
            </div>
            <div class="col-md-4">
              <label for="recruit_type" class="form-label">Job Type</label>
              <select class="form-select" id="recruit_type" name="recruit_type">
                <?php foreach ($recruitTypes as $val => $label): ?>
                  <option value="<?= $val ?>"
                    <?= $old['recruit_type'] === $val ? 'selected' : '' ?>>
                    <?= $label ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Salary range -->
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label for="salary_min" class="form-label">Minimum Salary</label>
              <select class="form-select" id="salary_min" name="salary_min">
                <?php foreach ($salaryOptions as $val => $label): ?>
                  <option value="<?= $val ?>"
                    <?= $old['salary_min'] === $val ? 'selected' : '' ?>>
                    <?= $label ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label for="salary_max" class="form-label">Maximum Salary</label>
              <select class="form-select" id="salary_max" name="salary_max">
                <?php foreach ($salaryOptions as $val => $label): ?>
                  <option value="<?= $val ?>"
                    <?= $old['salary_max'] === $val ? 'selected' : '' ?>>
                    <?= $label ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label for="currency" class="form-label">Currency</label>
              <select class="form-select" id="currency" name="currency">
                <?php foreach (['VND', 'USD', 'EUR'] as $cur): ?>
                  <option value="<?= $cur ?>"
                    <?= $old['currency'] === $cur ? 'selected' : '' ?>>
                    <?= $cur ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label for="end_date" class="form-label">Deadline</label>
              <input type="date" class="form-control" id="end_date" name="end_date"
                     min="<?= date('Y-m-d') ?>"
                     value="<?= htmlspecialchars($old['end_date']) ?>">
            </div>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">
              Job Description <span class="text-danger">*</span>
            </label>
            <textarea class="form-control" id="description" name="description"
                      rows="7" required><?= htmlspecialchars($old['description']) ?></textarea>
          </div>

          <div class="mb-4">
            <label for="requirements" class="form-label">Requirements</label>
            <textarea class="form-control" id="requirements" name="requirements"
                      rows="5" placeholder="List qualifications, skills, experience…"
                      ><?= htmlspecialchars($old['requirements']) ?></textarea>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-send me-1"></i>Post Job
            </button>
            <a href="/website/employer/manage_posts.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
