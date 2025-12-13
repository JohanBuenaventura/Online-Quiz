<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../classes/Session.php';
$code = $_GET['code'] ?? '';
if (!$code) { echo json_encode([]); exit; }
$sessionModel = new Session();
$s = $sessionModel->getSessionByCode($code);
if (!$s) { echo json_encode([]); exit; }
$board = $sessionModel->getLeaderboard($s['id']);
echo json_encode($board);
