-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 21, 2025 at 12:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_mmu_talent`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `posted_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `posted_by`, `created_at`) VALUES
(2, 'Testing123', '1234567dwadwa', NULL, '2025-06-13 13:50:51');

-- --------------------------------------------------------

--
-- Table structure for table `faq`
--

CREATE TABLE `faq` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text DEFAULT NULL,
  `submitted_by` varchar(100) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `visible` tinyint(1) DEFAULT 0,
  `type` enum('question','feedback') DEFAULT 'question',
  `flagged` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faq`
--

INSERT INTO `faq` (`id`, `question`, `answer`, `submitted_by`, `submitted_at`, `visible`, `type`, `flagged`) VALUES
(21, 'Please improve this page', NULL, 'shem@gmail.com', '2025-06-13 11:36:23', 0, 'question', 1),
(23, 'can you please help me do account recovery??', 'Yes', 'test@gmail.com', '2025-06-13 11:37:09', 1, 'question', 0),
(25, 'Ali said this is a goodwebsite', NULL, 'Ali@gmail.com', '2025-06-13 11:58:59', 0, 'question', 1),
(26, 'Test', NULL, 'crazydev2003@gmail.com', '2025-06-15 19:44:16', 0, 'feedback', 0);

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `post_id` int(11) NOT NULL,
  `post_content` text NOT NULL,
  `post_date` datetime NOT NULL DEFAULT current_timestamp(),
  `topic_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_topics`
--

CREATE TABLE `forum_topics` (
  `topic_id` int(11) NOT NULL,
  `topic_subject` varchar(255) NOT NULL,
  `topic_content` text NOT NULL,
  `topic_date` datetime NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_title` varchar(255) NOT NULL,
  `service_description` text DEFAULT NULL,
  `service_image` varchar(255) DEFAULT NULL,
  `service_price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `user_id`, `service_title`, `service_description`, `service_image`, `service_price`) VALUES
(2, 9, 'The Ultimate Copy Paster', 'I like copy pasting', '684f1823a4245-WhatsApp Image 2025-06-10 at 17.18.31_6d93164e.jpg', 0.00),
(3, 9, 'Doom Player', 'I like playing doom', '684f1921d13d4-c7e71bc7-a14b-432e-933d-db5b54984f1b.jpg', 0.00),
(5, 9, 'Gooner', 'Basically cum but better', '684f199f5c4d7-Screenshot 2024-08-13 211729.png', 0.00),
(6, 11, 'Gooner', 'asdasdasda', '684f7af48882d-Screenshot 2024-09-20 234654.png', 0.00),
(7, 11, 'Doom Player', 'sdsdfsdfsdfsdfsd', '684f7b02819c1-Screenshot 2025-01-20 211217.png', 0.00),
(8, 11, 'Cum', 'QWW ASDGFFSHDFSGHDSFH', '684f7b0b50014-Screenshot 2024-12-23 230830.png', 0.00),
(12, 14, 'Sleeping', 'I can sleep for an entire day and not give a fuck.', '68564dc3d12ad-Screenshot 2025-06-16 190304.png', 20.00),
(13, 14, 'Ultimate Gooning', 'Gooning', '68564dea19484-Screenshot 2025-06-16 185935.png', 100.00),
(14, 14, 'Brainwashing', 'I can brainwash little kids', '68564dfd2ddfc-Screenshot 2025-06-16 190304.png', 150.00);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `buyer_user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','completed') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `buyer_user_id`, `service_id`, `price_at_purchase`, `transaction_date`, `status`) VALUES
(1, 15, 12, 20.00, '2025-06-21 09:12:45', 'completed'),
(2, 15, 14, 150.00, '2025-06-21 09:35:46', 'completed'),
(3, 15, 14, 150.00, '2025-06-21 09:36:53', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('student','admin') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `student_id` varchar(50) DEFAULT NULL,
  `faculty` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `about_me` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT 'default_avatar.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `phone_number`, `password`, `role`, `created_at`, `student_id`, `faculty`, `date_of_birth`, `about_me`, `profile_picture`) VALUES
(1, 'Hong', 'testing123@gmail.com', NULL, '$2y$10$8fWMK67x3jgBTYdml.KlKOChoAbUyO0lSgt1lxuXSH7LJZtT6Ff0S', 'student', '2025-06-06 13:32:04', NULL, NULL, NULL, NULL, 'default_avatar.png'),
(2, '123', '123@gmail.com', NULL, '$2y$10$IkcTItO5pLNP/L.Fhxi3buS.dbOJ9km9naFov71.2PYC1IhB9MEn.', 'student', '2025-06-06 14:08:41', NULL, NULL, NULL, NULL, 'default_avatar.png'),
(3, '123', '12345@gmail.com', NULL, '$2y$10$DuYyTBb/XQTZCvfQbPgvP.Rh/akPn6o3AQpox1dSRyLA.9ux/1hzS', 'student', '2025-06-06 14:10:57', NULL, NULL, NULL, NULL, 'default_avatar.png'),
(4, '12345', '123456@gmail.com', NULL, '$2y$10$QWKWTEYpdURoQk3I62kc0e2vkqUi1euEu1rGXwU8QMPVqPsGxq.Hm', 'student', '2025-06-06 14:19:30', NULL, NULL, NULL, NULL, 'default_avatar.png'),
(5, '123', '123145124@1231', NULL, '$2y$10$EI5gPAUZf0KMfwtl.6ZMnOMNXaRq5ShG04rGlpgNkYeGTyEWFrtJa', 'student', '2025-06-06 14:20:40', NULL, NULL, NULL, NULL, 'default_avatar.png'),
(6, '123', '1231231@dwad', NULL, '$2y$10$jLBGBUNCGaXaLVFqx.c2mOTufcj0dfP/gWC1zVKnB/uLP3W6aAKJ2', 'student', '2025-06-06 14:22:26', NULL, NULL, NULL, NULL, 'default_avatar.png'),
(7, '12312312', '2313213@dswadw', NULL, '$2y$10$cP.L.DRXfxl1eC6w4ochMuGTl1/RCeKOeXQyHCwo2/bTMoAqn4D8a', 'student', '2025-06-06 14:23:43', NULL, NULL, NULL, NULL, 'default_avatar.png'),
(8, 'Hong', 'Hong@gmail.com', NULL, '$2y$10$LTYu4zvX2lLoOORv/daD2.9M3dqAExa1RqW0CRn98hNsHI8CLN43C', 'student', '2025-06-13 12:17:34', NULL, NULL, NULL, NULL, 'default_avatar.png'),
(9, 'Kalla Deveshwara Rao Rama Rao', '1211103169@student.mmu.edu.my', NULL, '$2y$10$GfukF3hpAXdRy85.98a5CuAc.UEOTZgGNg8eYdc4VDpX4bm0/e1sy', 'student', '2025-06-15 18:11:58', '1211103169', 'FCI', '2003-06-05', 'I like playing games', '684f15ca265f9-download.png'),
(11, 'Kalla Deveshwara Rao Rama Rao', 'crazydev2003@gmail.com', NULL, '$2y$10$JQoQ4JdsyDm2F4BgHwUh8OWHawWvPIKK/aDQjocSXZLnSMX2HJ5ES', 'admin', '2025-06-16 02:00:27', '1211104430', 'FCI', '2025-06-04', 'i like gay sex', '684f7ae8e269d-Screenshot 2024-08-13 211729.png'),
(13, 'Kalla Deveshwara Rao', 'Kalladeveshwararao@gmail.com', NULL, '$2y$10$kSTmEQX3vhjgJjHQ5mlK4.XTQ1dYWfl/tHvbugImqPP8rIM/a8Fle', 'student', '2025-06-20 14:39:22', NULL, NULL, NULL, NULL, 'default_avatar.png'),
(14, 'Sharvinthiran', 'sharvin@gmail.com', NULL, '$2y$10$3UjghEzlVVOqJNbQwkJnJeGRP13eIgNxjOmiMDasaH/SAZvM/vKlm', 'student', '2025-06-21 06:13:45', '1211103808', 'FCI', '2003-12-02', 'I like having sex with men', '68564dddb9e20-Screenshot 2025-06-19 025223.png'),
(15, 'Kirtanah Manalan', 'kirtanah7@gmail.com', NULL, '$2y$10$FoExuNIeYFMMibEzq7E7G.wuEhrQMnCE3CZhaU.7VThjp07WvC9Y6', 'student', '2025-06-21 06:15:55', NULL, NULL, NULL, NULL, 'default_avatar.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD PRIMARY KEY (`topic_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `buyer_user_id` (`buyer_user_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `forum_topics`
--
ALTER TABLE `forum_topics`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `forum_topics` (`topic_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_posts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD CONSTRAINT `forum_topics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`buyer_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
