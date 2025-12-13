<?php
// pages/session/reset_password.php
session_start();
require_once __DIR__ . '/../../database.php';

// 1. Connect to DB
$db = new Database();
$pdo = $db->connect();

$token = $_GET['token'] ?? '';
$errors = [];
$success = false;
$isValidToken = false;
$row = null;

// 2. Verify Token immediately
if ($token) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND created_at > (NOW() - INTERVAL 1 HOUR) LIMIT 1");
    $stmt->execute([$token]);
    $row = $stmt->fetch();

    if ($row) {
        $isValidToken = true;
    }
}

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isValidToken) {
    $pw = $_POST['password'] ?? '';
    $pw2 = $_POST['password_confirm'] ?? '';

    if (strlen($pw) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    } elseif ($pw !== $pw2) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // Update User
        $hash = password_hash($pw, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$hash, $row['user_id']]);

        // Delete used token
        $del = $pdo->prepare("DELETE FROM password_resets WHERE id = ?");
        $del->execute([$row['id']]);

        $success = true;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Quiz System</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/auth-pro.css">
</head>
<body>

    <div class="split-container">
        
        <div class="brand-panel">
            <h1>Secure Your Account</h1>
            <p>Create a new, strong password to protect your progress and personal information.</p>
        </div>

        <div class="form-panel">
            <div class="auth-box">

                <?php if ($success): ?>
                    <div style="text-align: center; padding: 20px;">
                        <div style="width: 60px; height: 60px; background: #dcfce7; color: #166534; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 20px auto;">
                            <i class="fas fa-check"></i>
                        </div>
                        <h2 style="color: var(--text-main); margin-bottom: 10px;">Password Reset!</h2>
                        <p style="color: var(--text-muted); margin-bottom: 25px;">
                            Your password has been successfully updated. You can now log in with your new credentials.
                        </p>
                        <a href="../pages/login.php" class="btn-submit" style="text-decoration: none; display: inline-block;">
                            Go to Login
                        </a>
                    </div>

                <?php elseif (!$isValidToken): ?>
                    <div style="text-align: center; padding: 20px;">
                        <div style="width: 60px; height: 60px; background: #fee2e2; color: #991b1b; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 20px auto;">
                            <i class="fas fa-link-slash"></i>
                        </div>
                        <h2 style="color: var(--text-main); margin-bottom: 10px;">Link Expired</h2>
                        <p style="color: var(--text-muted); margin-bottom: 25px;">
                            This password reset link is invalid or has expired. Please request a new one.
                        </p>
                        <a href="forgot_password.php" class="btn-submit" style="text-decoration: none; display: inline-block;">
                            Request New Link
                        </a>
                    </div>

                <?php else: ?>
                    <div class="form-header">
                        <h2>Set New Password</h2>
                        <p>Please enter your new password below.</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <?php foreach ($errors as $e) echo "<div>" . htmlspecialchars($e) . "</div>"; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="">
                        
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" id="pass1" class="form-input" placeholder="At least 6 characters" required autofocus>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password_confirm" id="pass2" class="form-input" placeholder="Re-type password" required>
                            </div>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; color: var(--text-muted); cursor: pointer;">
                                <input type="checkbox" onclick="togglePasswords()" style="accent-color: var(--primary);"> 
                                Show Passwords
                            </label>
                        </div>

                        <button type="submit" class="btn-submit">
                            Reset Password
                        </button>
                    </form>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script>
        function togglePasswords() {
            const p1 = document.getElementById('pass1');
            const p2 = document.getElementById('pass2');
            const type = p1.type === 'password' ? 'text' : 'password';
            p1.type = type;
            p2.type = type;
        }
    </script>

</body>
</html>