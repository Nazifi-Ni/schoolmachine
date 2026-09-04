ALTER TABLE students DROP INDEX admission_number;
ALTER TABLE students DROP COLUMN admission_number;

INSERT IGNORE INTO sessions (name, is_current) VALUES ('2025/2026', 1);
SET @session_id = LAST_INSERT_ID();

INSERT IGNORE INTO terms (name, session_id, is_current) VALUES ('First Term', @session_id, 1);
INSERT IGNORE INTO terms (name, session_id, is_current) VALUES ('Second Term', @session_id, 0);
INSERT IGNORE INTO terms (name, session_id, is_current) VALUES ('Third Term', @session_id, 0);
