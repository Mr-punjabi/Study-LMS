<?php
// admin/course-edit.php - Full Course, Module & Lesson Editor CMS
$page_title = "Edit Course";
require_once __DIR__ . '/includes/admin_header.php';

$db = getDBConnection();
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

// Fetch Course
$stmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    redirect('admin/courses.php');
}

// Handle Update Course Details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {
    $category_id = (int)$_POST['category_id'];
    $title = sanitize($_POST['title']);
    $instructor = sanitize($_POST['instructor_name']);
    $rating = (float)$_POST['rating'];
    $price = (float)$_POST['price'];
    $short_desc = sanitize($_POST['short_description']);
    $description = $_POST['description'];
    $level = sanitize($_POST['level']);
    $duration = (int)$_POST['duration_minutes'];

    $stmtUpd = $db->prepare("
        UPDATE courses 
        SET category_id = ?, title = ?, instructor_name = ?, rating = ?, price = ?, short_description = ?, description = ?, level = ?, duration_minutes = ?
        WHERE id = ?
    ");
    $stmtUpd->execute([$category_id, $title, $instructor, $rating, $price, $short_desc, $description, $level, $duration, $course_id]);
    
    set_flash('success', 'Course details updated successfully!');
    redirect("admin/course-edit.php?id=$course_id");
}

// Handle Add New Module
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_module'])) {
    $module_title = sanitize($_POST['module_title']);
    $sort_order = (int)($_POST['sort_order'] ?? 1);

    if (!empty($module_title)) {
        $stmtAddM = $db->prepare("INSERT INTO course_modules (course_id, title, sort_order) VALUES (?, ?, ?)");
        $stmtAddM->execute([$course_id, $module_title, $sort_order]);
        set_flash('success', 'New course module created successfully!');
        redirect("admin/course-edit.php?id=$course_id");
    }
}

// Handle Add New Lesson
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_lesson'])) {
    $module_id = (int)$_POST['module_id'];
    $lesson_title = sanitize($_POST['lesson_title']);
    $video_url = sanitize($_POST['video_url']);
    $content = $_POST['lesson_content'];
    $duration = (int)$_POST['duration_minutes'];

    if (!empty($lesson_title) && $module_id > 0) {
        $stmtAddL = $db->prepare("
            INSERT INTO lessons (module_id, title, slug, content, video_url, duration_minutes, sort_order)
            VALUES (?, ?, 'lesson-new', ?, ?, ?, 1)
        ");
        $stmtAddL->execute([$module_id, $lesson_title, $content, $video_url, $duration]);
        set_flash('success', 'New lesson added to curriculum!');
        redirect("admin/course-edit.php?id=$course_id");
    }
}

// Handle Delete Module
if (isset($_GET['action']) && $_GET['action'] === 'delete_module' && isset($_GET['module_id'])) {
    $delModId = (int)$_GET['module_id'];
    $stmtDelM = $db->prepare("DELETE FROM course_modules WHERE id = ? AND course_id = ?");
    $stmtDelM->execute([$delModId, $course_id]);
    set_flash('success', 'Module deleted.');
    redirect("admin/course-edit.php?id=$course_id");
}

