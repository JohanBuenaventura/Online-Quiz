<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/notifications.php';

$user_id = $_SESSION['user_id'] ?? null;
if (empty($user_id)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$ok = markNotificationsRead($user_id);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => (bool)$ok]);

?>
