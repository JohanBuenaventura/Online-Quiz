<?php
session_start();
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/notifications.php';
require_once __DIR__ . '/../includes/mailer.php';

$userModel = new User();
$errors = [];
$values = ['name' => '', 'email' => ''];

// CSRF Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    $values['name'] = trim(htmlspecialchars($_POST['name'] ?? ''));
    $values['email'] = trim(htmlspecialchars($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? '', ['student', 'teacher', 'admin']) ? $_POST['role'] : 'student';

    if ($values['name'] === '') $errors['name'] = 'Full name is required';
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email address is required';
    if (strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters';

    if (empty($errors)) {
        if ($userModel->existsByEmail($values['email'])) {
            $errors['email'] = 'Email is already registered';
        } else {
            $uid = $userModel->register($values['name'], $values['email'], $password, $role);
            if ($uid) {
                // Notify admins (DB notification + optional email)
                try {
                    $db = (new \Database())->connect();
                    $aStmt = $db->prepare("SELECT id, email, name FROM users WHERE role = 'admin'");
                    $aStmt->execute();
                    $admins = $aStmt->fetchAll(PDO::FETCH_ASSOC);
                    $notifTitle = 'New user registered';
                    $notifMsg = sprintf('%s (%s) registered as %s', $values['name'], $values['email'], $role);
                    foreach ($admins as $a) {
                        if (function_exists('createNotification')) createNotification($a['id'], $notifTitle, $notifMsg);
                        if (function_exists('sendMail') && !empty($a['email'])) {
                            $html = '<p>' . htmlspecialchars($notifMsg) . '</p>';
                            $html .= '<p><a href="' . (isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : '') . '../../pages/admin/notifications.php">View notifications</a></p>';
                            @sendMail($a['email'], $notifTitle, $html);
                        }
                    }
                } catch (Exception $e) {
                    error_log('Notify admins failed: ' . $e->getMessage());
                }

                $_SESSION['user_id'] = $uid;
                $_SESSION['user_name'] = $values['name'];
                $_SESSION['user_role'] = $role;
                header('Location: login.php');
                exit;
            } else {
                $errors['general'] = 'An error occurred during registration. Please try again.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Online Quiz System</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/auth-pro.css">
</head>
<body>

    <div class="split-container">
        
        <div class="brand-panel">
            <h1>Online Quiz System</h1>
            <p>A secure and interactive environment for assessments, grading, and student progress tracking.</p>
        </div>

        <div class="form-panel">
            <div class="auth-box">
                <div class="form-header">
                    <h2>Create Account</h2>
                    <p>Please enter your details to register.</p>
                </div>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($errors['general']) ?></span>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($values['name']) ?>">
                        </div>
                        <?php if (isset($errors['name'])): ?><div class="field-error"><?= $errors['name'] ?></div><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($values['email']) ?>">
                        </div>
                        <?php if (isset($errors['email'])): ?><div class="field-error"><?= $errors['email'] ?></div><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" class="form-input">
                        </div>
                        <?php if (isset($errors['password'])): ?><div class="field-error"><?= $errors['password'] ?></div><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <div class="input-group">
                            <i class="fas fa-id-badge"></i>
                            <select name="role" class="form-input">
                                <option value="student">Student</option>
                                <option value="teacher">Teacher</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">Register</button>

                    <div class="form-footer">
                        Already have an account? <a href="login.php">Sign In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>