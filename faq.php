<?php
// faq.php - Frequently Asked Questions
$page_title = "Frequently Asked Questions";
require_once __DIR__ . '/includes/header.php';
?>

<div class="py-section">
    <div class="container" style="max-width: 800px;">
        <h1 class="section-title">Frequently Asked Questions</h1>
        <p class="section-subtitle">Common questions regarding courses, certificates, and student accounts.</p>

        <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 30px;">
            <div style="margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
                <h3 style="font-size: 1.15rem; color: var(--primary-blue); margin-bottom: 8px;">Q: Are the courses free to enroll?</h3>
                <p style="color: var(--text-main);">A: Yes! Study Point Academy offers free foundational courses and downloadable study guides in HTML, CSS, JavaScript, and Web Development.</p>
            </div>

            <div style="margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
                <h3 style="font-size: 1.15rem; color: var(--primary-blue); margin-bottom: 8px;">Q: How do I earn a verified certificate?</h3>
                <p style="color: var(--text-main);">A: Once you enroll in a course, complete 100% of the lessons, and pass the final course quiz, a verified completion certificate with a unique verification code will automatically be issued to your account.</p>
            </div>

            <div style="margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
                <h3 style="font-size: 1.15rem; color: var(--primary-blue); margin-bottom: 8px;">Q: Can employers verify my certificate?</h3>
                <p style="color: var(--text-main);">A: Yes, anyone can verify your credential by visiting our <a href="certificates.php">Public Verification Portal</a> and entering your unique certificate code.</p>
            </div>

            <div>
                <h3 style="font-size: 1.15rem; color: var(--primary-blue); margin-bottom: 8px;">Q: Is learning self-paced?</h3>
                <p style="color: var(--text-main);">A: Absolutely. You can learn at your own speed, track completed lessons, and resume learning anytime from your student dashboard.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
