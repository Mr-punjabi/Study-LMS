<?php
// checkout.php - Secure Course Order & Checkout Portal
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    $course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
    redirect('login.php?redirect=checkout.php?course_id=' . $course_id);
}

$user_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : (isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0);
$db = getDBConnection();

// Fetch Course
$stmt = $db->prepare("
    SELECT c.*, cat.name AS category_name
    FROM courses c
    JOIN categories cat ON c.category_id = cat.id
    WHERE c.id = ? AND c.status = 'published'
");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    redirect('courses.php');
}

// Check if already enrolled
$status = get_enrollment_status($user_id, $course_id);
if ($status === 'approved') {
    set_flash('success', 'You are already enrolled with approved access to this course.');
    redirect('course-details.php?id=' . $course_id);
} elseif ($status === 'pending') {
    set_flash('error', 'Your payment for this course is currently pending administrator verification.');
    redirect('dashboard.php');
}

$error = '';

// Handle Payment & Pending Enrollment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $payment_method = sanitize($_POST['payment_method'] ?? 'card');
    $payment_status = ($course['price'] > 0) ? 'pending' : 'approved';
    
    try {
        $stmtEnroll = $db->prepare("
            INSERT INTO enrollments (user_id, course_id, payment_status)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE payment_status = ?
        ");
        $stmtEnroll->execute([$user_id, $course_id, $payment_status, $payment_status]);

        if ($payment_status === 'pending') {
            set_flash('success', 'Payment Submitted Successfully! Your enrollment in "' . htmlspecialchars($course['title']) . '" is now pending administrator confirmation. Access will be granted once approved.');
            redirect('dashboard.php');
        } else {
            set_flash('success', 'Free Enrollment Complete! Welcome to "' . htmlspecialchars($course['title']) . '".');
            redirect('dashboard.php');
        }
    } catch (Exception $e) {
        $error = "Enrollment processing failed: " . $e->getMessage();
    }
}

$page_title = "Checkout - " . htmlspecialchars($course['title']);
require_once __DIR__ . '/includes/header.php';
?>

<div style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); color: #ffffff; padding: 40px 0;">
    <div class="container" style="max-width: 900px;">
        <h1 style="color: #ffffff; font-size: 2rem; margin-bottom: 6px;"><i class="fa-solid fa-lock" style="color:var(--accent-emerald);"></i> Secure Course Checkout</h1>
        <p style="color: #cbd5e1; font-size: 0.95rem;">Review your order. Paid courses require administrator payment confirmation before access is granted.</p>
    </div>
</div>

<div class="py-section">
    <div class="container" style="max-width: 900px;">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 40px;">
            <!-- Left Column: Payment Options & Details -->
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 30px; box-shadow: var(--shadow-sm);">
                <h2 style="font-size: 1.25rem; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">Select Payment Method</h2>

                <form action="checkout.php" method="POST" id="checkoutForm">
                    <input type="hidden" name="process_payment" value="1">
                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">

                    <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 24px;">
                        <label style="display: flex; align-items: center; gap: 14px; padding: 16px; border: 2px solid var(--primary-blue); border-radius: var(--radius-sm); background: rgba(37,99,235,0.03); cursor: pointer;">
                            <input type="radio" name="payment_method" value="card" checked style="accent-color: var(--primary-blue);">
                            <div style="flex: 1;">
                                <strong style="display: block; font-size: 1rem; color: var(--primary-dark);">Credit or Debit Card</strong>
                                <span style="font-size: 0.85rem; color: var(--text-muted);">Visa, MasterCard, American Express</span>
                            </div>
                            <i class="fa-solid fa-credit-card" style="font-size: 1.5rem; color: var(--primary-blue);"></i>
                        </label>

                        <label style="display: flex; align-items: center; gap: 14px; padding: 16px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer;">
                            <input type="radio" name="payment_method" value="paypal" style="accent-color: var(--primary-blue);">
                            <div style="flex: 1;">
                                <strong style="display: block; font-size: 1rem; color: var(--primary-dark);">PayPal Checkout</strong>
                                <span style="font-size: 0.85rem; color: var(--text-muted);">Fast, safe online payment</span>
                            </div>
                            <i class="fa-brands fa-paypal" style="font-size: 1.5rem; color: #003087;"></i>
                        </label>

                        <label style="display: flex; align-items: center; gap: 14px; padding: 16px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer;">
                            <input type="radio" name="payment_method" value="demo" style="accent-color: var(--primary-blue);">
                            <div style="flex: 1;">
                                <strong style="display: block; font-size: 1rem; color: var(--primary-dark);">Academy Demo Payment</strong>
                                <span style="font-size: 0.85rem; color: var(--text-muted);">Submit order for admin verification</span>
                            </div>
                            <i class="fa-solid fa-bolt" style="font-size: 1.5rem; color: var(--accent-emerald);"></i>
                        </label>
                    </div>

                    <!-- Dummy Card Inputs -->
                    <div id="cardInputs" style="margin-bottom: 24px; background: #f8fafc; padding: 20px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Cardholder Name</label>
                            <input type="text" placeholder="Alex Johnson" value="Alex Johnson" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                        </div>
                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Card Number</label>
                            <input type="text" placeholder="4532 •••• •••• 8892" value="4532 8901 2345 8892" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-family: monospace;">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                            <div>
                                <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Expiry Date</label>
                                <input type="text" placeholder="MM/YY" value="12/28" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">CVC / CVV</label>
                                <input type="password" placeholder="CVC" value="382" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1.1rem; background: linear-gradient(135deg, var(--accent-emerald), #059669); border: 0;">
                        <i class="fa-solid fa-paper-plane"></i> Submit $<?php echo number_format($course['price'], 2); ?> Order for Admin Approval
                    </button>
                </form>
            </div>

            <!-- Right Column: Order Summary -->
            <div>
                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-sm); position: sticky; top: 90px;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">Order Summary</h3>
                    
                    <div style="display: flex; gap: 14px; margin-bottom: 20px;">
                        <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" style="width: 80px; height: 60px; object-fit: cover; border-radius: var(--radius-sm);">
                        <div>
                            <h4 style="font-size: 0.95rem; margin-bottom: 4px;"><?php echo htmlspecialchars($course['title']); ?></h4>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Instructor: <?php echo htmlspecialchars($course['instructor_name'] ?? 'Sarah Connor'); ?></span>
                        </div>
                    </div>

                    <div style="border-top: 1px solid var(--border-color); padding-top: 16px; margin-bottom: 20px; font-size: 0.9rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: var(--text-muted);">Course Price:</span>
                            <strong>$<?php echo number_format($course['price'], 2); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: var(--text-muted);">Verification Status:</span>
                            <span style="color: #d97706; font-weight: 700;">Pending Admin</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--border-color); padding-top: 12px; font-size: 1.15rem; font-weight: 800; color: var(--primary-dark);">
                            <span>Total Due:</span>
                            <span style="color: var(--accent-emerald);">$<?php echo number_format($course['price'], 2); ?></span>
                        </div>
                    </div>

                    <div style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.5; background: #fffbebf8; border: 1px solid #fef3c7; padding: 12px; border-radius: var(--radius-sm);">
                        <i class="fa-solid fa-circle-info" style="color: #d97706; margin-right: 4px;"></i>
                        <strong>Notice:</strong> Your enrollment will be locked until the administrator verifies the payment receipt.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
