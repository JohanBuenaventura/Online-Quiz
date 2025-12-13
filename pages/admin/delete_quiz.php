<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once __DIR__ . '/../../classes/Quiz.php';
$quizModel = new Quiz();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: manage_quizzes.php'); exit; }
$csrfPath = __DIR__ . '/../../includes/csrf.php';
if (file_exists($csrfPath)) require_once $csrfPath;
if (!isset($_POST['csrf_token']) || !csrf_check($_POST['csrf_token'])) die('Invalid CSRF token');
if (!isset($_POST['id'])) { header('Location: manage_quizzes.php'); exit; }
$id = (int)$_POST['id'];
if ($quizModel->adminDeleteQuiz($id)) { header('Location: manage_quizzes.php'); exit; } else exit('Failed');
