<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: /website/index.php');
    exit;
}

$errors = [];
$old    = ['email' => '', 'role' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']   ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');
    $role     = trim($_POST['role']     ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (!in_array($role, ['employer', 'jobseeker'], true)) {
        $errors[] = 'Please select a valid role.';
    }

    if (empty($errors)) {
        try {
            $pdo = getDB();

            // Check duplicate email
            $chk = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $chk->execute([$email]);
            if ($chk->fetch()) {
                $errors[] = 'An account with that email already exists.';
            } else {
                $pdo->beginTransaction();

                $hash = password_hash($password, PASSWORD_BCRYPT);
                $ins  = $pdo->prepare(
                    'INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)'
                );
                $ins->execute([$email, $hash, $role]);
                $userId = (int)$pdo->lastInsertId();

                // Create empty profile row
                if ($role === 'employer') {
                    $pdo->prepare(
                        'INSERT INTO employer_profiles (user_id, company_name) VALUES (?, ?)'
                    )->execute([$userId, '']);
                } else {
                    $pdo->prepare(
                        'INSERT INTO jobseeker_profiles (user_id, fullname) VALUES (?, ?)'
                    )->execute([$userId, '']);
                }

                $pdo->commit();

                setFlash('success', 'Account created! Please log in.');
                header('Location: /website/login.php');
                exit;
            }
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            error_log($e->getMessage());
            $errors[] = 'Registration failed. Please try again.';
        }
    }

    $old = ['email' => htmlspecialchars($email), 'role' => $role];
}

$pageTitle = 'Register';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h3 class="card-title mb-4 text-center">
          <i class="bi bi-person-plus me-2 text-primary"></i>Create Account
        </h3>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" novalidate>
          <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?= $old['email'] ?>" required autofocus>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password
              <small class="text-muted">(min 8 chars)</small></label>
            <input type="password" class="form-control" id="password"
                   name="password" required>
          </div>

          <div class="mb-3">
            <label for="confirm" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="confirm"
                   name="confirm" required>
          </div>

          <div class="mb-4">
            <label class="form-label">I am a…</label>
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="role"
                       id="roleJobseeker" value="jobseeker"
                       <?= $old['role'] === 'jobseeker' ? 'checked' : '' ?> required>
                <label class="form-check-label" for="roleJobseeker">
                  <i class="bi bi-person me-1"></i>Job Seeker
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="role"
                       id="roleEmployer" value="employer"
                       <?= $old['role'] === 'employer' ? 'checked' : '' ?>>
                <label class="form-check-label" for="roleEmployer">
                  <i class="bi bi-building me-1"></i>Employer
                </label>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-check-circle me-1"></i>Register
          </button>
        </form>

        <p class="text-center mt-3 mb-0">
          Already have an account?
          <a href="/website/login.php">Log in</a>
        </p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
