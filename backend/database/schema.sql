-- Student Feedback System Database Schema
-- MySQL Database Creation Script

-- Create database
CREATE DATABASE IF NOT EXISTS feedback_system;
USE feedback_system;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS teachers;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS departments;

-- Create departments table
CREATE TABLE departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    dept_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create students table
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    department_id INT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE
);

-- Create teachers table
CREATE TABLE teachers (
    teacher_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    department_id INT NOT NULL,
    role ENUM('teacher', 'HOD', 'admin') DEFAULT 'teacher',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE
);

-- Create subjects table
CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    teacher_id INT NOT NULL,
    department_id INT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE
);

-- Create feedback table
CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
);

-- Insert sample departments
INSERT INTO departments (dept_name) VALUES 
('Computer Science'),
('Mathematics'),
('Physics'),
('Chemistry'),
('Biology'),
('Engineering');

-- Insert sample teachers (password: password123)
INSERT INTO teachers (name, email, password, department_id, role) VALUES 
('Dr. Sarah Johnson', 'sarah.johnson@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 2, 'teacher'),
('Dr. Chen Wei', 'chen.wei@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 1, 'teacher'),
('Prof. Michael Brown', 'michael.brown@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 3, 'teacher'),
('Dr. Emily Davis', 'emily.davis@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 4, 'teacher'),
('Prof. Robert Wilson', 'robert.wilson@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 5, 'teacher'),
('Dr. Lisa Anderson', 'lisa.anderson@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 6, 'teacher'),
('Admin User', 'admin@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 1, 'admin');

-- Insert sample students (password: password123)
INSERT INTO students (name, email, password, department_id) VALUES 
('John Doe', 'john.doe@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 2),
('Sarah Wilson', 'sarah.wilson@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 1),
('Mike Johnson', 'mike.johnson@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 3),
('Emily Davis', 'emily.davis@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 4),
('Alex Chen', 'alex.chen@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 5),
('Lisa Wang', 'lisa.wang@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 6);

-- Insert sample subjects
INSERT INTO subjects (subject_name, teacher_id, department_id) VALUES 
('Advanced Mathematics', 1, 2),
('Data Structures & Algorithms', 2, 1),
('Quantum Physics', 3, 3),
('Organic Chemistry', 4, 4),
('Cell Biology', 5, 5),
('Mechanical Design', 6, 6);

-- Insert sample feedback
INSERT INTO feedback (student_id, teacher_id, subject_id, rating, comment) VALUES 
(1, 1, 1, 5, 'Excellent teaching methods and very helpful professor. The course material is well-structured and easy to understand.'),
(2, 2, 2, 4, 'Great course content and practical examples. Professor Chen explains complex concepts clearly.'),
(3, 3, 3, 5, 'Amazing course! Professor Brown makes quantum physics accessible and interesting.'),
(4, 4, 4, 4, 'Very informative course with good lab sessions. Professor Davis is knowledgeable and approachable.'),
(5, 5, 5, 3, 'Course content is good but could use more visual aids. Professor Wilson is helpful during office hours.'),
(6, 6, 6, 5, 'Outstanding course! Professor Anderson provides excellent real-world examples and industry insights.');

-- Create indexes for better performance
CREATE INDEX idx_feedback_student ON feedback(student_id);
CREATE INDEX idx_feedback_teacher ON feedback(teacher_id);
CREATE INDEX idx_feedback_subject ON feedback(subject_id);
CREATE INDEX idx_feedback_timestamp ON feedback(timestamp);
CREATE INDEX idx_students_department ON students(department_id);
CREATE INDEX idx_teachers_department ON teachers(department_id);
CREATE INDEX idx_subjects_teacher ON subjects(teacher_id);
CREATE INDEX idx_subjects_department ON subjects(department_id);
