-- ============================================
-- EXAM SEATING SYSTEM v2.0 - UPGRADED DATABASE
-- Run this FRESH in phpMyAdmin
-- First drop old database if exists
-- ============================================

DROP DATABASE IF EXISTS exam_seating;
CREATE DATABASE exam_seating;
USE exam_seating;

-- ---- USERS TABLE (Admin + Students login) ----
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','student') NOT NULL DEFAULT 'student',
    student_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---- STUDENTS TABLE ----
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_no VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    branch VARCHAR(50) NOT NULL,
    semester VARCHAR(10) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---- ROOMS TABLE (with bench_size) ----
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_no VARCHAR(20) NOT NULL UNIQUE,
    `rows` INT NOT NULL,
    cols INT NOT NULL,
    bench_size INT NOT NULL DEFAULT 1,
    capacity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---- EXAMS TABLE ----
CREATE TABLE exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_name VARCHAR(100) NOT NULL,
    exam_date DATE NOT NULL,
    subject VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---- SEATING TABLE (with bench position) ----
CREATE TABLE seating (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    room_id INT NOT NULL,
    student_id INT NOT NULL,
    seat_row INT NOT NULL,
    seat_col INT NOT NULL,
    bench_position INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- ============================================
-- SAMPLE DATA
-- ============================================

-- Default Admin (password: admin123)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Note: above is bcrypt of 'password' - we'll use simple MD5 for beginners
-- Actual admin password = admin123 (stored as MD5 below)

DELETE FROM users;
INSERT INTO users (username, password, role) VALUES
('admin', MD5('admin123'), 'admin');

-- Sample Students
INSERT INTO students (roll_no, name, branch, semester, email, phone) VALUES
('CSE001', 'Aarav Sharma',   'CSE',   '5th', 'aarav@college.edu',   '9876543210'),
('CSE002', 'Priya Patel',    'CSE',   '5th', 'priya@college.edu',   '9876543211'),
('CSE003', 'Rohit Verma',    'CSE',   '5th', 'rohit@college.edu',   '9876543212'),
('CSE004', 'Sneha Singh',    'CSE',   '5th', 'sneha@college.edu',   '9876543213'),
('ECE001', 'Arjun Gupta',    'ECE',   '5th', 'arjun@college.edu',   '9876543214'),
('ECE002', 'Nisha Yadav',    'ECE',   '5th', 'nisha@college.edu',   '9876543215'),
('ECE003', 'Vikram Joshi',   'ECE',   '5th', 'vikram@college.edu',  '9876543216'),
('ECE004', 'Pooja Mehta',    'ECE',   '5th', 'pooja@college.edu',   '9876543217'),
('ME001',  'Rahul Kumar',    'ME',    '5th', 'rahul@college.edu',   '9876543218'),
('ME002',  'Anjali Das',     'ME',    '5th', 'anjali@college.edu',  '9876543219'),
('ME003',  'Suresh Nair',    'ME',    '5th', 'suresh@college.edu',  '9876543220'),
('CIVIL001','Deepak Rao',    'CIVIL', '5th', 'deepak@college.edu',  '9876543221'),
('CIVIL002','Meena Iyer',    'CIVIL', '5th', 'meena@college.edu',   '9876543222'),
('CIVIL003','Karan Malhotra','CIVIL', '5th', 'karan@college.edu',   '9876543223'),
('IT001',  'Riya Desai',     'IT',    '5th', 'riya@college.edu',    '9876543224'),
('IT002',  'Mohit Soni',     'IT',    '5th', 'mohit@college.edu',   '9876543225');

-- Student login accounts (password = their roll number)
INSERT INTO users (username, password, role, student_id)
SELECT s.roll_no, MD5(s.roll_no), 'student', s.id FROM students s;

-- Sample Rooms (bench_size = students per bench)
INSERT INTO rooms (room_no, `rows`, cols, bench_size, capacity) VALUES
('Room 101', 5, 6, 2, 60),
('Room 102', 4, 5, 3, 60),
('Room 103', 4, 6, 1, 24);
