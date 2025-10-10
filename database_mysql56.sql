-- ========================================
-- WOF TRAINING INSTITUTE PORTAL DATABASE
-- MySQL 5.6 / XAMPP / PHPMyAdmin Compatible
-- ========================================
-- Run this script in phpMyAdmin to create the complete database structure
-- This script is optimized for MySQL 5.6 and will not break your system

-- Create database
CREATE DATABASE IF NOT EXISTS wof_training_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wof_training_portal;

-- ========================================
-- CORE TABLES
-- ========================================

-- Users table (admin, students, graduates)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    address TEXT DEFAULT NULL,
    nationality VARCHAR(100) DEFAULT 'Nigerian',
    linkedin_profile VARCHAR(255) DEFAULT NULL,
    emergency_contact VARCHAR(255) DEFAULT NULL,
    user_type ENUM('admin', 'student', 'graduate') DEFAULT 'student',
    status ENUM('pending', 'approved', 'rejected', 'graduated') DEFAULT 'pending',
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cohorts table
CREATE TABLE IF NOT EXISTS cohorts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    course_type VARCHAR(100) DEFAULT NULL,
    duration_months INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    max_students INT DEFAULT 50,
    status ENUM('upcoming', 'active', 'completed') DEFAULT 'upcoming',
    cohort_image VARCHAR(255) DEFAULT NULL,
    course_outline TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_course_type (course_type),
    INDEX idx_start_date (start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Applications table
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cohort_id INT NOT NULL,

    -- Personal Information
    surname VARCHAR(100) DEFAULT NULL,
    first_name VARCHAR(100) DEFAULT NULL,
    other_names VARCHAR(100) DEFAULT NULL,
    gender VARCHAR(20) DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    phone_number VARCHAR(20) DEFAULT NULL,
    email_address VARCHAR(255) DEFAULT NULL,
    contact_address TEXT DEFAULT NULL,
    state_of_origin VARCHAR(100) DEFAULT NULL,

    -- Education
    highest_qualification VARCHAR(100) DEFAULT NULL,
    qualification_other VARCHAR(200) DEFAULT NULL,

    -- English Proficiency
    english_speaking_level VARCHAR(100) DEFAULT NULL,
    english_understanding_level VARCHAR(100) DEFAULT NULL,

    -- Course Selection
    course_choice VARCHAR(100) DEFAULT NULL,
    preferred_session VARCHAR(50) DEFAULT NULL,

    -- Computer Literacy
    computer_understanding VARCHAR(100) DEFAULT NULL,
    computer_understanding_other VARCHAR(200) DEFAULT NULL,

    -- Marketing
    how_heard_about VARCHAR(100) DEFAULT NULL,
    how_heard_other VARCHAR(200) DEFAULT NULL,

    -- Legacy fields for backward compatibility
    motivation TEXT DEFAULT NULL,
    education_background TEXT DEFAULT NULL,
    work_experience TEXT DEFAULT NULL,

    -- Credentials Upload
    credential_filename VARCHAR(255) DEFAULT NULL,
    credential_file_type VARCHAR(50) DEFAULT NULL,
    credential_uploaded_at TIMESTAMP NULL DEFAULT NULL,

    -- Application Status
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT DEFAULT NULL,
    reviewed_by INT DEFAULT NULL,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_cohort (user_id, cohort_id),
    INDEX idx_user_id (user_id),
    INDEX idx_cohort_id (cohort_id),
    INDEX idx_status (status),
    INDEX idx_applied_at (applied_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enrollments table
CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cohort_id INT NOT NULL,
    application_id INT DEFAULT NULL,
    enrollment_date DATE NOT NULL,
    completion_date DATE DEFAULT NULL,
    status ENUM('enrolled', 'completed', 'dropped') DEFAULT 'enrolled',
    admission_letter_generated TINYINT(1) DEFAULT 0,
    admission_letter_filename VARCHAR(255) DEFAULT NULL,
    admission_letter_generated_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_cohort (user_id, cohort_id),
    INDEX idx_user_id (user_id),
    INDEX idx_cohort_id (cohort_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Examinations table
CREATE TABLE IF NOT EXISTS examinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cohort_id INT NOT NULL,
    enrollment_id INT DEFAULT NULL,
    exam_title VARCHAR(200) NOT NULL,
    exam_date DATE NOT NULL,
    exam_type ENUM('midterm', 'final', 'practical', 'assessment') DEFAULT 'final',
    score DECIMAL(5,2) NOT NULL,
    max_score DECIMAL(5,2) NOT NULL DEFAULT 100.00,
    total_marks INT DEFAULT 100,
    passing_marks INT DEFAULT 70,
    grade VARCHAR(10) DEFAULT NULL,
    status ENUM('scheduled', 'taken', 'passed', 'failed') DEFAULT 'scheduled',
    admin_feedback TEXT DEFAULT NULL,
    remarks TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE CASCADE,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_cohort_id (cohort_id),
    INDEX idx_enrollment_id (enrollment_id),
    INDEX idx_exam_date (exam_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Certificates table
CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    enrollment_id INT NOT NULL,
    examination_id INT DEFAULT NULL,
    certificate_number VARCHAR(50) UNIQUE NOT NULL,
    certificate_filename VARCHAR(255) DEFAULT NULL,
    course_name VARCHAR(200) NOT NULL,
    issue_date DATE NOT NULL,
    is_released TINYINT(1) DEFAULT 0,
    released_at TIMESTAMP NULL DEFAULT NULL,
    generated_by INT DEFAULT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (examination_id) REFERENCES examinations(id) ON DELETE SET NULL,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_enrollment (enrollment_id),
    INDEX idx_user_id (user_id),
    INDEX idx_certificate_number (certificate_number),
    INDEX idx_is_released (is_released)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admission Letters table
CREATE TABLE IF NOT EXISTS admission_letters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    user_id INT NOT NULL,
    cohort_id INT NOT NULL,
    letter_number VARCHAR(50) UNIQUE NOT NULL,
    letter_filename VARCHAR(255) DEFAULT NULL,
    issue_date DATE NOT NULL,
    generated_by INT DEFAULT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE CASCADE,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_application (application_id),
    INDEX idx_user_id (user_id),
    INDEX idx_cohort_id (cohort_id),
    INDEX idx_letter_number (letter_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Graduate profiles table
CREATE TABLE IF NOT EXISTS graduate_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    skills TEXT DEFAULT NULL,
    linkedin_url VARCHAR(255) DEFAULT NULL,
    portfolio_url VARCHAR(255) DEFAULT NULL,
    github_url VARCHAR(255) DEFAULT NULL,
    cv_filename VARCHAR(255) DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    years_experience INT DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    is_visible TINYINT(1) DEFAULT 1,
    is_job_ready TINYINT(1) DEFAULT 1,
    education_history TEXT DEFAULT NULL,
    work_history TEXT DEFAULT NULL,
    certifications TEXT DEFAULT NULL,
    languages TEXT DEFAULT NULL,
    achievements TEXT DEFAULT NULL,
    referenc TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id),
    INDEX idx_user_id (user_id),
    INDEX idx_is_visible (is_visible),
    INDEX idx_is_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Portfolio items table
CREATE TABLE IF NOT EXISTS portfolio_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    project_url VARCHAR(255) DEFAULT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    thumbnail VARCHAR(255) DEFAULT NULL,
    category VARCHAR(100) DEFAULT NULL,
    technologies TEXT DEFAULT NULL,
    project_date DATE DEFAULT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    is_visible TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_visible (is_visible),
    INDEX idx_is_featured (is_featured),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Testimonials table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cohort_id INT DEFAULT NULL,
    content TEXT NOT NULL,
    testimonial_text TEXT NOT NULL,
    rating INT DEFAULT 5,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 0,
    is_approved TINYINT(1) DEFAULT 0,
    approved_by INT DEFAULT NULL,
    approved_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_cohort_id (cohort_id),
    INDEX idx_is_active (is_active),
    INDEX idx_is_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Course materials table
CREATE TABLE IF NOT EXISTS course_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cohort_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    file_url TEXT DEFAULT NULL,
    material_type ENUM('document', 'video', 'assignment', 'resource') DEFAULT 'document',
    week_number INT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    is_visible TINYINT(1) DEFAULT 1,
    uploaded_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_cohort_id (cohort_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    event_type ENUM('examination', 'graduation', 'workshop', 'meeting', 'other') DEFAULT 'other',
    cohort_id INT DEFAULT NULL,
    start_date DATE NOT NULL,
    end_date DATE DEFAULT NULL,
    start_time TIME DEFAULT NULL,
    end_time TIME DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    max_participants INT DEFAULT NULL,
    status ENUM('scheduled', 'ongoing', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_cohort_id (cohort_id),
    INDEX idx_event_type (event_type),
    INDEX idx_start_date (start_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Graduate certifications table
CREATE TABLE IF NOT EXISTS graduate_certifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cohort_id INT NOT NULL,
    certification_date DATE NOT NULL,
    certificate_number VARCHAR(50) UNIQUE NOT NULL,
    skills_verified TEXT DEFAULT NULL,
    admin_notes TEXT DEFAULT NULL,
    is_job_ready TINYINT(1) DEFAULT 0,
    certification_level ENUM('basic', 'intermediate', 'advanced', 'expert') DEFAULT 'basic',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_cohort_id (cohort_id),
    INDEX idx_certificate_number (certificate_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'unread',
    admin_response TEXT DEFAULT NULL,
    responded_by INT DEFAULT NULL,
    responded_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (responded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- SAMPLE DATA
-- ========================================

-- Insert default admin user (password: admin123)
-- Password hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO users (email, password, first_name, last_name, user_type, status)
VALUES ('admin@wof.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'WOF', 'Administrator', 'admin', 'approved');

-- Insert sample cohorts
INSERT INTO cohorts (name, description, course_type, duration_months, start_date, end_date, max_students, status) VALUES
('Web Development Bootcamp Q1 2025', 'Intensive 6-month web development program covering full-stack technologies', 'fullstack_development', 6, '2025-01-15', '2025-07-15', 30, 'upcoming'),
('Digital Marketing Essentials', '4-month comprehensive digital marketing course', 'digital_marketing', 4, '2025-02-01', '2025-06-01', 25, 'upcoming'),
('Data Analytics Fundamentals', '3-month data analysis and visualization program', 'data_analytics', 3, '2025-03-01', '2025-06-01', 20, 'upcoming');

-- Insert sample testimonials
INSERT INTO testimonials (user_id, cohort_id, content, testimonial_text, rating, is_featured, is_active, is_approved) VALUES
(1, 1, 'The Web Development Bootcamp transformed my career completely. I went from having no coding experience to landing a job as a full-stack developer within 3 months of graduation. The instructors were amazing and the curriculum was very practical.', 'The Web Development Bootcamp transformed my career completely. I went from having no coding experience to landing a job as a full-stack developer within 3 months of graduation. The instructors were amazing and the curriculum was very practical.', 5, 1, 1, 1),
(1, 2, 'The Digital Marketing program gave me all the skills I needed to start my own agency. The hands-on projects and real-world case studies made all the difference. I now manage social media for 15+ local businesses.', 'The Digital Marketing program gave me all the skills I needed to start my own agency. The hands-on projects and real-world case studies made all the difference. I now manage social media for 15+ local businesses.', 5, 1, 1, 1),
(1, 3, 'Data Analytics Fundamentals opened up a whole new world for me. I learned Python, SQL, and data visualization tools that helped me transition from accounting to data science. The support from instructors was exceptional.', 'Data Analytics Fundamentals opened up a whole new world for me. I learned Python, SQL, and data visualization tools that helped me transition from accounting to data science. The support from instructors was exceptional.', 5, 0, 1, 1);

-- Insert sample events
INSERT INTO events (title, description, event_type, cohort_id, start_date, start_time, end_time, location, created_by) VALUES
('Web Development Final Examination', 'Final assessment for Web Development Bootcamp Q1 2025', 'examination', 1, '2025-07-10', '09:00:00', '12:00:00', 'WOF Training Center, Lagos', 1),
('Digital Marketing Graduation Ceremony', 'Graduation ceremony for Digital Marketing Essentials cohort', 'graduation', 2, '2025-06-15', '14:00:00', '17:00:00', 'WOF Foundation Hall', 1),
('Data Analytics Workshop', 'Advanced data visualization workshop for current students', 'workshop', 3, '2025-04-20', '10:00:00', '16:00:00', 'Online via Zoom', 1);

-- Insert sample certifications
INSERT INTO graduate_certifications (user_id, cohort_id, certification_date, certificate_number, skills_verified, is_job_ready, certification_level, created_by) VALUES
(1, 1, '2024-12-15', 'WOF-WD-2024-001', 'HTML, CSS, JavaScript, React, Node.js, Database Management', 1, 'advanced', 1);

-- ========================================
-- VERIFICATION QUERIES
-- ========================================
-- Run these queries after importing to verify everything worked correctly

-- Check all tables exist
-- SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'wof_training_portal' ORDER BY TABLE_NAME;

-- Count records in each table
-- SELECT 'users' AS table_name, COUNT(*) AS record_count FROM users
-- UNION ALL SELECT 'cohorts', COUNT(*) FROM cohorts
-- UNION ALL SELECT 'applications', COUNT(*) FROM applications
-- UNION ALL SELECT 'enrollments', COUNT(*) FROM enrollments
-- UNION ALL SELECT 'examinations', COUNT(*) FROM examinations
-- UNION ALL SELECT 'certificates', COUNT(*) FROM certificates
-- UNION ALL SELECT 'admission_letters', COUNT(*) FROM admission_letters
-- UNION ALL SELECT 'graduate_profiles', COUNT(*) FROM graduate_profiles
-- UNION ALL SELECT 'testimonials', COUNT(*) FROM testimonials
-- UNION ALL SELECT 'course_materials', COUNT(*) FROM course_materials
-- UNION ALL SELECT 'events', COUNT(*) FROM events
-- UNION ALL SELECT 'graduate_certifications', COUNT(*) FROM graduate_certifications
-- UNION ALL SELECT 'contact_messages', COUNT(*) FROM contact_messages;

-- ========================================
-- DATABASE SETUP COMPLETE
-- ========================================
-- Default Admin Login Credentials:
-- Email: admin@wof.edu
-- Password: admin123
--
-- IMPORTANT: Change the admin password after first login!
-- ========================================
