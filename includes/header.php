<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/auth.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pageTitle = $pageTitle ?? 'JobBoard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?> — JobBoard</title>
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="/website/assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/website/index.php">
      <i class="bi bi-briefcase-fill me-1"></i>JobBoard
    </a>
    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="/website/index.php">
            <i class="bi bi-house me-1"></i>Home
          </a>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if (!isLoggedIn()): ?>
          <li class="nav-item">
            <a class="nav-link" href="/website/login.php">
              <i class="bi bi-box-arrow-in-right me-1"></i>Login
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link btn btn-outline-light btn-sm px-3 ms-2"
               href="/website/register.php">Register</a>
          </li>
        <?php elseif (getCurrentRole() === 'employer'): ?>
          <li class="nav-item">
            <a class="nav-link" href="/website/employer/dashboard.php">
              <i class="bi bi-speedometer2 me-1"></i>Dashboard
            </a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#"
               data-bs-toggle="dropdown">
              <i class="bi bi-person-circle me-1"></i>Account
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/website/employer/profile.php">
                <i class="bi bi-building me-1"></i>Company Profile</a></li>
              <li><a class="dropdown-item" href="/website/employer/post_job.php">
                <i class="bi bi-plus-circle me-1"></i>Post a Job</a></li>
              <li><a class="dropdown-item" href="/website/employer/manage_posts.php">
                <i class="bi bi-list-ul me-1"></i>Manage Posts</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="/website/logout.php">
                <i class="bi bi-box-arrow-right me-1"></i>Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="/website/jobseeker/dashboard.php">
              <i class="bi bi-speedometer2 me-1"></i>Dashboard
            </a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#"
               data-bs-toggle="dropdown">
              <i class="bi bi-person-circle me-1"></i>Account
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/website/jobseeker/profile.php">
                <i class="bi bi-person me-1"></i>My Profile</a></li>
              <li><a class="dropdown-item" href="/website/jobseeker/my_applications.php">
                <i class="bi bi-file-earmark-text me-1"></i>My Applications</a></li>
              <li><a class="dropdown-item" href="/website/jobseeker/saved_jobs.php">
                <i class="bi bi-bookmark me-1"></i>Saved Jobs</a></li>
              <li><a class="dropdown-item" href="/website/jobseeker/followed_companies.php">
                <i class="bi bi-buildings me-1"></i>Followed Companies</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="/website/logout.php">
                <i class="bi bi-box-arrow-right me-1"></i>Logout</a></li>
            </ul>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Flash message -->
<?php if ($flash): ?>
<div class="container mt-3">
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($flash['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php endif; ?>

<main class="container my-4">
