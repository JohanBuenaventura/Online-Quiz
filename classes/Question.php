<?php
require_once __DIR__ . '/../database.php';

class Question {
    protected $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function addQuestion($quiz_id, $question_text, $question_type = 'mcq') {
        try {
            $sql = "INSERT INTO questions (quiz_id, question_text, question_type) VALUES (:quiz_id, :question_text, :question_type)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':quiz_id', $quiz_id, PDO::PARAM_INT);
            $stmt->bindValue(':question_text', $question_text);
            $stmt->bindValue(':question_type', $question_type);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getQuestionsByQuiz($quiz_id) {
        $sql = "SELECT * FROM questions WHERE quiz_id = :quiz_id ORDER BY id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':quiz_id', $quiz_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getQuestionById($id) {
        $sql = "SELECT * FROM questions WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function updateQuestion($id, $question_text, $question_type) {
        try {
            $sql = "UPDATE questions SET question_text = :question_text, question_type = :question_type WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':question_text', $question_text);
            $stmt->bindValue(':question_type', $question_type);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteQuestion($id) {
        try {
            $sql = "DELETE FROM questions WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Choices management
    public function addChoice($question_id, $choice_text, $is_correct = 0) {
        try {
            $sql = "INSERT INTO choices (question_id, choice_text, is_correct) VALUES (:question_id, :choice_text, :is_correct)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':question_id', $question_id, PDO::PARAM_INT);
            $stmt->bindValue(':choice_text', $choice_text);
            $stmt->bindValue(':is_correct', $is_correct, PDO::PARAM_INT);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getChoices($question_id) {
        $sql = "SELECT * FROM choices WHERE question_id = :question_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':question_id', $question_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateChoice($id, $choice_text, $is_correct) {
        try {
            $sql = "UPDATE choices SET choice_text = :choice_text, is_correct = :is_correct WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':choice_text', $choice_text);
            $stmt->bindValue(':is_correct', $is_correct, PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteChoice($id) {
        try {
            $sql = "DELETE FROM choices WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
