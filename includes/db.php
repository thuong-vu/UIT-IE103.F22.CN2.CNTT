<?php
/**
 * PDO database connection.
 * Update the four constants below to match your environment.
 */

// NOTE: Run this once in MySQL before using scheduled events:
//   SET GLOBAL event_scheduler = ON;
define('DB_HOST', 'localhost');
define('DB_NAME', 'recruitment_db');
define('DB_USER', 'root');
define('DB_PASS', '');      // XAMPP default: empty string
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never expose credentials in production
            error_log('DB connection failed: ' . $e->getMessage());
            die('<div class="alert alert-danger m-4">Database connection failed. Please try again later.</div>');
        }
    }
    return $pdo;
}
