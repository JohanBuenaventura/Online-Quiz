<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../classes/Session.php';
require_once __DIR__ . '/../../classes/Question.php';
require_once __DIR__ . '/../../database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'Invalid method']); exit; }
$session_id = (int)($_POST['session_id'] ?? 0);
$question_id = (int)($_POST['question_id'] ?? 0);
$student_id = $_SESSION['user_id'] ?? null;
if (!$student_id) { echo json_encode(['success'=>false,'message'=>'Not logged in']); exit; }

$questionModel = new Question();
$question = $questionModel->getQuestionById($question_id);
if (!$question) { echo json_encode(['success'=>false,'message'=>'Question not found']); exit; }

$correct = 0;
$choice_id = null;
$answer_text = null;
if ($question['question_type'] === 'mcq'){
    $choice_id = (int)($_POST['choice_id'] ?? 0);
    if ($choice_id <= 0) { echo json_encode(['success'=>false,'message'=>'No choice']); exit; }
    $choices = $questionModel->getChoices($question_id);
    foreach ($choices as $c) { if ($c['id'] == $choice_id && $c['is_correct']) { $correct = 1; break; } }
} elseif ($question['question_type'] === 'tf'){
    $answer_text = ($_POST['choice_text'] ?? '') === 'true' ? 'true' : 'false';
} else {
    $answer_text = trim($_POST['answer_text'] ?? '');
}

try{
    $db = (new Database())->connect();
    // Prevent duplicate answers for the same student + session + question
    $chk = $db->prepare("SELECT COUNT(*) FROM student_answers WHERE session_id = :sid AND question_id = :qid AND student_id = :uid");
    $chk->execute([':sid' => $session_id, ':qid' => $question_id, ':uid' => $student_id]);
    if ($chk->fetchColumn() > 0) {
        echo json_encode(['success'=>false,'message'=>'You have already answered this question']);
        exit;
    }
    $db->beginTransaction();
    $sql = "INSERT INTO student_answers (session_id, question_id, student_id, choice_id, answer_text, is_correct) VALUES (:sid, :qid, :uid, :cid, :atext, :isc)";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':sid', $session_id, PDO::PARAM_INT);
    $stmt->bindValue(':qid', $question_id, PDO::PARAM_INT);
    $stmt->bindValue(':uid', $student_id, PDO::PARAM_INT);
    $stmt->bindValue(':cid', $choice_id ?: null, PDO::PARAM_INT);
    $stmt->bindValue(':atext', $answer_text);
    $stmt->bindValue(':isc', $correct, PDO::PARAM_INT);
    $stmt->execute();
    if ($correct){
        $sql = "UPDATE scores SET score = score + 1 WHERE session_id = :sid AND student_id = :uid";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':sid', $session_id, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $student_id, PDO::PARAM_INT);
        $stmt->execute();
    }
    $db->commit();
    echo json_encode(['success'=>true]);
} catch (PDOException $e) {
    if ($db && $db->inTransaction()) $db->rollBack();
    $mysqlErrNo = $e->errorInfo[1] ?? null;
    if ($mysqlErrNo == 1062) {
        // Duplicate entry - someone raced to answer; treat as already answered
        echo json_encode(['success' => false, 'message' => 'You have already answered this question']);
        exit;
    }
    error_log('submit_answer_ajax PDOException: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error']);
} catch (Exception $e) {
    if ($db && $db->inTransaction()) $db->rollBack();
    error_log('submit_answer_ajax Exception: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error']);
}
