<?php
// tutorial-details.php - Standalone Preview Tutorial & Interactive Sandbox Reader
require_once __DIR__ . '/includes/functions.php';

$tut_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = getDBConnection();

$stmt = $db->prepare("SELECT * FROM standalone_tutorials WHERE id = ?");
$stmt->execute([$tut_id]);
$tut = $stmt->fetch();

if (!$tut) {
    redirect('tutorials.php');
}

$page_title = $tut['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); color: #ffffff; padding: 50px 0;">
    <div class="container" style="max-width: 860px;">
        <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 14px; font-size: 0.9rem; color: #94a3b8;">
            <a href="tutorials.php" style="color: #94a3b8;">Tutorials</a> &rarr; 
            <span><?php echo htmlspecialchars($tut['category']); ?> Preview</span>
        </div>
        <h1 style="color: #ffffff; font-size: 2.25rem; margin-bottom: 12px;"><?php echo htmlspecialchars($tut['title']); ?></h1>
        <p style="font-size: 1.1rem; color: #cbd5e1;"><?php echo htmlspecialchars($tut['short_description']); ?></p>
    </div>
</div>

<div class="py-section">
    <div class="container" style="max-width: 860px;">
        <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 40px; box-shadow: var(--shadow-sm); margin-bottom: 40px;">
            <div style="font-size: 1.05rem; line-height: 1.8; color: var(--text-main); margin-bottom: 30px;">
                <?php echo $tut['content']; ?>
            </div>

            <?php if (!empty($tut['demo_code'])): ?>
                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; margin-top: 30px;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 14px; color: var(--primary-dark);"><i class="fa-solid fa-flask" style="color:var(--primary-blue);"></i> Live Interactive Demo Preview</h3>
                    <div>
                        <?php echo $tut['demo_code']; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Banner Prompt to Purchase / Enroll in Full Course -->
        <div style="background: linear-gradient(135deg, var(--primary-blue), var(--accent-cyan)); color: #ffffff; border-radius: var(--radius-md); padding: 30px; text-align: center; box-shadow: var(--shadow-md);">
            <h2 style="color: #ffffff; font-size: 1.5rem; margin-bottom: 8px;">Ready for Complete Mastery?</h2>
            <p style="color: #e0f2fe; margin-bottom: 20px;">Enroll in our full structured courses to unlock step-by-step video lessons, module quizzes, and verified certificates.</p>
            <a href="courses.php" class="btn btn-secondary" style="background:#ffffff; color:var(--primary-dark); font-weight:700;"><i class="fa-solid fa-graduation-cap"></i> Browse Full Courses</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
