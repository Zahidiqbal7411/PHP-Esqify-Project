-- =====================================================
-- Esqify Mobile API - Database Tables Creation (SAFE VERSION)
-- =====================================================
-- This version safely handles existing tables
-- Run this in phpMyAdmin SQL tab
-- =====================================================

-- =====================================================
-- STEP 1: Drop tables if they exist (SAFE - recreates from scratch)
-- =====================================================
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `chats`;
DROP TABLE IF EXISTS `faqs`;
DROP TABLE IF EXISTS `positions`;
DROP TABLE IF EXISTS `citys`;
DROP TABLE IF EXISTS `bars`;
DROP TABLE IF EXISTS `states`;

-- =====================================================
-- STEP 2: Create tables with correct structure
-- =====================================================

-- 0. STATES TABLE (must be created first - other tables reference it)
CREATE TABLE `states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `abbreviation` varchar(2) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1. BARS TABLE (for Issue #1 - Bars Filter)
CREATE TABLE `bars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `state_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_state_id` (`state_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CITYS TABLE (for Issue #2 - Cities List)
CREATE TABLE `citys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `state_id` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_state_id` (`state_id`),
  KEY `idx_status` (`status`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. POSITIONS TABLE (for Issue #4 - Job Positions)
CREATE TABLE `positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. CHATS TABLE (for Issue #5 - Chat Functionality)
CREATE TABLE `chats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sender_id` (`sender_id`),
  KEY `idx_receiver_id` (`receiver_id`),
  -- Prevents duplicate chat threads between the same two users
  UNIQUE KEY `idx_unique_chat` (`sender_id`, `receiver_id`),
  -- Optimized for checking participation in either direction
  KEY `idx_both_users` (`sender_id`, `receiver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. MESSAGES TABLE (for Issue #5 - Chat Messages)
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chat_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message_text` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL COMMENT 'Filename only, not full URL',
  `message_type` enum('text','image','video','file') DEFAULT 'text',
  `is_read` tinyint(1) DEFAULT 0 COMMENT '0=Unread, 1=Read',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  -- Optimized for fetching chat history in order
  KEY `idx_chat_history` (`chat_id`, `created_at`),
  -- Standard indexes for filtering
  KEY `idx_chat_id` (`chat_id`),
  KEY `idx_sender_id` (`sender_id`),
  KEY `idx_receiver_id` (`receiver_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. FAQS TABLE (for Issue #6 - FAQs)
CREATE TABLE `faqs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) DEFAULT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `order` int(11) DEFAULT 0 COMMENT 'Display order',
  `status` tinyint(1) DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_order` (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 3: Insert sample data
-- =====================================================

-- Sample data for states (insert first - other data references these)
INSERT INTO `states` (`id`, `name`, `abbreviation`, `status`) VALUES
(5, 'California', 'CA', 1),
(10, 'Florida', 'FL', 1),
(14, 'Illinois', 'IL', 1),
(33, 'New York', 'NY', 1),
(44, 'Texas', 'TX', 1);

-- Sample data for bars
INSERT INTO `bars` (`title`, `state_id`, `description`, `status`) VALUES
('California State Bar', 5, 'State Bar of California', 1),
('New York State Bar', 33, 'New York State Bar Association', 1),
('Texas State Bar', 44, 'State Bar of Texas', 1),
('Florida Bar', 10, 'The Florida Bar', 1),
('Illinois State Bar', 14, 'Illinois State Bar Association', 1);

-- Sample data for cities
INSERT INTO `citys` (`name`, `state_id`, `status`) VALUES
('Los Angeles', 5, 1),
('San Francisco', 5, 1),
('San Diego', 5, 1),
('Sacramento', 5, 1),
('San Jose', 5, 1),
('New York City', 33, 1),
('Buffalo', 33, 1),
('Rochester', 33, 1),
('Albany', 33, 1),
('Houston', 44, 1),
('Dallas', 44, 1),
('Austin', 44, 1),
('San Antonio', 44, 1);

-- Sample data for job positions
INSERT INTO `positions` (`title`, `description`, `status`) VALUES
('Associate Attorney', 'Entry to mid-level attorney position', 1),
('Senior Associate', 'Mid to senior level attorney', 1),
('Partner', 'Partnership position', 1),
('Of Counsel', 'Senior advisory position', 1),
('Paralegal', 'Legal assistant position', 1),
('Legal Secretary', 'Administrative support position', 1),
('Law Clerk', 'Student or entry-level research position', 1),
('Managing Partner', 'Leadership and management position', 1),
('Counsel', 'Senior attorney position', 1),
('Junior Associate', 'Entry-level attorney position', 1);

-- Sample data for FAQs
INSERT INTO `faqs` (`subject`, `question`, `answer`, `order`, `status`) VALUES
('General', 'How do I create an account?', 'Click on the Sign Up button on the home page and fill in your details including first name, last name, email, and password.', 1, 1),
('General', 'How do I reset my password?', 'Click on the Forgot Password link on the login page and follow the instructions sent to your email.', 2, 1),
('Deals', 'How do I post a deal?', 'Navigate to the Deals section, click Post Deal, and fill in all required information including title, state, industry, practice area, and deal details.', 3, 1),
('Deals', 'Can I edit a deal after posting?', 'Yes, go to My Deals, select the deal you want to edit, and click the Edit button.', 4, 1),
('Jobs', 'How do I apply for a job?', 'Open the job listing you are interested in and click the Apply button. You may need to upload your resume.', 5, 1),
('Jobs', 'How do I post a job opening?', 'Go to the Jobs section, click Post Job, and fill in the job details including title, description, requirements, and salary range.', 6, 1),
('Chat', 'How do I send a message?', 'Navigate to the Messages section, select a contact or start a new chat, type your message, and click Send.', 7, 1),
('Profile', 'How do I update my profile?', 'Go to your Profile page, click Edit Profile, make your changes, and click Save.', 8, 1);

-- =====================================================
-- SUCCESS! All tables created with sample data
-- =====================================================
