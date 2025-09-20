/*
  # Add Testimonials System

  1. New Tables
    - `testimonials`
      - `id` (int, primary key, auto increment)
      - `user_id` (int, foreign key to users)
      - `cohort_id` (int, foreign key to cohorts)
      - `content` (text, testimonial content)
      - `rating` (int, 1-5 stars)
      - `is_featured` (boolean, for homepage display)
      - `is_active` (boolean, admin approval)
      - `created_at` (timestamp)
      - `updated_at` (timestamp)

  2. Security
    - Foreign key constraints for data integrity
    - Admin approval system for testimonials

  3. Sample Data
    - Insert sample testimonials for demonstration
*/

CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cohort_id INT NOT NULL,
    content TEXT NOT NULL,
    rating INT DEFAULT 5 CHECK (rating >= 1 AND rating <= 5),
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE CASCADE
);

-- Insert sample testimonials
INSERT INTO testimonials (user_id, cohort_id, content, rating, is_featured, is_active) VALUES
(1, 1, 'The Web Development Bootcamp transformed my career completely. I went from having no coding experience to landing a job as a full-stack developer within 3 months of graduation. The instructors were amazing and the curriculum was very practical.', 5, TRUE, TRUE),
(1, 2, 'The Digital Marketing program gave me all the skills I needed to start my own agency. The hands-on projects and real-world case studies made all the difference. I now manage social media for 15+ local businesses.', 5, TRUE, TRUE),
(1, 3, 'Data Analytics Fundamentals opened up a whole new world for me. I learned Python, SQL, and data visualization tools that helped me transition from accounting to data science. The support from instructors was exceptional.', 5, FALSE, TRUE);