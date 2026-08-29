<?php
// admin/quizzes.php - Quiz & Question Bank Manager
$page_title = "Manage Quizzes";
require_once __DIR__ . '/includes/admin_header.php';

$db = getDBConnection();
$error = '';

// ─── Handle Delete Quiz ────────────────────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    $db->prepare("DELETE FROM quizzes WHERE id = ?")->execute([$delId]);
    set_flash('success', 'Quiz deleted successfully.');
    redirect('admin/quizzes.php');
}

// ─── Handle Delete Question ────────────────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'delete_question' && isset($_GET['qid'])) {
    $delQId = (int)$_GET['qid'];
    $quiz_back = (int)$_GET['quiz_id'];
    $db->prepare("DELETE FROM questions WHERE id = ?")->execute([$delQId]);
    set_flash('success', 'Question deleted.');
    redirect("admin/quizzes.php?action=edit&id=$quiz_back");
}

// ─── Handle Add Question to Existing Quiz ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $quiz_id   = (int)$_POST['quiz_id'];
    $q_text    = sanitize($_POST['q_text']);
    $opt_a     = sanitize($_POST['opt_a']);
    $opt_b     = sanitize($_POST['opt_b']);
    $opt_c     = sanitize($_POST['opt_c']);
    $opt_d     = sanitize($_POST['opt_d']);
    $correct   = sanitize($_POST['correct_option']);
    $expl      = sanitize($_POST['explanation'] ?? '');

    if (!empty($q_text) && $quiz_id > 0) {
        $db->prepare("
            INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([$quiz_id, $q_text, $opt_a, $opt_b, $opt_c, $opt_d, $correct, $expl]);
        set_flash('success', 'Question added successfully!');
    }
    redirect("admin/quizzes.php?action=edit&id=$quiz_id");
}

// ─── Handle Create New Quiz ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_quiz'])) {
    $title       = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $course_id   = (int)$_POST['course_id'];
    $duration    = (int)$_POST['duration_minutes'];
    $passing     = (int)$_POST['passing_score'];

    if (empty($title)) {
        $error = "Quiz title is required.";
    } else {
        $stmt = $db->prepare("INSERT INTO quizzes (course_id, title, description, duration_minutes, passing_score) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$course_id > 0 ? $course_id : null, $title, $description, $duration, $passing]);
        $newQuizId = $db->lastInsertId();
        set_flash('success', 'Quiz created! Now add questions below.');
        redirect("admin/quizzes.php?action=edit&id=$newQuizId");
    }
}

