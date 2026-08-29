<?php
// blog.php - Academy Articles & Learning Guides
$page_title = "Blog & Learning Guides";
require_once __DIR__ . '/includes/header.php';

$db = getDBConnection();
$posts = [];
try {
    $stmt = $db->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
    $posts = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<div class="py-section">
    <div class="container">
        <div style="margin-bottom: 40px;">
            <h1 class="section-title">Blog & Industry Articles</h1>
            <p class="section-subtitle">Stay updated with web development trends, career advice, and tutorial guides.</p>
        </div>

        <div class="courses-grid" style="grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));">
            <?php foreach ($posts as $post): ?>
                <div class="course-card">
                    <!-- Clickable Image Link -->
                    <a href="blog-details.php?id=<?php echo $post['id']; ?>" class="course-thumb" style="height: 200px; display:block;">
                        <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    </a>
                    
                    <div class="course-content">
                        <span class="badge" style="background:rgba(37,99,235,0.1); color:var(--primary-blue); font-weight:700; font-size:0.75rem; width:fit-content; padding:4px 8px; border-radius:4px; margin-bottom:8px;">
                            <?php echo htmlspecialchars($post['category']); ?>
                        </span>
                        
                        <!-- Clickable Title Link -->
                        <h3 class="course-title" style="margin-bottom: 8px;">
                            <a href="blog-details.php?id=<?php echo $post['id']; ?>" style="color:var(--primary-dark); text-decoration:none;">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h3>
                        
                        <p class="course-desc"><?php echo htmlspecialchars($post['excerpt']); ?></p>

                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:auto; padding-top:14px; border-top:1px solid var(--border-color); font-size:0.85rem;">
                            <span style="color:var(--text-muted);"><i class="fa-regular fa-calendar"></i> <?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                            <a href="blog-details.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline">Read Article &rarr;</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
