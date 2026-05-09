<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('employer');

$user   = getCurrentUser();
$errors = [];

$uploadDir = __DIR__ . '/../assets/uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName    = trim($_POST['company_name']             ?? '');
    $description    = trim($_POST['description']              ?? '');
    $website        = trim($_POST['website']                  ?? '');
    $address        = trim($_POST['address']                  ?? '');
    $noEmployees    = $_POST['no_employees'] !== '' ? (int)$_POST['no_employees'] : null;
    $bizRegNo       = trim($_POST['business_registration_no'] ?? '');

    if ($companyName === '') {
        $errors[] = 'Company name is required.';
    }
    if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
        $errors[] = 'Please enter a valid website URL.';
    }
    if ($noEmployees !== null && $noEmployees < 1) {
        $errors[] = 'Number of employees must be at least 1.';
    }

    $logoPath = $user['logo_path'];

    // Handle logo upload
    if (!empty($_FILES['logo']['name'])) {
        $file     = $_FILES['logo'];
        $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowed, true)) {
            $errors[] = 'Logo must be a JPEG, PNG, GIF, or WebP image.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Logo must be under 2 MB.';
        } else {
            $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newName  = 'logo_' . uniqid('', true) . '.' . strtolower($ext);
            $destPath = $uploadDir . $newName;
            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                // Remove old logo
                if ($logoPath && file_exists(__DIR__ . '/../' . $logoPath)) {
                    unlink(__DIR__ . '/../' . $logoPath);
                }
                $logoPath = 'assets/uploads/' . $newName;
            } else {
                $errors[] = 'Failed to save logo. Check upload directory permissions.';
            }
        }
    }

    if (empty($errors)) {
        try {
            $pdo = getDB();
            $pdo->prepare(
                'UPDATE employer_profiles
                 SET company_name = ?, description = ?, logo_path = ?,
                     website = ?, address = ?,
                     no_employees = ?, business_registration_no = ?
                 WHERE user_id = ?'
            )->execute([
                $companyName, $description, $logoPath,
                $website, $address,
                $noEmployees, $bizRegNo ?: null,
                $_SESSION['user_id'],
            ]);

            setFlash('success', 'Company profile updated successfully.');
            header('Location: /website/employer/profile.php');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'Failed to save profile. Please try again.';
        }
    }
} else {
    $companyName = $user['company_name']             ?? '';
    $description = $user['description']              ?? '';
    $website     = $user['website']                  ?? '';
    $address     = $user['address']                  ?? '';
    $noEmployees = $user['no_employees']             ?? '';
    $bizRegNo    = $user['business_registration_no'] ?? '';
}

$pageTitle = 'Company Profile';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <h4 class="mb-4">
      <i class="bi bi-building me-2 text-primary"></i>Company Profile
    </h4>

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
        <form method="post" enctype="multipart/form-data" novalidate>

          <!-- Logo preview -->
          <div class="mb-4 text-center">
            <?php if ($user['logo_path']): ?>
              <img src="/website/<?= htmlspecialchars($user['logo_path']) ?>"
                   alt="Logo" class="company-logo-lg rounded mb-2">
            <?php else: ?>
              <div class="company-logo-placeholder-lg mx-auto mb-2">
                <i class="bi bi-building fs-1"></i>
              </div>
            <?php endif; ?>
            <div>
              <label class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-upload me-1"></i>Upload Logo
                <input type="file" name="logo" accept="image/*" class="d-none">
              </label>
              <div class="form-text">JPEG, PNG, GIF or WebP · max 2 MB</div>
            </div>
          </div>

          <div class="mb-3">
            <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="company_name"
                   name="company_name"
                   value="<?= htmlspecialchars($companyName) ?>" required>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Company Description</label>
            <textarea class="form-control" id="description" name="description"
                      rows="5"><?= htmlspecialchars($description) ?></textarea>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="website" class="form-label">Website URL</label>
              <input type="url" class="form-control" id="website" name="website"
                     placeholder="https://example.com"
                     value="<?= htmlspecialchars($website) ?>">
            </div>
            <div class="col-md-6">
              <label for="address" class="form-label">Address</label>
              <input type="text" class="form-control" id="address" name="address"
                     value="<?= htmlspecialchars($address) ?>">
            </div>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label for="no_employees" class="form-label">Number of Employees</label>
              <input type="number" class="form-control" id="no_employees"
                     name="no_employees" min="1"
                     value="<?= htmlspecialchars((string)$noEmployees) ?>"
                     placeholder="e.g. 50">
            </div>
            <div class="col-md-6">
              <label for="business_registration_no" class="form-label">
                Business Registration No.
              </label>
              <input type="text" class="form-control" id="business_registration_no"
                     name="business_registration_no"
                     value="<?= htmlspecialchars($bizRegNo) ?>"
                     placeholder="e.g. 0301234567">
            </div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-1"></i>Save Changes
            </button>
            <a href="/website/employer/dashboard.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
