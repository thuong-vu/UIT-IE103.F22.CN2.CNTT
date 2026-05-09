<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    $dest = getCurrentRole() === 'employer'
        ? '/website/employer/dashboard.php'
        : '/website/jobseeker/dashboard.php';
    header('Location: ' . $dest);
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } else {
        try {
            $pdo  = getDB();
            $stmt = $pdo->prepare('SELECT id, password_hash, role FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Update last_login and enforce status check
                $loginErr = recordLogin((int)$user['id'], $pdo);
                if ($loginErr !== null) {
                    $error = $loginErr;
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['role']    = $user['role'];

                    setFlash('success', 'Welcome back!');
                    $dest = $user['role'] === 'employer'
                        ? '/website/employer/dashboard.php'
                        : '/website/jobseeker/dashboard.php';
                    header('Location: ' . $dest);
                    exit;
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Login failed. Please try again.';
        }
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center" style="padding-top: 80px;">
  <div class="col-md-5 col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h3 class="card-title mb-4 text-center">
          <i class="bi bi-box-arrow-in-right me-2 text-primary"></i>Sign In
        </h3>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
          <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email"
                    value="<?= htmlspecialchars($email) ?>" required autofocus>
          </div>
          <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password"
                    name="password" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-box-arrow-in-right me-1"></i>Login
          </button>
        </form>

        <p class="text-center mt-3 mb-0">
          Don't have an account?
          <a href="/website/register.php">Register</a>
        </p>
      </div>
    </div>
  </div>
</div>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
