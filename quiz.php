<?php
// quiz.php - Interactive Quiz & Assessment System
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$db = getDBConnection();
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Fetch Quiz
if ($quiz_id) {
    $stmt = $db->prepare("SELECT * FROM quizzes WHERE id = ?");
    $stmt->execute([$quiz_id]);
} else if ($course_id) {
    $stmt = $db->prepare("SELECT * FROM quizzes WHERE course_id = ? LIMIT 1");
    $stmt->execute([$course_id]);
} else {
    redirect('courses.php');
}

$quiz = $stmt->fetch();
if (!$quiz) {
    redirect('dashboard.php');
}

// Fetch Questions
$stmtQ = $db->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC");
$stmtQ->execute([$quiz['id']]);
$questions = $stmtQ->fetchAll();

$submitted = false;
$score = 0;
$totalQuestions = count($questions);
$percentage = 0;
$passed = false;
$userAnswers = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $submitted = true;
    $userAnswers = $_POST['answers'] ?? [];

    foreach ($questions as $q) {
        $qId = $q['id'];
        $correct = $q['correct_option'];
        if (isset($userAnswers[$qId]) && strtoupper($userAnswers[$qId]) === strtoupper($correct)) {
            $score++;
        }
    }

    if ($totalQuestions > 0) {
        $percentage = round(($score / $totalQuestions) * 100, 2);
    }
    $passed = $percentage >= $quiz['passing_score'];

    // Save Quiz Attempt to DB
    $stmtSave = $db->prepare("
        INSERT INTO quiz_attempts (user_id, quiz_id, score, total_questions, percentage, passed)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmtSave->execute([$_SESSION['user_id'], $quiz['id'], $score, $totalQuestions, $percentage, $passed ? 1 : 0]);
}

