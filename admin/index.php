<?php
// admin/index.php - Enterprise CMS Dashboard Overview
$page_title = "Admin Dashboard";
require_once __DIR__ . '/includes/admin_header.php';

$db = getDBConnection();

// Metrics
$totalStudents = $db->query("SELECT COUNT(id) FROM users WHERE role = 'student'")->fetchColumn();
$totalCourses = $db->query("SELECT COUNT(id) FROM courses")->fetchColumn();
$totalLessons = $db->query("SELECT COUNT(id) FROM lessons")->fetchColumn();
$totalQuizzes = $db->query("SELECT COUNT(id) FROM quizzes")->fetchColumn();
$totalCertificates = $db->query("SELECT COUNT(id) FROM certificates")->fetchColumn();
$pendingApprovals = $db->query("SELECT COUNT(id) FROM enrollments WHERE payment_status = 'pending'")->fetchColumn();

// Fetch Recent Pending Enrollments
$recentPending = $db->query("
    SELECT e.*, u.name AS student_name, u.email AS student_email, c.title AS course_title, c.price AS course_price
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    WHERE e.payment_status = 'pending'
    ORDER BY e.enrolled_at DESC LIMIT 5
")->fetchAll();

// Fetch Recent Approved Enrollments
$recentEnrollments = $db->query("
    SELECT e.*, u.name AS student_name, u.email AS student_email, c.title AS course_title
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrolled_at DESC LIMIT 5
")->fetchAll();
?>

<div style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); color: #ffffff; padding: 40px 0;">
    <div class="container">
        <h1 style="color: #ffffff; font-size: 2rem; margin-bottom: 8px;"><i class="fa-solid fa-gauge-high" style="color:var(--accent-cyan);"></i> Enterprise Administration Panel</h1>
        <p style="color: #94a3b8;">Manage academy courses, payment approvals, quizzes, students, and certifications.</p>
    </div>
</div>

<div class="py-section">
    <div class="container">
        <?php render_flash('success'); ?>

        <!-- Stat Cards Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 40px;">
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px;">
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">STUDENTS</div>
                <h2 style="font-size: 2rem; color: var(--primary-blue); margin-top: 4px;"><?php echo $totalStudents; ?></h2>
            </div>
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px;">
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">COURSES</div>
                <h2 style="font-size: 2rem; color: var(--accent-emerald); margin-top: 4px;"><?php echo $totalCourses; ?></h2>
            </div>
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px;">
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">LESSONS</div>
                <h2 style="font-size: 2rem; color: var(--accent-cyan); margin-top: 4px;"><?php echo $totalLessons; ?></h2>
            </div>
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; <?php echo $pendingApprovals > 0 ? 'border-color:#fef3c7; background:#fffdf5;' : ''; ?>">
                <div style="font-size: 0.85rem; color: #d97706; font-weight: 700;">PENDING PAYMENTS</div>
                <h2 style="font-size: 2rem; color: #d97706; margin-top: 4px;"><?php echo $pendingApprovals; ?></h2>
            </div>
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px;">
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">CERTIFICATES</div>
                <h2 style="font-size: 2rem; color: #8b5cf6; margin-top: 4px;"><?php echo $totalCertificates; ?></h2>
            </div>
        </div>

        <!-- Quick Management Actions -->
        <div style="margin-bottom: 40px; display: flex; gap: 16px; flex-wrap: wrap;">
            <a href="enrollments.php?status=pending" class="btn btn-accent" style="background: linear-gradient(135deg, #d97706, #b45309); border:0;"><i class="fa-solid fa-clock"></i> Review Pending Payments (<?php echo $pendingApprovals; ?>)</a>
            <a href="courses.php?action=create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Create New Course</a>
            <a href="quizzes.php?action=create" class="btn btn-outline"><i class="fa-solid fa-file-pen"></i> Add Quiz</a>
        </div>

        <!-- Data Tables Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Pending Payment Orders -->
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="font-size: 1.15rem; color:#d97706;"><i class="fa-solid fa-clock"></i> Pending Payment Verification</h3>
                    <a href="enrollments.php?status=pending" style="font-size:0.85rem;">View All &rarr;</a>
                </div>

                <?php if (empty($recentPending)): ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">No pending payment verifications.</p>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-color); text-align: left; color: var(--text-muted);">
                                <th style="padding: 8px 0;">Student</th>
                                <th>Course</th>
                                <th>Price</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentPending as $rp): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 10px 0; font-weight: 600;"><?php echo htmlspecialchars($rp['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($rp['course_title']); ?></td>
                                    <td><strong>$<?php echo number_format($rp['course_price'], 2); ?></strong></td>
                                    <td style="text-align: right;">
                                        <a href="enrollments.php?action=approve&id=<?php echo $rp['id']; ?>" class="btn btn-sm btn-accent" onclick="return confirm('Approve payment access?');">Approve</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Recent Course Enrollments -->
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px;">
                <h3 style="font-size: 1.15rem; margin-bottom: 16px;"><i class="fa-solid fa-graduation-cap"></i> All Course Enrollments</h3>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border-color); text-align: left; color: var(--text-muted);">
                            <th style="padding: 8px 0;">Student</th>
                            <th>Course</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentEnrollments as $re): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 10px 0; font-weight: 600;"><?php echo htmlspecialchars($re['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($re['course_title']); ?></td>
                                <td>
                                    <?php if ($re['payment_status'] === 'approved'): ?>
                                        <span style="background:#ecfdf5; color:#059669; font-size:0.75rem; font-weight:700; padding:2px 6px; border-radius:4px;">APPROVED</span>
                                    <?php else: ?>
                                        <span style="background:#fffbe0; color:#d97706; font-size:0.75rem; font-weight:700; padding:2px 6px; border-radius:4px;">PENDING</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
