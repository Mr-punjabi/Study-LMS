<?php
// tutorials.php - Standalone Preview Tutorials & Demos Catalog
$page_title = "Free Developer Demos & Preview Tutorials";
require_once __DIR__ . '/includes/header.php';

$db = getDBConnection();
$tutorials = [];
try {
    $stmt = $db->query("SELECT * FROM standalone_tutorials ORDER BY created_at DESC");
    $tutorials = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<div class="py-section">
    <div class="container">
        <div style="margin-bottom: 40px; text-align: center; max-width: 760px; margin-left: auto; margin-right: auto;">
            <div class="hero-pill" style="margin-bottom: 12px;"><i class="fa-solid fa-flask"></i> Interactive Previews</div>
            <h1 class="section-title">Developer Demos & Preview Tutorials</h1>
            <p class="section-subtitle">Experience practical hands-on code sandboxes and topic demos before enrolling in full academy courses.</p>
        </div>

        <div class="courses-grid" style="grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));">
            <?php foreach ($tutorials as $tut): ?>
                <div class="course-card">
                    <!-- Clickable Image Link -->
                    <a href="tutorial-details.php?id=<?php echo $tut['id']; ?>" class="course-thumb" style="display:block;">
                        <span class="course-badge" style="background:var(--primary-blue);"><?php echo htmlspecialchars($tut['category']); ?> DEMO</span>
                        <img src="<?php echo htmlspecialchars($tut['image']); ?>" alt="<?php echo htmlspecialchars($tut['title']); ?>">
                    </a>

                    <div class="course-content">
                        <span class="badge" style="background:rgba(16,185,129,0.1); color:var(--accent-emerald); font-weight:700; font-size:0.75rem; width:fit-content; padding:4px 8px; border-radius:4px; margin-bottom:8px;">
                            Free Interactive Preview
                        </span>

                        <h3 class="course-title" style="margin-bottom: 8px;">
                            <a href="tutorial-details.php?id=<?php echo $tut['id']; ?>" style="color:var(--primary-dark); text-decoration:none;">
                                <?php echo htmlspecialchars($tut['title']); ?>
                            </a>
                        </h3>

                        <p class="course-desc"><?php echo htmlspecialchars($tut['short_description']); ?></p>

                        <div class="course-footer" style="margin-top:auto;">
                            <span style="font-size:0.85rem; color:var(--text-muted);"><i class="fa-solid fa-code"></i> Live Demo</span>
                            <a href="tutorial-details.php?id=<?php echo $tut['id']; ?>" class="btn btn-sm btn-primary"><i class="fa-solid fa-laptop-code"></i> Try Demo &rarr;</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
