<?php
// admin/courses.php - Course Builder & Management CMS
$page_title = "Manage Courses";
require_once __DIR__ . '/includes/admin_header.php';

$db = getDBConnection();
$message = '';
$error = '';

// Handle Delete Course
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = (int)$_GET['id'];
    $stmtDel = $db->prepare("DELETE FROM courses WHERE id = ?");
    $stmtDel->execute([$deleteId]);
    set_flash('success', 'Course deleted successfully.');
    redirect('admin/courses.php');
}

// Handle Create Course Form Submit (Action points to courses.php inside /admin/)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_course'])) {
    $category_id = (int)$_POST['category_id'];
    $title = sanitize($_POST['title']);
    $slug = sanitize(strtolower(str_replace([' ', '/'], '-', $title)));
    $instructor = sanitize($_POST['instructor_name'] ?? 'Sarah Connor');
    $rating = (float)($_POST['rating'] ?? 4.9);
    $price = (float)($_POST['price'] ?? 0.00);
    $short_desc = sanitize($_POST['short_description']);
    $description = $_POST['description']; // HTML content
    $level = sanitize($_POST['level']);
    $duration = (int)$_POST['duration_minutes'];
    $thumbnail = sanitize($_POST['thumbnail'] ?? 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&w=600&q=80');

    // Initial Module & Lesson fields
    $module_title = sanitize($_POST['module_title'] ?? 'Module 1: Getting Started');
    $lesson_title = sanitize($_POST['lesson_title'] ?? 'Lesson 1: Introduction');
    $video_url = sanitize($_POST['video_url'] ?? 'https://www.youtube.com/embed/gT0LhB-Zp50');
    $lesson_content = $_POST['lesson_content'] ?? '<h3>Lesson Overview</h3><p>Welcome to this course!</p>';

    if (empty($title) || empty($short_desc)) {
        $error = "Course title and short description are required.";
    } else {
        $stmtIns = $db->prepare("
            INSERT INTO courses (category_id, title, slug, instructor_name, rating, short_description, description, level, thumbnail, duration_minutes, price, is_featured, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'published')
        ");
        $stmtIns->execute([$category_id, $title, $slug, $instructor, $rating, $short_desc, $description, $level, $thumbnail, $duration, $price]);
        $newCourseId = $db->lastInsertId();

        // Create Initial Module
        $stmtMod = $db->prepare("INSERT INTO course_modules (course_id, title, sort_order) VALUES (?, ?, 1)");
        $stmtMod->execute([$newCourseId, $module_title]);
        $modId = $db->lastInsertId();

        // Create Initial Lesson
        $stmtLes = $db->prepare("
            INSERT INTO lessons (module_id, title, slug, content, video_url, duration_minutes, sort_order)
            VALUES (?, ?, 'lesson-1', ?, ?, 15, 1)
        ");
        $stmtLes->execute([$modId, $lesson_title, $lesson_content, $video_url]);

        set_flash('success', 'New course and initial lesson created successfully!');
        redirect('admin/courses.php');
    }
}

// Fetch Courses List
$courses = $db->query("
    SELECT c.*, cat.name AS category_name, 
    (SELECT COUNT(id) FROM enrollments WHERE course_id = c.id) AS student_count
    FROM courses c 
    JOIN categories cat ON c.category_id = cat.id 
    ORDER BY c.created_at DESC
")->fetchAll();

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$showCreateForm = isset($_GET['action']) && $_GET['action'] === 'create';
?>

<div class="py-section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1 class="section-title" style="font-size: 1.75rem;">Course Builder CMS</h1>
                <p style="color: var(--text-muted);">Create, edit, and manage academy courses and lessons.</p>
            </div>
            <?php if (!$showCreateForm): ?>
                <a href="courses.php?action=create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Create New Course</a>
            <?php endif; ?>
        </div>

        <?php render_flash('success'); ?>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <?php if ($showCreateForm): ?>
            <!-- Create Course Form (Action: courses.php) -->
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 30px; margin-bottom: 40px; box-shadow: var(--shadow-md);">
                <h2 style="font-size: 1.25rem; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">Create New Enterprise Course</h2>
                
                <form action="courses.php" method="POST">
                    <input type="hidden" name="create_course" value="1">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Course Title</label>
                            <input type="text" name="title" required placeholder="e.g. Master React & Node.js" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Category</label>
                            <select name="category_id" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Instructor Name</label>
                            <input type="text" name="instructor_name" value="Sarah Connor" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Price ($0.00 = Free)</label>
                            <input type="number" step="0.01" name="price" value="0.00" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Rating (1.0 to 5.0)</label>
                            <input type="number" step="0.1" name="rating" value="4.9" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Difficulty Level</label>
                            <select name="level" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                                <option value="Beginner">Beginner</option>
                                <option value="Intermediate">Intermediate</option>
                                <option value="Advanced">Advanced</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Duration (Minutes)</label>
                            <input type="number" name="duration_minutes" value="120" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Short Overview</label>
                        <input type="text" name="short_description" required placeholder="A concise 1-sentence description of the course." style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                    </div>

                    <div style="margin-bottom: 24px;">
                        <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Full Description (HTML Supported)</label>
                        <textarea name="description" rows="4" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-family: inherit;"><p>Learn modern concepts in this comprehensive course!</p></textarea>
                    </div>

                    <!-- Initial Lesson Creation Section -->
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 20px; margin-bottom: 24px;">
                        <h3 style="font-size: 1.05rem; margin-bottom: 14px; color: var(--primary-blue);"><i class="fa-solid fa-file-circle-plus"></i> Initial Lesson Setup</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 12px;">
                            <div>
                                <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Module Title</label>
                                <input type="text" name="module_title" value="Module 1: Introduction" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">First Lesson Title</label>
                                <input type="text" name="lesson_title" value="Lesson 1: Overview" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                            </div>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Lesson Video URL (YouTube Embed / MP4)</label>
                            <input type="text" name="video_url" value="https://www.youtube.com/embed/gT0LhB-Zp50" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save & Publish Course</button>
                    <a href="courses.php" class="btn btn-outline">Cancel</a>
                </form>
            </div>
        <?php endif; ?>

        <!-- Courses Data Table -->
        <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-sm);">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); text-align: left; color: var(--text-muted);">
                        <th style="padding: 10px 0;">Title</th>
                        <th>Category</th>
                        <th>Instructor</th>
                        <th>Price</th>
                        <th>Students</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $c): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 12px 0; font-weight: 600;">
                                <a href="<?php echo BASE_URL; ?>course-details.php?id=<?php echo $c['id']; ?>" target="_blank">
                                    <?php echo htmlspecialchars($c['title']); ?> <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:0.75rem;"></i>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($c['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($c['instructor_name'] ?? 'Sarah Connor'); ?></td>
                            <td><strong><?php echo $c['price'] > 0 ? '$' . number_format($c['price'], 2) : 'FREE'; ?></strong></td>
                            <td><?php echo $c['student_count']; ?> enrolled</td>
                            <td style="text-align: right;">
                                <a href="course-edit.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline" style="margin-right:4px;"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                <a href="courses.php?action=delete&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline" style="color:#ef4444;" onclick="return confirm('Are you sure you want to delete this course?');"><i class="fa-solid fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
