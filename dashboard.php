<?php
// dashboard.php - Student LMS Dashboard
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$page_title = "Student LMS Dashboard";
$user = get_current_user_data();
$user_id = $user['id'];
$db = getDBConnection();

// Fetch Enrolled Courses with progress and payment_status
$stmtCourses = $db->prepare("
    SELECT c.*, e.enrolled_at, e.payment_status, cat.name AS category_name
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN categories cat ON c.category_id = cat.id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmtCourses->execute([$user_id]);
$enrolledCourses = $stmtCourses->fetchAll();

// Count Metrics
$totalEnrolled = count($enrolledCourses);
$completedCoursesCount = 0;
foreach ($enrolledCourses as &$ec) {
    $ec['progress'] = get_course_progress($user_id, $ec['id']);
    if ($ec['progress'] >= 100 && $ec['payment_status'] === 'approved') {
        $completedCoursesCount++;
    }
}
unset($ec);

// Fetch Quiz Attempts
$stmtQuiz = $db->prepare("
    SELECT qa.*, q.title AS quiz_title
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    WHERE qa.user_id = ?
    ORDER BY qa.attempted_at DESC LIMIT 5
");
$stmtQuiz->execute([$user_id]);
$quizAttempts = $stmtQuiz->fetchAll();

// Fetch Certificates
$stmtCerts = $db->prepare("
    SELECT cert.*, c.title AS course_title
    FROM certificates cert
    JOIN courses c ON cert.course_id = c.id
    WHERE cert.user_id = ?
    ORDER BY cert.issued_at DESC
");
$stmtCerts->execute([$user_id]);
$certificates = $stmtCerts->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); color: #ffffff; padding: 40px 0;">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div>
                <h1 style="color: #ffffff; font-size: 2rem; margin-bottom: 8px;">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                <p style="color: #94a3b8;">Track your course progress, upcoming quizzes, and verified certificates.</p>
            </div>
            <a href="courses.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Browse New Courses</a>
        </div>
    </div>
</div>

<div class="py-section">
    <div class="container">
        <?php render_flash('success'); ?>
        <?php render_flash('error'); ?>

        <!-- Stats Overview Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; display: flex; align-items: center; gap: 20px;">
                <div style="width: 54px; height: 54px; border-radius: 12px; background: rgba(37, 99, 235, 0.1); color: var(--primary-blue); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.75rem; margin-bottom: 2px;"><?php echo $totalEnrolled; ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Enrolled Courses</p>
                </div>
            </div>

            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; display: flex; align-items: center; gap: 20px;">
                <div style="width: 54px; height: 54px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: var(--accent-emerald); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.75rem; margin-bottom: 2px;"><?php echo $completedCoursesCount; ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Completed Courses</p>
                </div>
            </div>

            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; display: flex; align-items: center; gap: 20px;">
                <div style="width: 54px; height: 54px; border-radius: 12px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.75rem; margin-bottom: 2px;"><?php echo count($quizAttempts); ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Quiz Attempts</p>
                </div>
            </div>

            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; display: flex; align-items: center; gap: 20px;">
                <div style="width: 54px; height: 54px; border-radius: 12px; background: rgba(6, 182, 212, 0.1); color: var(--accent-cyan); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fa-solid fa-award"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.75rem; margin-bottom: 2px;"><?php echo count($certificates); ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Certificates Earned</p>
                </div>
            </div>
        </div>

        <!-- Enrolled Courses Grid -->
        <div style="margin-bottom: 50px;">
            <h2 style="font-size: 1.5rem; margin-bottom: 20px;"><i class="fa-solid fa-graduation-cap"></i> My Active Courses</h2>
            <?php if (empty($enrolledCourses)): ?>
                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 40px; text-align: center;">
                    <i class="fa-solid fa-folder-plus" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 12px;"></i>
                    <h3>You are not enrolled in any courses yet</h3>
                    <p style="color: var(--text-muted); margin-bottom: 20px;">Browse our catalog and start learning today!</p>
                    <a href="courses.php" class="btn btn-primary">Browse Courses</a>
                </div>
            <?php else: ?>
                <div class="courses-grid">
                    <?php foreach ($enrolledCourses as $course): ?>
                        <div class="course-card">
                            <div class="course-content">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <span class="badge" style="background:rgba(37,99,235,0.1); color:var(--primary-blue); font-weight:700; font-size:0.75rem; padding:4px 8px; border-radius:4px;">
                                        <?php echo htmlspecialchars($course['category_name']); ?>
                                    </span>

                                    <!-- Status Badge -->
                                    <?php if ($course['payment_status'] === 'pending'): ?>
                                        <span style="background:#fffbe0; color:#d97706; border:1px solid #fef3c7; font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:12px;">
                                            <i class="fa-solid fa-clock"></i> Pending Admin
                                        </span>
                                    <?php else: ?>
                                        <span style="background:#ecfdf5; color:#059669; font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:12px;">
                                            <i class="fa-solid fa-circle-check"></i> Access Approved
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                                
                                <div style="margin-top: auto; padding-top: 16px;">
                                    <div style="display:flex; justify-content:space-between; font-size:0.85rem; margin-bottom:6px;">
                                        <span>Course Completion</span>
                                        <span style="font-weight:700; color:var(--primary-blue);"><?php echo $course['progress']; ?>%</span>
                                    </div>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: <?php echo $course['progress']; ?>%;"></div>
                                    </div>
                                    
                                    <?php if ($course['payment_status'] === 'approved'): ?>
                                        <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary btn-sm" style="width: 100%; margin-top: 14px;">
                                            <i class="fa-solid fa-play"></i> Launch Course & Lessons
                                        </a>
                                    <?php else: ?>
                                        <div style="width: 100%; margin-top: 14px; background: #fffbe0; border: 1px solid #fef3c7; color: #b45309; padding: 8px 12px; border-radius: var(--radius-sm); font-size: 0.85rem; text-align: center; font-weight: 600;">
                                            <i class="fa-solid fa-lock"></i> Payment Pending Admin Verification
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Certificates & Quiz Performance Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
            <!-- Quiz Attempts -->
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 30px;">
                <h3 style="font-size: 1.25rem; margin-bottom: 20px;"><i class="fa-solid fa-list-check"></i> Recent Quiz Results</h3>
                <?php if (empty($quizAttempts)): ?>
                    <p style="color: var(--text-muted);">No quiz attempts recorded yet.</p>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-color); text-align: left; color: var(--text-muted);">
                                <th style="padding: 10px 0;">Quiz Title</th>
                                <th>Score</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quizAttempts as $qa): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 0; font-weight: 600;"><?php echo htmlspecialchars($qa['quiz_title']); ?></td>
                                    <td><?php echo $qa['score']; ?> / <?php echo $qa['total_questions']; ?> (<?php echo $qa['percentage']; ?>%)</td>
                                    <td>
                                        <?php if ($qa['passed']): ?>
                                            <span style="background:#d1fae5; color:#065f46; font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:12px;">PASSED</span>
                                        <?php else: ?>
                                            <span style="background:#fee2e2; color:#991b1b; font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:12px;">FAILED</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Certificates -->
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 30px;">
                <h3 style="font-size: 1.25rem; margin-bottom: 20px;"><i class="fa-solid fa-award"></i> Verified Certificates</h3>
                <?php if (empty($certificates)): ?>
                    <p style="color: var(--text-muted);">Complete 100% of any course lessons to earn your verified certificate.</p>
                <?php else: ?>
                    <ul style="list-style: none;">
                        <?php foreach ($certificates as $cert): ?>
                            <li style="padding: 12px 0; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h4 style="font-size: 0.95rem; margin-bottom: 2px;"><?php echo htmlspecialchars($cert['course_title']); ?></h4>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">Code: <code><?php echo htmlspecialchars($cert['certificate_code']); ?></code></span>
                                </div>
                                <a href="certificates.php?code=<?php echo htmlspecialchars($cert['certificate_code']); ?>" class="btn btn-sm btn-outline"><i class="fa-solid fa-certificate"></i> View</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
