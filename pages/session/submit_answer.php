<?php
session_start();
require_once __DIR__ . '/../../classes/Session.php';
require_once __DIR__ . '/../../classes/Question.php';

$sessionModel = new Session();
$questionModel = new Question();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit('Invalid');
$session_id = (int)($_POST['session_id'] ?? 0);
$question_id = (int)($_POST['question_id'] ?? 0);
$student_id = $_SESSION['user_id'] ?? null;

if (!$student_id) exit('Not logged in');

$question = $questionModel->getQuestionById($question_id);
if (!$question) exit('Question not found');

$correct = 0;
$choice_id = null;
$answer_text = null;

if ($question['question_type'] === 'mcq') {
    $choice_id = (int)($_POST['choice_id'] ?? 0);
    if ($choice_id <= 0) exit('No choice');
    // check correctness
    $choices = $questionModel->getChoices($question_id);
    $is_correct = 0;
    foreach ($choices as $c) {
        if ($c['id'] == $choice_id && $c['is_correct']) { $is_correct = 1; break; }
    }
    $correct = $is_correct;
} elseif ($question['question_type'] === 'tf') {
    $ans = ($_POST['choice_text'] ?? '') === 'true' ? 1 : 0;
    // no correct answer stored for tf in this simple model; assume teacher marks choices so skip correctness
    $answer_text = $ans ? 'true' : 'false';
    $correct = 0;
} else {
    $answer_text = trim($_POST['answer_text'] ?? '');
}

// store student_answers
try {
    $db = (new Database())->connect();
    // Prevent duplicate answers
    $chk = $db->prepare("SELECT COUNT(*) FROM student_answers WHERE session_id = :sid AND question_id = :qid AND student_id = :uid");
    $chk->execute([':sid' => $session_id, ':qid' => $question_id, ':uid' => $student_id]);
    if ($chk->fetchColumn() > 0) {
        // Already answered - redirect back to play
        header('Location: play.php?code=' . urlencode((new Session())->getSessionById($session_id)['session_code']));
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

    // update score if correct
    if ($correct) {
        $sql = "UPDATE scores SET score = score + 1 WHERE session_id = :sid AND student_id = :uid";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':sid', $session_id, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $student_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    $db->commit();
    header('Location: play.php?code=' . urlencode((new Session())->getSessionById($session_id)['session_code']));
    exit;
} catch (PDOException $e) {
    if ($db && $db->inTransaction()) $db->rollBack();
    $mysqlErrNo = $e->errorInfo[1] ?? null;
    if ($mysqlErrNo == 1062) {
        // Duplicate insert - another request already saved the answer
        header('Location: play.php?code=' . urlencode((new Session())->getSessionById($session_id)['session_code']));
        exit;
    }
    error_log('submit_answer PDOException: ' . $e->getMessage());
    exit('Error saving answer');
} catch (Exception $e) {
    if ($db && $db->inTransaction()) $db->rollBack();
    error_log('submit_answer Exception: ' . $e->getMessage());
    exit('Error saving answer');
}
