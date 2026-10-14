-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 15, 2026 at 02:20 AM
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
-- Database: `cmi`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `activity` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `customer_id` int(11) DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `users_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `activity`, `created_at`, `updated_at`, `customer_id`, `payment_id`, `users_id`) VALUES
(1, 'New admin created', '2024-01-02 04:26:48', '2024-01-02 04:26:48', NULL, NULL, 20),
(2, 'New admin created', '2024-01-02 05:51:46', '2024-01-02 05:51:46', NULL, NULL, 20),
(3, 'New admin created', '2024-01-02 07:07:39', '2024-01-02 07:07:39', NULL, NULL, 20),
(4, 'New admin created', '2024-01-02 07:11:51', '2024-01-02 07:11:51', NULL, NULL, 20),
(5, 'New admin created', '2024-01-02 08:25:07', '2024-01-02 08:25:07', NULL, NULL, 20),
(6, 'New admin created', '2024-01-02 09:22:14', '2024-01-02 09:22:14', NULL, NULL, 20),
(7, 'New admin created', '2024-01-02 12:14:14', '2024-01-02 12:14:14', NULL, NULL, 20),
(8, 'New admin created', '2024-01-03 06:05:55', '2024-01-03 06:05:55', NULL, NULL, 20),
(9, 'New payment type created', '2024-01-03 11:47:48', '2024-01-03 11:47:48', NULL, NULL, 20),
(10, 'New admin created', '2024-01-03 17:13:46', '2024-01-03 17:13:46', NULL, NULL, 20),
(11, 'New admin created', '2024-01-07 19:48:34', '2024-01-07 19:48:34', NULL, NULL, 20),
(12, 'New admin created', '2024-01-07 20:01:54', '2024-01-07 20:01:54', NULL, NULL, 20),
(13, 'New admin created', '2024-01-07 20:08:10', '2024-01-07 20:08:10', NULL, NULL, 20),
(14, 'New admin created', '2024-01-07 20:13:22', '2024-01-07 20:13:22', NULL, NULL, 20),
(15, 'New admin created', '2024-01-07 20:14:34', '2024-01-07 20:14:34', NULL, NULL, 20),
(16, 'New admin created', '2024-01-07 20:18:31', '2024-01-07 20:18:31', NULL, NULL, 20),
(17, 'New admin created', '2024-01-07 20:19:36', '2024-01-07 20:19:36', NULL, NULL, 20),
(18, 'New admin created', '2024-01-08 11:37:25', '2024-01-08 11:37:25', NULL, NULL, 20),
(19, 'New admin created', '2024-01-08 11:38:48', '2024-01-08 11:38:48', NULL, NULL, 20),
(20, 'Organisation profile updated', '2024-01-09 10:10:55', '2024-01-09 10:10:55', NULL, NULL, 20),
(21, 'Organisation profile updated', '2024-01-09 10:11:31', '2024-01-09 10:11:31', NULL, NULL, 20),
(22, 'Organisation profile updated', '2024-01-11 06:26:58', '2024-01-11 06:26:58', NULL, NULL, 20),
(23, 'Organisation profile updated', '2024-01-11 06:27:23', '2024-01-11 06:27:23', NULL, NULL, 20),
(24, 'New admin created', '2024-01-16 06:23:46', '2024-01-16 06:23:46', NULL, NULL, 20),
(25, 'New admin created', '2024-01-18 05:58:03', '2024-01-18 05:58:03', NULL, NULL, 20),
(26, 'New admin created', '2024-01-19 17:38:34', '2024-01-19 17:38:34', NULL, NULL, 20),
(27, 'New admin created', '2024-01-19 17:42:30', '2024-01-19 17:42:30', NULL, NULL, 20),
(28, 'New admin created', '2024-01-20 10:52:49', '2024-01-20 10:52:49', NULL, NULL, 20),
(29, 'New Student created', '2024-01-20 11:14:37', '2024-01-20 11:14:37', NULL, NULL, 20),
(30, 'New Student created', '2024-01-20 11:47:29', '2024-01-20 11:47:29', NULL, NULL, 20),
(31, 'New Student created', '2024-01-20 12:01:28', '2024-01-20 12:01:28', NULL, NULL, 20),
(32, 'New Student created', '2024-01-20 12:05:53', '2024-01-20 12:05:53', NULL, NULL, 20),
(33, 'New Parent created', '2024-01-20 12:45:42', '2024-01-20 12:45:42', NULL, NULL, 20),
(34, 'New Student created', '2024-01-23 17:45:25', '2024-01-23 17:45:25', NULL, NULL, 20),
(35, 'New Parent created', '2024-01-23 17:49:41', '2024-01-23 17:49:41', NULL, NULL, 20),
(36, 'New Parent created', '2024-01-23 17:52:06', '2024-01-23 17:52:06', NULL, NULL, 20),
(37, 'New admin created', '2024-01-24 04:33:50', '2024-01-24 04:33:50', NULL, NULL, 20),
(38, 'Admin updated', '2024-01-24 04:35:01', '2024-01-24 04:35:01', NULL, NULL, 20),
(39, 'New admin created', '2024-01-24 04:36:32', '2024-01-24 04:36:32', NULL, NULL, 20),
(40, 'New Student created', '2024-01-24 04:38:10', '2024-01-24 04:38:10', NULL, NULL, 20),
(41, 'New Student created', '2024-01-24 04:39:15', '2024-01-24 04:39:15', NULL, NULL, 20),
(42, 'New Student created', '2024-01-24 04:40:40', '2024-01-24 04:40:40', NULL, NULL, 20),
(43, 'New Parent created', '2024-01-24 04:42:10', '2024-01-24 04:42:10', NULL, NULL, 20),
(44, 'New Parent created', '2024-01-24 04:43:51', '2024-01-24 04:43:51', NULL, NULL, 20),
(45, 'New Parent created', '2024-01-24 04:45:13', '2024-01-24 04:45:13', NULL, NULL, 20),
(46, 'New admin created', '2024-01-24 06:37:44', '2024-01-24 06:37:44', NULL, NULL, 20),
(47, 'New admin created', '2024-01-24 07:29:18', '2024-01-24 07:29:18', NULL, NULL, 20),
(48, 'New Student created', '2024-01-24 07:31:03', '2024-01-24 07:31:03', NULL, NULL, 20),
(49, 'New Parent created', '2024-01-24 07:32:52', '2024-01-24 07:32:52', NULL, NULL, 20),
(50, 'New admin created', '2024-01-24 07:55:46', '2024-01-24 07:55:46', NULL, NULL, 20),
(51, 'New Student created', '2024-01-24 07:57:12', '2024-01-24 07:57:12', NULL, NULL, 20),
(52, 'New Parent created', '2024-01-24 07:58:53', '2024-01-24 07:58:53', NULL, NULL, 20),
(53, 'New admin created', '2024-01-26 09:09:34', '2024-01-26 09:09:34', NULL, NULL, 20),
(54, 'New admin created', '2024-01-26 09:13:15', '2024-01-26 09:13:15', NULL, NULL, 20),
(55, 'Organisation profile updated', '2024-01-30 05:17:40', '2024-01-30 05:17:40', NULL, NULL, 20),
(56, 'New Student created', '2024-02-16 10:54:58', '2024-02-16 10:54:58', NULL, NULL, 20),
(57, 'New Student created', '2024-02-16 11:15:45', '2024-02-16 11:15:45', NULL, NULL, 20),
(58, 'Admin updated', '2024-02-26 10:54:42', '2024-02-26 10:54:42', NULL, NULL, 20),
(59, 'Admin updated', '2024-02-26 11:00:02', '2024-02-26 11:00:02', NULL, NULL, 20),
(60, 'Admin updated', '2024-02-26 11:00:21', '2024-02-26 11:00:21', NULL, NULL, 20),
(61, 'New admin created', '2024-03-20 05:08:35', '2024-03-20 05:08:35', NULL, NULL, 20),
(62, 'Admin updated', '2024-03-20 05:09:03', '2024-03-20 05:09:03', NULL, NULL, 20),
(63, 'Organisation profile updated', '2024-03-20 05:26:08', '2024-03-20 05:26:08', NULL, NULL, 20),
(64, 'Organisation profile updated', '2024-03-20 05:26:27', '2024-03-20 05:26:27', NULL, NULL, 20);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organisation_details`
--

