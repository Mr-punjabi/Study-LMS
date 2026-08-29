<?php
// includes/header.php - Global Enterprise Header
require_once __DIR__ . '/functions.php';
$current_user = get_current_user_data();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' . SITE_NAME : SITE_NAME . ' | Enterprise LMS'; ?></title>
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Enterprise CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <nav class="navbar">
                <a href="<?php echo BASE_URL; ?>" class="brand-logo">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span>STUDY POINT <span style="font-weight:400; opacity:0.8;">ACADEMY</span></span>
                    <span class="brand-badge">Enterprise</span>
                </a>

                <ul class="nav-menu">
                    <li><a href="<?php echo BASE_URL; ?>" class="nav-link">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>courses.php" class="nav-link">Courses</a></li>
                    <li><a href="<?php echo BASE_URL; ?>tutorials.php" class="nav-link">Tutorials</a></li>
                    <li><a href="<?php echo BASE_URL; ?>notes.php" class="nav-link">Notes</a></li>
                    <li><a href="<?php echo BASE_URL; ?>practice.php" class="nav-link">Practice</a></li>
                    <li><a href="<?php echo BASE_URL; ?>blog.php" class="nav-link">Blog</a></li>
                    <li><a href="<?php echo BASE_URL; ?>about.php" class="nav-link">About</a></li>
                </ul>

                <div class="nav-actions">
                    <?php if (is_logged_in()): ?>
                        <?php if (is_admin()): ?>
                            <a href="<?php echo BASE_URL; ?>admin/index.php" class="btn btn-sm btn-secondary"><i class="fa-solid fa-gauge-high"></i> Admin Portal</a>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-sm btn-primary"><i class="fa-solid fa-user-gear"></i> Dashboard</a>
                        <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-sm btn-outline" style="color:#ffffff; border-color:rgba(255,255,255,0.3);"><i class="fa-solid fa-right-from-bracket"></i></a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-sm btn-secondary">Login</a>
                        <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-sm btn-primary"><i class="fa-solid fa-rocket"></i> Start Learning</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    <main class="main-content">
