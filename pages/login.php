<?php
session_start();
require_once __DIR__ . '/../classes/User.php';
$userModel = new User();

$errors = [];

// CSRF Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required';
    if (empty($password)) $errors['password'] = 'Password is required';

    if (empty($errors)) {
        $user = $userModel->login($email, $password);
        if ($user) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: dashboard.php');
            exit;
        } else {
            $errors['general'] = 'Invalid email or password.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Online Quiz System</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/auth-pro.css">
</head>
<body>

    <div class="split-container">
        
        <div class="brand-panel">
            <h1>Welcome Back</h1>
            <p>Access your dashboard to manage quizzes, view grades, and continue your learning journey.</p>
        </div>

        <div class="form-panel">
            <div class="auth-box">
                <div class="form-header">
                    <h2>Sign In</h2>
                    <p>Please enter your credentials to continue.</p>
                </div>

                <?php if (isset($_GET['registered'])): ?>
                    <div class="alert" style="background: #ecfdf5; border-color: #6ee7b7; color: #065f46;">
                        <i class="fas fa-check-circle"></i>
                        <span>Account created! You can now sign in.</span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($errors['general']) ?></span>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($email ?? '') ?>">
                        </div>
                        <?php if (isset($errors['email'])): ?><div class="field-error"><?= $errors['email'] ?></div><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <label class="form-label" style="margin-bottom: 0;">Password</label>
                            <a href="session/reset_password.php" style="font-size: 0.85rem; color: var(--accent); text-decoration: none;">Forgot password?</a>
                        </div>
                        
                        <div class="input-group" style="margin-top: 8px;">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="passwordInput" class="form-input">
                        </div>
                        <?php if (isset($errors['password'])): ?><div class="field-error"><?= $errors['password'] ?></div><?php endif; ?>

                        <div style="margin-top: 10px;">
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; color: var(--text-muted); cursor: pointer;">
                                <input type="checkbox" onclick="togglePassword()" style="accent-color: var(--primary);"> 
                                Show Password
                            </label>
                        </div>
                    </div>

                    <button class="btn-submit" type="submit">Sign In</button>

                    <div class="form-footer">
                        Don't have an account? <a href="register.php">Create Account</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        }
    </script>
</body>
</html>