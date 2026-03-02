-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 25, 2026 at 06:42 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lmsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `table_affected` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `target_type` enum('all','role','student_year_level','student_education_level','student_strand_course') NOT NULL DEFAULT 'all',
  `target_value` varchar(100) DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcement_recipients`
--

CREATE TABLE `announcement_recipients` (
  `id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_schedules`
--

CREATE TABLE `class_schedules` (
  `schedule_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room` varchar(50) DEFAULT NULL,
  `school_year` varchar(20) NOT NULL,
  `semester` enum('First','Second') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_schedules`
--

INSERT INTO `class_schedules` (`schedule_id`, `teacher_id`, `subject_id`, `section_id`, `day_of_week`, `start_time`, `end_time`, `room`, `school_year`, `semester`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Monday', '08:00:00', '09:30:00', 'Room 201', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(2, 1, 1, 1, 'Wednesday', '08:00:00', '09:30:00', 'Room 201', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(3, 1, 1, 1, 'Friday', '08:00:00', '09:30:00', 'Room 201', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(4, 1, 1, 2, 'Tuesday', '08:00:00', '09:30:00', 'Room 202', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(5, 1, 1, 2, 'Thursday', '08:00:00', '09:30:00', 'Room 202', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(6, 1, 1, 2, 'Saturday', '08:00:00', '09:30:00', 'Room 202', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(7, 1, 2, 1, 'Tuesday', '10:00:00', '11:30:00', 'Room 201', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(8, 1, 2, 1, 'Thursday', '10:00:00', '11:30:00', 'Room 201', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(9, 1, 2, 1, 'Saturday', '10:00:00', '11:30:00', 'Room 201', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(10, 1, 2, 2, 'Monday', '10:00:00', '11:30:00', 'Room 202', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(11, 1, 2, 2, 'Wednesday', '10:00:00', '11:30:00', 'Room 202', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(12, 1, 2, 2, 'Friday', '10:00:00', '11:30:00', 'Room 202', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(13, 2, 3, 3, 'Monday', '08:00:00', '09:30:00', 'Room 301', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(14, 2, 3, 3, 'Wednesday', '08:00:00', '09:30:00', 'Room 301', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(15, 2, 3, 3, 'Friday', '08:00:00', '09:30:00', 'Room 301', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(16, 2, 5, 3, 'Tuesday', '08:00:00', '09:30:00', 'Room 301', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(17, 2, 5, 3, 'Thursday', '08:00:00', '09:30:00', 'Room 301', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(18, 2, 5, 3, 'Saturday', '08:00:00', '09:30:00', 'Room 301', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(19, 2, 9, 3, 'Monday', '10:00:00', '11:30:00', 'Room 301', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(20, 2, 9, 3, 'Wednesday', '10:00:00', '11:30:00', 'Room 301', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(21, 2, 9, 3, 'Friday', '10:00:00', '11:30:00', 'Room 301', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(22, 2, 6, 4, 'Tuesday', '10:00:00', '11:30:00', 'Room 302', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(23, 2, 6, 4, 'Thursday', '10:00:00', '11:30:00', 'Room 302', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(24, 2, 6, 4, 'Saturday', '10:00:00', '11:30:00', 'Room 302', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(25, 2, 7, 4, 'Monday', '13:00:00', '14:30:00', 'Room 302', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(26, 2, 7, 4, 'Wednesday', '13:00:00', '14:30:00', 'Room 302', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(27, 2, 7, 4, 'Friday', '13:00:00', '14:30:00', 'Room 302', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(28, 2, 10, 4, 'Tuesday', '13:00:00', '14:30:00', 'Room 302', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(29, 2, 10, 4, 'Thursday', '13:00:00', '14:30:00', 'Room 302', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(30, 2, 10, 4, 'Saturday', '13:00:00', '14:30:00', 'Room 302', '2025-2026', 'First', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_documents`
--

CREATE TABLE `enrollment_documents` (
  `document_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `psa_birth_certificate` tinyint(1) DEFAULT 0,
  `form_138_report_card` tinyint(1) DEFAULT 0,
  `good_moral_certificate` tinyint(1) DEFAULT 0,
  `id_pictures` tinyint(1) DEFAULT 0,
  `medical_certificate` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_payments`
--

CREATE TABLE `enrollment_payments` (
  `payment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `semester` enum('First','Second') NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `net_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','partial','paid') DEFAULT 'pending',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_payments`
--

INSERT INTO `enrollment_payments` (`payment_id`, `student_id`, `school_year`, `semester`, `total_amount`, `discount_amount`, `net_amount`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-2026', 'First', 18000.00, 0.00, 18000.00, 'pending', 1, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(2, 2, '2025-2026', 'First', 18000.00, 0.00, 18000.00, 'pending', 1, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(3, 3, '2025-2026', 'First', 20000.00, 0.00, 20000.00, 'pending', 1, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(4, 4, '2025-2026', 'First', 20000.00, 0.00, 20000.00, 'pending', 1, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(5, 5, '2025-2026', 'First', 20000.00, 0.00, 20000.00, 'pending', 1, '2026-02-25 17:41:57', '2026-02-25 17:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `fee_config`
--

CREATE TABLE `fee_config` (
  `fee_id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `education_level` enum('senior_high','college') NOT NULL,
  `strand_course` varchar(50) NOT NULL,
  `tuition_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `miscellaneous` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_fees` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fee_config`
--

INSERT INTO `fee_config` (`fee_id`, `school_year`, `education_level`, `strand_course`, `tuition_fee`, `miscellaneous`, `other_fees`, `created_at`, `updated_at`) VALUES
(1, '1st Year', 'college', 'BSIT', 18000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(2, '2nd Year', 'college', 'BSIT', 18000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(3, '3rd Year', 'college', 'BSIT', 18000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(4, '4th Year', 'college', 'BSIT', 18000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(5, '1st Year', 'college', 'BSOA', 18000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(6, '2nd Year', 'college', 'BSOA', 18000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(7, '3rd Year', 'college', 'BSOA', 18000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(8, '4th Year', 'college', 'BSOA', 18000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(9, '1st Year', 'college', 'BSHM', 20000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(10, '2nd Year', 'college', 'BSHM', 20000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(11, '3rd Year', 'college', 'BSHM', 20000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(12, '4th Year', 'college', 'BSHM', 20000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(13, '1st Year', 'college', 'ACT', 18000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(14, '2nd Year', 'college', 'ACT', 18000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(15, 'Grade 11', 'senior_high', 'STEM', 20000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(16, 'Grade 12', 'senior_high', 'STEM', 20000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(17, 'Grade 11', 'senior_high', 'ABM', 20000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(18, 'Grade 12', 'senior_high', 'ABM', 20000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(19, 'Grade 11', 'senior_high', 'HUMSS', 20000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(20, 'Grade 12', 'senior_high', 'HUMSS', 20000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(21, 'Grade 11', 'senior_high', 'GAS', 20000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(22, 'Grade 12', 'senior_high', 'GAS', 20000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(23, 'Grade 11', 'senior_high', 'TVL', 20000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(24, 'Grade 12', 'senior_high', 'TVL', 20000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(25, 'Grade 11', 'senior_high', 'ICT', 20000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(26, 'Grade 12', 'senior_high', 'ICT', 20000.00, 0.00, 0.00, '2026-02-25 17:41:57', '2026-02-25 17:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `grade_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `grading_period` enum('Prelim','Midterm','Prefinal','Final') NOT NULL,
  `semester` enum('First','Second') NOT NULL,
  `grade_value` decimal(5,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `school_year` varchar(20) NOT NULL,
  `graded_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grading_periods`
--

CREATE TABLE `grading_periods` (
  `period_id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `semester` enum('First','Second') NOT NULL,
  `grading_period` enum('Prelim','Midterm','Prefinal','Final') NOT NULL,
  `deadline_date` date NOT NULL,
  `is_locked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grading_periods`
--

INSERT INTO `grading_periods` (`period_id`, `school_year`, `semester`, `grading_period`, `deadline_date`, `is_locked`, `created_at`, `updated_at`) VALUES
(1, '2025-2026', 'First', 'Prelim', '2026-01-15', 0, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(2, '2025-2026', 'First', 'Midterm', '2026-02-15', 0, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(3, '2025-2026', 'First', 'Prefinal', '2026-03-15', 0, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(4, '2025-2026', 'First', 'Final', '2026-04-28', 0, '2026-02-25 17:41:57', '2026-02-25 17:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `login_lockouts`
--

CREATE TABLE `login_lockouts` (
  `ip_address` varchar(45) NOT NULL,
  `fail_count` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `last_sent_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `transaction_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `received_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_settings`
--

CREATE TABLE `school_settings` (
  `id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `semester` enum('First','Second') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `advanced_by` int(11) DEFAULT NULL,
  `advanced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_settings`
--

INSERT INTO `school_settings` (`id`, `school_year`, `semester`, `is_active`, `advanced_by`, `advanced_at`, `created_at`, `updated_at`) VALUES
(1, '2025-2026', 'First', 1, 7, '2026-02-25 17:41:57', '2026-02-25 17:41:57', '2026-02-25 17:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `education_level` enum('senior_high','college') NOT NULL,
  `year_level` varchar(50) NOT NULL,
  `strand_course` varchar(50) NOT NULL,
  `max_capacity` int(11) DEFAULT NULL,
  `school_year` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `section_name`, `education_level`, `year_level`, `strand_course`, `max_capacity`, `school_year`, `created_at`, `updated_at`) VALUES
(1, 'BSIT 2A', 'college', '2nd Year', 'BSIT', 35, '2025-2026', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(2, 'BSIT 2B', 'college', '2nd Year', 'BSIT', 35, '2025-2026', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(3, 'Grade 11 - STEM A', 'senior_high', 'Grade 11', 'STEM', 40, '2025-2026', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(4, 'Grade 12 - HUMSS A', 'senior_high', 'Grade 12', 'HUMSS', 40, '2025-2026', '2026-02-25 17:41:57', '2026-02-25 17:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `section_subjects`
--

CREATE TABLE `section_subjects` (
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section_subjects`
--

INSERT INTO `section_subjects` (`section_id`, `subject_id`, `school_year`, `created_at`) VALUES
(1, 1, '2025-2026', '2026-02-25 17:41:57'),
(1, 2, '2025-2026', '2026-02-25 17:41:57'),
(2, 1, '2025-2026', '2026-02-25 17:41:57'),
(2, 2, '2025-2026', '2026-02-25 17:41:57'),
(3, 3, '2025-2026', '2026-02-25 17:41:57'),
(3, 5, '2025-2026', '2026-02-25 17:41:57'),
(3, 9, '2025-2026', '2026-02-25 17:41:57'),
(4, 6, '2025-2026', '2026-02-25 17:41:57'),
(4, 7, '2025-2026', '2026-02-25 17:41:57'),
(4, 10, '2025-2026', '2026-02-25 17:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  `lrn` varchar(12) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `year_level` varchar(50) NOT NULL,
  `education_level` enum('senior_high','college') NOT NULL,
  `strand_course` varchar(50) NOT NULL,
  `enrollment_status` enum('active','inactive','graduated','dropped') DEFAULT 'active',
  `guardian_contact` varchar(20) DEFAULT NULL,
  `guardian` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `first_name`, `middle_name`, `last_name`, `student_number`, `lrn`, `section_id`, `year_level`, `education_level`, `strand_course`, `enrollment_status`, `guardian_contact`, `guardian`, `created_at`, `updated_at`) VALUES
(1, 3, 'Nicka', 'Garcia', 'Reyes', '2025-00001', NULL, 1, '2nd Year', 'college', 'BSIT', 'active', '09171234567', 'Maria Dela Cruz', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(2, 4, 'Pedro', 'Cruz', 'Ramos', '2025-00002', NULL, NULL, '2nd Year', 'college', 'BSIT', 'active', NULL, NULL, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(3, 5, 'Ana', 'Marie', 'Torres', '2025-00003', '123456789012', 3, 'Grade 11', 'senior_high', 'STEM', 'active', '09981234567', 'Juan Santos', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(4, 6, 'Carlos', 'David', 'Gonzales', '2025-00004', '123456789013', NULL, 'Grade 12', 'senior_high', 'HUMSS', 'active', NULL, NULL, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(5, NULL, 'John', 'Michael', 'Dela Cruz', '2025-00005', '123456789014', NULL, 'Grade 11', 'senior_high', 'STEM', 'active', '09181234567', 'Maria Dela Cruz', '2026-02-25 17:41:57', '2026-02-25 17:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `student_assignments`
--

CREATE TABLE `student_assignments` (
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_assignments`
--

INSERT INTO `student_assignments` (`assignment_id`, `student_id`, `section_id`, `assigned_by`, `assigned_at`) VALUES
(1, 1, 1, 7, '2026-02-25 17:41:57'),
(2, 3, 3, 7, '2026-02-25 17:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `profile_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `home_address` text DEFAULT NULL,
  `previous_school` varchar(255) DEFAULT NULL,
  `special_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`profile_id`, `student_id`, `email`, `date_of_birth`, `gender`, `contact_number`, `home_address`, `previous_school`, `special_notes`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, '2003-05-15', 'female', '09171234567', '123 Rizal St., Quezon City', 'Quezon City Science High School', NULL, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(2, 2, NULL, '2003-08-22', 'male', NULL, '456 Mabini Ave., Manila', 'Manila High School', NULL, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(3, 3, NULL, '2007-02-10', 'female', '09981234567', '789 Bonifacio St., Makati', 'Makati Science High School', NULL, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(4, 4, NULL, '2006-11-30', 'male', NULL, '321 Luna St., Pasig', 'Pasig High School', NULL, '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(5, 5, 'john.delacruz@email.com', '2007-07-04', 'male', '09181234567', '654 Aguinaldo St., Caloocan', 'Caloocan National High School', NULL, '2026-02-25 17:41:57', '2026-02-25 17:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `student_section_history`
--

CREATE TABLE `student_section_history` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `section_name` varchar(100) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `semester` enum('First','Second') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_subject_enrollments`
--

CREATE TABLE `student_subject_enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `semester` enum('First','Second') NOT NULL,
  `enrolled_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_subject_enrollments`
--

INSERT INTO `student_subject_enrollments` (`enrollment_id`, `student_id`, `subject_id`, `school_year`, `semester`, `enrolled_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-2026', 'First', '2026-02-26', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(2, 1, 2, '2025-2026', 'First', '2026-02-26', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(3, 2, 1, '2025-2026', 'First', '2026-02-26', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(4, 2, 2, '2025-2026', 'First', '2026-02-26', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(5, 3, 3, '2025-2026', 'First', '2026-02-26', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(6, 3, 5, '2025-2026', 'First', '2026-02-26', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(7, 3, 9, '2025-2026', 'First', '2026-02-26', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(8, 4, 6, '2025-2026', 'First', '2026-02-26', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(9, 4, 7, '2025-2026', 'First', '2026-02-26', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(10, 4, 10, '2025-2026', 'First', '2026-02-26', '2026-02-25 17:41:57', '2026-02-25 17:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_code`, `subject_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'PROG1', 'Programming 1', 'Introduction to programming concepts and fundamentals', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(2, 'DSA', 'Data Structure and Algorithm', 'Study of data structures and algorithm design and analysis', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(3, 'GEN-MATH', 'General Mathematics', 'Fundamental concepts in mathematics for senior high', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(4, 'BASIC-CAL', 'Basic Calculus', 'Introduction to differential and integral calculus', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(5, 'PRE-CAL', 'Pre-Calculus', 'Preparation for calculus and advanced mathematics', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(6, 'PHIL-HIST', 'Philippine History', 'Study of Philippine history and culture', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(7, 'SOC-SCI', 'Social Science', 'Introduction to social sciences and humanities', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(8, 'CREATIVE-WRITING', 'Creative Writing', 'Development of creative writing skills', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(9, 'ENGLISH', 'English', 'English language and literature', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(10, 'FILIPINO', 'Filipino', 'Filipino language and literature', '2026-02-25 17:41:57', '2026-02-25 17:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subject_assignments`
--

CREATE TABLE `teacher_subject_assignments` (
  `assignment_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `year_level` varchar(50) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `semester` enum('First','Second') NOT NULL DEFAULT 'First',
  `assigned_date` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_subject_assignments`
--

INSERT INTO `teacher_subject_assignments` (`assignment_id`, `teacher_id`, `subject_id`, `section_id`, `year_level`, `school_year`, `semester`, `assigned_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2nd Year', '2025-2026', 'First', '2026-02-26', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(2, 1, 1, 2, '2nd Year', '2025-2026', 'First', '2026-02-26', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(3, 1, 2, 1, '2nd Year', '2025-2026', 'First', '2026-02-26', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(4, 1, 2, 2, '2nd Year', '2025-2026', 'First', '2026-02-26', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(5, 2, 3, 3, 'Grade 11', '2025-2026', 'First', '2026-02-26', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(6, 2, 5, 3, 'Grade 11', '2025-2026', 'First', '2026-02-26', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(7, 2, 9, 3, 'Grade 11', '2025-2026', 'First', '2026-02-26', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(8, 2, 6, 4, 'Grade 12', '2025-2026', 'First', '2026-02-26', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(9, 2, 7, 4, 'Grade 12', '2025-2026', 'First', '2026-02-26', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57'),
(10, 2, 10, 4, 'Grade 12', '2025-2026', 'First', '2026-02-26', 'active', '2026-02-25 17:41:57', '2026-02-25 17:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher','registrar','admin','superadmin') NOT NULL,
  `status` enum('active','inactive','suspended','graduated') DEFAULT 'active',
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `status`, `first_name`, `middle_name`, `last_name`, `created_by`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'teacher1', 'teacher1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'active', 'Juan', 'Santos', 'Dela Cruz', NULL, '2026-02-25 17:41:57', '2026-02-25 17:41:57', NULL),
(2, 'teacher2', 'teacher2@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'active', 'Maria', 'Lopez', 'Santos', NULL, '2026-02-25 17:41:57', '2026-02-25 17:41:57', NULL),
(3, 'student1', 'student1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', 'Nicka', 'Garcia', 'Reyes', NULL, '2026-02-25 17:41:57', '2026-02-25 17:41:57', NULL),
(4, 'student2', 'student2@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', 'Pedro', 'Cruz', 'Ramos', NULL, '2026-02-25 17:41:57', '2026-02-25 17:41:57', NULL),
(5, 'student3', 'student3@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', 'Ana', 'Marie', 'Torres', NULL, '2026-02-25 17:41:57', '2026-02-25 17:41:57', NULL),
(6, 'student4', 'student4@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', 'Carlos', 'David', 'Gonzales', NULL, '2026-02-25 17:41:57', '2026-02-25 17:41:57', NULL),
(7, 'admin1', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', 'Admin', 'System', 'User', NULL, '2026-02-25 17:41:57', '2026-02-25 17:41:57', NULL),
(8, 'registrar1', 'registrar1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'registrar', 'active', 'Rosa', 'Dela', 'Cruz', NULL, '2026-02-25 17:41:57', '2026-02-25 17:41:57', NULL),
(9, 'superadmin1', 'superadmin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', 'active', 'Super', 'Admin', 'User', NULL, '2026-02-25 17:41:57', '2026-02-25 17:41:57', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_table` (`table_affected`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_published_at` (`published_at`);

--
-- Indexes for table `announcement_recipients`
--
ALTER TABLE `announcement_recipients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_recipient` (`announcement_id`,`user_id`),
  ADD KEY `idx_user_unread` (`user_id`,`is_read`),
  ADD KEY `idx_announcement` (`announcement_id`);

--
-- Indexes for table `class_schedules`
--
ALTER TABLE `class_schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD UNIQUE KEY `unique_schedule` (`teacher_id`,`subject_id`,`section_id`,`day_of_week`,`start_time`,`school_year`,`semester`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `enrollment_documents`
--
ALTER TABLE `enrollment_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD UNIQUE KEY `unique_docs` (`student_id`,`school_year`);

--
-- Indexes for table `enrollment_payments`
--
ALTER TABLE `enrollment_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `fee_config`
--
ALTER TABLE `fee_config`
  ADD PRIMARY KEY (`fee_id`),
  ADD UNIQUE KEY `unique_fee` (`school_year`,`education_level`,`strand_course`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`grade_id`),
  ADD UNIQUE KEY `unique_grade` (`student_id`,`subject_id`,`grading_period`,`semester`,`school_year`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `grading_periods`
--
ALTER TABLE `grading_periods`
  ADD PRIMARY KEY (`period_id`),
  ADD UNIQUE KEY `unique_period` (`school_year`,`semester`,`grading_period`);

--
-- Indexes for table `login_lockouts`
--
ALTER TABLE `login_lockouts`
  ADD PRIMARY KEY (`ip_address`),
  ADD KEY `idx_locked` (`locked_until`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `received_by` (`received_by`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `school_settings`
--
ALTER TABLE `school_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `advanced_by` (`advanced_by`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD UNIQUE KEY `unique_section` (`section_name`,`year_level`,`school_year`);

--
-- Indexes for table `section_subjects`
--
ALTER TABLE `section_subjects`
  ADD PRIMARY KEY (`section_id`,`subject_id`,`school_year`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `idx_students_user_id` (`user_id`);

--
-- Indexes for table `student_assignments`
--
ALTER TABLE `student_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_section` (`section_id`),
  ADD KEY `idx_assigned_at` (`assigned_at`);

--
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Indexes for table `student_section_history`
--
ALTER TABLE `student_section_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_snapshot` (`student_id`,`section_id`,`school_year`,`semester`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `student_subject_enrollments`
--
ALTER TABLE `student_subject_enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`subject_id`,`school_year`,`semester`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- Indexes for table `teacher_subject_assignments`
--
ALTER TABLE `teacher_subject_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD UNIQUE KEY `unique_teacher_subject_section_semester` (`teacher_id`,`subject_id`,`year_level`,`school_year`,`semester`,`section_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `fk_teacher_section` (`section_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `created_by` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcement_recipients`
--
ALTER TABLE `announcement_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_schedules`
--
ALTER TABLE `class_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `enrollment_documents`
--
ALTER TABLE `enrollment_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollment_payments`
--
ALTER TABLE `enrollment_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `fee_config`
--
ALTER TABLE `fee_config`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grading_periods`
--
ALTER TABLE `grading_periods`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_settings`
--
ALTER TABLE `school_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `student_assignments`
--
ALTER TABLE `student_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `student_section_history`
--
ALTER TABLE `student_section_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_subject_enrollments`
--
ALTER TABLE `student_subject_enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `teacher_subject_assignments`
--
ALTER TABLE `teacher_subject_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `announcement_recipients`
--
ALTER TABLE `announcement_recipients`
  ADD CONSTRAINT `announcement_recipients_ibfk_1` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`announcement_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcement_recipients_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_schedules`
--
ALTER TABLE `class_schedules`
  ADD CONSTRAINT `class_schedules_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_schedules_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_schedules_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollment_documents`
--
ALTER TABLE `enrollment_documents`
  ADD CONSTRAINT `enrollment_documents_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollment_payments`
--
ALTER TABLE `enrollment_payments`
  ADD CONSTRAINT `enrollment_payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollment_payments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `payment_transactions_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `enrollment_payments` (`payment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_transactions_ibfk_2` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `school_settings`
--
ALTER TABLE `school_settings`
  ADD CONSTRAINT `school_settings_ibfk_1` FOREIGN KEY (`advanced_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `section_subjects`
--
ALTER TABLE `section_subjects`
  ADD CONSTRAINT `section_subjects_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `section_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`);

--
-- Constraints for table `student_assignments`
--
ALTER TABLE `student_assignments`
  ADD CONSTRAINT `student_assignments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_assignments_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `student_profiles_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_section_history`
--
ALTER TABLE `student_section_history`
  ADD CONSTRAINT `student_section_history_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_section_history_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE SET NULL;

--
-- Constraints for table `student_subject_enrollments`
--
ALTER TABLE `student_subject_enrollments`
  ADD CONSTRAINT `student_subject_enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_subject_enrollments_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_subject_assignments`
--
ALTER TABLE `teacher_subject_assignments`
  ADD CONSTRAINT `fk_teacher_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_subject_assignments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_subject_assignments_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
