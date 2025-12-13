<?php
session_start();
require_once __DIR__ . '/../../classes/Session.php';
require_once __DIR__ . '/../../classes/Question.php';
require_once __DIR__ . '/../../classes/Quiz.php';

$sessionModel = new Session();
$questionModel = new Question();
$quizModel = new Quiz();

$errors = [];
$code = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim(htmlspecialchars($_POST['code'] ?? ''));
    
    if ($code === '') {
        $errors['code'] = 'Please enter a 6-digit session code.';
    } else {
        $s = $sessionModel->getSessionByCode($code);
        
        if (!$s || !$s['is_live']) {
            $errors['code'] = 'Session not found or has ended.';
        } else {
            // User Identity Logic:
            // 1. If logged in, use that ID.
            // 2. If not, create a temporary "Guest" user.
            if (!empty($_SESSION['user_id'])) {
                $uid = $_SESSION['user_id'];
            } else {
                require_once __DIR__ . '/../../classes/User.php';
                $userModel = new User();
                
                // Create unique guest email/name
                $guest_suffix = bin2hex(random_bytes(4));
                $guest_email = 'guest_' . $guest_suffix . '@local';
                $guest_name = 'Guest ' . substr($guest_suffix, 0, 4);
                
                // Register guest
                $uid = $userModel->register($guest_name, $guest_email, bin2hex(random_bytes(6)), 'student');
                
                // Set session
                $_SESSION['user_id'] = $uid;
                $_SESSION['user_name'] = $guest_name;
                $_SESSION['user_role'] = 'student';
            }

            // Add to session participants
            $sessionModel->addParticipantIfNotExists($s['id'], $uid);
            
            // Redirect to Game
            header('Location: play.php?code=' . urlencode($code));
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Session | Quiz System</title>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Focus CSS -->
    <link rel="stylesheet" href="../../assets/css/join.css">
</head>
<body>

    <div style="width: 100%; max-width: 450px;">
        
        <div class="join-card">
            <!-- Animated Icon -->
            <div class="icon-circle">
                <i class="fas fa-gamepad"></i>
            </div>

            <h1>Enter Session Code</h1>
            <p>Join your classmates in a live interactive quiz session.</p>

            <!-- Error Banner -->
            <?php if (!empty($errors['code'])): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i> 
                    <span><?= htmlspecialchars($errors['code']) ?></span>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="form-group">
                    <label class="code-label">Session Code</label>
                    <input 
                        type="text" 
                        name="code" 
                        class="code-input" 
                        placeholder="123456" 
                        value="<?= htmlspecialchars($code) ?>"
                        autocomplete="off"
                        maxlength="10"
                        autofocus
                    >
                </div>
                
                <button type="submit" class="btn-join">
                    Join Game <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>

        <div style="text-align: center;">
            <a href="../dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

    </div>

</body>
</html>