-- WOF Training Institute Portal Database Schema
-- Run this script in phpMyAdmin to create the database structure

CREATE DATABASE IF NOT EXISTS wof_training_portal;
USE wof_training_portal;

-- Users table (admin, students, graduates)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    user_type ENUM('admin', 'student', 'graduate') DEFAULT 'student',
    status ENUM('pending', 'approved', 'rejected', 'graduated') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Cohorts table
CREATE TABLE cohorts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    duration_months INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    max_students INT DEFAULT 50,
    status ENUM('upcoming', 'active', 'completed') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Applications table
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cohort_id INT NOT NULL,
    motivation TEXT,
    education_background TEXT,
    work_experience TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE CASCADE
);

-- Enrollments table
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cohort_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    completion_date DATE NULL,
    status ENUM('enrolled', 'completed', 'dropped') DEFAULT 'enrolled',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE CASCADE
);

-- Examinations table
CREATE TABLE examinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cohort_id INT NOT NULL,
    exam_date DATE NOT NULL,
    score DECIMAL(5,2),
    status ENUM('scheduled', 'taken', 'passed', 'failed') DEFAULT 'scheduled',
    admin_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE CASCADE
);

-- Graduate profiles table
CREATE TABLE graduate_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200),
    bio TEXT,
    skills TEXT,
    linkedin_url VARCHAR(255),
    portfolio_url VARCHAR(255),
    cv_filename VARCHAR(255),
    profile_image VARCHAR(255),
    years_experience INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_visible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Course materials table
CREATE TABLE course_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cohort_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_path VARCHAR(255),
    material_type ENUM('document', 'video', 'assignment', 'resource') DEFAULT 'document',
    week_number INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO users (email, password, first_name, last_name, user_type, status) 
VALUES ('admin@wof.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'WOF', 'Administrator', 'admin', 'approved');

-- Insert sample cohorts
INSERT INTO cohorts (name, description, duration_months, start_date, end_date, max_students, status) VALUES
('Web Development Bootcamp Q1 2025', 'Intensive 6-month web development program covering full-stack technologies', 6, '2025-01-15', '2025-07-15', 30, 'upcoming'),
('Digital Marketing Essentials', '4-month comprehensive digital marketing course', 4, '2025-02-01', '2025-06-01', 25, 'upcoming'),
('Data Analytics Fundamentals', '3-month data analysis and visualization program', 3, '2025-03-01', '2025-06-01', 20, 'upcoming');