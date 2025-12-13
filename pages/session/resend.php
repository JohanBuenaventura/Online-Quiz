<?php
// pages/session/resend.php
session_start();
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../includes/security.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../../login.php'); exit;
}

$uid = (int)$_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT email, name, is_verified FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();
if (!$user) { echo "User not found"; exit; }
if ($user['is_verified']) { echo "Already verified."; exit; }

sendVerificationEmail($uid, $user['email']);
echo "<p>Verification email sent to " . htmlspecialchars($user['email']) . ". Check your inbox.</p>";
