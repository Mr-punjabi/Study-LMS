<?php
// admin/enrollments.php - Payment Verification & Enrollment Approval Manager
$page_title = "Manage Payment Approvals";
require_once __DIR__ . '/includes/admin_header.php';

$db = getDBConnection();

// Handle Approve Payment Action
if (isset($_GET['action']) && $_GET['action'] === 'approve' && isset($_GET['id'])) {
    $enrollment_id = (int)$_GET['id'];
    $stmtApprove = $db->prepare("UPDATE enrollments SET payment_status = 'approved' WHERE id = ?");
    $stmtApprove->execute([$enrollment_id]);
    set_flash('success', 'Payment confirmed! Course access has been approved and unlocked for the student.');
    redirect('admin/enrollments.php');
}

// Handle Reject Payment Action
if (isset($_GET['action']) && $_GET['action'] === 'reject' && isset($_GET['id'])) {
    $enrollment_id = (int)$_GET['id'];
    $stmtReject = $db->prepare("UPDATE enrollments SET payment_status = 'rejected' WHERE id = ?");
    $stmtReject->execute([$enrollment_id]);
    set_flash('error', 'Payment rejected.');
    redirect('admin/enrollments.php');
}

// Filter status if requested
$filterStatus = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$query = "
    SELECT e.*, u.name AS student_name, u.email AS student_email, c.title AS course_title, c.price AS course_price, cat.name AS category_name
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    JOIN categories cat ON c.category_id = cat.id
";

if (!empty($filterStatus)) {
    $query .= " WHERE e.payment_status = " . $db->quote($filterStatus);
}

$query .= " ORDER BY (e.payment_status = 'pending') DESC, e.enrolled_at DESC";

$enrollments = $db->query($query)->fetchAll();
$pendingCount = $db->query("SELECT COUNT(id) FROM enrollments WHERE payment_status = 'pending'")->fetchColumn();
?>

<div class="py-section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 class="section-title" style="font-size: 1.75rem;">Payment Verification & Approvals</h1>
                <p style="color: var(--text-muted);">Confirm student course purchases to grant access to lessons and certificates.</p>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <a href="enrollments.php" class="btn btn-sm <?php echo empty($filterStatus) ? 'btn-primary' : 'btn-outline'; ?>">All Orders</a>
                <a href="enrollments.php?status=pending" class="btn btn-sm <?php echo $filterStatus === 'pending' ? 'btn-primary' : 'btn-outline'; ?>" style="<?php echo $pendingCount > 0 ? 'border-color:#f59e0b; color:#d97706;' : ''; ?>">
                    Pending Verification <?php if ($pendingCount > 0): ?><span style="background:#ef4444; color:#fff; padding:2px 6px; border-radius:10px; font-size:0.75rem; margin-left:4px;"><?php echo $pendingCount; ?></span><?php endif; ?>
                </a>
                <a href="enrollments.php?status=approved" class="btn btn-sm <?php echo $filterStatus === 'approved' ? 'btn-primary' : 'btn-outline'; ?>">Approved</a>
            </div>
        </div>

        <?php render_flash('success'); ?>
        <?php render_flash('error'); ?>

        <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-sm);">
            <?php if (empty($enrollments)): ?>
                <div style="text-align: center; padding: 40px 20px;">
                    <i class="fa-solid fa-receipt" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 12px;"></i>
                    <h3>No enrollments found</h3>
                    <p style="color: var(--text-muted);">There are currently no payment transactions matching this filter.</p>
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border-color); text-align: left; color: var(--text-muted);">
                            <th style="padding: 10px 0;">Order ID</th>
                            <th>Student</th>
                            <th>Course Title</th>
                            <th>Amount</th>
                            <th>Submitted Date</th>
                            <th>Payment Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $e): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; <?php echo $e['payment_status'] === 'pending' ? 'background:#fffdf5;' : ''; ?>">
                                <td style="padding: 12px 0;">#ORD-<?php echo $e['id']; ?></td>
                                <td>
                                    <strong style="display: block; color: var(--primary-dark);"><?php echo htmlspecialchars($e['student_name']); ?></strong>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($e['student_email']); ?></span>
                                </td>
                                <td>
                                    <span style="font-weight: 600;"><?php echo htmlspecialchars($e['course_title']); ?></span>
                                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($e['category_name']); ?></span>
                                </td>
                                <td>
                                    <strong style="color: <?php echo $e['course_price'] > 0 ? 'var(--primary-blue)' : 'var(--accent-emerald)'; ?>;">
                                        <?php echo $e['course_price'] > 0 ? '$' . number_format($e['course_price'], 2) : 'FREE'; ?>
                                    </strong>
                                </td>
                                <td><?php echo date('M j, Y • g:i A', strtotime($e['enrolled_at'])); ?></td>
                                <td>
                                    <?php if ($e['payment_status'] === 'pending'): ?>
                                        <span style="background:#fffbe0; color:#d97706; border:1px solid #fde68a; font-size:0.75rem; font-weight:700; padding:3px 10px; border-radius:12px;">
                                            <i class="fa-solid fa-clock"></i> PENDING VERIFICATION
                                        </span>
                                    <?php elseif ($e['payment_status'] === 'approved'): ?>
                                        <span style="background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; font-size:0.75rem; font-weight:700; padding:3px 10px; border-radius:12px;">
                                            <i class="fa-solid fa-circle-check"></i> APPROVED
                                        </span>
                                    <?php else: ?>
                                        <span style="background:#fef2f2; color:#991b1b; border:1px solid #fca5a5; font-size:0.75rem; font-weight:700; padding:3px 10px; border-radius:12px;">
                                            <i class="fa-solid fa-circle-xmark"></i> REJECTED
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php if ($e['payment_status'] === 'pending'): ?>
                                        <a href="enrollments.php?action=approve&id=<?php echo $e['id']; ?>" class="btn btn-sm btn-accent" style="margin-right: 4px;" onclick="return confirm('Approve payment and unlock course access for this student?');">
                                            <i class="fa-solid fa-check"></i> Approve Payment
                                        </a>
                                        <a href="enrollments.php?action=reject&id=<?php echo $e['id']; ?>" class="btn btn-sm btn-outline" style="color:#ef4444; border-color:#fca5a5;" onclick="return confirm('Reject this payment transaction?');">
                                            Reject
                                        </a>
                                    <?php elseif ($e['payment_status'] === 'approved'): ?>
                                        <a href="enrollments.php?action=reject&id=<?php echo $e['id']; ?>" class="btn btn-sm btn-outline" style="color:#64748b;" onclick="return confirm('Revoke approval for this course?');">
                                            Revoke
                                        </a>
                                    <?php else: ?>
                                        <a href="enrollments.php?action=approve&id=<?php echo $e['id']; ?>" class="btn btn-sm btn-outline" style="color:var(--primary-blue);" onclick="return confirm('Re-approve payment?');">
                                            Re-approve
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
