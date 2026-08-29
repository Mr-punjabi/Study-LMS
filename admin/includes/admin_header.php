<?php
// admin/includes/admin_header.php - Enterprise CMS Admin Header
require_once __DIR__ . '/../../includes/functions.php';

if (!is_admin()) {
    set_flash('error', 'Access denied. Administrator privileges required.');
    redirect('login.php');
}

$current_user = get_current_user_data();

// Count pending payments for badge
$db = getDBConnection();
$pendingPaymentCount = 0;
try {
    $pendingPaymentCount = $db->query("SELECT COUNT(id) FROM enrollments WHERE payment_status = 'pending'")->fetchColumn();
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Admin' : 'Admin Portal - Study Point Academy'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body style="background:#f1f5f9;">
    <header class="site-header" style="background:#0f172a;">
        <div class="container">
            <nav class="navbar">
                <a href="<?php echo BASE_URL; ?>admin/index.php" class="brand-logo">
                    <i class="fa-solid fa-gauge-high" style="color:var(--accent-cyan);"></i>
                    <span>ADMIN CMS <span style="font-weight:400; opacity:0.8;">PORTAL</span></span>
                </a>

                <ul class="nav-menu">
                    <li><a href="<?php echo BASE_URL; ?>admin/index.php" class="nav-link">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>admin/courses.php" class="nav-link">Courses</a></li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/enrollments.php" class="nav-link" style="position:relative;">
                            Payment Approvals
                            <?php if ($pendingPaymentCount > 0): ?>
                                <span style="background:#ef4444; color:#fff; font-size:0.7rem; font-weight:800; padding:2px 6px; border-radius:10px; margin-left:4px;"><?php echo $pendingPaymentCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>admin/quizzes.php" class="nav-link">Quizzes</a></li>
                    <li><a href="<?php echo BASE_URL; ?>admin/users.php" class="nav-link">Users</a></li>
                </ul>

                <div class="nav-actions">
                    <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-sm btn-outline" style="color:#fff; border-color:rgba(255,255,255,0.3);"><i class="fa-solid fa-globe"></i> View Site</a>
                    <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-sm btn-secondary"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            </nav>
        </div>
    </header>
    <main class="main-content">
