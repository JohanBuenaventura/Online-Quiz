<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/mailer.php';

# Create token
function generateToken() {
    return bin2hex(random_bytes(32));
}

# ========== EMAIL VERIFICATION ==========

function sendVerificationEmail($user_id, $email) {
    global $pdo;

    $token = generateToken();
    $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $pdo->prepare("INSERT INTO email_verification (user_id, token, expires_at) VALUES (?, ?, ?)")
        ->execute([$user_id, $token, $expires]);

    $link = "http://../../online_quiz/pages/session/verify.php?token=$token";

    $body = "
        <h2>Email Verification</h2>
        <p>Click the link below to verify your account:</p>
        <a href='$link'>Verify Email</a>
    ";

    sendMail($email, "Verify Your Account", $body);
}

# ========== PASSWORD RESET ==========

function sendPasswordResetEmail($user_id, $email) {
    global $pdo;

    $token = generateToken();
    $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)")
        ->execute([$user_id, $token, $expires]);

    $link = "http://../../pages/session/reset_password.php?token=$token";

    $body = "
        <h2>Password Reset</h2>
        <p>Click the link below to reset your password:</p>
        <a href='$link'>Reset Password</a>
    ";

    sendMail($email, "Reset Your Password", $body);
}
