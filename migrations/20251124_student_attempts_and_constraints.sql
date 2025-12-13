-- Migration: Create student_attempts table and add unique constraint on student_answers

CREATE TABLE IF NOT EXISTS student_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  session_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  question_order JSON DEFAULT NULL,
  choices_map JSON DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (session_id),
  INDEX (student_id),
  CONSTRAINT fk_sa_session FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_sa_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add a UNIQUE constraint to avoid duplicate answers at DB level
ALTER TABLE student_answers
  ADD CONSTRAINT uq_student_answer_unique_session_question_student UNIQUE (session_id, question_id, student_id);
