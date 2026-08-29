<?php
// lesson.php - Enterprise LMS Interactive Lesson Player
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$lesson_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];
$db = getDBConnection();

// Fetch current lesson & module & course
$stmt = $db->prepare("
    SELECT l.*, m.title AS module_title, m.course_id, c.title AS course_title
    FROM lessons l
    JOIN course_modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE l.id = ?
");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();

if (!$lesson) {
    redirect('dashboard.php');
}

$course_id = $lesson['course_id'];

// Check enrollment & payment approval status
if (!is_enrolled($user_id, $course_id)) {
    redirect('course-details.php?id=' . $course_id);
}

// Lock access if payment is pending administrator confirmation
if (!is_enrollment_approved($user_id, $course_id)) {
    $page_title = "Access Pending Approval";
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="py-section">
        <div class="container" style="max-width: 600px;">
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 40px; text-align: center; box-shadow: var(--shadow-md);">
                <div style="width: 70px; height: 70px; border-radius: 50%; background: #fffbe0; color: #d97706; font-size: 2rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; border: 1px solid #fef3c7;">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <h1 style="font-size: 1.75rem; margin-bottom: 12px; color: var(--primary-dark);">Payment Pending Approval</h1>
                <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.6; margin-bottom: 30px;">
                    Your enrollment in <strong>"<?php echo htmlspecialchars($lesson['course_title']); ?>"</strong> has been submitted and is currently pending administrator verification.
                </p>
                <div style="background: #f8fafc; border: 1px solid var(--border-color); padding: 16px; border-radius: var(--radius-sm); font-size: 0.9rem; color: var(--text-muted); margin-bottom: 30px; text-align: left;">
                    <i class="fa-solid fa-circle-info" style="color: var(--primary-blue); margin-right: 6px;"></i>
                    Once an administrator confirms your payment, your lesson videos, code sandboxes, and quiz assessments will be unlocked automatically.
                </div>
                <a href="dashboard.php" class="btn btn-primary"><i class="fa-solid fa-house"></i> Return to Student Dashboard</a>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Check if current lesson is completed by user
$stmtComp = $db->prepare("SELECT is_completed FROM lesson_progress WHERE user_id = ? AND lesson_id = ?");
$stmtComp->execute([$user_id, $lesson_id]);
$isCompleted = (bool)$stmtComp->fetchColumn();

// Fetch all modules & lessons for sidebar navigation
$stmtMod = $db->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY sort_order ASC");
$stmtMod->execute([$course_id]);
$modules = $stmtMod->fetchAll();

$allLessonsOrdered = [];
foreach ($modules as &$m) {
    $stmtL = $db->prepare("
        SELECT l.*, 
        (SELECT is_completed FROM lesson_progress WHERE user_id = ? AND lesson_id = l.id) AS is_completed
        FROM lessons l 
        WHERE l.module_id = ? 
        ORDER BY l.sort_order ASC
    ");
    $stmtL->execute([$user_id, $m['id']]);
    $m['lessons'] = $stmtL->fetchAll();
    foreach ($m['lessons'] as $les) {
        $allLessonsOrdered[] = $les;
    }
}
unset($m);

// Find Previous & Next Lesson IDs
$prevLessonId = null;
$nextLessonId = null;
for ($i = 0; $i < count($allLessonsOrdered); $i++) {
    if ($allLessonsOrdered[$i]['id'] == $lesson_id) {
        if ($i > 0) $prevLessonId = $allLessonsOrdered[$i - 1]['id'];
        if ($i < count($allLessonsOrdered) - 1) $nextLessonId = $allLessonsOrdered[$i + 1]['id'];
        break;
    }
}

$videoInfo = get_embed_video_info($lesson['video_url']);
$page_title = $lesson['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="lMS-player">
    <!-- Sidebar Navigation -->
    <aside class="lMS-sidebar">
        <div style="margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            <a href="course-details.php?id=<?php echo $course_id; ?>" style="font-size: 0.85rem; color: var(--text-muted);">&larr; Back to Course Overview</a>
            <h3 style="font-size: 1.1rem; margin-top: 6px;"><?php echo htmlspecialchars($lesson['course_title']); ?></h3>
        </div>

        <?php foreach ($modules as $mod): ?>
            <div class="module-title"><?php echo htmlspecialchars($mod['title']); ?></div>
            <ul class="lesson-list">
                <?php foreach ($mod['lessons'] as $les): ?>
                    <li>
                        <a href="lesson.php?id=<?php echo $les['id']; ?>" class="lesson-item <?php echo $les['id'] == $lesson_id ? 'active' : ''; ?>">
                            <i class="<?php echo $les['is_completed'] ? 'fa-solid fa-circle-check' : 'fa-regular fa-circle-play'; ?>" style="<?php echo $les['is_completed'] ? 'color:var(--accent-emerald);' : ''; ?>"></i>
                            <span style="flex:1;"><?php echo htmlspecialchars($les['title']); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </aside>

    <!-- Main Lesson Reader -->
    <main class="lesson-main">
        <div class="lesson-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
                <div>
                    <span style="font-size: 0.85rem; color: var(--primary-blue); font-weight: 600; text-transform: uppercase;"><?php echo htmlspecialchars($lesson['module_title']); ?></span>
                    <h1 style="font-size: 2rem; margin-top: 4px;"><?php echo htmlspecialchars($lesson['title']); ?></h1>
                </div>
                <button id="markLessonCompleteBtn" data-lesson-id="<?php echo $lesson['id']; ?>" data-course-id="<?php echo $course_id; ?>" class="btn <?php echo $isCompleted ? 'btn-accent' : 'btn-primary'; ?>">
                    <i class="<?php echo $isCompleted ? 'fa-solid fa-circle-check' : 'fa-regular fa-square-check'; ?>"></i>
                    <?php echo $isCompleted ? 'Completed' : 'Mark Complete'; ?>
                </button>
            </div>

            <!-- Video Player Component -->
            <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; border-radius: var(--radius-md); margin-bottom: 30px; background: #000; box-shadow: var(--shadow-md);">
                <?php if ($videoInfo['type'] === 'iframe'): ?>
                    <iframe src="<?php echo htmlspecialchars($videoInfo['url']); ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                <?php else: ?>
                    <video controls preload="auto" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                        <source src="<?php echo htmlspecialchars($videoInfo['url']); ?>" type="video/mp4">
                        Your browser does not support video playback.
                    </video>
                <?php endif; ?>
            </div>

            <div style="font-size: 1.05rem; line-height: 1.8; color: var(--text-main);">
                <?php echo $lesson['content']; ?>
            </div>
        </div>

        <!-- Previous & Next Navigation -->
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <?php if ($prevLessonId): ?>
                <a href="lesson.php?id=<?php echo $prevLessonId; ?>" class="btn btn-outline">&larr; Previous Lesson</a>
            <?php else: ?>
                <div></div>
            <?php endif; ?>

            <?php if ($nextLessonId): ?>
                <a href="lesson.php?id=<?php echo $nextLessonId; ?>" class="btn btn-primary">Next Lesson &rarr;</a>
            <?php else: ?>
                <a href="quiz.php?course_id=<?php echo $course_id; ?>" class="btn btn-accent"><i class="fa-solid fa-file-pen"></i> Take Final Course Quiz &rarr;</a>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
