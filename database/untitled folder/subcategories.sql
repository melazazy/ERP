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
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`id`, `name`, `category_id`, `created_at`, `updated_at`) VALUES
(1, 'المواسير', 1, '2025-03-21 08:13:17', '2025-03-21 08:13:17'),
(2, 'الجلبات', 1, '2025-03-21 08:13:17', '2025-03-21 08:13:17'),
(3, 'الكوعات', 1, '2025-03-21 08:13:17', '2025-03-21 08:13:17'),
(4, 'التي والمسلوبات', 1, '2025-03-21 08:13:17', '2025-03-21 08:13:17'),
(5, 'الأدوات الصحية والإكسسوارات', 1, '2025-03-21 08:13:17', '2025-03-21 08:13:17'),
(6, 'الغراء والمنظفات', 1, '2025-03-21 08:13:17', '2025-03-21 08:13:17'),
(7, 'المحابس والصمامات', 1, '2025-03-21 08:13:17', '2025-03-21 08:13:17'),
(8, 'الحديد والصلب', 1, '2025-03-21 08:13:17', '2025-03-21 08:13:17'),
(9, 'الأدوات الكهربائية واليدوية', 2, '2025-03-21 08:13:17', '2025-03-21 08:13:17'),
(10, 'الأسلاك والكابلات', 2, '2025-03-21 08:13:17', '2025-03-21 08:13:17'),
(11, 'الخراطيم والمواسير المرنة', 1, '2025-03-21 08:13:17', '2025-03-21 08:13:17'),
(12, 'المسامير والبراغي', 1, '2025-03-21 08:13:17', '2025-03-21 08:13:17'),
(13, 'الإضاءة', 2, '2025-03-21 08:13:17', '2025-03-21 08:13:17'),
(14, 'الأدوات الأخرى', 1, '2025-03-21 08:13:17', '2025-03-21 08:13:17'),
(15, 'الكراسي الخشبية', 1, '2025-03-21 09:15:22', '2025-03-21 09:15:22'),
(16, 'باب وشباك', 5, '2025-03-30 10:42:52', '2025-03-30 10:42:52'),
(17, 'ادوات نجارة', 5, '2025-03-30 10:43:14', '2025-03-30 10:43:14'),
(18, 'عام', 6, '2025-03-30 12:15:07', '2025-03-30 12:15:07'),
(19, 'ادوات مكتب', 4, '2025-03-30 12:50:12', '2025-03-30 12:50:12'),
(20, 'مطبوعات', 4, '2025-03-30 12:50:26', '2025-03-30 12:50:26'),
(21, 'دهانات', 5, '2025-03-30 13:02:30', '2025-03-30 13:02:30'),
(22, 'اسمنت', 7, '2025-03-30 13:11:34', '2025-03-30 13:11:34'),
(23, 'رمل', 7, '2025-03-30 13:11:46', '2025-03-30 13:11:46'),
(24, 'طوب', 7, '2025-03-30 13:12:00', '2025-03-30 13:12:00'),
(25, 'خراطيم ومواسير', 2, '2025-04-02 05:53:33', '2025-04-02 05:53:33'),
(26, 'يونيفورم', 3, '2025-04-02 06:08:30', '2025-04-02 06:08:30'),
(27, 'سيراميك', 8, '2025-04-02 07:15:27', '2025-04-02 07:15:27'),
(28, 'محروقات', 9, '2025-04-02 07:37:20', '2025-04-02 07:40:35'),
(29, 'عام', 10, '2025-04-02 09:06:01', '2025-04-02 09:06:01'),
(30, 'مراحيض و قعدات', 1, '2025-04-05 16:17:45', '2025-04-05 16:17:45'),
(31, 'زيوت', 9, '2025-04-07 08:50:31', '2025-04-07 08:50:31'),
(32, 'مستلزمات', 9, '2025-04-07 08:50:51', '2025-04-07 08:50:51'),
(33, 'اطباق', 6, '2025-04-17 05:09:13', '2025-04-17 05:09:13'),
(34, 'مفروشات', 4, '2025-08-01 03:57:21', '2025-08-01 03:57:21'),
(35, 'سقف', 8, '2025-08-04 10:18:43', '2025-08-04 10:18:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subcategories_category_id_foreign` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
