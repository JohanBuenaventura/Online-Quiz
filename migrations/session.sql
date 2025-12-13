
ALTER TABLE `sessions` 
  ADD COLUMN `current_question_id` INT UNSIGNED NULL AFTER `started_at`,
  ADD COLUMN `is_live` TINYINT(1) NOT NULL DEFAULT 0 AFTER `current_question_id`;

-- Optionally add an index for session_code or current_question_id if needed
ALTER TABLE `sessions` ADD INDEX (`session_code`);


