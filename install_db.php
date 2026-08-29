<?php
// install_db.php - Automatic Database Initializer & Reset
require_once __DIR__ . '/config/db.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop and recreate clean database
    $pdo->exec("DROP DATABASE IF EXISTS `" . DB_NAME . "`;");
    $pdo->exec("CREATE DATABASE `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `" . DB_NAME . "`;");
    
    // Read and execute schema.sql
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    $pdo->exec($sql);

    // Compute fresh valid password hash for password123
    $passHash = password_hash('password123', PASSWORD_DEFAULT);
    
    $stmtPass = $pdo->prepare("UPDATE users SET password = ? WHERE email IN ('admin@studypoint.com', 'alex@studypoint.com', 'sarah@studypoint.com')");
    $stmtPass->execute([$passHash]);
    
    echo "<div style='font-family:sans-serif; max-width:600px; margin:50px auto; padding:25px; border-radius:10px; background:#ecfdf5; border:1px solid #10b981; color:#065f46;'>
            <h2 style='margin-top:0;'>Study Point Academy LMS Setup Complete!</h2>
            <p>Database <strong>" . DB_NAME . "</strong> has been created and populated successfully with seed data, courses, lessons, quizzes, and verified user accounts.</p>
            <ul>
                <li><strong>Admin Account:</strong> <code>admin@studypoint.com</code> | Password: <code>password123</code></li>
                <li><strong>Student Account:</strong> <code>alex@studypoint.com</code> | Password: <code>password123</code></li>
            </ul>
            <p><a href='index.php' style='display:inline-block; padding:10px 20px; background:#059669; color:#fff; text-decoration:none; border-radius:5px; font-weight:bold;'>Go to Academy Homepage &rarr;</a></p>
          </div>";
} catch (Exception $e) {
    echo "<div style='font-family:sans-serif; max-width:600px; margin:50px auto; padding:25px; border-radius:10px; background:#fef2f2; border:1px solid #f87171; color:#991b1b;'>
            <h2 style='margin-top:0;'>Database Setup Failed</h2>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
            <p>Please make sure MySQL service is running in XAMPP.</p>
          </div>";
}
