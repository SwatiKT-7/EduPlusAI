-- =====================================================
-- Database: eduplusai
-- Automated Student Attendance Monitoring & Analytics
-- =====================================================

CREATE DATABASE IF NOT EXISTS eduplusai;
USE eduplusai;

-- ==============================
-- 1. Roles (Admin, Faculty, Student)
-- ==============================
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO roles (role_name) VALUES ('Admin'), ('Faculty'), ('Student');

-- ==============================
-- 2. Users
-- ==============================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    dept_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- ==============================
-- 3. Departments
-- ==============================
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- ==============================
-- 4. Subjects
-- ==============================
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dept_id INT NOT NULL,
    faculty_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) UNIQUE,
    FOREIGN KEY (dept_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==============================
-- 5. Sessions (Class Sessions)
-- ==============================
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    faculty_id INT NOT NULL,
    session_date DATETIME NOT NULL,
    qr_code VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==============================
-- 6. Attendance
-- ==============================
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('Present', 'Absent', 'Late') DEFAULT 'Present',
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (session_id, student_id)
);

-- ==============================
-- 7. AI Insights (Logs from GPT API)
-- ==============================
CREATE TABLE ai_insights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    generated_for ENUM('Faculty','Admin') NOT NULL,
    user_id INT NOT NULL,
    insight_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==============================
-- Sample Data for Testing
-- ==============================

-- Departments
INSERT INTO departments (name) VALUES ('Computer Science'), ('Electrical'), ('Mechanical');

-- Users
INSERT INTO users (name, email, password, role_id, dept_id) 
VALUES 
('Super Admin', 'admin@eduplusai.com', 'admin123', 1, NULL),
('Dr. Sharma', 'sharma@eduplusai.com', 'faculty123', 2, 1),
('Ravi Kumar', 'ravi@eduplusai.com', 'student123', 3, 1),
('Anita Singh', 'anita@eduplusai.com', 'student123', 3, 1);

-- Subjects
INSERT INTO subjects (dept_id, faculty_id, name, code) 
VALUES 
(1, 2, 'Data Structures', 'CS101'),
(1, 2, 'Operating Systems', 'CS102');

-- Sessions
INSERT INTO sessions (subject_id, faculty_id, session_date, qr_code)
VALUES (1, 2, NOW(), 'qr_code_sample.png');

-- Attendance (Example: Ravi present in DS class)
INSERT INTO attendance (session_id, student_id, status)
VALUES (1, 3, 'Present');

-- AI Insight Example
INSERT INTO ai_insights (generated_for, user_id, insight_text)
VALUES ('Faculty', 2, 'Student Ravi Kumar has below 60% attendance in Data Structures.');
