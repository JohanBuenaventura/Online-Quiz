<?php
require_once __DIR__ . '/../database.php';

class Session {
    protected $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    protected function generateCode($length = 6) {
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i=0;$i<$length;$i++) $code .= $chars[random_int(0, strlen($chars)-1)];
        return $code;
    }

    public function createSession($quiz_id, $teacher_id) {
        try {
            // ensure unique code
            $attempts = 0;
            do {
                $code = $this->generateCode(6);
                $stmt = $this->conn->prepare("SELECT id FROM sessions WHERE session_code = :code LIMIT 1");
                $stmt->bindValue(':code', $code);
                $stmt->execute();
                $exists = $stmt->fetch();
                $attempts++;
            } while ($exists && $attempts < 10);

            $sql = "INSERT INTO sessions (quiz_id, session_code, host_teacher_id, created_at) VALUES (:quiz_id, :code, :teacher_id, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':quiz_id', $quiz_id, PDO::PARAM_INT);
            $stmt->bindValue(':code', $code);
            $stmt->bindValue(':teacher_id', $teacher_id, PDO::PARAM_INT);
            $stmt->execute();
            return ['id' => $this->conn->lastInsertId(), 'code' => $code];
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getSessionByCode($code) {
        $sql = "SELECT * FROM sessions WHERE session_code = :code LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':code', $code);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getSessionById($id) {
        $sql = "SELECT * FROM sessions WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function startSession($session_id) {
        $sql = "UPDATE sessions SET started_at = NOW(), is_live = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $session_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function endSession($session_id) {
        $sql = "UPDATE sessions SET ended_at = NOW(), is_live = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $session_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function setCurrentQuestion($session_id, $question_id) {
        $sql = "UPDATE sessions SET current_question_id = :qid WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':qid', $question_id, PDO::PARAM_INT);
        $stmt->bindValue(':id', $session_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function addParticipantIfNotExists($session_id, $student_id) {
        $sql = "SELECT id FROM scores WHERE session_id = :sid AND student_id = :uid LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':sid', $session_id, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $student_id, PDO::PARAM_INT);
        $stmt->execute();
        $exists = $stmt->fetch();
        if ($exists) return $exists['id'];
        $sql = "INSERT INTO scores (session_id, student_id, score) VALUES (:sid, :uid, 0)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':sid', $session_id, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $student_id, PDO::PARAM_INT);
        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    public function getLeaderboard($session_id) {
        $sql = "SELECT s.student_id, u.name, s.score FROM scores s JOIN users u ON u.id = s.student_id WHERE s.session_id = :sid ORDER BY s.score DESC, s.created_at ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':sid', $session_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
