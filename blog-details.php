<?php
// blog-details.php - Single Blog Article Reader
require_once __DIR__ . '/includes/functions.php';

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = getDBConnection();

$stmt = $db->prepare("
    SELECT b.*, u.name AS author_name 
    FROM blog_posts b 
    LEFT JOIN users u ON b.author_id = u.id 
    WHERE b.id = ?
");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    redirect('blog.php');
}

$page_title = $post['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); color: #ffffff; padding: 60px 0;">
    <div class="container" style="max-width: 840px;">
        <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 14px; font-size: 0.9rem; color: #94a3b8;">
            <a href="blog.php" style="color: #94a3b8;">Blog</a> &rarr; 
            <span><?php echo htmlspecialchars($post['category']); ?></span>
        </div>
        <h1 style="color: #ffffff; font-size: 2.5rem; margin-bottom: 16px;"><?php echo htmlspecialchars($post['title']); ?></h1>
        <div style="display: flex; gap: 20px; font-size: 0.9rem; color: #cbd5e1;">
            <span><i class="fa-solid fa-user-pen" style="color:var(--accent-cyan);"></i> By <?php echo htmlspecialchars($post['author_name'] ?? 'Sarah Connor'); ?></span>
            <span><i class="fa-regular fa-calendar" style="color:var(--accent-cyan);"></i> <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
        </div>
    </div>
</div>

<div class="py-section">
    <div class="container" style="max-width: 840px;">
        <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 40px; box-shadow: var(--shadow-sm); margin-bottom: 40px;">
            <div style="margin-bottom: 30px; border-radius: var(--radius-md); overflow: hidden; height: 350px;">
                <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
            </div>

            <div style="font-size: 1.1rem; line-height: 1.8; color: var(--text-main);">
                <?php echo $post['content']; ?>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="blog.php" class="btn btn-outline">&larr; Back to Blog Articles</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
