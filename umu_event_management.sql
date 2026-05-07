-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 07, 2026 at 07:53 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `umu_event_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` enum('Academic','Sports','Cultural','Religious','Social','Career') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Academic',
  `event_date` datetime NOT NULL,
  `location` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `category`, `event_date`, `location`, `image_url`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Freshers Orientation Week', 'Welcome event for all new students joining Uganda Martyrs University. Includes campus tours, faculty introductions, and registration assistance.', 'Academic', '2024-08-15 09:00:00', 'Main Auditorium', 'uploads/1777365501_images (3).jfif', 1, '2026-04-27 22:57:32', '2026-04-28 08:38:21'),
(2, 'Annual Inter-Faculty Sports Gala', 'Competitive sports tournament between faculties featuring football, basketball, netball, and athletics.', 'Sports', '2026-08-30 08:00:00', 'University Sports Ground', 'uploads/1777364583_images.jfif', 1, '2026-04-27 22:57:32', '2026-04-28 08:23:03'),
(3, 'Cultural Diversity Day', 'Celebration of diverse cultures through music, dance, food, and traditional attire exhibitions.', 'Cultural', '2026-11-05 07:00:00', 'Freedom Square', 'uploads/1777364439_WE-BANNER.jpg', 1, '2026-04-27 22:57:32', '2026-04-28 08:20:39'),
(4, 'General semester Opening Mass', 'Special lenten mass celebration open to all students and staff.', 'Religious', '2024-02-14 07:00:00', 'University Chapel', 'uploads/1777365652_download.jfif', 1, '2026-04-27 22:57:32', '2026-04-28 08:40:52'),
(5, 'Career Fair 2024', 'Connect with potential employers, attend CV workshops, and explore internship opportunities.', 'Career', '2026-08-10 09:00:00', 'Science Block Lawn', 'uploads/1777364994_download.png', 1, '2026-04-27 22:57:32', '2026-04-28 08:29:54'),
(6, 'MR and MISS UMU', 'A contest where students compete to be crowned “Mr. University” and “Miss University.” and focuses on personality, confidence, talent, intelligence, and sometimes appearance.', 'Social', '2026-08-22 11:44:00', 'university graduation square', 'uploads/1777365983_download (1).jfif', 1, '2026-04-28 08:46:23', '2026-04-28 08:46:23'),
(7, 'Freshers Bash', 'A welcome celebration for first-year (“freshers”) students.\r\nFocus: Socializing, fun, and helping newcomers settle in.', 'Social', '2026-08-16 16:00:00', 'University graduation square', 'uploads/1777366373_images (4).jfif', 1, '2026-04-28 08:51:43', '2026-04-28 08:52:53'),
(8, 'Camp fire and Tea party', 'A campfire event is a casual, outdoor social gathering centered around a fire.\r\nStudents gather (often at night) around a bonfire or fire pit.', 'Social', '2026-09-05 20:00:00', 'University Sports Ground', 'uploads/1777366572_download (3).jfif', 1, '2026-04-28 08:56:12', '2026-04-28 08:56:12'),
(9, 'Science Exhibition', 'A showcase where individuals or groups display science projects to an audience—this could include students, teachers, judges, and sometimes the public.', 'Academic', '2026-10-02 07:00:00', 'University graduation square', 'uploads/1777366813_download (4).jfif', 1, '2026-04-28 09:00:13', '2026-04-28 09:00:13');

-- --------------------------------------------------------

--
-- Table structure for table `rsvps`
--

DROP TABLE IF EXISTS `rsvps`;
CREATE TABLE IF NOT EXISTS `rsvps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `event_id` int NOT NULL,
  `status` enum('attending','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'attending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rsvp` (`user_id`,`event_id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rsvps`
--

INSERT INTO `rsvps` (`id`, `user_id`, `event_id`, `status`, `created_at`, `updated_at`) VALUES
(20, 2, 6, 'attending', '2026-05-07 07:20:28', '2026-05-07 07:20:28'),
(10, 2, 1, 'attending', '2026-05-06 13:33:44', '2026-05-06 13:33:44'),
(19, 2, 2, 'attending', '2026-05-06 19:57:55', '2026-05-06 19:57:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('student','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'student',
  `reset_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`, `reset_token`, `reset_expires`, `created_at`, `updated_at`) VALUES
(1, 'System Administrator', 'admin@umu.ac.ug', '$2y$10$0FxAPRIFpqVatC6qiZk44uLnWMn/CCKHUR7GkvNp30qrqJNF4voTW', 'admin', NULL, NULL, '2026-04-27 22:57:32', '2026-05-06 03:43:33'),
(2, 'KIROKO HAMIDU', 'kiroko.hamidu@stud.umu.ac.ug', '$2y$10$W.oqNWUVo0yJs//H5WBtDecz0H9epw6HEIAy2FdnaYvUCmW0GJBZS', 'student', NULL, NULL, '2026-04-28 00:19:25', '2026-05-06 03:48:58');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
