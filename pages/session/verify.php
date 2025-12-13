<?php
// pages/session/verify.php
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../database.php'; // ensures $pdo exists

$token = $_GET['token'] ?? '';
if (!$token) {
    echo "Missing token.";
    exit;
}

// find token
$stmt = $pdo->prepare("SELECT * FROM email_verification WHERE token = ? LIMIT 1");
$stmt->execute([$token]);
$row = $stmt->fetch();

if (!$row) {
    echo "Invalid or expired token.";
    exit;
}

// verify and delete token (or set used)
$pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = ?")->execute([$row['user_id']]);
$pdo->prepare("DELETE FROM email_verification WHERE id = ?")->execute([$row['id']]);

echo "<h2>Account verified!</h2><p>You can now <a href='../../login.php'>login</a>.</p>";