CREATE TABLE `organisation_details` (
  `id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `email` varchar(45) NOT NULL,
  `address` text NOT NULL,
  `telephone` varchar(45) NOT NULL,
  `logo` text NOT NULL,
  `account_no` varchar(45) NOT NULL,
  `bank_name` varchar(45) NOT NULL,
  `swift_code` varchar(45) DEFAULT NULL,
  `country` varchar(45) NOT NULL,
  `active` int(11) NOT NULL DEFAULT 1 COMMENT '1 for active',
  `deleted` int(11) NOT NULL DEFAULT 0 COMMENT '1 for deleted',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `users_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organisation_details`
--

INSERT INTO `organisation_details` (`id`, `name`, `email`, `address`, `telephone`, `logo`, `account_no`, `bank_name`, `swift_code`, `country`, `active`, `deleted`, `created_at`, `updated_at`, `users_id`) VALUES
(1, 'Test Companyyyy', 'company@gmail.com', 'Negombo', '08888888888', '1710912387_65fa73834b72e.jpeg', '123456789632', 'Sampath', '123444444', 'Sri Lanka', 1, 0, '2024-03-20 06:21:27', '2024-03-20 05:26:27', 20);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 20, 'app-token', '4c68a2b511237467c89d9b7d4d25c968e66925ce945acdc8ab60a66c30cedf47', '[\"*\"]', NULL, '2024-01-02 23:50:38', '2024-01-02 23:50:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `telephone` varchar(15) DEFAULT NULL,
  `age` varchar(2) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 for active',
  `deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 for deleted',
  `type` varchar(100) DEFAULT NULL COMMENT 'Admin, Super Admin, Parent or Student',
  `country_code` varchar(20) DEFAULT '94',
  `enrolment_number` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `address`, `telephone`, `age`, `gender`, `dob`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `image_path`, `active`, `deleted`, `type`, `country_code`, `enrolment_number`) VALUES
(20, 'admin', NULL, NULL, NULL, NULL, NULL, 'admin@gmail.com', NULL, '$2y$10$/CK.4gSwm5Ba.cn4B8tyu.4fzjelQwaGkhQWxWyqOrN4/tDRgxxGe', NULL, '2023-04-03 02:10:52', '2023-04-03 02:10:52', NULL, 1, 0, 'Super Admin', '0', NULL),
(86, 'Jhone Smith', NULL, NULL, NULL, NULL, NULL, 'smithj@gmail.com', NULL, '$2y$10$WtlvAVSYS/Pa463KfVdrVOESQZDbxWYgcb/7C2Lzr3CF57wLP/arm', NULL, '2024-03-19 23:38:35', '2024-03-19 23:39:39', NULL, 1, 1, 'Admin', '94', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `organisation_details`
--
ALTER TABLE `organisation_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_organisation_details_users1_idx` (`users_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `uniqe` (`enrolment_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `organisation_details`
--
ALTER TABLE `organisation_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `organisation_details`
--
ALTER TABLE `organisation_details`
  ADD CONSTRAINT `fk_organisation_details_users1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
