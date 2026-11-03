-- ============================================================
-- Student Admission Portal - DB schema (MySQL 8 / MariaDB 10+)
-- Run:  mysql -u root -p < schema.sql
-- ============================================================
CREATE DATABASE IF NOT EXISTS student_portal
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_portal;

-- Student master record (Q2 CRUD)
CREATE TABLE IF NOT EXISTS students (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  roll_no    VARCHAR(20)  NOT NULL UNIQUE,
  email      VARCHAR(120) NOT NULL,
  course     VARCHAR(60)  NOT NULL,
  phone      VARCHAR(15)  NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- File metadata: path, type, upload date, linked to a student (Q2 + Q3)
-- ON DELETE CASCADE removes the metadata rows when a student is deleted.
CREATE TABLE IF NOT EXISTS student_files (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  student_id    INT NOT NULL,
  category      ENUM('profile','id_proof','certificate') NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  file_path     VARCHAR(255) NOT NULL,      -- relative to the uploads folder
  file_type     VARCHAR(100) NOT NULL,      -- MIME type, e.g. image/png
  file_size     INT NOT NULL,               -- bytes
  uploaded_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_file_student FOREIGN KEY (student_id)
    REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Portal users for the servlet login (Q3). Password = SHA-256 hex digest.
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50) NOT NULL UNIQUE,
  password_hash CHAR(64)    NOT NULL
) ENGINE=InnoDB;

-- demo login:  admin / admin123
INSERT IGNORE INTO users (username, password_hash)
VALUES ('admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9');

-- sample students
INSERT IGNORE INTO students (name, roll_no, email, course, phone) VALUES
 ('Arun Kumar',  '21CS001', 'arun@example.com',  'B.E. CSE', '9876543210'),
 ('Divya Priya', '21CS002', 'divya@example.com', 'B.E. CSE', '9876501234');
