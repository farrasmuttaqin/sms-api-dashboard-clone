SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `AD_API_USER_REPORT` (
  `report_id` int(10) UNSIGNED NOT NULL,
  `api_user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `AD_PRIVILEGES` (
  `privilege_id` int(10) UNSIGNED NOT NULL,
  `privilege_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `AD_PRIVILEGES` (`privilege_id`, `privilege_name`) VALUES
(1, 'user.acc.system'),
(2, 'user.acc.company'),
(3, 'user.page.read'),
(4, 'user.page.write'),
(5, 'user.page.delete'),
(6, 'report.acc.system'),
(7, 'apiuser.acc.system'),
(8, 'user.acc.company'),
(9, 'user.page.read'),
(10, 'user.page.write'),
(11, 'user.page.delete'),
(12, 'apiuser.acc.company'),
(13, 'report.acc.company'),
(14, 'report.acc.own'),
(15, 'apiuser.acc.own'),
(16, 'report.page.read'),
(17, 'report.page.download'),
(18, 'report.page.generate'),
(19, 'report.page.delete');

CREATE TABLE `AD_PRIVILEGE_ROLE` (
  `privilege_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `AD_PRIVILEGE_ROLE` (`privilege_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 2),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 2),
(14, 3),
(15, 3),
(16, 3),
(17, 3),
(18, 3),
(19, 3);

CREATE TABLE `AD_QUEUE_FAILED_JOB` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `AD_QUEUE_JOB` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `AD_REPORT` (
  `report_id` int(10) UNSIGNED NOT NULL,
  `report_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `message_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `generate_status` int(11) NOT NULL DEFAULT 0,
  `pid` int(11) NOT NULL DEFAULT 0,
  `file_type` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'csv',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `percentage` decimal(3,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `AD_ROLES` (
  `role_id` int(10) UNSIGNED NOT NULL,
  `role_name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `AD_ROLES` (`role_id`, `role_name`) VALUES
(1, 'Super Admin'),
(2, 'Admin'),
(3, 'Report');

CREATE TABLE `AD_ROLE_USER` (
  `role_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `AD_ROLE_USER` (`role_id`, `user_id`) VALUES
(1, 1),
(2, 1),
(3, 1);

CREATE TABLE `AD_USER` (
  `ad_user_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_login` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expired_token` timestamp NULL DEFAULT NULL,
  `forget_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `AD_USER` (`ad_user_id`, `name`, `avatar`, `email`, `client_id`, `created_by`, `password`, `last_login`, `expired_token`, `forget_token`, `remember_token`, `created_at`, `updated_at`, `active`, `deleted_at`) VALUES
(1, 'Super Admin', NULL, 'super.admin@1rstwap.com', 1, NULL, '$2y$10$vPRF.qnQHw32frC1wdO6oOxViS/uNtxESRN.FSppAvTubkruINol2', '2017-12-12 06:56:49', NULL, NULL, NULL, '2017-12-11 23:56:49', '2017-12-11 23:56:49', 1, NULL);

CREATE TABLE `AD_USER_APIUSER` (
  `ad_user_id` int(10) UNSIGNED NOT NULL,
  `api_user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `AD_USER_APIUSER` (`ad_user_id`, `api_user_id`) VALUES
(1, 1),
(1, 3),
(1, 4),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 12),
(1, 15),
(1, 25),
(1, 30),
(1, 35),
(1, 36),
(1, 38),
(1, 43),
(1, 46),
(1, 776);


ALTER TABLE `AD_API_USER_REPORT`
  ADD KEY `ad_api_user_report_report_id_foreign` (`report_id`),
  ADD KEY `ad_api_user_report_api_user_id_foreign` (`api_user_id`);

ALTER TABLE `AD_PRIVILEGES`
  ADD PRIMARY KEY (`privilege_id`);

ALTER TABLE `AD_PRIVILEGE_ROLE`
  ADD KEY `ad_privilege_role_privilege_id_foreign` (`privilege_id`),
  ADD KEY `ad_privilege_role_role_id_foreign` (`role_id`);

ALTER TABLE `AD_QUEUE_FAILED_JOB`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `AD_QUEUE_JOB`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ad_queue_job_queue_index` (`queue`);

ALTER TABLE `AD_REPORT`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `ad_report_created_by_index` (`created_by`);

ALTER TABLE `AD_ROLES`
  ADD PRIMARY KEY (`role_id`);

ALTER TABLE `AD_ROLE_USER`
  ADD KEY `ad_role_user_role_id_foreign` (`role_id`),
  ADD KEY `ad_role_user_user_id_foreign` (`user_id`);

ALTER TABLE `AD_USER`
  ADD PRIMARY KEY (`ad_user_id`),
  ADD UNIQUE KEY `ad_user_email_unique` (`email`),
  ADD KEY `ad_user_client_id_foreign` (`client_id`);

ALTER TABLE `AD_USER_APIUSER`
  ADD KEY `ad_user_apiuser_ad_user_id_foreign` (`ad_user_id`),
  ADD KEY `ad_user_apiuser_api_user_id_foreign` (`api_user_id`);


ALTER TABLE `AD_PRIVILEGES`
  MODIFY `privilege_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
ALTER TABLE `AD_QUEUE_FAILED_JOB`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `AD_QUEUE_JOB`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `AD_REPORT`
  MODIFY `report_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `AD_ROLES`
  MODIFY `role_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `AD_USER`
  MODIFY `ad_user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `AD_API_USER_REPORT`
  ADD CONSTRAINT `ad_api_user_report_api_user_id_foreign` FOREIGN KEY (`api_user_id`) REFERENCES `USER` (`USER_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ad_api_user_report_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `AD_REPORT` (`report_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `AD_PRIVILEGE_ROLE`
  ADD CONSTRAINT `ad_privilege_role_privilege_id_foreign` FOREIGN KEY (`privilege_id`) REFERENCES `AD_PRIVILEGES` (`privilege_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ad_privilege_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `AD_ROLES` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `AD_ROLE_USER`
  ADD CONSTRAINT `ad_role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `AD_ROLES` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ad_role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `AD_USER` (`ad_user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `AD_USER`
  ADD CONSTRAINT `ad_user_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `CLIENT` (`CLIENT_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `AD_USER_APIUSER`
  ADD CONSTRAINT `ad_user_apiuser_ad_user_id_foreign` FOREIGN KEY (`ad_user_id`) REFERENCES `AD_USER` (`ad_user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ad_user_apiuser_api_user_id_foreign` FOREIGN KEY (`api_user_id`) REFERENCES `USER` (`USER_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
