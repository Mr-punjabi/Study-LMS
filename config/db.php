<?php
// config/db.php - Database Configuration & System Initialization

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'studylms_db');
define('SITE_NAME', 'Study Point Academy');
define('BASE_URL', '/studylms/');

function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Attempt auto-creation if database doesn't exist yet
            try {
                $rootDsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
                $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS);
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $ex) {
                die("<div style='font-family:sans-serif; padding:20px; background:#fee2e2; border:1px solid #ef4444; border-radius:8px; margin:20px;'>
                        <h3 style='color:#991b1b; margin-top:0;'>Database Connection Error</h3>
                        <p style='color:#7f1d1d;'>Could not connect to MySQL server. Please ensure MySQL is running in XAMPP Control Panel.</p>
                        <small style='color:#b91c1c;'>Error details: " . htmlspecialchars($ex->getMessage()) . "</small>
                    </div>");
            }
        }
    }
    return $pdo;
}
