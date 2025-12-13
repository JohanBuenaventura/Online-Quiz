<?php
// Export reports as CSV
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.php'); exit;
}
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Quiz.php';
$userModel = new User();
$quizModel = new Quiz();

$type = $_GET['type'] ?? 'users';
if ($type === 'users') {
    $rows = $userModel->getAllUsers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="users.csv"');
    $out = fopen('php://output','w');
    fputcsv($out, ['id','name','email','role','created_at']);
    foreach ($rows as $r) fputcsv($out, [$r['id'],$r['name'],$r['email'],$r['role'],$r['created_at']]);
    fclose($out);
    exit;
} else if ($type === 'quizzes') {
    $rows = $quizModel->getAllQuizzes();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="quizzes.csv"');
    $out = fopen('php://output','w');
    fputcsv($out, ['id','title','teacher_name','is_published','created_at']);
    foreach ($rows as $r) fputcsv($out, [$r['id'],$r['title'],$r['teacher_name'],$r['is_published'],$r['created_at']]);
    fclose($out);
    exit;
}
header('Location: reports.php');
