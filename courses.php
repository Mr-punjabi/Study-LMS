<?php
// courses.php - Course Catalog Page
$page_title = "Explore Courses & Learning Paths";
require_once __DIR__ . '/includes/header.php';

$db = getDBConnection();

$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$cat_slug = isset($_GET['cat']) ? sanitize($_GET['cat']) : '';
$level = isset($_GET['level']) ? sanitize($_GET['level']) : '';

// Fetch all categories for filter sidebar
$categories = [];
try {
    $stmtCat = $db->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmtCat->fetchAll();
} catch (Exception $e) {}

// Build Query with lesson counts
$query = "
    SELECT c.*, cat.name AS category_name, cat.slug AS category_slug,
    (SELECT COUNT(l.id) FROM lessons l JOIN course_modules m ON l.module_id = m.id WHERE m.course_id = c.id) AS lesson_count
    FROM courses c 
    JOIN categories cat ON c.category_id = cat.id 
    WHERE c.status = 'published'
";
$params = [];

if (!empty($search)) {
    $query .= " AND (c.title LIKE ? OR c.short_description LIKE ? OR c.description LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($cat_slug)) {
    $query .= " AND cat.slug = ?";
    $params[] = $cat_slug;
}

if (!empty($level)) {
    $query .= " AND c.level = ?";
    $params[] = $level;
}

$query .= " ORDER BY c.created_at DESC";

$courses = [];
try {
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $courses = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<div class="py-section">
    <div class="container">
        <div style="margin-bottom: 40px;">
            <h1 class="section-title">Course Catalog</h1>
            <p class="section-subtitle">Discover structured learning pathways in HTML, CSS, JavaScript, and Full-Stack Engineering.</p>
        </div>

        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 40px;">
            <!-- Filter Sidebar -->
            <aside style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; height: fit-content; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 1.1rem; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                    <i class="fa-solid fa-filter"></i> Filter Courses
                </h3>
                <form action="courses.php" method="GET">
                    <?php if (!empty($search)): ?>
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($search); ?>">
                    <?php endif; ?>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Categories</label>
                        <select name="cat" class="form-control" style="width: 100%; padding: 10px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo $c['slug']; ?>" <?php echo $cat_slug === $c['slug'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Skill Level</label>
                        <select name="level" class="form-control" style="width: 100%; padding: 10px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);" onchange="this.form.submit()">
                            <option value="">All Levels</option>
                            <option value="Beginner" <?php echo $level === 'Beginner' ? 'selected' : ''; ?>>Beginner</option>
                            <option value="Intermediate" <?php echo $level === 'Intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="Advanced" <?php echo $level === 'Advanced' ? 'selected' : ''; ?>>Advanced</option>
                        </select>
                    </div>

                    <a href="courses.php" class="btn btn-outline btn-sm" style="width: 100%; text-align: center;">Reset Filters</a>
                </form>
            </aside>

            <!-- Main Course List -->
            <div>
                <?php if (!empty($search) || !empty($cat_slug) || !empty($level)): ?>
                    <div style="margin-bottom: 20px; font-size: 0.95rem; color: var(--text-muted);">
                        Showing results for 
                        <?php if ($search) echo "search <strong>\"" . htmlspecialchars($search) . "\"</strong> "; ?>
                        <?php if ($cat_slug) echo "category <strong>\"" . htmlspecialchars($cat_slug) . "\"</strong> "; ?>
                        <?php if ($level) echo "level <strong>\"" . htmlspecialchars($level) . "\"</strong>"; ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($courses)): ?>
                    <div style="text-align: center; padding: 60px 20px; background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                        <i class="fa-solid fa-folder-open" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 16px;"></i>
                        <h3>No courses found</h3>
                        <p style="color: var(--text-muted); margin-bottom: 20px;">Try adjusting your filter settings or search keywords.</p>
                        <a href="courses.php" class="btn btn-primary btn-sm">Clear Filters</a>
                    </div>
                <?php else: ?>
                    <div class="courses-grid">
                        <?php foreach ($courses as $c): ?>
                            <div class="course-card">
                                <!-- Clickable Thumbnail Image Link -->
                                <a href="course-details.php?id=<?php echo $c['id']; ?>" class="course-thumb" style="display:block;">
                                    <span class="course-badge"><?php echo htmlspecialchars($c['category_name']); ?></span>
                                    <img src="<?php echo htmlspecialchars($c['thumbnail']); ?>" alt="<?php echo htmlspecialchars($c['title']); ?>">
                                </a>

                                <div class="course-content">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 0.85rem;">
                                        <!-- Instructor Name -->
                                        <span style="color: var(--text-muted); font-weight: 600;">
                                            <i class="fa-solid fa-user-tie" style="color: var(--primary-blue); margin-right: 4px;"></i>
                                            <?php echo htmlspecialchars($c['instructor_name'] ?? 'Sarah Connor'); ?>
                                        </span>
                                        <!-- Star Rating -->
                                        <span style="color: #f59e0b; font-weight: 700;">
                                            <i class="fa-solid fa-star"></i> <?php echo number_format($c['rating'] ?? 4.9, 1); ?>
                                        </span>
                                    </div>

                                    <!-- Clickable Title Link -->
                                    <h3 class="course-title" style="margin-bottom: 8px;">
                                        <a href="course-details.php?id=<?php echo $c['id']; ?>" style="color: var(--primary-dark); text-decoration: none;">
                                            <?php echo htmlspecialchars($c['title']); ?>
                                        </a>
                                    </h3>

                                    <div class="course-meta" style="margin-bottom: 14px;">
                                        <span><i class="fa-regular fa-clock"></i> <?php echo format_duration($c['duration_minutes']); ?></span>
                                        <span><i class="fa-solid fa-book-open"></i> <?php echo $c['lesson_count']; ?> Lessons</span>
                                        <span><i class="fa-solid fa-layer-group"></i> <?php echo htmlspecialchars($c['level']); ?></span>
                                    </div>

                                    <p class="course-desc"><?php echo htmlspecialchars($c['short_description']); ?></p>

                                    <div class="course-footer">
                                        <span class="course-price" style="color: <?php echo $c['price'] > 0 ? 'var(--primary-blue)' : 'var(--accent-emerald)'; ?>;">
                                            <?php echo $c['price'] > 0 ? '$' . number_format($c['price'], 2) : '<span style="background:#ecfdf5; color:#059669; padding:2px 8px; border-radius:12px; font-size:0.85rem; font-weight:700;">FREE</span>'; ?>
                                        </span>
                                        <a href="course-details.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-primary">Enroll / Details &rarr;</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
