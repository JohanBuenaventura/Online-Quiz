<?php
// pages/session/my_scores_export.php
session_start();
if (empty($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
require_once __DIR__ . '/../../database.php';
$db = (new Database())->connect();
$userId = (int)$_SESSION['user_id'];
$sessionId = (int)($_GET['session_id'] ?? 0);

// If session_id provided, export that one student's row + per-question answers if available; otherwise export all rows
if ($sessionId) {
    // export one session row for this student
    $stmt = $db->prepare("SELECT s.session_id, s.score, s.created_at AS scored_at, se.session_code, se.created_at AS session_created, q.title AS quiz_title,
                                 (SELECT COUNT(*) + 1 FROM scores sc WHERE sc.session_id = s.session_id AND sc.score > s.score) AS rank
                          FROM scores s
                          LEFT JOIN sessions se ON se.id = s.session_id
                          LEFT JOIN quizzes q ON q.id = se.quiz_id
                          WHERE s.student_id = :uid AND s.session_id = :sid LIMIT 1");
    $stmt->execute([':uid' => $userId, ':sid' => $sessionId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { http_response_code(404); echo 'Not found'; exit; }

    // Try detailed answers
    $answers = [];
    $hasDetailed = true;
    try {
        $qa = $db->prepare("SELECT sa.*, q.question_text, q.correct_answer
                            FROM student_answers sa
                            LEFT JOIN questions q ON q.id = sa.question_id
                            WHERE sa.session_id = :sid AND sa.student_id = :uid
                            ORDER BY sa.created_at ASC");
        $qa->execute([':sid' => $sessionId, ':uid' => $userId]);
        $answers = $qa->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        $hasDetailed = false;
    }

    $code = preg_replace('/[^A-Za-z0-9_-]/','', $row['session_code'] ?? 'session');
    $filename = sprintf('my_session_%s_%d_%s.csv', $code, $sessionId, date('Ymd_His'));
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output','w');

    if ($hasDetailed && !empty($answers)) {
        fputcsv($out, ['Question','Student Answer','Correct Answer','Is Correct','Answered At']);
        foreach ($answers as $a) {
            $isCorrect = isset($a['is_correct']) ? ($a['is_correct'] ? 'Yes' : 'No') : (isset($a['answer']) && isset($a['correct_answer']) ? ((string)$a['answer'] === (string)$a['correct_answer'] ? 'Yes' : 'No') : '—');
            fputcsv($out, [ $a['question_text'] ?? ('Q#' . ($a['question_id'] ?? '')), $a['answer_text'] ?? $a['answer'] ?? '', $a['correct_answer'] ?? '', $isCorrect, $a['created_at'] ?? '' ]);
        }
    } else {
        fputcsv($out, ['Session Code','Quiz Title','Date','Score','Rank']);
        fputcsv($out, [ $row['session_code'] ?? '', $row['quiz_title'] ?? '', $row['session_created'] ?? $row['scored_at'], $row['score'], $row['rank'] ]);
    }
    fclose($out);
    exit;
}

// Export all rows for this student
$stmt = $db->prepare("SELECT s.session_id, s.score, s.created_at AS scored_at, se.session_code, se.created_at AS session_created, q.title AS quiz_title,
                             (SELECT COUNT(*) + 1 FROM scores sc WHERE sc.session_id = s.session_id AND sc.score > s.score) AS rank
                      FROM scores s
                      LEFT JOIN sessions se ON se.id = s.session_id
                      LEFT JOIN quizzes q ON q.id = se.quiz_id
                      WHERE s.student_id = :uid
                      ORDER BY s.created_at DESC");
$stmt->execute([':uid' => $userId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = sprintf('my_scores_%d_%s.csv', $userId, date('Ymd_His'));
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$out = fopen('php://output','w');

fputcsv($out, ['Session ID','Session Code','Quiz Title','Date','Score','Rank']);
foreach ($rows as $r) {
    fputcsv($out, [ $r['session_id'], $r['session_code'], $r['quiz_title'], $r['session_created'] ?? $r['scored_at'], $r['score'], $r['rank'] ]);
}
fclose($out);
exit;
