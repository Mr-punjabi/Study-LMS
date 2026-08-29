<?php
// includes/functions.php - Helper utility functions

require_once __DIR__ . '/../config/db.php';

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header("Location: " . BASE_URL . ltrim($path, '/'));
    exit;
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function get_current_user_data() {
    if (!is_logged_in()) return null;
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id, name, email, role, avatar, bio, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function set_flash($key, $message, $type = 'success') {
    $_SESSION['flash'][$key] = [
        'message' => $message,
        'type' => $type
    ];
}

function get_flash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function render_flash($key) {
    $flash = get_flash($key);
    if ($flash) {
        $typeClass = $flash['type'] === 'error' ? 'alert-danger' : 'alert-success';
        echo '<div class="alert ' . $typeClass . ' alert-dismissible fade show" role="alert">' . 
             htmlspecialchars($flash['message']) . 
             '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
}

function format_duration($minutes) {
    if ($minutes < 60) {
        return $minutes . " mins";
    }
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return $hours . "h " . ($mins > 0 ? $mins . "m" : "");
}

function get_course_progress($user_id, $course_id) {
    $db = getDBConnection();
    $stmt = $db->prepare("
        SELECT COUNT(l.id) AS total_lessons
        FROM lessons l
        JOIN course_modules m ON l.module_id = m.id
        WHERE m.course_id = ?
    ");
    $stmt->execute([$course_id]);
    $total = $stmt->fetchColumn();

    if ($total == 0) return 0;

    $stmt = $db->prepare("
        SELECT COUNT(lp.lesson_id) AS completed_lessons
        FROM lesson_progress lp
        JOIN lessons l ON lp.lesson_id = l.id
        JOIN course_modules m ON l.module_id = m.id
        WHERE lp.user_id = ? AND m.course_id = ? AND lp.is_completed = 1
    ");
    $stmt->execute([$user_id, $course_id]);
    $completed = $stmt->fetchColumn();

    return round(($completed / $total) * 100);
}

function is_enrolled($user_id, $course_id) {
    if (!$user_id) return false;
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    return $stmt->fetch() ? true : false;
}

function get_enrollment_status($user_id, $course_id) {
    if (!$user_id) return false;
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT payment_status FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    $status = $stmt->fetchColumn();
    return $status !== false ? $status : false;
}

function is_enrollment_approved($user_id, $course_id) {
    return get_enrollment_status($user_id, $course_id) === 'approved';
}

function get_embed_video_info($url) {
    if (empty($url)) {
        return [
            'type' => 'mp4',
            'url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4'
        ];
    }

    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $url, $matches)) {
        return [
            'type' => 'iframe',
            'url' => 'https://www.youtube.com/embed/' . $matches[1] . '?autoplay=0&rel=0'
        ];
    }

    return [
        'type' => 'mp4',
        'url' => $url
    ];
}
