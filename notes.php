<?php
// notes.php - Downloadable Resources & Study Notes Center
$page_title = "Downloadable Study Notes & Cheatsheets";
require_once __DIR__ . '/includes/header.php';

$db = getDBConnection();
$resources = [];
try {
    $stmt = $db->query("SELECT * FROM resources ORDER BY created_at DESC");
    $resources = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<div class="py-section">
    <div class="container">
        <div style="margin-bottom: 40px;">
            <h1 class="section-title">Study Notes & Cheat Sheets</h1>
            <p class="section-subtitle">Download free high-quality PDF guides, HTML5 semantic cheatsheets, and CSS layout references.</p>
        </div>

        <div class="courses-grid">
            <?php foreach ($resources as $res): ?>
                <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:24px; display:flex; flex-direction:column;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <span style="background:rgba(16,185,129,0.1); color:var(--accent-emerald); font-weight:700; font-size:0.75rem; padding:4px 8px; border-radius:4px;">
                            <?php echo htmlspecialchars($res['category']); ?> • <?php echo htmlspecialchars($res['file_type']); ?>
                        </span>
                        <span style="font-size:0.85rem; color:var(--text-muted);"><i class="fa-solid fa-download"></i> <?php echo $res['download_count']; ?> downloads</span>
                    </div>

                    <h3 style="font-size: 1.2rem; margin-bottom: 8px;"><?php echo htmlspecialchars($res['title']); ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px; flex: 1;"><?php echo htmlspecialchars($res['description']); ?></p>

                    <a href="database/schema.sql" download class="btn btn-primary btn-sm" style="margin-top:auto;"><i class="fa-solid fa-file-arrow-down"></i> Download Resource</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
