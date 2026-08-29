<?php
// certificates.php - Official Verified Course Certificate Generator & Viewer
require_once __DIR__ . '/includes/functions.php';

$code = isset($_GET['code']) ? sanitize($_GET['code']) : '';
$db = getDBConnection();

$cert = null;
if (!empty($code)) {
    $stmt = $db->prepare("
        SELECT cert.*, u.name AS student_name, c.title AS course_title, cat.name AS category_name
        FROM certificates cert
        JOIN users u ON cert.user_id = u.id
        JOIN courses c ON cert.course_id = c.id
        JOIN categories cat ON c.category_id = cat.id
        WHERE cert.certificate_code = ?
    ");
    $stmt->execute([$code]);
    $cert = $stmt->fetch();
}

$page_title = $cert ? "Certificate - " . htmlspecialchars($cert['course_title']) : "Certificate Verification";
require_once __DIR__ . '/includes/header.php';
?>

<div class="py-section">
    <div class="container" style="max-width: 900px;">
        <?php if ($cert): ?>
            <!-- Certificate Canvas Card -->
            <div style="background: #ffffff; border: 12px solid #0f172a; border-radius: var(--radius-md); padding: 50px; text-align: center; box-shadow: var(--shadow-lg); position: relative; background: radial-gradient(circle, #ffffff 0%, #f8fafc 100%);">
                <div style="position: absolute; top: 20px; right: 20px; color: var(--accent-cyan); font-weight: 800; font-size: 0.85rem; letter-spacing: 0.05em; border: 1px dashed var(--accent-cyan); padding: 4px 12px; border-radius: 4px;">
                    VERIFIED OFFICIAL CERTIFICATE
                </div>

                <div style="margin-bottom: 20px; color: var(--primary-blue); font-size: 3rem;">
                    <i class="fa-solid fa-award"></i>
                </div>

                <h2 style="font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.2em; color: var(--text-muted); margin-bottom: 10px;">STUDY POINT ACADEMY</h2>
                <h1 style="font-size: 2.25rem; font-family: serif; color: var(--primary-dark); margin-bottom: 20px;">Certificate of Completion</h1>

                <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 10px;">This certificate is proudly awarded to</p>
                <h2 style="font-size: 2.5rem; color: var(--primary-blue); border-bottom: 2px solid var(--border-color); display: inline-block; padding-bottom: 6px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($cert['student_name']); ?>
                </h2>

                <p style="color: var(--text-muted); font-size: 1.1rem; max-width: 650px; margin: 0 auto 30px;">
                    for successfully demonstrating comprehensive knowledge and completing all lessons and assessments for the course:
                </p>

                <h3 style="font-size: 1.75rem; color: var(--primary-dark); margin-bottom: 40px;">
                    "<?php echo htmlspecialchars($cert['course_title']); ?>"
                </h3>

                <div style="display: flex; justify-content: space-between; align-items: flex-end; border-top: 1px solid var(--border-color); padding-top: 30px; text-align: left;">
                    <div>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 2px;">Issued Date: <strong><?php echo date('F j, Y', strtotime($cert['issued_at'])); ?></strong></p>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Verification Code: <code style="color:var(--primary-blue); font-weight:700;"><?php echo htmlspecialchars($cert['certificate_code']); ?></code></p>
                    </div>

                    <div style="text-align: center;">
                        <div style="font-family: cursive; font-size: 1.5rem; color: var(--primary-dark); border-bottom: 1px solid var(--text-muted); padding-bottom: 2px;">
                            Study Point Academy Board
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Authorized Registrar Signature</span>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <button onclick="window.print();" class="btn btn-primary"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
            </div>
        <?php else: ?>
            <!-- Verification Search Form -->
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 40px; text-align: center; box-shadow: var(--shadow-md);">
                <i class="fa-solid fa-shield-halved" style="font-size: 3rem; color: var(--primary-blue); margin-bottom: 16px;"></i>
                <h1 style="font-size: 1.75rem; margin-bottom: 8px;">Public Certificate Verification</h1>
                <p style="color: var(--text-muted); margin-bottom: 30px;">Enter the unique certificate code found on any Study Point Academy credential to verify its authenticity.</p>

                <form action="certificates.php" method="GET" style="max-width: 480px; margin: 0 auto;">
                    <div style="display: flex; gap: 10px;">
                        <input type="text" name="code" required placeholder="e.g. SPA-8F3A2B1C9D" style="flex: 1; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 1rem; font-family: monospace;">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check-double"></i> Verify</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
