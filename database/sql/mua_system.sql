-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 22 Apr 2026 pada 16.29
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mua_system`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `backend_audit_logs`
--

CREATE TABLE `backend_audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `target_type` varchar(120) DEFAULT NULL,
  `target_id` bigint(20) UNSIGNED DEFAULT NULL,
  `target_label` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `backend_audit_logs`
--

INSERT INTO `backend_audit_logs` (`id`, `actor_id`, `action`, `target_type`, `target_id`, `target_label`, `ip_address`, `user_agent`, `meta`, `created_at`, `updated_at`) VALUES
(1, 2, 'tenant_subscription_updated', 'App\\Models\\User', 1, 'is3wahyunni@gmail.com', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"plan\":\"premium\",\"expired_at\":\"2027-04-22 23:59:59\",\"bookings_consumed_total\":1}', '2026-04-22 05:19:30', '2026-04-22 05:19:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `billing_logs`
--

CREATE TABLE `billing_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `event_type` varchar(60) NOT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `billing_logs`
--

INSERT INTO `billing_logs` (`id`, `tenant_id`, `payment_id`, `event_type`, `amount`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'payment_dp_marked_paid', 750000.00, '{\"booking_id\":1,\"status\":\"pending\",\"payment_method\":\"manual\"}', '2026-04-19 07:05:42', '2026-04-19 07:05:42'),
(2, 1, NULL, 'subscription_upgrade', NULL, '{\"plan\":\"premium\",\"expired_at\":\"2027-04-22T23:59:59+00:00\"}', '2026-04-22 05:19:30', '2026-04-22 05:19:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `total_people` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `end_time` time DEFAULT NULL,
  `google_event_id` varchar(255) DEFAULT NULL,
  `tomorrow_reminder_sent_at` timestamp NULL DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('pending','confirmed','completed','canceled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `bookings`
--

INSERT INTO `bookings` (`id`, `tenant_id`, `customer_id`, `service_id`, `total_people`, `booking_date`, `booking_time`, `end_time`, `google_event_id`, `tomorrow_reminder_sent_at`, `location`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, '2026-04-22', '04:00:00', '04:45:00', NULL, NULL, 'https://maps.app.goo.gl/6eccFDFvBLg8q94h6', 'pending', 'Test', '2026-04-19 06:58:18', '2026-04-19 06:58:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking_items`
--

CREATE TABLE `booking_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `people_count` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `duration_minutes` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `booking_items`
--

INSERT INTO `booking_items` (`id`, `tenant_id`, `booking_id`, `service_id`, `people_count`, `unit_price`, `duration_minutes`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 750000.00, 45, 750000.00, '2026-04-19 06:58:18', '2026-04-19 06:58:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `calendar_integrations`
--

CREATE TABLE `calendar_integrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `google_event_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `customers`
--

INSERT INTO `customers` (`id`, `tenant_id`, `name`, `phone`, `email`, `instagram`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ratna', '1234567890', NULL, NULL, '2026-04-19 06:57:43', '2026-04-19 06:57:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
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
-- Struktur dari tabel `google_calendar_tokens`
--

CREATE TABLE `google_calendar_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `access_token` text NOT NULL,
  `refresh_token` text DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `token_type` varchar(255) DEFAULT NULL,
  `scope` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
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
-- Struktur dari tabel `job_batches`
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
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_03_24_000100_create_services_table', 1),
(5, '2026_03_24_000200_create_customers_table', 1),
(6, '2026_03_24_000300_create_bookings_table', 1),
(7, '2026_03_24_010000_add_end_time_to_bookings_table', 1),
(8, '2026_03_24_010100_create_calendar_integrations_table', 1),
(9, '2026_03_24_010200_backfill_end_time_on_bookings_table', 1),
(10, '2026_03_24_020000_create_payments_table', 1),
(11, '2026_03_24_020100_backfill_payments_table', 1),
(12, '2026_03_24_030000_add_tenant_id_to_core_tables', 1),
(13, '2026_03_24_030100_add_role_to_users_table', 1),
(14, '2026_03_24_030200_create_subscriptions_table', 1),
(15, '2026_03_24_030300_create_billing_logs_table', 1),
(16, '2026_03_25_000100_create_booking_items_table', 1),
(17, '2026_03_25_000200_add_google_event_id_to_bookings_table', 1),
(18, '2026_03_25_000210_create_google_calendar_tokens_table', 1),
(19, '2026_03_30_000100_create_public_booking_forms_table', 1),
(20, '2026_03_30_000200_add_studio_location_fields_to_users_table', 1),
(21, '2026_04_08_120000_add_onboarding_completed_at_to_users_table', 1),
(22, '2026_04_08_220000_add_notify_tomorrow_booking_to_users_table', 1),
(23, '2026_04_08_220100_add_tomorrow_reminder_sent_at_to_bookings_table', 1),
(24, '2026_04_08_230000_add_bookings_consumed_total_to_subscriptions_table', 1),
(25, '2026_04_08_233000_add_dp_fields_to_payments_table', 1),
(26, '2026_04_22_000100_create_plan_overrides_table', 2),
(27, '2026_04_22_000200_add_suspension_fields_to_users_table', 3),
(28, '2026_04_22_000300_create_backend_audit_logs_table', 3),
(29, '2026_04_22_000400_add_logo_path_to_users_table', 4),
(30, '2026_04_22_000500_add_discount_amount_to_payments_table', 5),
(31, '2026_04_22_000600_add_payment_details_to_users_table', 5),
(32, '2026_04_22_000700_create_tenant_payment_accounts_table', 6);

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `dp_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) NOT NULL DEFAULT 'manual',
  `dp_paid_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `payments`
--

INSERT INTO `payments` (`id`, `tenant_id`, `booking_id`, `amount`, `discount_amount`, `dp_amount`, `paid_amount`, `status`, `payment_method`, `dp_paid_at`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 750000.00, 0.00, 225001.00, 225001.00, 'pending', 'manual', '2026-04-19 07:05:42', NULL, '2026-04-19 06:58:18', '2026-04-19 07:05:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `plan_overrides`
--

CREATE TABLE `plan_overrides` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `plan_key` varchar(30) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` varchar(255) DEFAULT NULL,
  `billing_cycle` varchar(255) DEFAULT NULL,
  `booking_limit_total` int(10) UNSIGNED DEFAULT NULL,
  `benefit` text DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `feature_flags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`feature_flags`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `public_booking_forms`
--

CREATE TABLE `public_booking_forms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `max_submissions` int(10) UNSIGNED DEFAULT NULL,
  `submission_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `duration` int(10) UNSIGNED NOT NULL COMMENT 'Duration in minutes',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `services`
--

INSERT INTO `services` (`id`, `tenant_id`, `name`, `price`, `duration`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Bridal Makeup', 750000.00, 45, 'Paket Makeup dan Hairdo untuk Bridal Makeup', '2026-04-19 06:57:28', '2026-04-19 06:57:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('5XH17m7YdDO1AAkRlKFqyWdskJJnLu5zrxuSwR2b', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSXI5MFduSTFvUlB5UThhUGZNWmtzU2Q1RHRsNzQwNVIzMkhucHg2RSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9iYWNrZW5kL3RlbmFudHMiO3M6NToicm91dGUiO3M6MjE6ImJhY2tlbmQudGVuYW50cy5pbmRleCI7fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO30=', 1776867560),
('fqsAZpCezwp2EbhGEJKlU71ADytiaEoBp87g5aN2', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiOFE0T05kenB1R2lxN0NBbFh4djBpcERwTUxUNlljYmgzT0huWUVQTSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9ib29raW5ncyI7czo1OiJyb3V0ZSI7czoyMDoiYWRtaW4uYm9va2luZ3MuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1776867577);

-- --------------------------------------------------------

--
-- Struktur dari tabel `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `plan` varchar(30) NOT NULL DEFAULT 'free',
  `bookings_consumed_total` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `expired_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `plan`, `bookings_consumed_total`, `expired_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'premium', 1, '2027-04-22 15:59:59', '2026-04-19 06:56:13', '2026-04-22 05:19:30'),
(2, 2, 'free', 0, NULL, '2026-04-22 05:17:16', '2026-04-22 05:17:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tenant_payment_accounts`
--

CREATE TABLE `tenant_payment_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `bank_name` varchar(120) NOT NULL,
  `account_name` varchar(120) NOT NULL,
  `account_number` varchar(80) NOT NULL,
  `contact` varchar(120) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'tenant',
  `is_suspended` tinyint(1) NOT NULL DEFAULT 0,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `suspended_reason` varchar(255) DEFAULT NULL,
  `studio_name` varchar(255) DEFAULT NULL,
  `studio_location` varchar(255) DEFAULT NULL,
  `studio_maps_link` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `payment_bank_name` varchar(120) DEFAULT NULL,
  `payment_account_name` varchar(120) DEFAULT NULL,
  `payment_account_number` varchar(80) DEFAULT NULL,
  `payment_contact` varchar(120) DEFAULT NULL,
  `payment_instructions` text DEFAULT NULL,
  `notify_tomorrow_booking` tinyint(1) NOT NULL DEFAULT 1,
  `onboarding_completed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `is_suspended`, `suspended_at`, `suspended_reason`, `studio_name`, `studio_location`, `studio_maps_link`, `logo_path`, `payment_bank_name`, `payment_account_name`, `payment_account_number`, `payment_contact`, `payment_instructions`, `notify_tomorrow_booking`, `onboarding_completed_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Yunni Makeup', 'is3wahyunni@gmail.com', NULL, '$2y$12$GHh0RnLd4LbF5v5koXQfS.837lqUQVmGfB5Jxqx4cbytJKU5ge6Sq', 'tenant', 0, NULL, NULL, 'Yunni Makeup Studio', 'Jl. Pulau Belitung, Babakan Sari, Gg. Punduh, No. 2A, Pedungan, Denpasar Selatan, 80222', 'https://maps.app.goo.gl/oNPPC7NfkdoZuDj86', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-19 06:58:18', NULL, '2026-04-19 06:56:13', '2026-04-19 07:04:11'),
(2, 'Super Admin', 'admin@domain.com', NULL, '$2y$12$gGt9zM.ApvAjg2YQWU9AMuJpcXHo2VN9bAwv0i1BqLPBuBqujqujy', 'super_admin', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'u1Zew9kUo1waGQ3YraU5KjMpHwkft5tDnuoqcNc51LyRe55UUZ7ns82wtLrP', '2026-04-22 05:12:34', '2026-04-22 05:12:34');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `backend_audit_logs`
--
ALTER TABLE `backend_audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `backend_audit_logs_actor_id_foreign` (`actor_id`),
  ADD KEY `backend_audit_logs_action_created_at_index` (`action`,`created_at`),
  ADD KEY `backend_audit_logs_target_type_target_id_index` (`target_type`,`target_id`);

--
-- Indeks untuk tabel `billing_logs`
--
ALTER TABLE `billing_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `billing_logs_payment_id_foreign` (`payment_id`),
  ADD KEY `billing_logs_tenant_id_event_type_index` (`tenant_id`,`event_type`);

--
-- Indeks untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookings_customer_id_foreign` (`customer_id`),
  ADD KEY `bookings_service_id_foreign` (`service_id`),
  ADD KEY `bookings_booking_date_booking_time_index` (`booking_date`,`booking_time`),
  ADD KEY `bookings_status_index` (`status`),
  ADD KEY `bookings_tenant_id_index` (`tenant_id`),
  ADD KEY `bookings_google_event_id_index` (`google_event_id`);

--
-- Indeks untuk tabel `booking_items`
--
ALTER TABLE `booking_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_items_service_id_foreign` (`service_id`),
  ADD KEY `booking_items_tenant_id_booking_id_index` (`tenant_id`,`booking_id`),
  ADD KEY `booking_items_booking_id_service_id_index` (`booking_id`,`service_id`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `calendar_integrations`
--
ALTER TABLE `calendar_integrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `calendar_integrations_google_event_id_unique` (`google_event_id`),
  ADD KEY `calendar_integrations_booking_id_foreign` (`booking_id`);

--
-- Indeks untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customers_tenant_id_index` (`tenant_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `google_calendar_tokens`
--
ALTER TABLE `google_calendar_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `google_calendar_tokens_user_id_unique` (`user_id`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_booking_id_foreign` (`booking_id`),
  ADD KEY `payments_status_index` (`status`),
  ADD KEY `payments_tenant_id_index` (`tenant_id`);

--
-- Indeks untuk tabel `plan_overrides`
--
ALTER TABLE `plan_overrides`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plan_overrides_plan_key_unique` (`plan_key`);

--
-- Indeks untuk tabel `public_booking_forms`
--
ALTER TABLE `public_booking_forms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `public_booking_forms_token_unique` (`token`),
  ADD KEY `public_booking_forms_tenant_id_is_active_index` (`tenant_id`,`is_active`),
  ADD KEY `public_booking_forms_expires_at_index` (`expires_at`);

--
-- Indeks untuk tabel `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `services_tenant_id_index` (`tenant_id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscriptions_user_id_foreign` (`user_id`),
  ADD KEY `subscriptions_plan_expired_at_index` (`plan`,`expired_at`);

--
-- Indeks untuk tabel `tenant_payment_accounts`
--
ALTER TABLE `tenant_payment_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_payment_accounts_tenant_id_is_primary_index` (`tenant_id`,`is_primary`),
  ADD KEY `tenant_payment_accounts_tenant_id_sort_order_index` (`tenant_id`,`sort_order`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_index` (`role`),
  ADD KEY `users_is_suspended_role_index` (`is_suspended`,`role`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `backend_audit_logs`
--
ALTER TABLE `backend_audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `billing_logs`
--
ALTER TABLE `billing_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `booking_items`
--
ALTER TABLE `booking_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `calendar_integrations`
--
ALTER TABLE `calendar_integrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `google_calendar_tokens`
--
ALTER TABLE `google_calendar_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `plan_overrides`
--
ALTER TABLE `plan_overrides`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `public_booking_forms`
--
ALTER TABLE `public_booking_forms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `tenant_payment_accounts`
--
ALTER TABLE `tenant_payment_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `backend_audit_logs`
--
ALTER TABLE `backend_audit_logs`
  ADD CONSTRAINT `backend_audit_logs_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `billing_logs`
--
ALTER TABLE `billing_logs`
  ADD CONSTRAINT `billing_logs_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `billing_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `bookings_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `bookings_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `booking_items`
--
ALTER TABLE `booking_items`
  ADD CONSTRAINT `booking_items_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_items_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_items_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `calendar_integrations`
--
ALTER TABLE `calendar_integrations`
  ADD CONSTRAINT `calendar_integrations_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `google_calendar_tokens`
--
ALTER TABLE `google_calendar_tokens`
  ADD CONSTRAINT `google_calendar_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `payments_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `public_booking_forms`
--
ALTER TABLE `public_booking_forms`
  ADD CONSTRAINT `public_booking_forms_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tenant_payment_accounts`
--
ALTER TABLE `tenant_payment_accounts`
  ADD CONSTRAINT `tenant_payment_accounts_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
