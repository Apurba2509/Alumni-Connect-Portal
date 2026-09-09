-- =======================================================
-- BCAC591: Alumni Connect Portal Database Schema
-- Database: alumni-connect-portal
-- =======================================================

CREATE DATABASE IF NOT EXISTS `alumni-connect-portal` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `alumni-connect-portal`;

-- Disable foreign key checks during re-creation
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `applications`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `notices`;
DROP TABLE IF EXISTS `alumni_details`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------------------------------------
-- 1. Table: users
-- -------------------------------------------------------
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'alumni', 'admin') NOT NULL DEFAULT 'student',
    `phone` VARCHAR(15) NULL,
    `department` VARCHAR(50) NOT NULL,
    `avatar` VARCHAR(255) DEFAULT 'default.png',
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 2. Table: alumni_details
-- -------------------------------------------------------
CREATE TABLE `alumni_details` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `batch_year` INT NOT NULL,
    `current_company` VARCHAR(100) NOT NULL,
    `designation` VARCHAR(100) NOT NULL,
    `city` VARCHAR(50) NULL,
    `linkedin_url` VARCHAR(200) NULL,
    CONSTRAINT `fk_alumni_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 3. Table: jobs
-- -------------------------------------------------------
CREATE TABLE `jobs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `posted_by` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `company` VARCHAR(100) NOT NULL,
    `location` VARCHAR(100) NOT NULL,
    `job_type` ENUM('Full-Time', 'Part-Time', 'Internship', 'Referral') NOT NULL DEFAULT 'Full-Time',
    `salary` VARCHAR(50) NULL,
    `description` TEXT NOT NULL,
    `deadline` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_jobs_posted_by` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 4. Table: applications
-- -------------------------------------------------------
CREATE TABLE `applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `resume_file` VARCHAR(255) NOT NULL,
    `cover_note` TEXT NULL,
    `status` ENUM('Pending', 'Shortlisted', 'Referred', 'Rejected') DEFAULT 'Pending',
    `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_app_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_app_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 5. Table: notices
-- -------------------------------------------------------
CREATE TABLE `notices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `category` ENUM('General', 'Placement', 'Alumni Meet', 'Urgent') DEFAULT 'General',
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_notices_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- SEED DATA (Ready-to-use accounts for lab demonstration)
-- =======================================================

-- Passwords:
-- Password for ALL demo accounts is simply: 'changeme'
-- (Stored in database as a 60-character scrambled hash because syllabus requires password_hash())
-- Hashed value for 'changeme': $2y$10$DuodBteGETjiA8btnJ.Gy.SY3Rs69vapqe0dQ96Q4ii2Ow5EFxmHm

-- 1. Insert Users (1 Admin, 2 Alumni, 2 Students) - All passwords are: changeme
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `department`, `status`) VALUES
(1, 'College Admin', 'admin@college.edu', '$2y$10$DuodBteGETjiA8btnJ.Gy.SY3Rs69vapqe0dQ96Q4ii2Ow5EFxmHm', 'admin', '9876543210', 'Administration', 'active'),
(2, 'Rahul Sharma', 'rahul@gmail.com', '$2y$10$DuodBteGETjiA8btnJ.Gy.SY3Rs69vapqe0dQ96Q4ii2Ow5EFxmHm', 'alumni', '9812345678', 'BCA', 'active'),
(3, 'Priya Nair', 'priya@gmail.com', '$2y$10$DuodBteGETjiA8btnJ.Gy.SY3Rs69vapqe0dQ96Q4ii2Ow5EFxmHm', 'alumni', '9823456789', 'CSE', 'active'),
(4, 'Amit Verma', 'amit@college.edu', '$2y$10$DuodBteGETjiA8btnJ.Gy.SY3Rs69vapqe0dQ96Q4ii2Ow5EFxmHm', 'student', '9834567890', 'BCA', 'active'),
(5, 'Sneha Patel', 'sneha@college.edu', '$2y$10$DuodBteGETjiA8btnJ.Gy.SY3Rs69vapqe0dQ96Q4ii2Ow5EFxmHm', 'student', '9845678901', 'IT', 'active');

-- 2. Insert Alumni Details
INSERT INTO `alumni_details` (`user_id`, `batch_year`, `current_company`, `designation`, `city`, `linkedin_url`) VALUES
(2, 2021, 'Google', 'Software Development Engineer II', 'Bangalore', 'https://linkedin.com/in/example-rahul'),
(3, 2020, 'Microsoft', 'Senior Data Analyst', 'Hyderabad', 'https://linkedin.com/in/example-priya');

-- 3. Insert Jobs (Posted by Alumni and Admin)
INSERT INTO `jobs` (`id`, `posted_by`, `title`, `company`, `location`, `job_type`, `salary`, `description`, `deadline`) VALUES
(1, 2, 'Junior Frontend Developer (React/JS)', 'Google', 'Bangalore / Hybrid', 'Referral', '12 - 16 LPA', 'Looking to refer fresh graduates or final-year students proficient in HTML, CSS, JavaScript, and modern web frameworks. Strong problem-solving skills required.', DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
(2, 3, 'Data Analyst Intern', 'Microsoft', 'Hyderabad / Remote', 'Internship', '45k / month', '6-month paid internship for BCA/B.Tech students interested in SQL, Python, PowerBI, and data pipelines. Mentorship provided throughout the internship.', DATE_ADD(CURDATE(), INTERVAL 20 DAY)),
(3, 1, 'TCS National Qualifier Test (NQT) Drive 2026', 'Tata Consultancy Services', 'Campus / On-Site', 'Full-Time', '3.8 - 7.5 LPA', 'Official campus recruitment notification. Open for all eligible final-year students of BCA and Computer Science departments.', DATE_ADD(CURDATE(), INTERVAL 15 DAY));

-- 4. Insert Sample Applications (Students applying for jobs)
INSERT INTO `applications` (`job_id`, `student_id`, `resume_file`, `cover_note`, `status`) VALUES
(1, 4, 'sample_resume_amit.pdf', 'Hi Rahul Sir, I have built 3 full-stack web applications and practiced 200+ LeetCode problems. Would love your referral!', 'Shortlisted'),
(2, 5, 'sample_resume_sneha.pdf', 'Hello Priya Maam, I am passionate about data analytics and completed certifications in SQL and Python. Please review my profile.', 'Pending');

-- 5. Insert College Notices (Published by Admin)
INSERT INTO `notices` (`admin_id`, `title`, `category`, `content`) VALUES
(1, 'Annual Alumni Homecoming & Networking Meet 2026', 'Alumni Meet', 'We are thrilled to announce the Annual Alumni Homecoming Meet on 15th October 2026 in the Main Auditorium. All alumni and graduating students are cordially invited!'),
(1, 'On-Campus Placement Training & Mock Interviews', 'Placement', 'The Placement Cell is organizing weekly mock interview sessions led by senior alumni from top tech companies starting this Friday.'),
(1, 'Project Details Submission for BCAC591 Lab', 'Urgent', 'All Semester 5 students must submit their Project Details Report via the official Google Form by the 3rd week of September.');
