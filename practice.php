<?php
// practice.php - Practice Center & Mock Tests Directory
$page_title = "Practice Center & Quizzes";
require_once __DIR__ . '/includes/header.php';

$db = getDBConnection();

$quizzes = [];
try {
    $stmt = $db->query("
        SELECT q.*, c.title AS course_title, 
        (SELECT COUNT(id) FROM questions WHERE quiz_id = q.id) AS total_questions 
        FROM quizzes q 
        LEFT JOIN courses c ON q.course_id = c.id 
        ORDER BY q.created_at DESC
    ");
    $quizzes = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<div class="py-section">
    <div class="container">
        <div style="margin-bottom: 40px;">
            <h1 class="section-title">Practice Center & Assessments</h1>
            <p class="section-subtitle">Test your knowledge with topic-wise multiple choice quizzes and timed exams.</p>
        </div>

        <div class="courses-grid">
            <?php foreach ($quizzes as $q): ?>
                <div class="quiz-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <span class="badge" style="background:rgba(37,99,235,0.1); color:var(--primary-blue); font-weight:700; font-size:0.75rem; padding:4px 8px; border-radius:4px;">
                            <?php echo $q['course_title'] ? htmlspecialchars($q['course_title']) : 'General Quiz'; ?>
                        </span>
                        <span style="font-size:0.85rem; color:var(--text-muted);"><i class="fa-regular fa-clock"></i> <?php echo $q['duration_minutes']; ?> mins</span>
                    </div>
                    <h3 style="font-size: 1.2rem; margin-bottom: 8px;"><?php echo htmlspecialchars($q['title']); ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px; flex: 1;"><?php echo htmlspecialchars($q['description']); ?></p>
                    
                    <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:16px;">
                        <span style="font-size:0.85rem; color:var(--text-muted);"><i class="fa-solid fa-circle-question"></i> <?php echo $q['total_questions']; ?> Questions</span>
                        <a href="quiz.php?id=<?php echo $q['id']; ?>" class="btn btn-sm btn-primary"><i class="fa-solid fa-play"></i> Start Quiz</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
