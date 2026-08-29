<?php
// login.php - Student & Admin Authentication
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$page_title = "Sign In to Your Account";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            $redirectUrl = isset($_GET['redirect']) ? sanitize($_GET['redirect']) : 'dashboard.php';
            redirect($redirectUrl);
        } else {
            $error = "Invalid email address or password.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-section" style="min-height: 80vh; display: flex; align-items: center;">
    <div class="container" style="max-width: 480px;">
        <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 40px; box-shadow: var(--shadow-lg);">
            <div style="text-align: center; margin-bottom: 30px;">
                <i class="fa-solid fa-user-lock" style="font-size: 2.5rem; color: var(--primary-blue); margin-bottom: 12px;"></i>
                <h1 style="font-size: 1.75rem;">Welcome Back</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Sign in to access your courses and progress.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Email Address</label>
                    <input type="email" name="email" required placeholder="alex@studypoint.com" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 1rem;">
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 1rem;">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem;"><i class="fa-solid fa-right-to-bracket"></i> Sign In</button>
            </form>

            <div style="margin-top: 24px; text-align: center; font-size: 0.9rem; color: var(--text-muted); border-top: 1px solid var(--border-color); padding-top: 20px;">
                Don't have an account? <a href="register.php" style="font-weight: 600;">Sign up for free &rarr;</a>
            </div>
            
            <div style="margin-top: 16px; background: #f8fafc; padding: 12px; border-radius: var(--radius-sm); font-size: 0.8rem; color: var(--text-muted);">
                <strong>Demo Credentials:</strong><br>
                Student: <code>alex@studypoint.com</code> | <code>password123</code><br>
                Admin: <code>admin@studypoint.com</code> | <code>password123</code>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
