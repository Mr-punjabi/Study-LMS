<?php
// course-details.php - Dedicated Course Landing Page & Enrollment
require_once __DIR__ . '/includes/functions.php';

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = getDBConnection();

// Fetch Course Details
$stmt = $db->prepare("
    SELECT c.*, cat.name AS category_name,
    (SELECT COUNT(l.id) FROM lessons l JOIN course_modules m ON l.module_id = m.id WHERE m.course_id = c.id) AS lesson_count
    FROM courses c 
    JOIN categories cat ON c.category_id = cat.id 
    WHERE c.id = ? AND c.status = 'published'
");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    redirect('courses.php');
}

$page_title = $course['title'];

// Fetch Modules & Lessons
$stmtMod = $db->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY sort_order ASC");
$stmtMod->execute([$course_id]);
$modules = $stmtMod->fetchAll();

foreach ($modules as &$mod) {
    $stmtLes = $db->prepare("SELECT * FROM lessons WHERE module_id = ? ORDER BY sort_order ASC");
    $stmtLes->execute([$mod['id']]);
    $mod['lessons'] = $stmtLes->fetchAll();
}
unset($mod);

// Enrollment & Purchase Handler
$user_id = is_logged_in() ? $_SESSION['user_id'] : null;
$enrolled = is_enrolled($user_id, $course_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enroll') {
    if (!is_logged_in()) {
        if ($course['price'] > 0) {
            redirect('login.php?redirect=checkout.php?course_id=' . $course_id);
        } else {
            redirect('login.php?redirect=course-details.php?id=' . $course_id);
        }
    }
    
    if (!$enrolled) {
        // If course is paid, redirect to checkout.php
        if ($course['price'] > 0) {
            redirect('checkout.php?course_id=' . $course_id);
        }

        // If course is free, enroll directly
        $stmtEnroll = $db->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
        $stmtEnroll->execute([$user_id, $course_id]);
        $enrolled = true;
        set_flash('success', 'Congratulations! You have enrolled in ' . htmlspecialchars($course['title']));
    }
    
    // Redirect to first lesson if enrolled
    $firstLessonId = 0;
    if (!empty($modules) && !empty($modules[0]['lessons'])) {
        $firstLessonId = $modules[0]['lessons'][0]['id'];
        redirect('lesson.php?id=' . $firstLessonId);
    } else {
        redirect('dashboard.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<div style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); color: #ffffff; padding: 60px 0;">
    <div class="container">
        <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 16px; font-size: 0.9rem; color: #94a3b8;">
            <a href="courses.php" style="color: #94a3b8;">Courses</a> &rarr; 
            <span><?php echo htmlspecialchars($course['category_name']); ?></span>
        </div>
        <h1 style="color: #ffffff; font-size: 2.5rem; margin-bottom: 16px;"><?php echo htmlspecialchars($course['title']); ?></h1>
        <p style="font-size: 1.15rem; color: #cbd5e1; max-width: 800px; margin-bottom: 24px;"><?php echo htmlspecialchars($course['short_description']); ?></p>
        <div style="display: flex; gap: 24px; font-size: 0.9rem; color: #e2e8f0; flex-wrap: wrap;">
            <span><i class="fa-solid fa-user-tie" style="color:var(--accent-cyan);"></i> Instructor: <?php echo htmlspecialchars($course['instructor_name'] ?? 'Sarah Connor'); ?></span>
            <span><i class="fa-solid fa-star" style="color:#f59e0b;"></i> Rating: <?php echo number_format($course['rating'] ?? 4.9, 1); ?></span>
            <span><i class="fa-solid fa-layer-group" style="color:var(--accent-cyan);"></i> Level: <?php echo htmlspecialchars($course['level']); ?></span>
            <span><i class="fa-regular fa-clock" style="color:var(--accent-cyan);"></i> Duration: <?php echo format_duration($course['duration_minutes']); ?></span>
            <span><i class="fa-solid fa-book-open" style="color:var(--accent-cyan);"></i> <?php echo $course['lesson_count']; ?> Lessons</span>
        </div>
    </div>
</div>

<div class="py-section">
    <div class="container">
        <?php render_flash('success'); ?>
        <div style="display: grid; grid-template-columns: 1fr 360px; gap: 40px;">
            <!-- Left Column: Curriculum & Description -->
            <div>
                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 30px; margin-bottom: 30px; box-shadow: var(--shadow-sm);">
                    <h2 style="font-size: 1.5rem; margin-bottom: 16px;">About This Course</h2>
                    <div style="color: var(--text-main); font-size: 1rem; line-height: 1.7;">
                        <?php echo $course['description']; ?>
                    </div>
                </div>

                <!-- Curriculum Accordion -->
                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 30px; box-shadow: var(--shadow-sm);">
                    <h2 style="font-size: 1.5rem; margin-bottom: 20px;"><i class="fa-solid fa-list-ol"></i> Course Curriculum</h2>
                    
                    <?php if (empty($modules)): ?>
                        <p style="color: var(--text-muted);">Curriculum is currently being updated for this course.</p>
                    <?php else: ?>
                        <?php foreach ($modules as $mod): ?>
                            <div style="margin-bottom: 20px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); overflow: hidden;">
                                <div style="background: #f8fafc; padding: 14px 20px; font-weight: 700; border-bottom: 1px solid var(--border-color);">
                                    <?php echo htmlspecialchars($mod['title']); ?>
                                </div>
                                <ul style="list-style: none;">
                                    <?php foreach ($mod['lessons'] as $les): ?>
                                        <li style="padding: 12px 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                                            <span>
                                                <i class="fa-regular fa-circle-play" style="color: var(--primary-blue); margin-right: 10px;"></i>
                                                <?php echo htmlspecialchars($les['title']); ?>
                                            </span>
                                            <span style="font-size: 0.85rem; color: var(--text-muted);"><?php echo format_duration($les['duration_minutes']); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Enrollment Card -->
            <div>
                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 30px; position: sticky; top: 90px; box-shadow: var(--shadow-md);">
                    <div style="font-size: 2rem; font-weight: 800; color: <?php echo $course['price'] > 0 ? 'var(--primary-blue)' : 'var(--accent-emerald)'; ?>; margin-bottom: 20px;">
                        <?php echo $course['price'] > 0 ? '$' . number_format($course['price'], 2) : 'FREE'; ?>
                    </div>
                    
                    <form action="course-details.php?id=<?php echo $course['id']; ?>" method="POST">
                        <input type="hidden" name="action" value="enroll">
                        <?php if ($enrolled): ?>
                            <button type="submit" class="btn btn-accent" style="width: 100%; padding: 14px; font-size: 1.05rem;"><i class="fa-solid fa-play"></i> Continue Learning</button>
                        <?php else: ?>
                            <?php if ($course['price'] > 0): ?>
                                <a href="checkout.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1.05rem; display:block; text-align:center;"><i class="fa-solid fa-cart-shopping"></i> Buy Course & Enroll</a>
                            <?php else: ?>
                                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1.05rem;"><i class="fa-solid fa-bolt"></i> Enroll for Free</button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </form>

                    <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color); font-size: 0.9rem; color: var(--text-muted);">
                        <div style="margin-bottom: 10px;"><i class="fa-solid fa-check" style="color:var(--accent-emerald); margin-right:8px;"></i> Full lifetime access</div>
                        <div style="margin-bottom: 10px;"><i class="fa-solid fa-check" style="color:var(--accent-emerald); margin-right:8px;"></i> Self-paced interactive lessons</div>
                        <div style="margin-bottom: 10px;"><i class="fa-solid fa-check" style="color:var(--accent-emerald); margin-right:8px;"></i> Verified Certificate on completion</div>
                        <div><i class="fa-solid fa-check" style="color:var(--accent-emerald); margin-right:8px;"></i> Access on mobile and desktop</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
