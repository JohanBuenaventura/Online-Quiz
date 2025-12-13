<?php
session_start();
require_once __DIR__ . '/../../classes/Session.php';
require_once __DIR__ . '/../../classes/Question.php';
require_once __DIR__ . '/../../classes/Quiz.php';

$sessionModel = new Session();
$questionModel = new Question();
$quizModel = new Quiz();

if (empty($_GET['code'])) exit('Missing code');
$code = trim($_GET['code']);
$s = $sessionModel->getSessionByCode($code);

// Basic Validation
if (!$s || !$s['is_live']) {
    // You might want to design a nicer "Game Over" or "Not Found" page later
    exit('<div style="font-family:sans-serif;text-align:center;padding:50px;">Session not found or has ended.</div>');
}

$current_q = null;
if (!empty($s['current_question_id'])) {
    $current_q = $questionModel->getQuestionById($s['current_question_id']);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playing: <?= htmlspecialchars($quizModel->getQuizById($s['quiz_id'])['title']) ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/play.css">
</head>
<body>

    <div class="game-header">
        <div class="quiz-title">
            <i class="fas fa-book-open"></i> 
            <span id="quiz_title"><?= htmlspecialchars($quizModel->getQuizById($s['quiz_id'])['title']) ?></span>
        </div>
        <div class="session-badge">
            CODE: <strong id="session_code"><?= htmlspecialchars($s['session_code']) ?></strong>
        </div>
    </div>

    <div id="card" class="game-card">
        
        <input type="hidden" id="session_id" value="<?= (int)$s['id'] ?>">

        <div id="question_container">
            <div class="question-text" id="question_text">
                <div class="waiting-state">
                    <div class="pulse-ring"><i class="fas fa-hourglass-half"></i></div>
                    <h3>You're in!</h3>
                    <p>Waiting for the teacher to start...</p>
                </div>
            </div>
            
            <div class="choices-grid" id="choices">
                </div>
        </div>

        <div class="metadata">
            <div class="timer" id="timer_display">
                <i class="fas fa-clock"></i> <span>Live</span>
            </div>
            <div class="hint">
                <i class="fas fa-wifi"></i> Connected as <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Guest') ?></strong>
            </div>
        </div>

        <div class="leaderboard-section" id="leaderboard">
        <div class="lb-header">
            <div class="lb-title"><i class="fas fa-trophy"></i> Live Rankings</div>
            <div style="font-size:0.9rem; opacity:0.8;">Top Players</div>
        </div>
        <div id="leaderboard_list">
            <!-- List populated via JS -->
            <div style="text-align:center; color:rgba(255,255,255,0.5); padding:20px;">
                Waiting for scores...
            </div>
        </div>
    </div>

    </div>

    <script src="../../assets/js/session.js"></script>

</body>
</html>