// Fetch all quizzes list
$quizzes = $db->query("
    SELECT q.*, c.title AS course_title,
    (SELECT COUNT(id) FROM questions WHERE quiz_id = q.id) AS question_count
    FROM quizzes q
    LEFT JOIN courses c ON q.course_id = c.id
    ORDER BY q.created_at DESC
")->fetchAll();

$courses = $db->query("SELECT id, title FROM courses ORDER BY title ASC")->fetchAll();

$action           = $_GET['action'] ?? '';
$editQuizId       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$preselectedCourse = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$editQuiz     = null;
$editQuestions = [];

// Fetch quiz being edited
if ($action === 'edit' && $editQuizId > 0) {
    $stmtEQ = $db->prepare("SELECT q.*, c.title AS course_title FROM quizzes q LEFT JOIN courses c ON q.course_id = c.id WHERE q.id = ?");
    $stmtEQ->execute([$editQuizId]);
    $editQuiz = $stmtEQ->fetch();

    $stmtQs = $db->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC");
    $stmtQs->execute([$editQuizId]);
    $editQuestions = $stmtQs->fetchAll();
}
?>

<div class="py-section">
    <div class="container">

        <?php render_flash('success'); render_flash('error'); ?>

        <?php if ($action === 'edit' && $editQuiz): ?>
            <!-- ════════════════════════════════════════════════════════════ -->
            <!-- QUIZ EDITOR: Add questions to an existing quiz              -->
            <!-- ════════════════════════════════════════════════════════════ -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
                <div>
                    <a href="quizzes.php" style="color:var(--text-muted); font-size:0.9rem;">&larr; Back to All Quizzes</a>
                    <h1 class="section-title" style="font-size:1.75rem; margin-top:4px;">
                        Quiz Editor: <?php echo htmlspecialchars($editQuiz['title']); ?>
                    </h1>
                    <p style="color:var(--text-muted); margin-top:2px;">
                        <?php echo $editQuiz['course_title'] ? 'Linked to: <strong>' . htmlspecialchars($editQuiz['course_title']) . '</strong>' : '<span style="color:#d97706;">Standalone Quiz (not linked to any course)</span>'; ?>
                        &nbsp;|&nbsp; <?php echo count($editQuestions); ?> question(s) &nbsp;|&nbsp; 
                        <?php echo $editQuiz['duration_minutes']; ?> mins &nbsp;|&nbsp; 
                        Pass: <?php echo $editQuiz['passing_score']; ?>%
                    </p>
                </div>
                <a href="<?php echo BASE_URL; ?>quiz.php?id=<?php echo $editQuiz['id']; ?>" class="btn btn-outline btn-sm" target="_blank">
                    <i class="fa-solid fa-eye"></i> Preview Quiz
                </a>
            </div>

            <div style="display:grid; grid-template-columns:1fr 380px; gap:30px;">
                <!-- Left: Existing Questions List -->
                <div>
                    <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:24px; box-shadow:var(--shadow-sm);">
                        <h2 style="font-size:1.15rem; margin-bottom:18px;"><i class="fa-solid fa-list-ol"></i> Question Bank (<?php echo count($editQuestions); ?> questions)</h2>

                        <?php if (empty($editQuestions)): ?>
                            <div style="text-align:center; padding:30px 20px; color:var(--text-muted);">
                                <i class="fa-solid fa-circle-question" style="font-size:2rem; margin-bottom:10px; display:block;"></i>
                                No questions yet. Use the form on the right to add your first question.
                            </div>
                        <?php else: ?>
                            <?php foreach ($editQuestions as $idx => $q): ?>
                                <div style="background:#f8fafc; border:1px solid var(--border-color); border-radius:var(--radius-sm); padding:16px; margin-bottom:14px; position:relative;">
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
                                        <div style="flex:1;">
                                            <div style="font-weight:700; color:var(--primary-blue); font-size:0.8rem; margin-bottom:6px;">QUESTION <?php echo $idx + 1; ?></div>
                                            <p style="font-weight:600; margin-bottom:12px;"><?php echo htmlspecialchars($q['question_text']); ?></p>
                                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; font-size:0.85rem; margin-bottom:10px;">
                                                <span style="<?php echo strtoupper($q['correct_option']) === 'A' ? 'color:var(--accent-emerald); font-weight:700;' : 'color:var(--text-muted);'; ?>">A. <?php echo htmlspecialchars($q['option_a']); ?></span>
                                                <span style="<?php echo strtoupper($q['correct_option']) === 'B' ? 'color:var(--accent-emerald); font-weight:700;' : 'color:var(--text-muted);'; ?>">B. <?php echo htmlspecialchars($q['option_b']); ?></span>
                                                <span style="<?php echo strtoupper($q['correct_option']) === 'C' ? 'color:var(--accent-emerald); font-weight:700;' : 'color:var(--text-muted);'; ?>">C. <?php echo htmlspecialchars($q['option_c']); ?></span>
                                                <span style="<?php echo strtoupper($q['correct_option']) === 'D' ? 'color:var(--accent-emerald); font-weight:700;' : 'color:var(--text-muted);'; ?>">D. <?php echo htmlspecialchars($q['option_d']); ?></span>
                                            </div>
                                            <?php if ($q['explanation']): ?>
                                                <div style="font-size:0.8rem; color:var(--text-muted);"><i class="fa-solid fa-lightbulb" style="color:#f59e0b;"></i> <?php echo htmlspecialchars($q['explanation']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <a href="quizzes.php?action=delete_question&qid=<?php echo $q['id']; ?>&quiz_id=<?php echo $editQuiz['id']; ?>" style="color:#ef4444; font-size:0.8rem; white-space:nowrap;" onclick="return confirm('Delete this question?');">
                                            <i class="fa-solid fa-trash"></i> Remove
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right: Add New Question Form -->
                <div>
                    <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:24px; box-shadow:var(--shadow-sm); position:sticky; top:90px;">
                        <h3 style="font-size:1.1rem; margin-bottom:16px; color:var(--primary-blue);"><i class="fa-solid fa-circle-plus"></i> Add New Question</h3>

                        <form action="quizzes.php" method="POST">
                            <input type="hidden" name="add_question" value="1">
                            <input type="hidden" name="quiz_id" value="<?php echo $editQuiz['id']; ?>">

                            <div style="margin-bottom:12px;">
                                <label style="display:block; font-weight:600; font-size:0.85rem; margin-bottom:4px;">Question Text</label>
                                <textarea name="q_text" required rows="3" placeholder="e.g. Which CSS property controls spacing inside an element?" style="width:100%; padding:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm); font-family:inherit; resize:vertical;"></textarea>
                            </div>

                            <div style="margin-bottom:12px;">
                                <label style="display:block; font-weight:600; font-size:0.85rem; margin-bottom:4px;">Option A</label>
                                <input type="text" name="opt_a" required placeholder="Option A text" style="width:100%; padding:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm);">
                            </div>
                            <div style="margin-bottom:12px;">
                                <label style="display:block; font-weight:600; font-size:0.85rem; margin-bottom:4px;">Option B</label>
                                <input type="text" name="opt_b" required placeholder="Option B text" style="width:100%; padding:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm);">
                            </div>
                            <div style="margin-bottom:12px;">
                                <label style="display:block; font-weight:600; font-size:0.85rem; margin-bottom:4px;">Option C</label>
                                <input type="text" name="opt_c" required placeholder="Option C text" style="width:100%; padding:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm);">
                            </div>
                            <div style="margin-bottom:12px;">
                                <label style="display:block; font-weight:600; font-size:0.85rem; margin-bottom:4px;">Option D</label>
                                <input type="text" name="opt_d" required placeholder="Option D text" style="width:100%; padding:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm);">
                            </div>

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                                <div>
                                    <label style="display:block; font-weight:600; font-size:0.85rem; margin-bottom:4px;">Correct Answer</label>
                                    <select name="correct_option" style="width:100%; padding:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm);">
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="display:block; font-weight:600; font-size:0.85rem; margin-bottom:4px;">Explanation</label>
                                    <input type="text" name="explanation" placeholder="Brief reason..." style="width:100%; padding:8px; border:1px solid var(--border-color); border-radius:var(--radius-sm);">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width:100%;"><i class="fa-solid fa-plus"></i> Add Question</button>
                        </form>
                    </div>
                </div>
            </div>

        <?php elseif ($action === 'create'): ?>
            <!-- ════════════════════════════════════════════════════════════ -->
            <!-- CREATE NEW QUIZ FORM                                        -->
            <!-- ════════════════════════════════════════════════════════════ -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <div>
                    <a href="quizzes.php" style="color:var(--text-muted); font-size:0.9rem;">&larr; Cancel</a>
                    <h1 class="section-title" style="font-size:1.75rem; margin-top:4px;">Create New Quiz</h1>
                </div>
            </div>

            <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:30px; max-width:700px; box-shadow:var(--shadow-sm);">
                <form action="quizzes.php" method="POST">
                    <input type="hidden" name="create_quiz" value="1">

                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-weight:600; font-size:0.9rem; margin-bottom:6px;">Quiz Title *</label>
                        <input type="text" name="title" required placeholder="e.g. JavaScript ES6 Knowledge Check" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm);">
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-weight:600; font-size:0.9rem; margin-bottom:6px;">Description</label>
                        <input type="text" name="description" placeholder="Brief description of what this quiz evaluates." style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm);">
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-weight:600; font-size:0.9rem; margin-bottom:6px;">Link to Course <span style="color:var(--text-muted); font-weight:400;">(optional — leave as "Standalone" for a general quiz)</span></label>
                        <select name="course_id" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm);">
                            <option value="0" <?php echo $preselectedCourse === 0 ? 'selected' : ''; ?>>⚡ Standalone Quiz (not linked to a course)</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo $preselectedCourse === (int)$c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px;">
                        <div>
                            <label style="display:block; font-weight:600; font-size:0.9rem; margin-bottom:6px;">Timer Duration (Minutes)</label>
                            <input type="number" name="duration_minutes" value="15" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm);">
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; font-size:0.9rem; margin-bottom:6px;">Passing Score (%)</label>
                            <input type="number" name="passing_score" value="70" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm);">
                        </div>
                    </div>

                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:20px; background:#f8fafc; padding:10px 14px; border-radius:var(--radius-sm); border:1px solid var(--border-color);">
                        <i class="fa-solid fa-circle-info" style="color:var(--primary-blue);"></i>
                        After creating the quiz, you will be taken to the <strong>Quiz Editor</strong> where you can add as many questions as you like.
                    </p>

                    <div style="display:flex; gap:12px;">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Create Quiz & Add Questions</button>
                        <a href="quizzes.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <!-- ════════════════════════════════════════════════════════════ -->
            <!-- QUIZZES LIST TABLE                                          -->
            <!-- ════════════════════════════════════════════════════════════ -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap:wrap; gap:16px;">
                <div>
                    <h1 class="section-title" style="font-size:1.75rem;">Quiz & Assessment Manager</h1>
                    <p style="color:var(--text-muted);">Create standalone quizzes or course-linked assessments with full question banks.</p>
                </div>
                <a href="quizzes.php?action=create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Create New Quiz</a>
            </div>

            <?php render_flash('success'); ?>

            <div style="background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:24px; box-shadow:var(--shadow-sm);">
                <?php if (empty($quizzes)): ?>
                    <div style="text-align:center; padding:40px 20px; color:var(--text-muted);">
                        <i class="fa-solid fa-file-pen" style="font-size:2.5rem; margin-bottom:12px; display:block;"></i>
                        <h3>No quizzes created yet.</h3>
                        <a href="quizzes.php?action=create" class="btn btn-primary" style="margin-top:16px;">Create Your First Quiz</a>
                    </div>
                <?php else: ?>
                    <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border-color); text-align:left; color:var(--text-muted);">
                                <th style="padding:10px 0;">Quiz Title</th>
                                <th>Associated Course</th>
                                <th>Questions</th>
                                <th>Duration</th>
                                <th>Pass Score</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quizzes as $q): ?>
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:12px 0; font-weight:600;"><?php echo htmlspecialchars($q['title']); ?></td>
                                    <td>
                                        <?php if ($q['course_title']): ?>
                                            <span style="background:rgba(37,99,235,0.08); color:var(--primary-blue); padding:2px 8px; border-radius:4px; font-size:0.8rem;"><?php echo htmlspecialchars($q['course_title']); ?></span>
                                        <?php else: ?>
                                            <span style="background:#f1f5f9; color:var(--text-muted); padding:2px 8px; border-radius:4px; font-size:0.8rem;">Standalone</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="font-weight:700; <?php echo $q['question_count'] == 0 ? 'color:#ef4444;' : 'color:var(--accent-emerald);'; ?>">
                                            <?php echo $q['question_count']; ?> question<?php echo $q['question_count'] != 1 ? 's' : ''; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $q['duration_minutes']; ?> mins</td>
                                    <td><?php echo $q['passing_score']; ?>%</td>
                                    <td style="text-align:right;">
                                        <a href="quizzes.php?action=edit&id=<?php echo $q['id']; ?>" class="btn btn-sm btn-primary" style="margin-right:4px;"><i class="fa-solid fa-pen-to-square"></i> Edit Questions</a>
                                        <a href="<?php echo BASE_URL; ?>quiz.php?id=<?php echo $q['id']; ?>" class="btn btn-sm btn-outline" target="_blank" style="margin-right:4px;"><i class="fa-solid fa-eye"></i> Preview</a>
                                        <a href="quizzes.php?action=delete&id=<?php echo $q['id']; ?>" class="btn btn-sm btn-outline" style="color:#ef4444;" onclick="return confirm('Permanently delete this quiz and all its questions?');"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
