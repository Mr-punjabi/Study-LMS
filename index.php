<?php
// index.php - Study Point Academy Enterprise LMS Homepage
$page_title = "Production & Enterprise-Level Learning Platform";
require_once __DIR__ . '/includes/header.php';

$db = getDBConnection();

// Fetch Categories
$categories = [];
try {
    $stmtCat = $db->query("SELECT c.*, (SELECT COUNT(id) FROM courses WHERE category_id = c.id) AS course_count FROM categories c");
    $categories = $stmtCat->fetchAll();
} catch (Exception $e) {}

// Fetch Featured Courses with lesson counts
$courses = [];
try {
    $stmtCourse = $db->query("
        SELECT c.*, cat.name AS category_name,
        (SELECT COUNT(l.id) FROM lessons l JOIN course_modules m ON l.module_id = m.id WHERE m.course_id = c.id) AS lesson_count
        FROM courses c 
        JOIN categories cat ON c.category_id = cat.id 
        WHERE c.is_featured = 1 AND c.status = 'published' 
        LIMIT 6
    ");
    $courses = $stmtCourse->fetchAll();
} catch (Exception $e) {}

// Fetch Latest Blog Posts
$blogs = [];
try {
    $stmtBlog = $db->query("SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT 2");
    $blogs = $stmtBlog->fetchAll();
} catch (Exception $e) {}

// Enrolled Courses for Logged-In User
$enrolledCourses = [];
if (is_logged_in()) {
    try {
        $stmtEnrolled = $db->prepare("
            SELECT c.*, e.enrolled_at 
            FROM enrollments e 
            JOIN courses c ON e.course_id = c.id 
            WHERE e.user_id = ? 
            ORDER BY e.enrolled_at DESC LIMIT 3
        ");
        $stmtEnrolled->execute([$_SESSION['user_id']]);
        $enrolledCourses = $stmtEnrolled->fetchAll();
    } catch (Exception $e) {}
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-grid">
            <div>
                <div class="hero-pill">
                    <i class="fa-solid fa-bolt"></i> Upgrade to Enterprise Learning
                </div>
                <h1 class="hero-title">
                    Master Web & Software Skills With <span>Structured Courses</span>
                </h1>
                <p class="hero-description">
                    Evolve your learning with interactive courses, practice quizzes, downloadable study notes, real-time progress tracking, and verified certificates.
                </p>
                <form action="courses.php" method="GET" class="search-box">
                    <input type="text" name="q" id="globalSearchInput" placeholder="Search courses, tutorials, notes (e.g. HTML, CSS, JavaScript)..." required>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                </form>
                <div class="search-tags">
                    <span>Popular Searches:</span>
                    <span class="tag-badge">HTML5</span>
                    <span class="tag-badge">CSS3 Flexbox</span>
                    <span class="tag-badge">JavaScript</span>
                    <span class="tag-badge">PHP MySQL</span>
                    <span class="tag-badge">Notes</span>
                </div>
            </div>
            <div style="text-align: center;">
                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=600&q=80" alt="Enterprise Learning" style="max-width: 100%; border-radius: var(--radius-lg); height: auto; filter: drop-shadow(0 20px 30px rgba(0,0,0,0.3));">
            </div>
        </div>
    </div>
</section>

<!-- Stats Ribbon -->
<div class="container">
    <div class="stats-bar">
        <div class="stat-item">
            <h3><i class="fa-solid fa-book-open"></i> 25+</h3>
            <p>Interactive Courses</p>
        </div>
        <div class="stat-item">
            <h3><i class="fa-solid fa-users"></i> 10,000+</h3>
            <p>Active Learners</p>
        </div>
        <div class="stat-item">
            <h3><i class="fa-solid fa-circle-check"></i> 500+</h3>
            <p>Quizzes & Assessments</p>
        </div>
        <div class="stat-item">
            <h3><i class="fa-solid fa-award"></i> 100%</h3>
            <p>Verified Certificates</p>
        </div>
    </div>
</div>

<?php if (is_logged_in() && !empty($enrolledCourses)): ?>
<!-- Continue Learning Section -->
<section class="py-section" style="background:#edf2f7; padding-top:60px;">
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <div>
                <h2 class="section-title" style="font-size:1.75rem;"><i class="fa-solid fa-circle-play" style="color:var(--primary-blue);"></i> Continue Learning</h2>
                <p style="color:var(--text-muted);">Pick up right where you left off in your enrolled courses.</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline btn-sm">Go to Dashboard &rarr;</a>
        </div>
        <div class="courses-grid">
            <?php foreach ($enrolledCourses as $enc): ?>
                <?php $progressPct = get_course_progress($_SESSION['user_id'], $enc['id']); ?>
                <div class="course-card">
                    <div class="course-content">
                        <span class="badge" style="background:rgba(37,99,235,0.1); color:var(--primary-blue); font-weight:700; font-size:0.75rem; width:fit-content; padding:4px 8px; border-radius:4px; margin-bottom:8px;"><?php echo htmlspecialchars($enc['level']); ?></span>
                        <h3 class="course-title">
                            <a href="course-details.php?id=<?php echo $enc['id']; ?>" style="color:var(--primary-dark); text-decoration:none;">
                                <?php echo htmlspecialchars($enc['title']); ?>
                            </a>
                        </h3>
                        <div style="margin-top:auto;">
                            <div style="display:flex; justify-content:space-between; font-size:0.85rem; margin-bottom:4px;">
                                <span>Progress</span>
                                <span style="font-weight:700; color:var(--primary-blue);"><?php echo $progressPct; ?>%</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: <?php echo $progressPct; ?>%;"></div>
                            </div>
                            <a href="course-details.php?id=<?php echo $enc['id']; ?>" class="btn btn-primary btn-sm" style="width:100%; margin-top:12px;"><i class="fa-solid fa-play"></i> Resume Course</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Categories Section -->
<section class="py-section">
    <div class="container">
        <div style="text-align: center; max-width: 700px; margin: 0 auto 50px;">
            <h2 class="section-title">Explore Learning Categories</h2>
            <p class="section-subtitle">Choose from structured subject areas tailored for web developers and students.</p>
        </div>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="courses.php?cat=<?php echo $cat['slug']; ?>" class="category-card">
                    <div class="category-icon">
                        <i class="<?php echo htmlspecialchars($cat['icon']); ?>"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                    <p><?php echo htmlspecialchars($cat['description']); ?></p>
                    <span style="font-size:0.8rem; font-weight:700; color:var(--primary-blue); margin-top:12px; display:inline-block;">
                        <?php echo $cat['course_count']; ?> Courses &rarr;
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Courses Section -->
<section class="py-section" style="background:#ffffff; border-top:1px solid var(--border-color); border-bottom:1px solid var(--border-color);">
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:40px;">
            <div>
                <h2 class="section-title">Featured Enterprise Courses</h2>
                <p class="section-subtitle" style="margin-bottom:0;">Curated structured courses designed for mastery and career growth.</p>
            </div>
            <a href="courses.php" class="btn btn-outline">View All Courses &rarr;</a>
        </div>
        <div class="courses-grid">
            <?php foreach ($courses as $c): ?>
                <div class="course-card">
                    <!-- Clickable Image Link -->
                    <a href="course-details.php?id=<?php echo $c['id']; ?>" class="course-thumb" style="display:block;">
                        <span class="course-badge"><?php echo htmlspecialchars($c['category_name']); ?></span>
                        <img src="<?php echo htmlspecialchars($c['thumbnail']); ?>" alt="<?php echo htmlspecialchars($c['title']); ?>">
                    </a>

                    <div class="course-content">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 0.85rem;">
                            <!-- Instructor -->
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
                            <a href="course-details.php?id=<?php echo $c['id']; ?>" style="color:var(--primary-dark); text-decoration:none;">
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
                            <a href="course-details.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-primary">Course Details &rarr;</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Latest Blog Articles Preview -->
<section class="py-section">
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:40px;">
            <div>
                <h2 class="section-title">Latest Articles & Guides</h2>
                <p class="section-subtitle" style="margin-bottom:0;">Insights, tutorials, and career advice for modern developers.</p>
            </div>
            <a href="blog.php" class="btn btn-outline">Explore Blog &rarr;</a>
        </div>
        <div class="courses-grid" style="grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));">
            <?php foreach ($blogs as $b): ?>
                <div class="course-card">
                    <a href="blog-details.php?id=<?php echo $b['id']; ?>" class="course-thumb" style="height: 200px; display:block;">
                        <img src="<?php echo htmlspecialchars($b['image']); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>">
                    </a>
                    <div class="course-content">
                        <span class="badge" style="background:rgba(37,99,235,0.1); color:var(--primary-blue); font-weight:700; font-size:0.75rem; width:fit-content; padding:4px 8px; border-radius:4px; margin-bottom:8px;"><?php echo htmlspecialchars($b['category']); ?></span>
                        <h3 class="course-title">
                            <a href="blog-details.php?id=<?php echo $b['id']; ?>" style="color:var(--primary-dark); text-decoration:none;">
                                <?php echo htmlspecialchars($b['title']); ?>
                            </a>
                        </h3>
                        <p class="course-desc"><?php echo htmlspecialchars($b['excerpt']); ?></p>
                        <a href="blog-details.php?id=<?php echo $b['id']; ?>" class="btn btn-sm btn-outline" style="margin-top:auto; width:fit-content;">Read Article &rarr;</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
