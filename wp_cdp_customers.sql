-- AdminNeo 4.17.2 MySQL 8.0.35 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `wp_cdp_customers`;
CREATE TABLE `wp_cdp_customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `phone` bigint NOT NULL,
  `dob` date NOT NULL,
  `sex` enum('Male','Female','Other') COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `cr_number` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_520_ci,
  `city` varchar(50) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `country` varchar(50) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_520_ci DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `email_2` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_cdp_customers` (`id`, `name`, `email`, `phone`, `dob`, `sex`, `cr_number`, `address`, `city`, `country`, `status`) VALUES
(6,	'hgj',	'nhagadgets@gmail.com',	657567,	'2025-08-01',	'Male',	'56776456',	'fghn',	'ythfgh',	'lk;',	'active'),
(7,	'hgj',	'nhagadgetssss@gmail.com',	657567,	'2025-07-31',	'Male',	'5677',	'fgh',	'kl;',	'fghfgh',	'active');

-- 2025-08-20 09:11:55 UTC