// Handle Delete Lesson
if (isset($_GET['action']) && $_GET['action'] === 'delete_lesson' && isset($_GET['lesson_id'])) {
    $delLesId = (int)$_GET['lesson_id'];
    $stmtDelL = $db->prepare("DELETE FROM lessons WHERE id = ?");
    $stmtDelL->execute([$delLesId]);
    set_flash('success', 'Lesson deleted.');
    redirect("admin/course-edit.php?id=$course_id");
}

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

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<div class="py-section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <a href="courses.php" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Back to Courses List</a>
                <h1 class="section-title" style="font-size: 1.75rem; margin-top: 4px;">Edit Course: <?php echo htmlspecialchars($course['title']); ?></h1>
            </div>
            <div style="display:flex; gap:10px;">
                <a href="quizzes.php?action=create&course_id=<?php echo $course['id']; ?>" class="btn btn-sm btn-accent">
                    <i class="fa-solid fa-file-pen"></i> Add Quiz to this Course
                </a>
                <a href="<?php echo BASE_URL; ?>course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-outline btn-sm" target="_blank">
                    <i class="fa-solid fa-eye"></i> Preview Landing Page
                </a>
            </div>
        </div>

        <?php render_flash('success'); ?>

        <div style="display: grid; grid-template-columns: 1fr 420px; gap: 30px;">
            <!-- Left Column: Course Settings Form -->
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 30px; box-shadow: var(--shadow-sm); height: fit-content;">
                <h2 style="font-size: 1.2rem; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">Course Settings</h2>
                
                <form action="course-edit.php?id=<?php echo $course_id; ?>" method="POST">
                    <input type="hidden" name="update_course" value="1">
                    
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Course Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($course['title']); ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Category</label>
                            <select name="category_id" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $course['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Instructor Name</label>
                            <input type="text" name="instructor_name" value="<?php echo htmlspecialchars($course['instructor_name'] ?? 'Sarah Connor'); ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Price ($)</label>
                            <input type="number" step="0.01" name="price" value="<?php echo $course['price']; ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Rating</label>
                            <input type="number" step="0.1" name="rating" value="<?php echo $course['rating']; ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Level</label>
                            <select name="level" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                                <option value="Beginner" <?php echo $course['level'] === 'Beginner' ? 'selected' : ''; ?>>Beginner</option>
                                <option value="Intermediate" <?php echo $course['level'] === 'Intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                <option value="Advanced" <?php echo $course['level'] === 'Advanced' ? 'selected' : ''; ?>>Advanced</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Short Description</label>
                        <input type="text" name="short_description" value="<?php echo htmlspecialchars($course['short_description']); ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                    </div>

                    <div style="margin-bottom: 24px;">
                        <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Full Description (HTML)</label>
                        <textarea name="description" rows="5" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-family: inherit;"><?php echo htmlspecialchars($course['description']); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Update Course Changes</button>
                </form>
            </div>

            <!-- Right Column: Add Module, Add Lesson & Curriculum Overview -->
            <div>
                <!-- 1. Add New Module Card -->
                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; margin-bottom: 24px; box-shadow: var(--shadow-sm);">
                    <h3 style="font-size: 1.1rem; margin-bottom: 14px; color: var(--primary-blue);"><i class="fa-solid fa-folder-plus"></i> Add New Module</h3>
                    <form action="course-edit.php?id=<?php echo $course_id; ?>" method="POST">
                        <input type="hidden" name="add_module" value="1">
                        
                        <div style="margin-bottom: 12px;">
                            <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Module Title</label>
                            <input type="text" name="module_title" required placeholder="e.g. Module 3: Advanced Flexbox Patterns" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                        </div>

                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Sort Order</label>
                            <input type="number" name="sort_order" value="<?php echo count($modules) + 1; ?>" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary" style="width: 100%;"><i class="fa-solid fa-plus"></i> Create Module</button>
                    </form>
                </div>

                <!-- 2. Add New Lesson Card -->
                <?php if (!empty($modules)): ?>
                    <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; margin-bottom: 24px; box-shadow: var(--shadow-sm);">
                        <h3 style="font-size: 1.1rem; margin-bottom: 14px; color: var(--accent-emerald);"><i class="fa-solid fa-circle-plus"></i> Add New Lesson</h3>
                        <form action="course-edit.php?id=<?php echo $course_id; ?>" method="POST">
                            <input type="hidden" name="add_lesson" value="1">
                            
                            <div style="margin-bottom: 12px;">
                                <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Select Target Module</label>
                                <select name="module_id" required style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                                    <?php foreach ($modules as $mod): ?>
                                        <option value="<?php echo $mod['id']; ?>"><?php echo htmlspecialchars($mod['title']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div style="margin-bottom: 12px;">
                                <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Lesson Title</label>
                                <input type="text" name="lesson_title" required placeholder="e.g. Async Await Deep Dive" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                            </div>

                            <div style="margin-bottom: 12px;">
                                <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Video URL (YouTube Embed / MP4)</label>
                                <input type="text" name="video_url" value="https://www.youtube.com/embed/gT0LhB-Zp50" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                            </div>

                            <div style="margin-bottom: 16px;">
                                <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Lesson Explanation (HTML)</label>
                                <textarea name="lesson_content" rows="3" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-family: inherit;"><p>Lesson explanation content...</p></textarea>
                            </div>

                            <button type="submit" class="btn btn-sm btn-accent" style="width: 100%;"><i class="fa-solid fa-plus"></i> Add Lesson</button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- 3. Existing Curriculum Manager -->
                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 16px;"><i class="fa-solid fa-list-ul"></i> Course Curriculum Structure</h3>
                    
                    <?php if (empty($modules)): ?>
                        <p style="color: var(--text-muted); font-size: 0.85rem;">No modules added yet. Use the "Add New Module" form above to create your first module.</p>
                    <?php else: ?>
                        <?php foreach ($modules as $m): ?>
                            <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 12px; margin-bottom: 14px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <strong style="font-size: 0.9rem; color: var(--primary-blue);"><?php echo htmlspecialchars($m['title']); ?></strong>
                                    <a href="course-edit.php?id=<?php echo $course_id; ?>&action=delete_module&module_id=<?php echo $m['id']; ?>" style="color: #ef4444; font-size: 0.75rem;" onclick="return confirm('Delete this module and all its lessons?');"><i class="fa-solid fa-trash"></i> Delete Module</a>
                                </div>
                                
                                <ul style="list-style: none; padding-left: 6px;">
                                    <?php if (empty($m['lessons'])): ?>
                                        <li style="font-size: 0.8rem; color: var(--text-muted); padding: 4px 0;">No lessons in this module.</li>
                                    <?php else: ?>
                                        <?php foreach ($m['lessons'] as $les): ?>
                                            <li style="font-size: 0.85rem; padding: 6px 0; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                                                <span>
                                                    <i class="fa-regular fa-circle-play" style="margin-right: 6px; color: var(--text-muted);"></i>
                                                    <?php echo htmlspecialchars($les['title']); ?>
                                                </span>
                                                <a href="course-edit.php?id=<?php echo $course_id; ?>&action=delete_lesson&lesson_id=<?php echo $les['id']; ?>" style="color: #ef4444; font-size: 0.75rem;" onclick="return confirm('Delete this lesson?');"><i class="fa-solid fa-xmark"></i></a>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
