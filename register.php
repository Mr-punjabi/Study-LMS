<?php
// register.php - Student Registration
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$page_title = "Create a Free Student Account";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        $db = getDBConnection();
        $stmtCheck = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetch()) {
            $error = "An account with this email address already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmtInsert = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
            $stmtInsert->execute([$name, $email, $hashedPassword]);
            $newUserId = $db->lastInsertId();

            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'student';

            set_flash('success', 'Account created successfully! Welcome to Study Point Academy.');
            redirect('dashboard.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-section" style="min-height: 80vh; display: flex; align-items: center;">
    <div class="container" style="max-width: 520px;">
        <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 40px; box-shadow: var(--shadow-lg);">
            <div style="text-align: center; margin-bottom: 30px;">
                <i class="fa-solid fa-graduation-cap" style="font-size: 2.5rem; color: var(--primary-blue); margin-bottom: 12px;"></i>
                <h1 style="font-size: 1.75rem;">Create Your Account</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Join thousands of students learning modern web development.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Full Name</label>
                    <input type="text" name="name" required placeholder="John Doe" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 1rem;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Email Address</label>
                    <input type="email" name="email" required placeholder="john@example.com" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 1rem;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Password</label>
                    <input type="password" name="password" required placeholder="At least 6 characters" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 1rem;">
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Confirm Password</label>
                    <input type="password" name="confirm_password" required placeholder="Re-enter password" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 1rem;">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem;"><i class="fa-solid fa-rocket"></i> Create Account</button>
            </form>

            <div style="margin-top: 24px; text-align: center; font-size: 0.9rem; color: var(--text-muted); border-top: 1px solid var(--border-color); padding-top: 20px;">
                Already have an account? <a href="login.php" style="font-weight: 600;">Sign in here &rarr;</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
