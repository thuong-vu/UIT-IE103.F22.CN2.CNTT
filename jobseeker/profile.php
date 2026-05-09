<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('jobseeker');

$user      = getCurrentUser();
$uploadDir = __DIR__ . '/../assets/uploads/';
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname    = trim($_POST['fullname']  ?? '');
    $phone       = trim($_POST['phone']     ?? '');
    $bio         = trim($_POST['bio']       ?? '');
    $skills      = trim($_POST['skills']    ?? '');
    $education   = trim($_POST['education'] ?? '');
    $jspLocation = trim($_POST['location']  ?? '');

    if ($fullname === '') $errors[] = 'Full name is required.';

    // Handle avatar upload
    $avatarPath = $user['avatar_path'];
    if (!empty($_FILES['avatar']['name'])) {
        $file     = $_FILES['avatar'];
        $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowed, true)) {
            $errors[] = 'Avatar must be JPEG, PNG, GIF, or WebP.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Avatar must be under 2 MB.';
        } else {
            $ext     = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newName = 'avatar_' . uniqid('', true) . '.' . strtolower($ext);
            $dest    = $uploadDir . $newName;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                if ($avatarPath && file_exists(__DIR__ . '/../' . $avatarPath)) {
                    unlink(__DIR__ . '/../' . $avatarPath);
                }
                $avatarPath = 'assets/uploads/' . $newName;
            } else {
                $errors[] = 'Failed to save avatar.';
            }
        }
    }

    // Handle CV upload (PDF only, max 5 MB)
    $cvPath = $user['cv_path'];
    if (!empty($_FILES['cv']['name'])) {
        $file     = $_FILES['cv'];
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if ($mimeType !== 'application/pdf') {
            $errors[] = 'CV must be a PDF file.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'CV must be under 5 MB.';
        } else {
            $newName = 'cv_' . uniqid('', true) . '.pdf';
            $dest    = $uploadDir . $newName;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                if ($cvPath && file_exists(__DIR__ . '/../' . $cvPath)) {
                    unlink(__DIR__ . '/../' . $cvPath);
                }
                $cvPath = 'assets/uploads/' . $newName;
            } else {
                $errors[] = 'Failed to save CV.';
            }
        }
    }

    if (empty($errors)) {
        try {
            $pdo = getDB();
            // Do NOT include last_update — the DB trigger sets it automatically
            $pdo->prepare(
                'UPDATE jobseeker_profiles
                 SET fullname = ?, phone = ?, bio = ?,
                     cv_path = ?, avatar_path = ?,
                     skills = ?, education = ?, location = ?
                 WHERE user_id = ?'
            )->execute([
                $fullname, $phone, $bio,
                $cvPath, $avatarPath,
                $skills ?: null, $education ?: null, $jspLocation ?: null,
                $_SESSION['user_id'],
            ]);

            setFlash('success', 'Profile updated successfully.');
            header('Location: /website/jobseeker/profile.php');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'Failed to save profile.';
        }
    }
} else {
    $fullname    = $user['fullname']          ?? '';
    $phone       = $user['phone']             ?? '';
    $bio         = $user['bio']               ?? '';
    $skills      = $user['skills']            ?? '';
    $education   = $user['education']         ?? '';
    $jspLocation = $user['profile_location']  ?? '';
}

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <h4 class="mb-4">
      <i class="bi bi-person-circle me-2 text-primary"></i>My Profile
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

    <?php
    // Warn if last_update is older than 4 years (approaching the 5-year inactivity threshold)
    $lastUpdate = $user['last_update'] ?? null;
    if ($lastUpdate) {
        $daysSinceUpdate = (int)floor((time() - strtotime($lastUpdate)) / 86400);
        if ($daysSinceUpdate >= 1461) { // 4 years
            echo '<div class="alert alert-warning">'
               . '<i class="bi bi-exclamation-triangle me-1"></i>'
               . '<strong>Warning:</strong> Your profile has not been updated in over 4 years. '
               . 'Profile will be deactivated after 5 years without an update.'
               . '</div>';
        }
    }
    ?>

    <div class="card shadow-sm">
      <div class="card-body p-4">
        <form method="post" enctype="multipart/form-data" novalidate>

          <!-- Avatar -->
          <div class="mb-4 text-center">
            <?php if ($user['avatar_path']): ?>
              <img src="/website/<?= htmlspecialchars($user['avatar_path']) ?>"
                   class="rounded-circle mb-2" width="90" height="90"
                   style="object-fit:cover;" alt="avatar">
            <?php else: ?>
              <div class="avatar-placeholder mx-auto mb-2">
                <i class="bi bi-person fs-1"></i>
              </div>
            <?php endif; ?>
            <div>
              <label class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-camera me-1"></i>Change Photo
                <input type="file" name="avatar" accept="image/*" class="d-none">
              </label>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="fullname" class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="fullname" name="fullname"
                     value="<?= htmlspecialchars($fullname) ?>" required>
            </div>
            <div class="col-md-6">
              <label for="phone" class="form-label">Phone</label>
              <input type="text" class="form-control" id="phone" name="phone"
                     value="<?= htmlspecialchars($phone) ?>">
            </div>
          </div>

          <div class="mb-3">
            <label for="bio" class="form-label">Bio / Summary</label>
            <textarea class="form-control" id="bio" name="bio"
                      rows="4" placeholder="Tell employers about yourself…"
                      ><?= htmlspecialchars($bio) ?></textarea>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="skills" class="form-label">Skills
                <small class="text-muted">(comma-separated)</small>
              </label>
              <input type="text" class="form-control" id="skills" name="skills"
                     placeholder="e.g. PHP, MySQL, JavaScript"
                     value="<?= htmlspecialchars($skills) ?>">
            </div>
            <div class="col-md-6">
              <label for="education" class="form-label">Education</label>
              <input type="text" class="form-control" id="education" name="education"
                     placeholder="e.g. Ho Chi Minh City University of Technology"
                     value="<?= htmlspecialchars($education) ?>">
            </div>
          </div>

          <div class="mb-3">
            <label for="location" class="form-label">Preferred Work Location</label>
            <input type="text" class="form-control" id="location" name="location"
                   placeholder="e.g. Ho Chi Minh City, Hanoi"
                   value="<?= htmlspecialchars($jspLocation) ?>">
          </div>

          <?php if ($lastUpdate): ?>
            <div class="mb-3 p-3 bg-light rounded border">
              <small class="text-muted">
                <i class="bi bi-clock-history me-1"></i>
                Last updated:
                <strong><?= date('M j, Y H:i', strtotime($lastUpdate)) ?></strong>
                &nbsp;—&nbsp;Profile will be deactivated if not updated within 5 years.
              </small>
            </div>
          <?php endif; ?>

          <!-- CV Upload -->
          <div class="mb-4">
            <label class="form-label">CV / Resume (PDF, max 5 MB)</label>
            <?php if ($user['cv_path']): ?>
              <div class="mb-2">
                <a href="/website/<?= htmlspecialchars($user['cv_path']) ?>"
                   target="_blank" class="btn btn-outline-success btn-sm">
                  <i class="bi bi-file-earmark-pdf me-1"></i>View Current CV
                </a>
              </div>
            <?php endif; ?>
            <input type="file" name="cv" accept=".pdf,application/pdf"
                   class="form-control">
            <div class="form-text">Upload a new PDF to replace your current CV.</div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-1"></i>Save Changes
            </button>
            <a href="/website/jobseeker/dashboard.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
