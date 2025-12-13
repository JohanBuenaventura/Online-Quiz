<?php
// Notifications helper - lightweight and safe
require_once __DIR__ . '/../database.php';

function getPdo() {
    global $pdo;
    if (isset($pdo)) return $pdo;
    $db = new Database();
    return $db->connect();
}

function createNotification($user_id, $title, $message) {
    $pdo = getPdo();
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
    $ok = $stmt->execute([$user_id, $title, $message]);
    if (!$ok) {
        $err = $stmt->errorInfo();
        error_log('createNotification failed: ' . json_encode($err));
    }
    return $ok ? $pdo->lastInsertId() : false;
}

function getUnreadNotifications($user_id) {
    $pdo = getPdo();
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function markNotificationsRead($user_id) {
    $pdo = getPdo();
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    return $stmt->execute([$user_id]);
}

// Note: do not execute side-effectful code on include. Use the functions above from pages.

