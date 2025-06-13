-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2025 at 02:31 PM
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
-- Database: `db_mmu_talent`
--

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
(21, 'Please improve this page', NULL, 'shem@gmail.com', '2025-06-13 11:36:23', 0, 'feedback', 1),
(23, 'can you please help me do account recovery??', 'Yes', 'test@gmail.com', '2025-06-13 11:37:09', 1, 'question', 0),
(25, 'Ali said this is a goodwebsite', NULL, 'Ali@gmail.com', '2025-06-13 11:58:59', 0, 'feedback', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('student','admin') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Hong', 'testing123@gmail.com', '$2y$10$8fWMK67x3jgBTYdml.KlKOChoAbUyO0lSgt1lxuXSH7LJZtT6Ff0S', 'student', '2025-06-06 13:32:04'),
(2, '123', '123@gmail.com', '$2y$10$IkcTItO5pLNP/L.Fhxi3buS.dbOJ9km9naFov71.2PYC1IhB9MEn.', 'student', '2025-06-06 14:08:41'),
(3, '123', '12345@gmail.com', '$2y$10$DuYyTBb/XQTZCvfQbPgvP.Rh/akPn6o3AQpox1dSRyLA.9ux/1hzS', 'student', '2025-06-06 14:10:57'),
(4, '12345', '123456@gmail.com', '$2y$10$QWKWTEYpdURoQk3I62kc0e2vkqUi1euEu1rGXwU8QMPVqPsGxq.Hm', 'student', '2025-06-06 14:19:30'),
(5, '123', '123145124@1231', '$2y$10$EI5gPAUZf0KMfwtl.6ZMnOMNXaRq5ShG04rGlpgNkYeGTyEWFrtJa', 'student', '2025-06-06 14:20:40'),
(6, '123', '1231231@dwad', '$2y$10$jLBGBUNCGaXaLVFqx.c2mOTufcj0dfP/gWC1zVKnB/uLP3W6aAKJ2', 'student', '2025-06-06 14:22:26'),
(7, '12312312', '2313213@dswadw', '$2y$10$cP.L.DRXfxl1eC6w4ochMuGTl1/RCeKOeXQyHCwo2/bTMoAqn4D8a', 'student', '2025-06-06 14:23:43'),
(8, 'Hong', 'Hong@gmail.com', '$2y$10$LTYu4zvX2lLoOORv/daD2.9M3dqAExa1RqW0CRn98hNsHI8CLN43C', 'student', '2025-06-13 12:17:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
