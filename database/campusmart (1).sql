-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2025 at 06:05 PM
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
-- Database: `campusmart`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'create', 'listings', 4, NULL, '{\"title\":\"charger\",\"type\":\"sale\",\"price\":340}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-17 18:48:20'),
(2, 1, 'login', 'users', 1, NULL, NULL, NULL, NULL, '2025-10-17 23:27:31'),
(3, 1, 'logout', 'users', 1, NULL, NULL, NULL, NULL, '2025-10-17 23:27:31'),
(4, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-17 23:28:59'),
(5, 1, 'login', 'users', 1, NULL, NULL, NULL, NULL, '2025-10-17 23:45:27'),
(6, 1, 'login', 'users', 1, NULL, NULL, NULL, NULL, '2025-10-17 23:52:46'),
(7, 1, 'login', 'users', 1, NULL, NULL, NULL, NULL, '2025-10-18 00:13:58'),
(8, 1, 'login', 'users', 1, NULL, NULL, NULL, NULL, '2025-10-18 00:28:48'),
(9, 1, 'login', 'users', 1, NULL, NULL, NULL, NULL, '2025-10-18 01:16:31'),
(10, NULL, 'register', 'users', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 06:00:00'),
(11, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 06:03:20'),
(12, 1, 'create', 'services', 4, NULL, '{\"title\":\"web developement\",\"category\":\"freelance\",\"price_per_hour\":200}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 06:08:55'),
(13, 1, 'update', 'services', 4, NULL, '{\"title\":\"web development\",\"category\":\"freelance\",\"price_per_hour\":200}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 06:09:40'),
(14, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 07:31:09'),
(15, 5, 'register', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 07:37:55'),
(16, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 07:38:37'),
(17, 5, 'create', 'services', 5, NULL, '{\"title\":\"IPT1\",\"category\":\"tutoring\",\"price_per_hour\":200}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 07:50:57'),
(18, 5, 'logout', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 07:53:08'),
(19, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 07:53:23'),
(20, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 08:52:03'),
(21, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 08:52:14'),
(22, 5, 'create', 'listings', 5, NULL, '{\"title\":\"Dress\",\"type\":\"sale\",\"price\":750}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 08:59:42'),
(23, 5, 'create', 'services', 6, NULL, '{\"title\":\"Capstone Project\",\"category\":\"academic\",\"price_per_hour\":300}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 09:04:12'),
(24, 5, 'logout', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 09:07:25'),
(25, 3, 'login', 'users', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 09:07:37'),
(26, 3, 'logout', 'users', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 09:35:05'),
(27, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 09:36:25'),
(28, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:23:09'),
(29, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:24:28'),
(30, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:24:36'),
(31, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:24:39'),
(32, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:24:40'),
(33, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:24:45'),
(34, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:24:47'),
(35, 5, 'logout', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:28:28'),
(36, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:54:58'),
(37, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:55:08'),
(38, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:55:10'),
(39, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:55:46'),
(40, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:55:47'),
(41, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:57:25'),
(42, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:57:31'),
(43, 5, 'logout', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:00:14'),
(44, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:01:02'),
(45, 1, 'admin_login', 'admin_users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-22 15:32:14'),
(46, 1, 'admin_login', 'admin_users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-23 13:34:53'),
(47, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-23 13:37:33'),
(48, 5, 'logout', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-23 14:07:11'),
(49, 1, 'admin_login', 'admin_users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-27 04:46:40'),
(50, 1, 'admin_login', 'admin_users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-28 08:18:37'),
(51, 6, 'register', 'users', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-28 08:23:12'),
(52, 1, 'admin_logout', 'admin_users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-28 08:28:28'),
(53, 1, 'admin_login', 'admin_users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-28 08:31:51'),
(54, 1, 'admin_login', 'admin_users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-05 16:19:37');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `is_active`, `last_login`, `created_at`) VALUES
(1, 'admin', 'admin@campusmart.local', '$2y$10$YEuC1/1fSZ8EekdgggapN.GpeSWsseFSYT/eyRIeqWpxQNbV.nSEa', 'System Administrator', 'super_admin', 1, '2025-11-05 16:19:37', '2025-10-22 15:31:43'),
(2, 'moderator', 'moderator@campusmart.local', '$2y$10$/2qy7TEBkCxFtB1DqeXzwu4dKZzjd3AYdMA9GjFz4pH4eBh06nwXK', 'Content Moderator', 'moderator', 1, NULL, '2025-10-22 15:53:53'),
(3, 'admin2', 'admin2@campusmart.local', '$2y$10$dPkKDBsC./W804VRJFwiU.tjomkRSisO8yszTIaDdrIUEfjQIIZa.', 'Secondary Administrator', 'admin', 1, NULL, '2025-10-22 15:53:53');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `created_at`, `is_active`) VALUES
(1, 'Books & Academic Materials', 'books-and-academic-materials', 'Textbooks and study materials', 'book', '2025-10-17 14:43:42', 1),
(2, 'Electronics', 'electronics', 'Laptops, phones, gadgets', 'laptop', '2025-10-17 14:43:42', 1),
(3, 'Clothing & Accessories', 'clothing-and-accessories', 'Clothes, shoes, bags', 'shirt', '2025-10-17 14:43:42', 1),
(4, 'Other', 'other', 'Miscellaneous items', 'package', '2025-10-17 14:43:42', 1),
(5, 'Books', 'books', 'Books category', NULL, '2025-10-17 15:04:35', 1),
(6, 'Electronics', 'electronics-1', 'Electronics category', NULL, '2025-10-17 15:04:35', 1),
(7, 'Accessories', 'accessories', 'Accessories category', NULL, '2025-10-17 15:04:35', 1),
(8, 'Sports', 'sports', 'Sports category', NULL, '2025-10-17 15:04:35', 1);

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `participant_1` int(11) NOT NULL,
  `participant_2` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `participant_1`, `participant_2`, `created_at`, `last_activity`) VALUES
(1, 1, 3, '2025-10-17 23:40:18', '2025-10-18 06:25:33'),
(2, 1, 2, '2025-10-17 23:40:18', '2025-10-18 06:04:08'),
(3, 5, 1, '2025-10-18 07:52:39', '2025-10-18 07:53:37'),
(4, 5, 3, '2025-10-18 07:52:50', '2025-10-18 08:53:39');

-- --------------------------------------------------------

--
-- Table structure for table `failed_login_attempts`
--

CREATE TABLE `failed_login_attempts` (
  `id` int(11) NOT NULL,
  `login_id` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `friends`
--

CREATE TABLE `friends` (
  `id` int(11) NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `friend_requests`
--

CREATE TABLE `friend_requests` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','declined','blocked') DEFAULT 'pending',
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `friend_requests`
--

INSERT INTO `friend_requests` (`id`, `sender_id`, `receiver_id`, `status`, `message`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 'pending', 'Hi! I\'d like to connect with you on CampusMart.', '2025-10-18 01:04:47', '2025-10-18 01:04:47'),
(2, 2, 1, 'pending', 'Let\'s be friends!', '2025-10-18 01:04:47', '2025-10-18 01:04:47');

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `type` enum('sale','rent') NOT NULL DEFAULT 'sale',
  `condition_item` enum('New','Like New','Good','Fair','Poor') NOT NULL DEFAULT 'Good',
  `condition_type` enum('new','like_new','good','fair','poor') NOT NULL,
  `status` enum('active','available','sold','reserved','inactive') DEFAULT 'active',
  `views` int(11) DEFAULT 0,
  `featured` tinyint(1) DEFAULT 0,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `location` varchar(100) DEFAULT NULL,
  `rental_period` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`id`, `user_id`, `category_id`, `title`, `description`, `price`, `type`, `condition_item`, `condition_type`, `status`, `views`, `featured`, `images`, `location`, `rental_period`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Programming Textbook - Java Complete Reference', 'Comprehensive Java programming book in excellent condition. Perfect for Computer Science students.', 850.00, 'sale', 'Good', 'like_new', 'active', 3, 0, NULL, NULL, NULL, '2025-10-17 15:10:02', '2025-10-17 19:37:07'),
(2, 1, 2, 'Gaming Mouse - Logitech G502', 'High-performance gaming mouse with customizable buttons. Great for gaming and programming.', 2500.00, 'sale', 'Good', 'good', 'active', 0, 0, NULL, NULL, NULL, '2025-10-17 15:10:02', '2025-10-17 19:05:15'),
(3, 1, 3, 'Laptop Stand - Adjustable Aluminum', 'Ergonomic laptop stand to improve posture during long study sessions.', 1200.00, 'sale', 'Good', 'like_new', 'active', 0, 0, NULL, NULL, NULL, '2025-10-17 15:10:02', '2025-10-17 19:05:15'),
(4, 1, 2, 'charger', 'barato', 340.00, 'sale', 'New', 'new', 'active', 0, 0, '[\"68f28f74c3321_1760726900.jpg\"]', 'Main Campus', NULL, '2025-10-17 18:48:20', '2025-10-17 19:05:15'),
(5, 5, 7, 'Dress', 'In Good Condition', 750.00, 'sale', 'Like New', 'new', 'active', 0, 0, NULL, 'Sports Complex', NULL, '2025-10-18 08:59:42', '2025-10-18 08:59:42');

-- --------------------------------------------------------

--
-- Table structure for table `media_uploads`
--

CREATE TABLE `media_uploads` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_type` enum('image','video') NOT NULL,
  `thumbnail_path` varchar(500) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `message_type` enum('text','image','video','emoji') DEFAULT 'text',
  `media_url` varchar(500) DEFAULT NULL,
  `media_filename` varchar(255) DEFAULT NULL,
  `media_size` int(11) DEFAULT NULL,
  `media_mime_type` varchar(100) DEFAULT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `message_text`, `is_read`, `created_at`, `message_type`, `media_url`, `media_filename`, `media_size`, `media_mime_type`, `thumbnail_url`) VALUES
(1, 1, 1, 'Hi! I\'m interested in your Math Tutoring service.', 0, '2025-10-17 23:40:18', 'text', NULL, NULL, NULL, NULL, NULL),
(2, 1, 3, 'Hello! I\'d be happy to help you with math. What specific topics do you need help with?', 1, '2025-10-17 23:40:18', 'text', NULL, NULL, NULL, NULL, NULL),
(3, 1, 1, 'I\'m struggling with Calculus, particularly derivatives and integrals.', 0, '2025-10-17 23:40:18', 'text', NULL, NULL, NULL, NULL, NULL),
(4, 1, 3, 'Perfect! I specialize in Calculus. When would be a good time for you to meet?', 1, '2025-10-17 23:40:18', 'text', NULL, NULL, NULL, NULL, NULL),
(5, 2, 2, 'Hello! I saw your Web Development service. Can you help me build a website?', 1, '2025-10-17 23:40:18', 'text', NULL, NULL, NULL, NULL, NULL),
(6, 2, 1, 'Absolutely! I\'d love to help you with your website project. What kind of website are you looking to build?', 0, '2025-10-17 23:40:18', 'text', NULL, NULL, NULL, NULL, NULL),
(7, 1, 1, 'jjk', 0, '2025-10-18 00:55:42', 'text', NULL, NULL, NULL, NULL, NULL),
(8, 1, 1, 'bbb', 0, '2025-10-18 00:58:54', 'text', NULL, NULL, NULL, NULL, NULL),
(9, 1, 1, '❤️', 0, '2025-10-18 01:58:23', 'text', NULL, NULL, NULL, NULL, NULL),
(10, 1, 1, '😂', 0, '2025-10-18 02:39:21', 'text', NULL, NULL, NULL, NULL, NULL),
(11, 2, 1, '😂', 0, '2025-10-18 06:04:08', 'text', NULL, NULL, NULL, NULL, NULL),
(12, 1, 1, 'hi', 0, '2025-10-18 06:04:16', 'text', NULL, NULL, NULL, NULL, NULL),
(13, 1, 1, '😂', 0, '2025-10-18 06:25:33', 'text', NULL, NULL, NULL, NULL, NULL),
(14, 3, 5, '.', 1, '2025-10-18 07:52:39', 'text', NULL, NULL, NULL, NULL, NULL),
(15, 4, 5, 'sgs', 0, '2025-10-18 07:52:50', 'text', NULL, NULL, NULL, NULL, NULL),
(16, 4, 5, '😂', 0, '2025-10-18 07:52:58', 'text', NULL, NULL, NULL, NULL, NULL),
(17, 3, 1, 'hello', 0, '2025-10-18 07:53:37', 'text', NULL, NULL, NULL, NULL, NULL),
(18, 4, 5, 'dka love ni kivin', 0, '2025-10-18 08:53:26', 'text', NULL, NULL, NULL, NULL, NULL),
(19, 4, 5, 'HAHAHAHAH', 0, '2025-10-18 08:53:39', 'text', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `message_attachments`
--

CREATE TABLE `message_attachments` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_reactions`
--

CREATE TABLE `message_reactions` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reaction_type` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `category` enum('tutoring','freelance','academic','technical','creative','other') NOT NULL,
  `subject_skill` varchar(100) DEFAULT NULL,
  `price_per_hour` decimal(8,2) NOT NULL,
  `availability` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `status` enum('active','inactive') DEFAULT 'active',
  `views` int(11) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `user_id`, `title`, `description`, `category`, `subject_skill`, `price_per_hour`, `availability`, `location`, `images`, `status`, `views`, `rating`, `total_reviews`, `created_at`, `updated_at`) VALUES
(1, 1, 'Math Tutoring for Engineering Students', 'Experienced tutor offering comprehensive math support for engineering students. Specializing in Calculus, Linear Algebra, and Differential Equations. I have helped over 50 students improve their grades.', 'tutoring', 'Mathematics, Calculus, Linear Algebra', 250.00, 'Monday-Friday 2PM-6PM, Weekends flexible', 'Main Campus', NULL, 'active', 1, 0.00, 0, '2025-10-17 20:17:15', '2025-10-18 02:40:53'),
(2, 1, 'Web Development Services', 'Professional web development services for students and small businesses. I can create responsive websites, web applications, and provide technical consulting.', 'freelance', 'HTML, CSS, JavaScript, PHP, MySQL', 400.00, 'Evenings and weekends, flexible schedule', 'Online or Main Campus', NULL, 'active', 1, 0.00, 0, '2025-10-17 20:17:15', '2025-10-17 23:16:12'),
(3, 1, 'Graphic Design and Logo Creation', 'Creative graphic design services including logo design, posters, flyers, and digital artwork. Perfect for student organizations and projects.', 'creative', 'Photoshop, Illustrator, Logo Design', 300.00, 'Monday, Wednesday, Friday 1PM-5PM', 'Main Campus', NULL, 'active', 2, 0.00, 0, '2025-10-17 20:17:15', '2025-10-17 23:34:42'),
(4, 1, 'web development', 'aygstfd', 'freelance', 'JavaScript', 200.00, 'saturday morning', 'Main Campus', '[\"68f32ef798f2d_1760767735.jpg\"]', 'active', 2, 0.00, 0, '2025-10-18 06:08:55', '2025-10-18 06:09:42'),
(5, 5, 'IPT1', 'hello', 'tutoring', 'Programming', 200.00, 'Saturday Morning', 'Main Campus', '[\"68f346e1a30d5_1760773857.webp\"]', 'active', 1, 0.00, 0, '2025-10-18 07:50:57', '2025-10-18 07:51:01'),
(6, 5, 'Capstone Project', 'With 5 well done Capstone Projects', 'academic', 'Master In Database and Programming', 300.00, 'Every Monday morning', 'IT Building', '[\"68f3580ca277d_1760778252.jpg\"]', 'active', 1, 0.00, 0, '2025-10-18 09:04:12', '2025-10-18 09:04:15');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'CampusMart', NULL, '2025-10-23 14:05:51', '2025-10-23 14:05:51'),
(2, 'site_description', 'Student Marketplace for JH Cerilles State College', NULL, '2025-10-23 14:05:51', '2025-10-23 14:05:51'),
(3, 'contact_email', 'admin@campusmart.com', NULL, '2025-10-23 14:05:51', '2025-10-23 14:05:51'),
(4, 'maintenance_mode', '1', NULL, '2025-10-23 14:05:51', '2025-10-23 14:05:51');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `course` varchar(100) NOT NULL,
  `year_level` varchar(20) NOT NULL,
  `bio` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 1,
  `total_earnings` decimal(10,2) DEFAULT 0.00,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `student_id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `course`, `year_level`, `bio`, `profile_image`, `phone`, `contact_number`, `status`, `email_verified`, `total_earnings`, `rating`, `total_reviews`, `created_at`, `updated_at`) VALUES
(1, 'JH2024101', 'hilaos', 'maria.hilaos@jh.edu', '$2y$10$ycDfzM7c7IUnYESrrsEEhu2Tj0b0CeC3n4WMbm.ypzsKrxEODS0rq', 'Maria', 'Hilaos', 'Computer Science', '3rd Year', NULL, NULL, NULL, NULL, 'active', 1, 0.00, 0.00, 0, '2025-10-17 14:43:42', '2025-10-17 14:43:42'),
(2, 'JH2024102', 'sapuay', 'john.sapuay@jh.edu', '$2y$10$mvVGR1UVyR4.vtCirMwvZ.MMu2hbone1jhF5T4yan1VgYygSvmYEq', 'John', 'Sapuay', 'Business Administration', '2nd Year', NULL, NULL, NULL, NULL, 'active', 1, 0.00, 0.00, 0, '2025-10-17 14:43:42', '2025-10-17 14:43:42'),
(3, 'JH2024103', 'legaspi', 'anna.legaspi@jh.edu', '$2y$10$UpMASHAaPntf3Vl1eGK6P.wjbaSTDBmYviCrwnYkcV5xJRbz.D0I.', 'Anna', 'Legaspi', 'Engineering', '4th Year', NULL, NULL, NULL, NULL, 'active', 1, 0.00, 0.00, 0, '2025-10-17 14:43:42', '2025-10-17 14:43:42'),
(5, 'JH1112223', 'sap', 'sapcyryn@jh.edu', '$2y$10$QPbkQ1mHWHfqYQuG8O1nJ.1RBePeOV4H/c2jdBcBkcuiHudo0e9UC', 'Sap', 'Cy', 'Arts and Sciences', '3rd Year', 'hello', NULL, NULL, NULL, 'active', 1, 0.00, 0.00, 0, '2025-10-18 07:37:55', '2025-10-18 07:37:55'),
(6, 'JH1234568', 'Gly', 'gly@jh.edu', '$2y$10$EXyo8fhsOMmUbBaXt.VaA.2omyJKG96P2Qlsm98d1PxQLXTnsJxvy', 'gly123', 'Bianna', 'Computer Science', '3rd Year', 'rekll', NULL, NULL, NULL, 'active', 1, 0.00, 0.00, 0, '2025-10-28 08:23:12', '2025-10-28 08:23:12');

-- --------------------------------------------------------

--
-- Table structure for table `user_status`
--

CREATE TABLE `user_status` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status_message` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_status`
--

INSERT INTO `user_status` (`id`, `user_id`, `is_online`, `last_seen`, `status_message`, `created_at`) VALUES
(1, 1, 0, '2025-10-18 08:01:27', NULL, '2025-10-18 01:04:47'),
(2, 2, 1, '2025-10-18 01:16:32', NULL, '2025-10-18 01:04:47'),
(3, 3, 0, '2025-10-18 09:29:13', NULL, '2025-10-18 01:04:47'),
(47, 5, 0, '2025-10-21 15:00:14', NULL, '2025-10-18 07:52:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `participant_2` (`participant_2`),
  ADD KEY `idx_participants` (`participant_1`,`participant_2`);

--
-- Indexes for table `failed_login_attempts`
--
ALTER TABLE `failed_login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_id_time` (`login_id`,`attempt_time`);

--
-- Indexes for table `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user2_id` (`user2_id`),
  ADD KEY `idx_friendship` (`user1_id`,`user2_id`);

--
-- Indexes for table `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_friend_request` (`sender_id`,`receiver_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `media_uploads`
--
ALTER TABLE `media_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_uploads` (`user_id`,`upload_date`),
  ADD KEY `idx_file_type` (`file_type`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

--
-- Indexes for table `message_reactions`
--
ALTER TABLE `message_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_reaction` (`message_id`,`user_id`,`reaction_type`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_services_user_id` (`user_id`),
  ADD KEY `idx_services_category` (`category`),
  ADD KEY `idx_services_status` (`status`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_status`
--
ALTER TABLE `user_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_status` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_login_attempts`
--
ALTER TABLE `failed_login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `friends`
--
ALTER TABLE `friends`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `friend_requests`
--
ALTER TABLE `friend_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `media_uploads`
--
ALTER TABLE `media_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `message_attachments`
--
ALTER TABLE `message_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_reactions`
--
ALTER TABLE `message_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_status`
--
ALTER TABLE `user_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`participant_1`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`participant_2`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `friends`
--
ALTER TABLE `friends`
  ADD CONSTRAINT `friends_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friends_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD CONSTRAINT `friend_requests_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friend_requests_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `listings`
--
ALTER TABLE `listings`
  ADD CONSTRAINT `listings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `listings_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `media_uploads`
--
ALTER TABLE `media_uploads`
  ADD CONSTRAINT `media_uploads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD CONSTRAINT `message_attachments_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_reactions`
--
ALTER TABLE `message_reactions`
  ADD CONSTRAINT `message_reactions_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_status`
--
ALTER TABLE `user_status`
  ADD CONSTRAINT `user_status_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