$page_title = $quiz['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="py-section">
    <div class="container" style="max-width: 840px;">
        <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 30px; margin-bottom: 30px; box-shadow: var(--shadow-sm);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 style="font-size: 1.75rem; margin-bottom: 4px;"><?php echo htmlspecialchars($quiz['title']); ?></h1>
                    <p style="color: var(--text-muted); font-size: 0.9rem;"><?php echo htmlspecialchars($quiz['description']); ?></p>
                </div>
                <?php if (!$submitted): ?>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="background: rgba(37, 99, 235, 0.1); color: var(--primary-blue); padding: 8px 16px; border-radius: 20px; font-weight: 700;">
                            <i class="fa-solid fa-clock"></i> Time Left: <span id="quizTimer" data-duration="<?php echo $quiz['duration_minutes']; ?>">--:--</span>
                        </div>
                        <button type="button" id="exitQuizBtn" class="btn btn-sm btn-outline" style="color:#ef4444; border-color:#fca5a5;">
                            <i class="fa-solid fa-right-from-bracket"></i> Exit Quiz
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($submitted): ?>
                <!-- Result Summary Banner -->
                <div style="padding: 24px; border-radius: var(--radius-md); margin-bottom: 30px; text-align: center; <?php echo $passed ? 'background:#ecfdf5; border:1px solid #10b981; color:#065f46;' : 'background:#fef2f2; border:1px solid #ef4444; color:#991b1b;'; ?>">
                    <i class="<?php echo $passed ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark'; ?>" style="font-size: 3rem; margin-bottom: 12px;"></i>
                    <h2><?php echo $passed ? 'Quiz Passed! Excellent Job!' : 'Quiz Attempt Complete'; ?></h2>
                    <p style="font-size: 1.25rem; font-weight: 700; margin: 8px 0;">Your Score: <?php echo $score; ?> / <?php echo $totalQuestions; ?> (<?php echo $percentage; ?>%)</p>
                    <p style="font-size: 0.9rem;">Passing score threshold: <?php echo $quiz['passing_score']; ?>%</p>
                    <div style="margin-top: 20px;">
                        <a href="dashboard.php" class="btn btn-primary btn-sm">Go to Dashboard</a>
                        <a href="quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-outline btn-sm">Retake Quiz</a>
                    </div>
                </div>

                <!-- Answer Breakdown & Explanations -->
                <h3 style="margin-bottom: 20px;">Review Answers & Explanations</h3>
                <?php foreach ($questions as $idx => $q): ?>
                    <?php 
                        $userAns = $userAnswers[$q['id']] ?? 'None';
                        $isCorrect = strtoupper($userAns) === strtoupper($q['correct_option']);
                    ?>
                    <div class="quiz-card" style="border-left: 4px solid <?php echo $isCorrect ? '#10b981' : '#ef4444'; ?>;">
                        <div style="font-weight: 700; margin-bottom: 12px;">
                            Question <?php echo $idx + 1; ?>: <?php echo htmlspecialchars($q['question_text']); ?>
                        </div>
                        <div style="font-size: 0.9rem; margin-bottom: 6px;">Your Answer: <strong style="color:<?php echo $isCorrect ? '#10b981' : '#ef4444'; ?>;"><?php echo htmlspecialchars($userAns); ?></strong></div>
                        <div style="font-size: 0.9rem; margin-bottom: 12px;">Correct Answer: <strong><?php echo htmlspecialchars($q['correct_option']); ?></strong></div>
                        <?php if ($q['explanation']): ?>
                            <div style="background: #f8fafc; padding: 12px; border-radius: var(--radius-sm); font-size: 0.85rem; color: var(--text-muted);">
                                <strong>Explanation:</strong> <?php echo htmlspecialchars($q['explanation']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Quiz Form -->
                <form id="quizForm" action="quiz.php?id=<?php echo $quiz['id']; ?>" method="POST">
                    <input type="hidden" name="submit_quiz" value="1">
                    
                    <?php foreach ($questions as $idx => $q): ?>
                        <div class="quiz-card">
                            <h4 style="font-size: 1.1rem; margin-bottom: 16px;">
                                <span style="color: var(--primary-blue);">Q<?php echo $idx + 1; ?>:</span> <?php echo htmlspecialchars($q['question_text']); ?>
                            </h4>

                            <label class="quiz-option">
                                <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="A" required>
                                <strong>A.</strong> <?php echo htmlspecialchars($q['option_a']); ?>
                            </label>

                            <label class="quiz-option">
                                <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="B">
                                <strong>B.</strong> <?php echo htmlspecialchars($q['option_b']); ?>
                            </label>

                            <label class="quiz-option">
                                <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="C">
                                <strong>C.</strong> <?php echo htmlspecialchars($q['option_c']); ?>
                            </label>

                            <label class="quiz-option">
                                <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="D">
                                <strong>D.</strong> <?php echo htmlspecialchars($q['option_d']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" class="btn btn-primary btn-accent" style="width: 100%; padding: 14px; font-size: 1.1rem;">
                        <i class="fa-solid fa-paper-plane"></i> Submit Answers & View Score
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Custom Exit Quiz On-Screen Warning Modal -->
<div id="customQuizExitModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(15,23,42,0.7); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:#ffffff; border-radius:12px; padding:30px; max-width:440px; text-align:center; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2); border:1px solid #e2e8f0; animation: modalPop 0.2s ease;">
        <div style="width:60px; height:60px; border-radius:50%; background:#fee2e2; color:#ef4444; font-size:1.8rem; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 style="font-size:1.35rem; margin-bottom:8px; color:#0f172a;">Exit Active Quiz?</h3>
        <p style="color:#64748b; font-size:0.95rem; margin-bottom:24px;">Are you sure you want to leave? Your selected answers and quiz progress will be reset.</p>
        <div style="display:flex; gap:12px;">
            <button type="button" id="confirmStayQuizBtn" class="btn btn-primary" style="flex:1;">Continue Quiz</button>
            <button type="button" id="confirmExitQuizBtn" class="btn btn-outline" style="flex:1; color:#ef4444; border-color:#fca5a5;">Yes, Exit Quiz</button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
