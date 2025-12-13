<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/notifications.php';

$user_id = $_SESSION['user_id'] ?? null;
if (empty($user_id)) {
    echo json_encode([]);
    exit;
}

$pdo = getPdo();
$stmt = $pdo->prepare("SELECT id, title, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rows);

?>
    