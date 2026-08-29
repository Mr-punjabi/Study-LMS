<?php
// api/complete_lesson.php - AJAX Handler for Lesson Completion
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$lesson_id = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;
$course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;

if (!$lesson_id || !$course_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$db = getDBConnection();

try {
    // Record or update lesson completion
    $stmt = $db->prepare("
        INSERT INTO lesson_progress (user_id, lesson_id, is_completed, completed_at)
        VALUES (?, ?, 1, NOW())
        ON DUPLICATE KEY UPDATE is_completed = 1, completed_at = NOW()
    ");
    $stmt->execute([$user_id, $lesson_id]);

    // Calculate new overall course progress
    $progress = get_course_progress($user_id, $course_id);

    // If 100% complete, issue certificate automatically if not issued already
    if ($progress >= 100) {
        $stmtCert = $db->prepare("SELECT id FROM certificates WHERE user_id = ? AND course_id = ?");
        $stmtCert->execute([$user_id, $course_id]);
        if (!$stmtCert->fetch()) {
            $certCode = 'SPA-' . strtoupper(substr(md5(uniqid($user_id . $course_id, true)), 0, 10));
            $issueStmt = $db->prepare("INSERT INTO certificates (user_id, course_id, certificate_code) VALUES (?, ?, ?)");
            $issueStmt->execute([$user_id, $course_id, $certCode]);
        }
    }

    echo json_encode(['success' => true, 'progress' => $progress]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
