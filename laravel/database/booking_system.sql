-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2025 at 05:13 AM
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
-- Database: `booking_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `facility_id` bigint(20) UNSIGNED NOT NULL,
  `booking_type` varchar(255) NOT NULL DEFAULT 'per_hour',
  `booking_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `number_of_people` int(11) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `facility_id`, `booking_type`, `booking_date`, `start_time`, `end_time`, `number_of_people`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 9, 20, 'per_day', '2025-11-30', NULL, NULL, 1, 'completed', NULL, '2025-11-29 23:53:20', '2025-11-30 00:03:52'),
(2, 7, 14, 'per_hour', '2025-11-30', '07:00:00', '09:00:00', 1, 'cancelled', NULL, '2025-11-29 23:59:49', '2025-11-30 00:04:08'),
(3, 7, 14, 'per_hour', '2025-11-30', '11:00:00', '14:00:00', 1, 'cancelled', NULL, '2025-11-30 00:00:46', '2025-11-30 00:04:11'),
(4, 7, 21, 'per_hour', '2025-11-30', '07:00:00', '08:00:00', 1, 'cancelled', NULL, '2025-11-30 00:02:15', '2025-11-30 00:04:14'),
(5, 4, 22, 'per_hour', '2025-11-30', '07:00:00', '09:00:00', 1, 'confirmed', NULL, '2025-11-30 00:05:09', '2025-11-30 00:44:02');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `icon`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Dewan & Panggung', 'Dewan dan panggung untuk pelbagai majlis dan acara', 'hall-icon', 1, '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(2, 'Padang & Gelanggang', 'Padang dan gelanggang sukan untuk aktiviti luar', 'sports-icon', 1, '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(3, 'Pusat Komuniti', 'Ruang komuniti untuk mesyuarat dan aktiviti penduduk', 'community-icon', 1, '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(4, 'Kemudahan Sukan', 'Kemudahan sukan tertutup dan terbuka', 'recreation-icon', 1, '2025-11-29 23:45:32', '2025-11-29 23:45:32');

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `district` varchar(255) NOT NULL DEFAULT 'Hulu Terengganu',
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL,
  `price_per_hour` decimal(10,2) NOT NULL,
  `price_per_day` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `district`, `category_id`, `name`, `description`, `location`, `capacity`, `price_per_hour`, `price_per_day`, `image`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 'Hulu Terengganu', 1, 'Dewan Jubli Perak', 'Dewan besar dengan kapasiti 500 orang, sesuai untuk majlis perkahwinan, seminar dan persidangan. Dilengkapi dengan pentas, sistem PA dan pendingin hawa.', 'Kompleks MDHT, Kuala Berang', 500, 150.00, 1200.00, NULL, 1, '2025-11-29 23:45:32', '2025-11-30 01:00:38'),
