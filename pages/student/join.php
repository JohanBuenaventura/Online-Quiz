<?php
session_start();
// allow both logged-in students and guests; if you want only logged-in students, keep the check
// if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'student') {
//     header('Location: ../login.php');
//     exit;
// }

require_once __DIR__ . '/../../classes/Session.php';
require_once __DIR__ . '/../../classes/Question.php';
require_once __DIR__ . '/../../classes/Quiz.php';

$sessionModel = new Session();
$questionModel = new Question();
$quizModel = new Quiz();

$errors = [];
$code = '';

// Accept either POST (form submit) or GET (dashboard quick-join)
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' || ($method === 'GET' && !empty($_GET['code']))) {
    $code = trim(htmlspecialchars(($method === 'POST' ? ($_POST['code'] ?? '') : ($_GET['code'] ?? ''))));
    if ($code === '') {
        $errors['code'] = 'Session code is required';
    } else {
        $s = $sessionModel->getSessionByCode($code);
        if (!$s || !$s['is_live']) {
            $errors['code'] = 'Session not found or not live';
        } else {
            // determine or create a student identity
            if (!empty($_SESSION['user_id'])) {
                $uid = $_SESSION['user_id'];
            } else {
                require_once __DIR__ . '/../../classes/User.php';
                $userModel = new User();
                $guest_email = 'guest_' . bin2hex(random_bytes(4)) . '@local';
                $uid = $userModel->register('Guest', $guest_email, bin2hex(random_bytes(6)), 'student');
                $_SESSION['user_id'] = $uid;
                $_SESSION['user_name'] = 'Guest';
                $_SESSION['user_role'] = 'student';
            }

            // register participant if not exists
            $sessionModel->addParticipantIfNotExists($s['id'], $uid);
            header('Location: ../session/play.php?code=' . urlencode($code));
            exit;
        }
    }
}

// Determine Back Link location
$backLink = !empty($_SESSION['user_id']) ? '../dashboard.php' : '../login.php';
$backText = !empty($_SESSION['user_id']) ? 'Back to Dashboard' : 'Back to Login';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Quiz | Quiz System</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/join.css">
</head>
<body>

  <div class="join-card">
    
    <div class="icon-circle">
        <i class="fas fa-gamepad"></i>
    </div>

    <h1>Join Session</h1>
    <p>Enter the code shared by your instructor.</p>

    <?php if (!empty($errors['code'])): ?>
        <div class="error-msg">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['code']) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
      <div class="form-group">
          <label class="code-label">SESSION CODE</label>
          <input 
            type="text" 
            name="code" 
            class="code-input"
            placeholder="e.g. 48291" 
            value="<?= htmlspecialchars($code) ?>"
            autocomplete="off"
            autofocus
          >
      </div>
      
      <button type="submit" class="btn-join">
          Join Now <i class="fas fa-arrow-right"></i>
      </button>
    </form>

    <a href="<?= $backLink ?>" class="back-link">
        <i class="fas fa-arrow-left"></i> <?= $backText ?>
    </a>
  </div>

</body>
</html>