-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 08, 2025 at 06:35 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `warehouse`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'السباكة', '2025-03-05 07:07:46', '2025-06-13 17:56:39'),
(2, 'الكهرباء', '2025-03-05 07:07:46', '2025-03-05 07:07:46'),
(3, 'ملابس', '2025-03-05 07:07:46', '2025-03-05 07:07:46'),
(4, 'عام', '2025-03-05 07:07:46', '2025-03-05 07:07:46'),
(5, 'نجارة', '2025-03-30 10:40:08', '2025-03-30 10:40:08'),
(6, 'مطبخ', '2025-03-30 12:14:52', '2025-03-30 12:14:52'),
(7, 'مواد بناء', '2025-03-30 13:10:56', '2025-03-30 13:10:56'),
(8, 'ارضيات وحوائط', '2025-04-02 07:15:07', '2025-04-02 07:15:07'),
(9, 'بترول ومشتقاته', '2025-04-02 07:37:08', '2025-04-02 07:37:08'),
(10, 'عدد وادوات', '2025-04-02 09:05:48', '2025-04-02 09:05:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
