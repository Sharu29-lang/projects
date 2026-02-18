CREATE DATABASE IF NOT EXISTS smart_attendance;
USE smart_attendance;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) UNIQUE NOT NULL
);

INSERT INTO roles (name) VALUES ('admin'), ('staff');

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

INSERT INTO users (role_id, username, full_name, password_hash)
VALUES
((SELECT id FROM roles WHERE name='admin'), 'admin', 'System Administrator', '$2y$12$1PWawuWCVt2iOrwdejby8OwioNkiHXGkkYsu51XlX9cCu.LcN4fBm'),
((SELECT id FROM roles WHERE name='staff'), 'staff1', 'Staff Member One', '$2y$12$1PWawuWCVt2iOrwdejby8OwioNkiHXGkkYsu51XlX9cCu.LcN4fBm');

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL
);

INSERT INTO departments (name) VALUES ('Computer Science'), ('Electronics'), ('Mechanical');

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(30) UNIQUE NOT NULL,
    name VARCHAR(120) NOT NULL,
    department_id INT,
    year_level TINYINT NOT NULL,
    section VARCHAR(10) NOT NULL,
    face_folder VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    subject_name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO subjects (subject_code, subject_name)
VALUES ('CS101', 'Data Structures'), ('CS102', 'Database Systems'), ('CS103', 'Operating Systems');

CREATE TABLE staff_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject_id INT NOT NULL,
    UNIQUE KEY unique_staff_subject (user_id, subject_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);

INSERT INTO staff_subjects (user_id, subject_id)
SELECT u.id, s.id
FROM users u
JOIN subjects s ON s.subject_code IN ('CS101', 'CS102')
WHERE u.username='staff1';

CREATE TABLE attendance_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    staff_id INT NOT NULL,
    period_label VARCHAR(50) NOT NULL,
    attendance_date DATE NOT NULL,
    started_at DATETIME NOT NULL,
    ended_at DATETIME,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (staff_id) REFERENCES users(id)
);

CREATE TABLE attendance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attendance_session_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('present', 'absent') NOT NULL DEFAULT 'absent',
    marked_at DATETIME,
    UNIQUE KEY unique_attendance_student (attendance_session_id, student_id),
    FOREIGN KEY (attendance_session_id) REFERENCES attendance_sessions(id),
    FOREIGN KEY (student_id) REFERENCES students(id)
);
