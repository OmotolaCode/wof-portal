/*
  # Enhanced Application Form System

  1. New Tables
    - Enhanced `applications` table with comprehensive student information
    - All required fields from the standard application form

  2. Security
    - Maintains existing foreign key constraints
    - Admin approval workflow remains intact

  3. Features
    - Complete student profile capture during application
    - Educational background tracking
    - English proficiency assessment
    - Computer literacy evaluation
    - Course preferences and scheduling
    - Marketing source tracking
*/

-- Add comprehensive fields to applications table
ALTER TABLE applications 
ADD COLUMN IF NOT EXISTS surname VARCHAR(100) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS first_name VARCHAR(100) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS other_names VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS gender ENUM('male', 'female') NOT NULL DEFAULT 'male',
ADD COLUMN IF NOT EXISTS date_of_birth DATE NOT NULL DEFAULT '1990-01-01',
ADD COLUMN IF NOT EXISTS phone_number VARCHAR(20) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS email_address VARCHAR(255) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS contact_address TEXT NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS state_of_origin VARCHAR(100) NOT NULL DEFAULT '',
ADD COLUMN IF NOT EXISTS highest_qualification ENUM(
    'olevel_ssce', 
    'undergraduate', 
    'national_diploma', 
    'nce', 
    'hnd', 
    'degree', 
    'postgraduate', 
    'other'
) NOT NULL DEFAULT 'olevel_ssce',
ADD COLUMN IF NOT EXISTS qualification_other VARCHAR(200) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS english_speaking_level ENUM(
    'natural', 
    'easier_but_confused', 
    'quite_tricky', 
    'hard_to_string'
) NOT NULL DEFAULT 'natural',
ADD COLUMN IF NOT EXISTS english_understanding_level ENUM(
    'natural_as_native', 
    'most_time_but_lost', 
    'slow_and_clear', 
    'lost_easily'
) NOT NULL DEFAULT 'natural_as_native',
ADD COLUMN IF NOT EXISTS course_choice ENUM(
    'desktop_publishing',
    'graphics_design_ui_ux',
    'web_design',
    'digital_marketing',
    'photography_video_editing',
    'frontend_development',
    'backend_development',
    'fullstack_development',
    'mobile_app_development',
    'data_analytics'
) NOT NULL DEFAULT 'web_design',
ADD COLUMN IF NOT EXISTS preferred_session ENUM(
    'morning_10_12',
    'afternoon_230_430',
    'weekends_8_1'
) NOT NULL DEFAULT 'morning_10_12',
ADD COLUMN IF NOT EXISTS computer_understanding ENUM(
    'can_operate',
    'have_personal_effective',
    'no_personal_but_operate',
    'never_operated',
    'other'
) NOT NULL DEFAULT 'can_operate',
ADD COLUMN IF NOT EXISTS computer_understanding_other VARCHAR(200) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS how_heard_about ENUM(
    'flyers',
    'banner',
    'road_jingle',
    'social_media',
    'radio_jingle',
    'friend_relative',
    'wof_batch1_student',
    'other'
) NOT NULL DEFAULT 'social_media',
ADD COLUMN IF NOT EXISTS how_heard_other VARCHAR(200) DEFAULT NULL;

-- Update existing applications to have default values for new required fields
UPDATE applications SET 
    surname = COALESCE((SELECT last_name FROM users WHERE users.id = applications.user_id), 'Unknown'),
    first_name = COALESCE((SELECT first_name FROM users WHERE users.id = applications.user_id), 'Unknown'),
    phone_number = COALESCE((SELECT phone FROM users WHERE users.id = applications.user_id), ''),
    email_address = COALESCE((SELECT email FROM users WHERE users.id = applications.user_id), '')
WHERE surname = '' OR first_name = '' OR phone_number = '' OR email_address = '';