-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 29, 2026 at 01:40 PM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_schedules`
--

INSERT INTO `class_schedules` (`schedule_id`, `teacher_id`, `subject_id`, `section_id`, `day_of_week`, `start_time`, `end_time`, `room`, `school_year`, `semester`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Monday', '08:00:00', '09:30:00', 'Room 201', '2025-2026', 'First', '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(2, 1, 1, 1, 'Tuesday', '08:00:00', '09:30:00', 'Room 201', '2025-2026', 'First', '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(3, 1, 1, 1, 'Wednesday', '08:00:00', '09:30:00', 'Room 201', '2025-2026', 'First', '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(4, 1, 1, 1, 'Thursday', '08:00:00', '09:30:00', 'Room 201', '2025-2026', 'First', '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(5, 1, 1, 1, 'Friday', '08:00:00', '09:30:00', 'Room 201', '2025-2026', 'First', '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(6, 1, 1, 1, 'Saturday', '08:00:00', '09:30:00', 'Room 201', '2025-2026', 'First', '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(7, 1, 1, 1, 'Sunday', '08:00:00', '09:30:00', 'Room 201', '2025-2026', 'First', '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(8, 1, 2, 1, 'Monday', '13:00:00', '14:30:00', NULL, '2025-2026', 'First', '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(9, 1, 2, 1, 'Tuesday', '13:00:00', '14:30:00', NULL, '2025-2026', 'First', '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(10, 1, 2, 1, 'Wednesday', '13:00:00', '14:30:00', NULL, '2025-2026', 'First', '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(11, 1, 2, 1, 'Thursday', '13:00:00', '14:30:00', NULL, '2025-2026', 'First', '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(12, 1, 2, 1, 'Friday', '13:00:00', '14:30:00', NULL, '2025-2026', 'First', '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(13, 1, 2, 1, 'Saturday', '13:00:00', '14:30:00', NULL, '2025-2026', 'First', '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(14, 1, 2, 1, 'Sunday', '13:00:00', '14:30:00', NULL, '2025-2026', 'First', '2026-01-29 05:34:49', '2026-01-29 05:34:49');

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

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`grade_id`, `student_id`, `subject_id`, `teacher_id`, `grading_period`, `semester`, `grade_value`, `remarks`, `school_year`, `graded_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Prelim', 'First', 81.00, 'GOOD BOY', '2025-2026', '2026-01-29 13:35:36', '2026-01-29 05:35:36', '2026-01-29 05:35:36');

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
(1, '2025-2026', 'First', 'Prelim', '2026-01-15', 0, '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(2, '2025-2026', 'First', 'Midterm', '0000-00-00', 0, '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(3, '2025-2026', 'First', 'Prefinal', '2026-03-15', 0, '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(4, '2025-2026', 'First', 'Final', '2026-04-28', 0, '2026-01-29 05:34:49', '2026-01-29 05:34:49');

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
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `year_level` varchar(50) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `section_name`, `year_level`, `school_year`, `created_at`, `updated_at`) VALUES
(1, 'BSIT 2A', '2nd Year', '2025-2026', '2026-01-29 05:34:49', '2026-01-29 05:34:49');

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

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  `lrn` varchar(12) DEFAULT NULL,
  `section_id` int(11) NOT NULL,
  `year_level` varchar(50) NOT NULL,
  `education_level` enum('senior_high','college') NOT NULL,
  `enrollment_status` enum('active','inactive','graduated','dropped') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `student_number`, `lrn`, `section_id`, `year_level`, `education_level`, `enrollment_status`, `created_at`, `updated_at`) VALUES
(1, 2, '2025-00001', NULL, 1, '2nd Year', 'college', 'active', '2026-01-29 05:34:49', '2026-01-29 05:34:49');

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
(1, 1, 1, '2025-2026', 'First', '2026-01-29', '2026-01-29 05:34:49', '2026-01-29 05:34:49'),
(2, 1, 2, '2025-2026', 'First', '2026-01-29', '2026-01-29 05:34:49', '2026-01-29 05:34:49');

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
(1, 'PROG1', 'Programming 1', 'Introduction to programming concepts and fundamentals', '2026-01-29 05:34:48', '2026-01-29 05:34:48'),
(2, 'DSA', 'Data Structure and Algorithm', 'Study of data structures and algorithm design and analysis', '2026-01-29 05:34:48', '2026-01-29 05:34:48');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subject_assignments`
--

CREATE TABLE `teacher_subject_assignments` (
  `assignment_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `year_level` varchar(50) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `assigned_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_subject_assignments`
--

INSERT INTO `teacher_subject_assignments` (`assignment_id`, `teacher_id`, `subject_id`, `year_level`, `school_year`, `assigned_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2nd Year', '2025-2026', '2026-01-29', '2026-01-29 05:34:48', '2026-01-29 05:34:48'),
(2, 1, 2, '2nd Year', '2025-2026', '2026-01-29', '2026-01-29 05:34:48', '2026-01-29 05:34:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin','superadmin') NOT NULL,
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
(1, 'teacher1', 'teacher1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'active', 'Juan', 'Santos', 'Dela Cruz', NULL, '2026-01-29 05:34:48', '2026-01-29 05:34:48', NULL),
(2, 'student1', 'student1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', 'Nicka', 'Garcia', 'Reyes', NULL, '2026-01-29 05:34:48', '2026-01-29 05:34:48', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `class_schedules`
--
ALTER TABLE `class_schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD UNIQUE KEY `unique_schedule` (`teacher_id`,`subject_id`,`section_id`,`day_of_week`,`start_time`,`school_year`,`semester`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `section_id` (`section_id`);

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
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD UNIQUE KEY `unique_section` (`section_name`,`school_year`);

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
  ADD KEY `user_id` (`user_id`),
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
  ADD UNIQUE KEY `unique_teacher_subject` (`teacher_id`,`subject_id`,`year_level`,`school_year`),
  ADD KEY `subject_id` (`subject_id`);

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
-- AUTO_INCREMENT for table `class_schedules`
--
ALTER TABLE `class_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `grading_periods`
--
ALTER TABLE `grading_periods`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_subject_enrollments`
--
ALTER TABLE `student_subject_enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `teacher_subject_assignments`
--
ALTER TABLE `teacher_subject_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `class_schedules`
--
ALTER TABLE `class_schedules`
  ADD CONSTRAINT `class_schedules_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_schedules_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_schedules_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`);

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
