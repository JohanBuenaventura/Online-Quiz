<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../classes/Session.php';
require_once __DIR__ . '/../../classes/Question.php';
require_once __DIR__ . '/../../classes/Quiz.php';
require_once __DIR__ . '/../../database.php';

session_start();
$code = $_GET['code'] ?? '';
if (!$code) { echo json_encode(['error'=>'missing code']); exit; }
$sessionModel = new Session();
$session = $sessionModel->getSessionByCode($code);
if (!$session) { echo json_encode(['error'=>'session not found']); exit; }
$quizModel = new Quiz();
$q = $quizModel->getQuizById($session['quiz_id']);
$response = ['session_code' => $session['session_code'], 'quiz_title' => $q['title'] ?? null, 'is_live' => (bool)$session['is_live']];

// persistent student attempt mapping: question order + per-question choice order
$db = (new Database())->connect();
$studentId = $_SESSION['user_id'] ?? null;

// Ensure student_attempts table exists
$createSql = "CREATE TABLE IF NOT EXISTS `student_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `question_order` JSON DEFAULT NULL,
  `choices_map` JSON DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_student` (`session_id`,`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
try { $db->exec($createSql); } catch (Exception $e) { /* ignore */ }

// helper to create mapping for a student on a session
function ensure_student_mapping($db, $session, $studentId) {
    if (!$studentId) return null;
    $sid = (int)$session['id'];
    $check = $db->prepare("SELECT * FROM student_attempts WHERE session_id = :sid AND student_id = :uid LIMIT 1");
    $check->execute([':sid'=>$sid, ':uid'=>$studentId]);
    $row = $check->fetch(PDO::FETCH_ASSOC);
    if ($row) return $row;

    // Build mapping
    $questionModel = new Question();
    $questions = $questionModel->getQuestionsByQuiz($session['quiz_id']);
    $qids = array_map(function($q){ return (int)$q['id']; }, $questions);
    // shuffle question order for this attempt
    $shuffledQ = $qids;
    shuffle($shuffledQ);

    $choices_map = [];
    foreach ($qids as $qid) {
        $choices = $questionModel->getChoices($qid);
        $cids = array_map(function($c){ return (int)$c['id']; }, $choices);
        shuffle($cids);
        $choices_map[$qid] = $cids;
    }

    $ins = $db->prepare("INSERT INTO student_attempts (session_id, student_id, question_order, choices_map) VALUES (:sid, :uid, :qord, :cmap)");
    $ins->execute([':sid'=>$sid, ':uid'=>$studentId, ':qord'=>json_encode($shuffledQ), ':cmap'=>json_encode($choices_map)]);
    $check->execute([':sid'=>$sid, ':uid'=>$studentId]);
    return $check->fetch(PDO::FETCH_ASSOC);
}

$mapping = null;
if ($studentId) {
    try { $mapping = ensure_student_mapping($db, $session, $studentId); } catch (Exception $e) { /* ignore mapping failures */ }
}

if (!empty($session['current_question_id'])){
    $questionModel = new Question();
    $question = $questionModel->getQuestionById($session['current_question_id']);
    if ($question){
        $response['current_question'] = ['id' => (int)$question['id'], 'question_text' => $question['question_text'], 'question_type' => $question['question_type']];
        if ($question['question_type'] === 'mcq'){
            $choices = $questionModel->getChoices($question['id']);
            // if we have a persistent mapping for this student, order choices accordingly
            if ($mapping && !empty($mapping['choices_map'])){
                $map = json_decode($mapping['choices_map'], true);
                if (isset($map[$question['id']])){
                    $ordered = [];
                    $byId = [];
                    foreach ($choices as $c) $byId[$c['id']] = $c;
                    foreach ($map[$question['id']] as $cid) {
                        if (isset($byId[$cid])) $ordered[] = $byId[$cid];
                    }
                    // fallback: if mapping misses some choices, append them
                    foreach ($choices as $c){ if (!in_array($c['id'], $map[$question['id']])) $ordered[] = $c; }
                    $choices = $ordered;
                }
            }
            $response['current_question']['choices'] = $choices;
        }
    }
}

// include leaderboard snapshot
$response['leaderboard'] = $sessionModel->getLeaderboard($session['id']);

echo json_encode($response);
