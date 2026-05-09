<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

/** Returns true if a user is logged in. */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

/** Returns the current user's role or null. */
function getCurrentRole(): ?string
{
    return $_SESSION['role'] ?? null;
}

/**
 * Returns full user row from DB including profile columns.
 * Employer extras : no_employees, business_registration_no
 * Jobseeker extras: skills, education, profile_location, profile_status, last_update
 * User extras     : status, last_login
 */
function getCurrentUser(): ?array
{
    if (!isLoggedIn()) return null;

    $pdo  = getDB();
    $role = $_SESSION['role'];

    if ($role === 'employer') {
        $stmt = $pdo->prepare(
            'SELECT u.*, ep.id AS profile_id, ep.company_name, ep.description,
                    ep.logo_path, ep.website, ep.address,
                    ep.no_employees, ep.business_registration_no
             FROM users u
             LEFT JOIN employer_profiles ep ON ep.user_id = u.id
             WHERE u.id = ?'
        );
    } else {
        $stmt = $pdo->prepare(
            'SELECT u.*, jsp.id AS profile_id, jsp.fullname, jsp.phone,
                    jsp.bio, jsp.cv_path, jsp.avatar_path,
                    jsp.skills, jsp.education,
                    jsp.location  AS profile_location,
                    jsp.status    AS profile_status,
                    jsp.last_update
             FROM users u
             LEFT JOIN jobseeker_profiles jsp ON jsp.user_id = u.id
             WHERE u.id = ?'
        );
    }

    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * Records a successful login: sets last_login = NOW() and checks account status.
 * Returns null on success, or an error message string if account is inactive.
 * Call this immediately after password_verify() succeeds, before setting session vars.
 */
function recordLogin(int $userId, PDO $pdo): ?string
{
    try {
        $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')
            ->execute([$userId]);

        $stmt = $pdo->prepare('SELECT status FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if ($row && $row['status'] === 'inactive') {
            return 'Your account is inactive. Please contact support.';
        }
    } catch (PDOException $e) {
        error_log('recordLogin error: ' . $e->getMessage());
        // Do not block login on transient DB error
    }
    return null;
}

/**
 * Redirects to login if not logged in or wrong role.
 * Pass null to require only authentication regardless of role.
 */
function requireRole(?string $role = null): void
{
    if (!isLoggedIn()) {
        $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Please log in to continue.'];
        header('Location: /website/login.php');
        exit;
    }
    if ($role !== null && getCurrentRole() !== $role) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Access denied.'];
        $redirect = (getCurrentRole() === 'employer')
            ? '/website/employer/dashboard.php'
            : '/website/jobseeker/dashboard.php';
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Sets a flash message to be displayed on the next page load.
 */
function setFlash(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
