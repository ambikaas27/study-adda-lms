-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 22, 2026 at 09:24 PM
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
-- Database: `studyadda_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `instructor` varchar(100) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `class` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `instructor`, `duration`, `price`, `class`, `image`, `created_at`) VALUES
(1, 'Maths-World', 'Simplified course', 'Mrs. Uma', '3 months', 479.00, 8, 'course_6a5b78ae702ea.png', '2026-06-29 14:26:09'),
(8, 'English Grammer', 'A grammer course that makes you confident in speaking and writing.', 'Mr. M.P. Shukla', '6', 49.00, 6, 'course_6a5b4db1b3c69.jpg', '2026-07-18 09:56:01'),
(9, 'English language stock', 'Well structured course', 'Mr. M.P. Shukla', '6', 99.00, 8, 'course_6a5b4e4088e69.jpg', '2026-07-18 09:58:24'),
(10, 'English language', 'Well structured course', 'Mr. M.P. Shukla', '3', 109.00, 10, 'course_6a5b4ed01464b.jpg', '2026-07-18 10:00:48'),
(11, 'English World', 'Well structured course', 'Mr. M.P. Shukla', '6', 199.00, 9, 'course_6a5b4f34864dc.jpg', '2026-07-18 10:02:28'),
(12, 'English Steps', 'Well structured course', 'Mr. M.P. Shukla', '6', 199.00, 2, 'course_6a5b4f4e3859f.jpg', '2026-07-18 10:02:54'),
(13, 'Science', 'All about it', 'Shivam', '9', 89.00, 2, 'course_6a5b7e4ed8c6b.jpg', '2026-07-18 13:23:26'),
(14, 'Maths', 'All about it', 'Shivam', '9', 89.00, 4, 'course_6a5b7e664456a.jpg', '2026-07-18 13:23:50'),
(15, 'Computer', 'All you need to learn is here', 'Uma', '6', 199.00, 10, 'course_6a611488652d0.png', '2026-07-22 19:05:44');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `enrolled_at`) VALUES
(1, 2, 1, '2026-07-15 08:55:02'),
(3, 2, 14, '2026-07-22 19:02:49'),
(4, 2, 13, '2026-07-22 19:03:00'),
(5, 2, 12, '2026-07-22 19:03:08'),
(6, 2, 11, '2026-07-22 19:03:25'),
(7, 2, 15, '2026-07-22 19:08:24');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `lesson_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `course_id`, `title`, `video_url`, `notes`, `description`, `lesson_order`, `created_at`) VALUES
(3, 15, 'Computer our friend', 'https://youtu.be/VBAAZW9BFII?si=eoFP62U9HTNVibRH', 'Computers are our friend.', 'Abc', 1, '2026-07-22 19:07:54');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_completions`
--

CREATE TABLE `lesson_completions` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `progress`
--

CREATE TABLE `progress` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `status` enum('enrolled','in_progress','completed') DEFAULT 'enrolled',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `progress`
--

INSERT INTO `progress` (`id`, `student_id`, `course_id`, `status`, `updated_at`) VALUES
(1, 2, 1, 'enrolled', '2026-07-15 08:55:02'),
(3, 2, 14, 'enrolled', '2026-07-22 19:02:49'),
(4, 2, 13, 'enrolled', '2026-07-22 19:03:00'),
(5, 2, 12, 'enrolled', '2026-07-22 19:03:08'),
(6, 2, 11, 'enrolled', '2026-07-22 19:03:25'),
(7, 2, 15, 'enrolled', '2026-07-22 19:08:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','instructor','admin') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `security_question`, `security_answer`) VALUES
(1, 'Admin', 'admin@studyadda.com', '$2y$10$4ORIsosC5gIWIlUS5MgzF.buv9v4yzUGcHz0IrUT2G9UVPbZ7Lnhy', 'admin', '2026-05-29 14:18:37', 'What was your childhood nickname?', '$2b$12$H0YvSeskyHEc25Z1J7IRL.5l7rAYTUhRVhUWSmEI/lYDElDRzOcJS'),
(2, 'Ambika', 'ambika@gmail.com', '$2y$10$nrBAfhfjzDgAom.fNu1BdOfqK05iWEl9/xlcGoLtPkJojhRN6bW02', 'student', '2026-06-26 11:55:36', 'What was the name of your first pet?', '$2b$12$5JEOY6eB3z0kC6MtpK0oPe8ake8OrI9poMUXltz137B.S8/usacW.'),
(3, 'Uma', 'uma@gmail.com', '$2y$10$PnkSIObEOIFRKjtGlLEmg.K0DUPWMCi67vg82WX/IR3bJpGxlLoCy', 'instructor', '2026-07-15 03:00:12', 'What city were you born in?', '$2b$12$e2xcJH8ZqHbm0xe3dedBVu9GjbrRMg3SQKGOMQd7ZJnMhgZOHcuNG'),
(4, 'Aman Sharma', 'aman@gmail.com', '$2y$10$RNFVIR7510YckCu66oVADO3CwwsGLsBUypuoD4vrp/VU9J74mBqzK', 'instructor', '2026-07-17 15:14:25', 'What is your favorite teacher\'s name?', '$2b$12$vM6qWjiCecNAmXNHx/DLP.U7NzuxLzCeeTtNnyoUe9oujwlAstt6u'),
(5, 'Mr. M.P. Shukla', 'shukla@gmail.com', '$2y$10$iWpam72HrMsR285SuQTLge7QZ86DBAQAn55vafPbgyVCqR1P280eG', 'instructor', '2026-07-18 09:53:32', 'What is your favorite teacher\'s name?', '$2y$10$FsvF/uOsE4vezWI/R0H50e9VZyGjbJ.UjJzWFON4I3XHdoHNaQr6m'),
(6, 'Shivam', 'shivam@gmail.com', '$2y$10$zVR71AeY9YDfk67grSHLyuw9kjzEeHMb1PkAKP2Ba9mr6nOSF8NfC', 'instructor', '2026-07-18 13:20:26', 'What was your childhood nickname?', '$2y$10$moVWg3io/m.7cj2y5NzTNeWmdFlN3Z6mWe5W8E/4L/A933OwFyVYy');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `lesson_completions`
--
ALTER TABLE `lesson_completions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_completion` (`student_id`,`lesson_id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lesson_completions`
--
ALTER TABLE `lesson_completions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `progress`
--
ALTER TABLE `progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_completions`
--
ALTER TABLE `lesson_completions`
  ADD CONSTRAINT `lesson_completions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_completions_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `progress_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progress_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
