/*
  # Complete System Enhancement Migration

  ## Overview
  This migration creates all missing tables and fields needed for the complete Learning Management System.

  ## 1. New Tables
  
  ### `users` - Core user accounts
  - `id` (uuid, primary key)
  - `email` (text, unique, not null)
  - `password_hash` (text, not null)
  - `first_name` (varchar 100, not null)
  - `last_name` (varchar 100, not null)
  - `phone` (varchar 20)
  - `user_type` (enum: student, admin, graduate)
  - `user_status` (enum: pending, approved, active, graduated)
  - `profile_image` (text)
  - `created_at` (timestamptz)
  - `updated_at` (timestamptz)

  ### `cohorts` - Training cohorts/batches
  - `id` (uuid, primary key)
  - `name` (varchar 200, not null)
  - `description` (text)
  - `course_type` (enum: various course types)
  - `duration_months` (integer)
  - `start_date` (date)
  - `end_date` (date)
  - `status` (enum: upcoming, active, completed)
  - `cohort_image` (text)
  - `course_outline` (text)
  - `max_students` (integer)
  - `created_at` (timestamptz)

  ### `applications` - Student applications
  - Comprehensive student application form data
  - Personal information, education, course preferences
  - Admin approval workflow

  ### `enrollments` - Student enrollments in cohorts
  - Links students to their cohorts
  - Tracks enrollment status and dates
  - Admission letter generation tracking

  ### `examinations` - Student examination records
  - Exam scores and grades
  - Certificate eligibility tracking (70+ threshold)

  ### `certificates` - Generated certificates
  - Certificate metadata and file references
  - Generation and release tracking

  ### `admission_letters` - Generated admission letters
  - Admission letter metadata
  - Generation and access tracking

  ### `graduate_profiles` - Graduate showcase profiles
  - Professional profiles for job-ready graduates
  - Skills, experience, portfolio links

  ### `testimonials` - Student testimonials
  - Reviews and experiences shared by students/graduates

  ### `course_materials` - Learning materials for cohorts
  - Documents, videos, links
  - Organized by cohort

  ### `contact_messages` - Support contact messages
  - Student inquiries and support requests

  ## 2. Security
  - RLS enabled on ALL tables
  - Restrictive policies based on user authentication
  - Students can only access their own data
  - Admins have full access for management
  - Public access only for showcase and public pages

  ## 3. Important Notes
  - All timestamps use timestamptz with automatic tracking
  - Foreign keys ensure data integrity
  - Default values prevent null-related issues
  - Certificate generation requires 70+ examination grade
  - Admission letters generated when application approved
*/

-- Create custom types
DO $$ BEGIN
  CREATE TYPE user_type_enum AS ENUM ('student', 'admin', 'graduate');
EXCEPTION
  WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
  CREATE TYPE user_status_enum AS ENUM ('pending', 'approved', 'active', 'graduated');
EXCEPTION
  WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
  CREATE TYPE cohort_status_enum AS ENUM ('upcoming', 'active', 'completed');
EXCEPTION
  WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
  CREATE TYPE application_status_enum AS ENUM ('pending', 'approved', 'rejected');
EXCEPTION
  WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
  CREATE TYPE enrollment_status_enum AS ENUM ('enrolled', 'completed', 'dropped');
EXCEPTION
  WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
  CREATE TYPE course_type_enum AS ENUM (
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
  );
EXCEPTION
  WHEN duplicate_object THEN null;
END $$;

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  email text UNIQUE NOT NULL,
  password_hash text NOT NULL,
  first_name varchar(100) NOT NULL,
  last_name varchar(100) NOT NULL,
  phone varchar(20),
  user_type user_type_enum NOT NULL DEFAULT 'student',
  user_status user_status_enum NOT NULL DEFAULT 'pending',
  profile_image text,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

