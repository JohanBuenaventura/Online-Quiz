<?php
// pages/session/forgot_password.php
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../includes/security.php';

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if ($email) {
        // Assuming $pdo is defined in database.php
        // If your database.php uses a class, adjust accordingly: e.g., $pdo = (new Database())->connect();
        global $pdo; 
        if (!isset($pdo)) {
             $db = new Database();
             $pdo = $db->connect();
        }

        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        
        if ($u) {
            if (function_exists('sendPasswordResetEmail')) {
                sendPasswordResetEmail($u['id'], $email);
            }
        }
        // Show message regardless for security (prevent user enumeration)
        $sent = true;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Quiz System</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/auth-pro.css">
</head>
<body>

    <div class="split-container">
        
        <div class="brand-panel">
            <h1>Account Recovery</h1>
            <p>Don't worry, it happens to the best of us. Enter your email and we'll help you get back into your account.</p>
        </div>

        <div class="form-panel">
            <div class="auth-box">
                
                <?php if ($sent): ?>
                    <div style="text-align: center; padding: 20px;">
                        <div style="width: 60px; height: 60px; background: #dcfce7; color: #166534; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 20px auto;">
                            <i class="fas fa-check"></i>
                        </div>
                        <h2 style="color: var(--text-main); margin-bottom: 10px;">Check your inbox</h2>
                        <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">
                            If an account exists for <strong><?= htmlspecialchars($email) ?></strong>, we have sent a password reset link.
                        </p>
                        <a href="../../pages/login.php" class="btn-submit" style="text-decoration: none; display: inline-block; margin-top: 20px;">
                            Return to Login
                        </a>
                    </div>

                <?php else: ?>
                    <div class="form-header">
                        <h2>Forgot Password?</h2>
                        <p>No problem. Enter your email address below.</p>
                    </div>

                    <form method="post" action="">
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" class="form-input" required autofocus>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">
                            Send Reset Link
                        </button>

                        <div class="form-footer">
                            Remember your password? <a href="../../pages/login.php">Log In</a>
                        </div>
                    </form>
                <?php endif; ?>

            </div>
        </div>
    </div>

</body>
</html>