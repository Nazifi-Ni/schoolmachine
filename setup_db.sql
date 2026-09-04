-- Create database
CREATE DATABASE IF NOT EXISTS iams_arms;
USE iams_arms;

-- 1. Roles Table
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

INSERT IGNORE INTO roles (id, name) VALUES (1, 'Head Teacher'), (2, 'Class Teacher');

-- 2. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) UNIQUE,
    google_id VARCHAR(255) UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Insert default Head Teacher
INSERT IGNORE INTO users (username, email, password, role_id, status) VALUES 
('headteacher', 'headteacher@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'active'); 
-- password is 'password'

-- 3. Teachers Table
CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE,
    phone VARCHAR(20),
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. Classes Table
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    level VARCHAR(50) NOT NULL,
    teacher_id INT NULL,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
);

INSERT IGNORE INTO classes (name, level) VALUES
('Nursery 1', 'Nursery'),
('Nursery 2', 'Nursery'),
('Primary 1', 'Primary'),
('Primary 2', 'Primary'),
('Primary 3', 'Primary'),
('Primary 4', 'Primary'),
('Primary 5', 'Primary'),
('Primary 6', 'Primary');

-- 5. Subjects Table
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    class_id INT NOT NULL,
    session_id INT NULL,
    term_id INT NULL,
    UNIQUE(name, class_id, session_id, term_id),
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

-- 6. Sessions Table
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL UNIQUE,
    is_current BOOLEAN DEFAULT FALSE
);

-- 7. Terms Table
CREATE TABLE IF NOT EXISTS terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL,
    session_id INT NOT NULL,
    is_current BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- 8. Students Table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_number VARCHAR(50) UNIQUE,
    surname VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    gender ENUM('Male', 'Female') NOT NULL,
    dob DATE NOT NULL,
    parent_name VARCHAR(200),
    phone VARCHAR(20),
    address TEXT,
    current_class_id INT NOT NULL,
    status ENUM('active', 'graduated', 'withdrawn', 'suspended') DEFAULT 'active',
    admission_date DATE,
    passport VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (current_class_id) REFERENCES classes(id)
);

-- 9. Results Table
CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    session_id INT NOT NULL,
    term_id INT NOT NULL,
    status ENUM('OPEN', 'PENDING', 'APPROVED', 'PUBLISHED') DEFAULT 'OPEN',
    class_teacher_remark TEXT NULL,
    head_teacher_remark TEXT NULL,
    head_teacher_name VARCHAR(255) NULL,
    attendance INT NULL,
    resumption_date VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE(student_id, class_id, session_id, term_id),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE
);

-- 10. Result Items Table
CREATE TABLE IF NOT EXISTS result_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    result_id INT NOT NULL,
    subject_id INT NOT NULL,
    ca1 DECIMAL(5,2) DEFAULT 0,
    ca2 DECIMAL(5,2) DEFAULT 0,
    exam DECIMAL(5,2) DEFAULT 0,
    total DECIMAL(5,2) DEFAULT 0,
    grade VARCHAR(5),
    remark VARCHAR(50),
    UNIQUE(result_id, subject_id),
    FOREIGN KEY (result_id) REFERENCES results(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- 11. Attendance Table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    session_id INT NOT NULL,
    term_id INT NOT NULL,
    days_present INT DEFAULT 0,
    days_opened INT DEFAULT 0,
    UNIQUE(student_id, class_id, session_id, term_id),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE
);

-- 12. Grading System Table
CREATE TABLE IF NOT EXISTS grading_system (
    id INT AUTO_INCREMENT PRIMARY KEY,
    min_score INT NOT NULL,
    max_score INT NOT NULL,
    grade VARCHAR(5) NOT NULL,
    remark VARCHAR(50) NOT NULL
);

INSERT IGNORE INTO grading_system (min_score, max_score, grade, remark) VALUES
(90, 100, 'A1', 'Excellent'),
(80, 89, 'B2', 'Excellent'),
(70, 79, 'B3', 'Very Good'),
(65, 69, 'C4', 'Good'),
(60, 64, 'C5', 'Good'),
(50, 59, 'C6', 'Good'),
(45, 49, 'D7', 'Fair'),
(40, 44, 'E8', 'Poor'),
(0, 39, 'F9', 'Very Poor');

-- 13. Comments Table (AI / Predefined)
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    min_average DECIMAL(5,2) NOT NULL,
    max_average DECIMAL(5,2) NOT NULL,
    text TEXT NOT NULL
);

INSERT IGNORE INTO comments (min_average, max_average, text) VALUES
(90, 100, 'Outstanding performance. Keep maintaining your excellent academic standard.'),
(70, 89.99, 'Very good performance. Keep it up.'),
(50, 69.99, 'Good effort. Greater consistency and improved study habits are encouraged.'),
(0, 49.99, 'Poor performance. You need to work harder next term.');

-- 14. Promotions Table
CREATE TABLE IF NOT EXISTS promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    from_class_id INT NOT NULL,
    to_class_id INT NULL,
    session_id INT NOT NULL,
    promoted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (from_class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (to_class_id) REFERENCES classes(id) ON DELETE SET NULL,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- 15. Audit Logs Table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 16. School Information Table
CREATE TABLE IF NOT EXISTS school_information (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    email VARCHAR(100),
    phone VARCHAR(20),
    website VARCHAR(100),
    logo VARCHAR(255)
);

INSERT IGNORE INTO school_information (name, address, email, phone, website) VALUES
('Ismail Ahmad Memorial Nursery and Primary School', 'Agency for Mass Education Women Centre No.428 Tsohon Layi Jahun Jigawa State', 'ismailahmadmemorialschool@yahoo.com', '08036644211', 'https://ismailahmadmemorial.skoolstack.com');