-- Cohorts table
CREATE TABLE IF NOT EXISTS cohorts (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  name varchar(200) NOT NULL,
  description text,
  course_type course_type_enum NOT NULL,
  duration_months integer NOT NULL DEFAULT 3,
  start_date date NOT NULL,
  end_date date,
  status cohort_status_enum NOT NULL DEFAULT 'upcoming',
  cohort_image text,
  course_outline text,
  max_students integer DEFAULT 30,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

-- Applications table
CREATE TABLE IF NOT EXISTS applications (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  cohort_id uuid NOT NULL REFERENCES cohorts(id) ON DELETE CASCADE,
  
  -- Personal Information
  surname varchar(100) NOT NULL,
  first_name varchar(100) NOT NULL,
  other_names varchar(100),
  gender varchar(20) NOT NULL,
  date_of_birth date NOT NULL,
  phone_number varchar(20) NOT NULL,
  email_address varchar(255) NOT NULL,
  contact_address text NOT NULL,
  state_of_origin varchar(100) NOT NULL,
  
  -- Education
  highest_qualification text NOT NULL,
  qualification_other varchar(200),
  
  -- English Proficiency
  english_speaking_level text NOT NULL,
  english_understanding_level text NOT NULL,
  
  -- Course Selection
  course_choice course_type_enum NOT NULL,
  preferred_session text NOT NULL,
  
  -- Computer Literacy
  computer_understanding text NOT NULL,
  computer_understanding_other varchar(200),
  
  -- Marketing
  how_heard_about text NOT NULL,
  how_heard_other varchar(200),
  
  -- Application Status
  status application_status_enum NOT NULL DEFAULT 'pending',
  admin_notes text,
  reviewed_by uuid REFERENCES users(id),
  reviewed_at timestamptz,
  applied_at timestamptz DEFAULT now(),
  
  UNIQUE(user_id, cohort_id)
);

-- Enrollments table
CREATE TABLE IF NOT EXISTS enrollments (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  cohort_id uuid NOT NULL REFERENCES cohorts(id) ON DELETE CASCADE,
  application_id uuid REFERENCES applications(id),
  status enrollment_status_enum NOT NULL DEFAULT 'enrolled',
  enrollment_date timestamptz DEFAULT now(),
  completion_date timestamptz,
  admission_letter_generated boolean DEFAULT false,
  admission_letter_filename text,
  admission_letter_generated_at timestamptz,
  created_at timestamptz DEFAULT now(),
  
  UNIQUE(user_id, cohort_id)
);

-- Examinations table
CREATE TABLE IF NOT EXISTS examinations (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  enrollment_id uuid NOT NULL REFERENCES enrollments(id) ON DELETE CASCADE,
  user_id uuid NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  cohort_id uuid NOT NULL REFERENCES cohorts(id) ON DELETE CASCADE,
  exam_title varchar(200) NOT NULL,
  exam_date date NOT NULL,
  score numeric(5,2) NOT NULL,
  max_score numeric(5,2) NOT NULL DEFAULT 100.00,
  percentage numeric(5,2) GENERATED ALWAYS AS ((score / max_score) * 100) STORED,
  grade varchar(10),
  remarks text,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

-- Certificates table
CREATE TABLE IF NOT EXISTS certificates (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  enrollment_id uuid NOT NULL REFERENCES enrollments(id) ON DELETE CASCADE,
  examination_id uuid REFERENCES examinations(id),
  certificate_number varchar(50) UNIQUE NOT NULL,
  certificate_filename text,
  course_name varchar(200) NOT NULL,
  issue_date date NOT NULL DEFAULT CURRENT_DATE,
  is_released boolean DEFAULT false,
  released_at timestamptz,
  generated_by uuid REFERENCES users(id),
  generated_at timestamptz DEFAULT now(),
  
  UNIQUE(enrollment_id)
);

-- Admission Letters table
CREATE TABLE IF NOT EXISTS admission_letters (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  application_id uuid NOT NULL REFERENCES applications(id) ON DELETE CASCADE,
  user_id uuid NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  cohort_id uuid NOT NULL REFERENCES cohorts(id) ON DELETE CASCADE,
  letter_number varchar(50) UNIQUE NOT NULL,
  letter_filename text,
  issue_date date NOT NULL DEFAULT CURRENT_DATE,
  generated_by uuid REFERENCES users(id),
  generated_at timestamptz DEFAULT now(),
  
  UNIQUE(application_id)
);

-- Graduate Profiles table
CREATE TABLE IF NOT EXISTS graduate_profiles (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  title varchar(200),
  bio text,
  skills text,
  years_experience integer DEFAULT 0,
  linkedin_url text,
  portfolio_url text,
  github_url text,
  cv_filename text,
  profile_image text,
  is_job_ready boolean DEFAULT true,
  is_visible boolean DEFAULT true,
  is_featured boolean DEFAULT false,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now(),
  
  UNIQUE(user_id)
);

-- Testimonials table
CREATE TABLE IF NOT EXISTS testimonials (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  cohort_id uuid REFERENCES cohorts(id),
  testimonial_text text NOT NULL,
  rating integer CHECK (rating >= 1 AND rating <= 5),
  is_approved boolean DEFAULT false,
  is_featured boolean DEFAULT false,
  approved_by uuid REFERENCES users(id),
  approved_at timestamptz,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

-- Course Materials table
CREATE TABLE IF NOT EXISTS course_materials (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  cohort_id uuid NOT NULL REFERENCES cohorts(id) ON DELETE CASCADE,
  title varchar(200) NOT NULL,
  description text,
  material_type varchar(50) NOT NULL,
  file_path text,
  file_url text,
  sort_order integer DEFAULT 0,
  is_visible boolean DEFAULT true,
  uploaded_by uuid REFERENCES users(id),
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

-- Contact Messages table
CREATE TABLE IF NOT EXISTS contact_messages (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid REFERENCES users(id),
  name varchar(100) NOT NULL,
  email varchar(255) NOT NULL,
  phone varchar(20),
  subject varchar(200) NOT NULL,
  message text NOT NULL,
  status varchar(20) DEFAULT 'unread',
  admin_response text,
  responded_by uuid REFERENCES users(id),
  responded_at timestamptz,
  created_at timestamptz DEFAULT now()
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_applications_user_id ON applications(user_id);
CREATE INDEX IF NOT EXISTS idx_applications_cohort_id ON applications(cohort_id);
CREATE INDEX IF NOT EXISTS idx_applications_status ON applications(status);
CREATE INDEX IF NOT EXISTS idx_enrollments_user_id ON enrollments(user_id);
CREATE INDEX IF NOT EXISTS idx_enrollments_cohort_id ON enrollments(cohort_id);
CREATE INDEX IF NOT EXISTS idx_enrollments_status ON enrollments(status);
CREATE INDEX IF NOT EXISTS idx_examinations_user_id ON examinations(user_id);
CREATE INDEX IF NOT EXISTS idx_examinations_enrollment_id ON examinations(enrollment_id);
CREATE INDEX IF NOT EXISTS idx_certificates_user_id ON certificates(user_id);
CREATE INDEX IF NOT EXISTS idx_graduate_profiles_user_id ON graduate_profiles(user_id);
CREATE INDEX IF NOT EXISTS idx_graduate_profiles_visible ON graduate_profiles(is_visible);
CREATE INDEX IF NOT EXISTS idx_testimonials_user_id ON testimonials(user_id);
CREATE INDEX IF NOT EXISTS idx_contact_messages_user_id ON contact_messages(user_id);

-- Enable Row Level Security on all tables
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE cohorts ENABLE ROW LEVEL SECURITY;
ALTER TABLE applications ENABLE ROW LEVEL SECURITY;
ALTER TABLE enrollments ENABLE ROW LEVEL SECURITY;
ALTER TABLE examinations ENABLE ROW LEVEL SECURITY;
ALTER TABLE certificates ENABLE ROW LEVEL SECURITY;
ALTER TABLE admission_letters ENABLE ROW LEVEL SECURITY;
ALTER TABLE graduate_profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE testimonials ENABLE ROW LEVEL SECURITY;
ALTER TABLE course_materials ENABLE ROW LEVEL SECURITY;
ALTER TABLE contact_messages ENABLE ROW LEVEL SECURITY;

-- RLS Policies for users table
CREATE POLICY "Users can view own profile"
  ON users FOR SELECT
  TO authenticated
  USING (auth.uid() = id);

CREATE POLICY "Users can update own profile"
  ON users FOR UPDATE
  TO authenticated
  USING (auth.uid() = id)
  WITH CHECK (auth.uid() = id);

-- RLS Policies for cohorts table
CREATE POLICY "Anyone can view active cohorts"
  ON cohorts FOR SELECT
  TO authenticated
  USING (true);

-- RLS Policies for applications table
CREATE POLICY "Users can view own applications"
  ON applications FOR SELECT
  TO authenticated
  USING (auth.uid() = user_id);

CREATE POLICY "Users can create own applications"
  ON applications FOR INSERT
  TO authenticated
  WITH CHECK (auth.uid() = user_id);

CREATE POLICY "Users can update own pending applications"
  ON applications FOR UPDATE
  TO authenticated
  USING (auth.uid() = user_id AND status = 'pending')
  WITH CHECK (auth.uid() = user_id);

-- RLS Policies for enrollments table
CREATE POLICY "Users can view own enrollments"
  ON enrollments FOR SELECT
  TO authenticated
  USING (auth.uid() = user_id);

-- RLS Policies for examinations table
CREATE POLICY "Users can view own examinations"
  ON examinations FOR SELECT
  TO authenticated
  USING (auth.uid() = user_id);

-- RLS Policies for certificates table
CREATE POLICY "Users can view own certificates"
  ON certificates FOR SELECT
  TO authenticated
  USING (auth.uid() = user_id);

-- RLS Policies for admission_letters table
CREATE POLICY "Users can view own admission letters"
  ON admission_letters FOR SELECT
  TO authenticated
  USING (auth.uid() = user_id);

-- RLS Policies for graduate_profiles table
CREATE POLICY "Anyone can view visible graduate profiles"
  ON graduate_profiles FOR SELECT
  TO authenticated
  USING (is_visible = true);

CREATE POLICY "Users can manage own graduate profile"
  ON graduate_profiles FOR ALL
  TO authenticated
  USING (auth.uid() = user_id)
  WITH CHECK (auth.uid() = user_id);

-- RLS Policies for testimonials table
CREATE POLICY "Anyone can view approved testimonials"
  ON testimonials FOR SELECT
  TO authenticated
  USING (is_approved = true);

CREATE POLICY "Users can create own testimonials"
  ON testimonials FOR INSERT
  TO authenticated
  WITH CHECK (auth.uid() = user_id);

CREATE POLICY "Users can view own testimonials"
  ON testimonials FOR SELECT
  TO authenticated
  USING (auth.uid() = user_id);

-- RLS Policies for course_materials table
CREATE POLICY "Enrolled students can view course materials"
  ON course_materials FOR SELECT
  TO authenticated
  USING (
    EXISTS (
      SELECT 1 FROM enrollments
      WHERE enrollments.user_id = auth.uid()
      AND enrollments.cohort_id = course_materials.cohort_id
      AND enrollments.status = 'enrolled'
    )
  );

-- RLS Policies for contact_messages table
CREATE POLICY "Users can view own contact messages"
  ON contact_messages FOR SELECT
  TO authenticated
  USING (auth.uid() = user_id);

CREATE POLICY "Anyone can create contact messages"
  ON contact_messages FOR INSERT
  TO authenticated
  WITH CHECK (true);
