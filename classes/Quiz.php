<?php
require_once __DIR__ . '/../database.php';

class Quiz {
    protected $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function createQuiz($title, $teacher_id, $is_published = 0, $settings = null) {
        try {
            $sql = "INSERT INTO quizzes (title, teacher_id, is_published, settings) VALUES (:title, :teacher_id, :is_published, :settings)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':teacher_id', $teacher_id, PDO::PARAM_INT);
            $stmt->bindValue(':is_published', $is_published, PDO::PARAM_INT);
            $stmt->bindValue(':settings', $settings);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getQuizzesByTeacher($teacher_id) {
        $sql = "SELECT * FROM quizzes WHERE teacher_id = :teacher_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':teacher_id', $teacher_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Admin: list all quizzes
    public function getAllQuizzes() {
        $sql = "SELECT q.*, u.name as teacher_name FROM quizzes q JOIN users u ON u.id = q.teacher_id ORDER BY q.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function adminDeleteQuiz($id) {
        return $this->deleteQuiz($id);
    }

    public function getQuizById($id) {
        $sql = "SELECT * FROM quizzes WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function updateQuiz($id, $title, $is_published = 0, $settings = null) {
        try {
            $sql = "UPDATE quizzes SET title = :title, is_published = :is_published, settings = :settings WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':is_published', $is_published, PDO::PARAM_INT);
            $stmt->bindValue(':settings', $settings);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteQuiz($id) {
        try {
            $sql = "DELETE FROM quizzes WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
