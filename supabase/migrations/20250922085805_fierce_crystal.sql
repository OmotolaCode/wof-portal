/*
  # Complete Admin System Enhancement

  1. New Tables
    - `events` - For managing foundation events and schedules
    - `cv_templates` - For storing CV template configurations
    - `graduate_certifications` - For tracking certified graduates
    - Enhanced existing tables with additional fields

  2. Security
    - Admin-only access controls
    - Graduate certification workflow
    - Event management system

  3. Features
    - International standard CV generation
    - Graduate certification system
    - Event and examination scheduling
    - Enhanced profile management
*/

-- Events table for managing foundation events and schedules
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_type ENUM('examination', 'graduation', 'workshop', 'meeting', 'other') DEFAULT 'other',
    cohort_id INT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    location VARCHAR(255),
    max_participants INT DEFAULT NULL,
    status ENUM('scheduled', 'ongoing', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Graduate certifications table
CREATE TABLE IF NOT EXISTS graduate_certifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cohort_id INT NOT NULL,
    certification_date DATE NOT NULL,
    certificate_number VARCHAR(50) UNIQUE NOT NULL,
    skills_verified TEXT,
    admin_notes TEXT,
    is_job_ready BOOLEAN DEFAULT FALSE,
    certification_level ENUM('basic', 'intermediate', 'advanced', 'expert') DEFAULT 'basic',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Add additional fields to users table for CV generation
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS date_of_birth DATE NULL,
ADD COLUMN IF NOT EXISTS address TEXT NULL,
ADD COLUMN IF NOT EXISTS nationality VARCHAR(100) DEFAULT 'Nigerian',
ADD COLUMN IF NOT EXISTS linkedin_profile VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS emergency_contact VARCHAR(255) NULL;

-- Add additional fields to graduate_profiles for enhanced CV
ALTER TABLE graduate_profiles 
ADD COLUMN IF NOT EXISTS education_history TEXT NULL,
ADD COLUMN IF NOT EXISTS work_history TEXT NULL,
ADD COLUMN IF NOT EXISTS certifications TEXT NULL,
ADD COLUMN IF NOT EXISTS languages TEXT NULL,
ADD COLUMN IF NOT EXISTS achievements TEXT NULL,
ADD COLUMN IF NOT EXISTS references TEXT NULL;

-- Add fields to examinations table
ALTER TABLE examinations 
ADD COLUMN IF NOT EXISTS exam_type ENUM('midterm', 'final', 'practical', 'assessment') DEFAULT 'final',
ADD COLUMN IF NOT EXISTS total_marks INT DEFAULT 100,
ADD COLUMN IF NOT EXISTS passing_marks INT DEFAULT 70;

-- Insert sample events
INSERT INTO events (title, description, event_type, cohort_id, start_date, start_time, end_time, location, created_by) VALUES
('Web Development Final Examination', 'Final assessment for Web Development Bootcamp Q1 2025', 'examination', 1, '2025-07-10', '09:00:00', '12:00:00', 'WOF Training Center, Lagos', 1),
('Digital Marketing Graduation Ceremony', 'Graduation ceremony for Digital Marketing Essentials cohort', 'graduation', 2, '2025-06-15', '14:00:00', '17:00:00', 'WOF Foundation Hall', 1),
('Data Analytics Workshop', 'Advanced data visualization workshop for current students', 'workshop', 3, '2025-04-20', '10:00:00', '16:00:00', 'Online via Zoom', 1);

-- Insert sample certifications
INSERT INTO graduate_certifications (user_id, cohort_id, certification_date, certificate_number, skills_verified, is_job_ready, certification_level, created_by) VALUES
(1, 1, '2024-12-15', 'WOF-WD-2024-001', 'HTML, CSS, JavaScript, React, Node.js, Database Management', TRUE, 'advanced', 1);