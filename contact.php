<?php
// contact.php - Contact & Support Center
$page_title = "Contact & Support";
require_once __DIR__ . '/includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please complete all fields in the contact form.";
    } else {
        $db = getDBConnection();
        $stmt = $db->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        $success = "Thank you! Your message has been sent to our support team.";
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-section">
    <div class="container" style="max-width: 600px;">
        <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 40px; box-shadow: var(--shadow-md);">
            <div style="text-align: center; margin-bottom: 30px;">
                <i class="fa-solid fa-headset" style="font-size: 2.5rem; color: var(--primary-blue); margin-bottom: 12px;"></i>
                <h1 style="font-size: 1.75rem;">Contact & Student Support</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Have questions about courses, certificates, or student accounts? Reach out to us!</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="contact.php" method="POST">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Your Name</label>
                    <input type="text" name="name" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Email Address</label>
                    <input type="email" name="email" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Subject</label>
                    <input type="text" name="subject" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Message</label>
                    <textarea name="message" rows="5" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-family: inherit;"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem;"><i class="fa-solid fa-paper-plane"></i> Send Message</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