(2, 'Hulu Terengganu', 1, 'Dewan Serbaguna Tasik Kenyir', 'Dewan sederhana dengan kapasiti 200 orang. Sesuai untuk majlis kenduri, kursus dan mesyuarat besar.', 'Kawasan Tasik Kenyir', 200, 80.00, NULL, NULL, 1, '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(3, 'Hulu Terengganu', 1, 'Panggung Mini MDHT', 'Panggung kecil dengan pentas dan tempat duduk 100 orang. Sesuai untuk persembahan budaya dan ceramah.', 'Kompleks MDHT, Kuala Berang', 100, 50.00, NULL, NULL, 1, '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(4, 'Hulu Terengganu', 2, 'Padang Bola Sepak Kuala Berang', 'Padang bola sepak bersaiz penuh dengan rumput semula jadi. Dilengkapi dengan lampu limpah untuk perlawanan malam.', 'Stadium MDHT, Kuala Berang', 1000, 100.00, NULL, NULL, 1, '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(5, 'Hulu Terengganu', 2, 'Gelanggang Futsal Tertutup', 'Gelanggang futsal dalam bangunan dengan lantai sintetik berkualiti tinggi. Sesuai untuk latihan dan perlawanan.', 'Kompleks Sukan MDHT', 50, 60.00, NULL, NULL, 1, '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(6, 'Hulu Terengganu', 2, 'Padang Ragbi Mini', 'Padang ragbi saiz standard untuk latihan dan perlawanan ragbi 7 sebelah.', 'Stadium MDHT, Kuala Berang', 500, 80.00, NULL, NULL, 1, '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(7, 'Hulu Terengganu', 3, 'Bilik Mesyuarat A', 'Bilik mesyuarat lengkap dengan meja, kerusi, projektor dan papan putih. Sesuai untuk mesyuarat rasmi dan pembentangan.', 'Pejabat MDHT, Tingkat 2', 30, 40.00, NULL, NULL, 1, '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(8, 'Hulu Terengganu', 3, 'Bilik Aktiviti Komuniti', 'Ruang serbaguna untuk aktiviti penduduk seperti kelas kemahiran, kursus dan bengkel.', 'Pusat Komuniti Kuala Berang', 50, 30.00, NULL, NULL, 1, '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(9, 'Hulu Terengganu', 3, 'Dewan Kuliah Mini', 'Bilik kuliah kecil dengan sistem audio dan video. Sesuai untuk ceramah dan kelas pendidikan.', 'Pusat Komuniti Kuala Berang', 80, 50.00, NULL, NULL, 1, '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(10, 'Hulu Terengganu', 4, 'Gelanggang Badminton (4 Gelanggang)', 'Kompleks badminton dengan 4 gelanggang standard dilengkapi dengan lantai getah dan sistem pencahayaan LED.', 'Kompleks Sukan MDHT', 80, 80.00, NULL, NULL, 1, '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(11, 'Hulu Terengganu', 4, 'Gelanggang Takraw', 'Gelanggang sepak takraw standard dengan jaring dan lampu. Lantai getah berkualiti tinggi.', 'Kompleks Sukan MDHT', 40, 40.00, NULL, NULL, 1, '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(12, 'Hulu Terengganu', 4, 'Dewan Ping Pong (6 Meja)', 'Dewan ping pong dengan 6 meja standard dan peralatan lengkap.', 'Kompleks Sukan MDHT', 24, 30.00, NULL, NULL, 1, '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(13, 'Hulu Terengganu', 4, 'Kolam Renang Awam', 'Kolam renang saiz olimpik dengan kawasan kanak-kanak. Dilengkapi dengan penyelamat dan bilik persalinan.', 'Kompleks Akuatik MDHT', 150, 70.00, NULL, NULL, 1, '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(14, 'Besut', 1, 'Dewan Serbaguna Besut', 'Dewan serbaguna dengan kapasiti 300 orang.', 'Jertih, Besut', 300, 120.00, NULL, NULL, 1, '2025-11-29 23:45:36', '2025-11-29 23:45:36'),
(15, 'Besut', 2, 'Gelanggang Futsal Besut', 'Gelanggang futsal indoor.', 'Jertih, Besut', 20, 50.00, NULL, NULL, 1, '2025-11-29 23:45:36', '2025-11-29 23:45:36'),
(16, 'Besut', 3, 'Bilik Mesyuarat MDB', 'Bilik mesyuarat dengan kapasiti 30 orang.', 'Pejabat MDB, Jertih', 30, 60.00, NULL, NULL, 1, '2025-11-29 23:45:36', '2025-11-29 23:45:36'),
(17, 'Marang', 1, 'Dewan Komuniti Marang', 'Dewan komuniti berkapasiti 250 orang.', 'Marang', 250, 100.00, NULL, NULL, 1, '2025-11-29 23:45:36', '2025-11-29 23:45:36'),
(18, 'Marang', 2, 'Padang Bola MDM', 'Padang bola sepak dengan rumput berkualiti.', 'Marang', 50, 80.00, NULL, NULL, 1, '2025-11-29 23:45:36', '2025-11-29 23:45:36'),
(19, 'Marang', 3, 'Bilik Seminar MDM', 'Bilik seminar dengan kapasiti 50 orang.', 'Pejabat MDM', 50, 70.00, NULL, NULL, 1, '2025-11-29 23:45:36', '2025-11-29 23:45:36'),
(20, 'Setiu', 1, 'Dewan Tertutup Sungai Tarom', 'Dewan serbaguna dengan kapasiti 200 orang.', 'Permaisuri, Setiu', 200, 90.00, 720.00, '/storage/facilities/J85tjV7RhvuBRtt6wXHuiXZwGJKtvmMvj0QCfR7e.png', 1, '2025-11-29 23:45:36', '2025-11-30 01:20:47'),
(21, 'Setiu', 2, 'Padang Bola Sungai Tarom', 'Padang Bola', 'Sungai Tarom, Setiu', 22, 150.00, 1000.00, NULL, 1, '2025-11-29 23:45:36', '2025-11-30 01:19:33'),
(22, 'Setiu', 1, 'Dewan Terbuka Sungai Tarom', 'Dewan Terbuka', 'Setiu', 20, 100.00, 800.00, '/storage/facilities/svtKSKlNcw92r7Iir1pUkvsUMKr2pT4aI88AceRJ.png', 1, '2025-11-29 23:45:36', '2025-11-30 01:20:58');

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
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
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

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_01_01_000000_create_categories_table', 1),
(5, '2024_01_02_000000_create_facilities_table', 1),
(6, '2024_01_03_000000_create_time_slots_table', 1),
(7, '2024_01_04_000000_create_bookings_table', 1),
(8, '2024_01_05_000000_create_payments_table', 1),
(9, '2025_01_17_000000_add_is_active_to_categories_table', 1),
(10, '2025_01_17_000001_add_ic_number_to_users_table', 1),
(11, '2025_11_16_005931_create_personal_access_tokens_table', 1),
(12, '2025_11_17_023517_add_district_to_tables', 1),
(13, '2025_11_17_023719_update_user_roles', 1),
(14, '2025_11_24_000001_add_booking_type_times_to_bookings', 1),
(15, '2025_11_24_000002_add_price_per_day_to_facilities', 1),
(16, '2025_11_24_000003_make_time_slot_id_nullable', 1),
(17, '2025_11_24_000004_drop_time_slot_id_from_bookings', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit_card','debit_card','online_banking','e-wallet') NOT NULL DEFAULT 'cash',
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(17, 'App\\Models\\User', 4, 'auth_token', '4508d75e30ce09925d484363b8ef4d69fabf5b3a37196d503a76b1277d430903', '[\"*\"]', '2025-11-30 01:20:58', NULL, '2025-11-30 01:15:05', '2025-11-30 01:20:58');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `facility_id` bigint(20) UNSIGNED NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `time_slots`
--

INSERT INTO `time_slots` (`id`, `facility_id`, `start_time`, `end_time`, `created_at`, `updated_at`) VALUES
(1, 1, '08:00:00', '10:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(2, 1, '10:00:00', '12:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(3, 1, '12:00:00', '14:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(4, 1, '14:00:00', '16:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(5, 1, '16:00:00', '18:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(6, 1, '18:00:00', '20:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(7, 1, '20:00:00', '22:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(8, 2, '08:00:00', '10:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(9, 2, '10:00:00', '12:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(10, 2, '12:00:00', '14:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(11, 2, '14:00:00', '16:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(12, 2, '16:00:00', '18:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(13, 2, '18:00:00', '20:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(14, 2, '20:00:00', '22:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(15, 3, '08:00:00', '10:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(16, 3, '10:00:00', '12:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(17, 3, '12:00:00', '14:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(18, 3, '14:00:00', '16:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(19, 3, '16:00:00', '18:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(20, 3, '18:00:00', '20:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(21, 3, '20:00:00', '22:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(22, 4, '08:00:00', '10:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(23, 4, '10:00:00', '12:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(24, 4, '12:00:00', '14:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(25, 4, '14:00:00', '16:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(26, 4, '16:00:00', '18:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(27, 4, '18:00:00', '20:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(28, 4, '20:00:00', '22:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(29, 5, '08:00:00', '10:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(30, 5, '10:00:00', '12:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(31, 5, '12:00:00', '14:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(32, 5, '14:00:00', '16:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(33, 5, '16:00:00', '18:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(34, 5, '18:00:00', '20:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(35, 5, '20:00:00', '22:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(36, 6, '08:00:00', '10:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(37, 6, '10:00:00', '12:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(38, 6, '12:00:00', '14:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(39, 6, '14:00:00', '16:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(40, 6, '16:00:00', '18:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(41, 6, '18:00:00', '20:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(42, 6, '20:00:00', '22:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(43, 7, '08:00:00', '10:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(44, 7, '10:00:00', '12:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(45, 7, '12:00:00', '14:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(46, 7, '14:00:00', '16:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(47, 7, '16:00:00', '18:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(48, 7, '18:00:00', '20:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(49, 7, '20:00:00', '22:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(50, 8, '08:00:00', '10:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(51, 8, '10:00:00', '12:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(52, 8, '12:00:00', '14:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(53, 8, '14:00:00', '16:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(54, 8, '16:00:00', '18:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(55, 8, '18:00:00', '20:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(56, 8, '20:00:00', '22:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(57, 9, '08:00:00', '10:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(58, 9, '10:00:00', '12:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(59, 9, '12:00:00', '14:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(60, 9, '14:00:00', '16:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(61, 9, '16:00:00', '18:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(62, 9, '18:00:00', '20:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(63, 9, '20:00:00', '22:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(64, 10, '08:00:00', '10:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(65, 10, '10:00:00', '12:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(66, 10, '12:00:00', '14:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(67, 10, '14:00:00', '16:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(68, 10, '16:00:00', '18:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(69, 10, '18:00:00', '20:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(70, 10, '20:00:00', '22:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(71, 11, '08:00:00', '10:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(72, 11, '10:00:00', '12:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(73, 11, '12:00:00', '14:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(74, 11, '14:00:00', '16:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(75, 11, '16:00:00', '18:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(76, 11, '18:00:00', '20:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(77, 11, '20:00:00', '22:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(78, 12, '08:00:00', '10:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(79, 12, '10:00:00', '12:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(80, 12, '12:00:00', '14:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(81, 12, '14:00:00', '16:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(82, 12, '16:00:00', '18:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(83, 12, '18:00:00', '20:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(84, 12, '20:00:00', '22:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(85, 13, '08:00:00', '10:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(86, 13, '10:00:00', '12:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(87, 13, '12:00:00', '14:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(88, 13, '14:00:00', '16:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(89, 13, '16:00:00', '18:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(90, 13, '18:00:00', '20:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32'),
(91, 13, '20:00:00', '22:00:00', '2025-11-29 23:45:32', '2025-11-29 23:45:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `ic_number` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `district` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `phone`, `ic_number`, `role`, `district`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin MDHT', 'admin@mdht.com', '2025-11-29 23:42:34', '$2y$12$E8Zaz7nNBV4nfeiIFJ5kZuHdv7n4HkvBYG7pt6Zj7sOYuDC0LdRcu', '0123456789', NULL, 'admin', NULL, 'RrsdTj6zWj', '2025-11-29 23:42:34', '2025-11-29 23:47:35'),
(2, 'Test User', 'user@mdht.com', '2025-11-29 23:42:34', '$2y$12$59YJ0WhgvhfYh.tjgToF3eVV3wMJBJ8oMMCifR1xxOncYFQf6fUTq', '0123456788', NULL, 'user', NULL, 'DlczNqQPBH', '2025-11-29 23:42:34', '2025-11-29 23:47:35'),
(4, 'Rauf - Master Admin', 'rauf6821@gmail.com', NULL, '$2y$12$xOH6cCgnV/rWWn41VJU3wOEJ5UoMdD2.qNDJGSWijqYJIxpROu1tC', '0123456789', '990101-01-0001', 'master_admin', NULL, NULL, '2025-11-29 23:49:29', '2025-11-29 23:49:29'),
(5, 'Ahmad - State Admin', 'state.admin@terengganu.gov.my', NULL, '$2y$12$R9bFnMrzlWpZei5/VZ7eMeQLxPiMnmgcpymerKOlCtIR5ujvpRGQW', '0199876543', '850505-03-0002', 'state_admin', NULL, NULL, '2025-11-29 23:49:29', '2025-11-29 23:49:29'),
(6, 'Fatimah - Admin MDHT', 'admin.mdht@terengganu.gov.my', NULL, '$2y$12$F9HA47.n6.GUGTuhP30Kt.l7N5HaOIQsv5cedxU7nkH9BKOamzf/m', '0198765432', '880808-03-0003', 'district_admin', 'Hulu Terengganu', NULL, '2025-11-29 23:49:29', '2025-11-29 23:49:29'),
(7, 'Hassan - Admin MDB', 'admin.mdb@terengganu.gov.my', NULL, '$2y$12$9wfgrJb4bjA4fK9X4KiY1uFUWIWcEgAujHwXSBhQNVBltIHkp/iM.', '0197654321', '870707-03-0004', 'district_admin', 'Besut', NULL, '2025-11-29 23:49:29', '2025-11-29 23:49:29'),
(8, 'Siti - Admin MDM', 'admin.mdm@terengganu.gov.my', NULL, '$2y$12$MIMKgAnXTAOJvhTLxtn08euTe6ZkBMjby3RG6FToLfdJueUfRLbui', '0196543210', '900909-03-0005', 'district_admin', 'Marang', NULL, '2025-11-29 23:49:29', '2025-11-29 23:49:29'),
(9, 'Ali - Admin MDS', 'admin.mds@terengganu.gov.my', NULL, '$2y$12$t78YRucs9rku0iHY3yj0I.fslhJ/VstaIyHF0yWhAK8ibYjYuf5UW', '0195432109', '920202-03-0006', 'district_admin', 'Setiu', NULL, '2025-11-29 23:49:29', '2025-11-29 23:49:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookings_user_id_foreign` (`user_id`),
  ADD KEY `bookings_facility_id_foreign` (`facility_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_name_unique` (`name`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `facilities_category_id_foreign` (`category_id`),
  ADD KEY `facilities_district_index` (`district`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_booking_id_foreign` (`booking_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `time_slots_facility_id_foreign` (`facility_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_district_index` (`district`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `facilities`
--
ALTER TABLE `facilities`
  ADD CONSTRAINT `facilities_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD CONSTRAINT `time_slots_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
