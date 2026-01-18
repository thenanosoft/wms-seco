-- MySQL dump 10.13  Distrib 9.5.0, for macos14.8 (x86_64)
--
-- Host: localhost    Database: wms
-- ------------------------------------------------------
-- Server version	9.5.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ '06de8fd2-e81a-11f0-a46f-37570f452e15:1-1796';

--
-- Table structure for table `app_settings`
--

DROP TABLE IF EXISTS `app_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings`
--

LOCK TABLES `app_settings` WRITE;
/*!40000 ALTER TABLE `app_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_settings`
--

DROP TABLE IF EXISTS `backup_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `frequency` enum('daily','weekly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'daily',
  `weekly_day` tinyint unsigned NOT NULL DEFAULT '1',
  `time_hm` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '02:00',
  `backup_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_settings`
--

LOCK TABLES `backup_settings` WRITE;
/*!40000 ALTER TABLE `backup_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backups`
--

DROP TABLE IF EXISTS `backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `checksum` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backups_created_by_foreign` (`created_by`),
  KEY `backups_file_name_index` (`file_name`),
  CONSTRAINT `backups_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backups`
--

LOCK TABLES `backups` WRITE;
/*!40000 ALTER TABLE `backups` DISABLE KEYS */;
/*!40000 ALTER TABLE `backups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groups_group_code_unique` (`group_code`),
  KEY `groups_group_code_index` (`group_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'51','Steel Material','2026-01-13 19:41:25','2026-01-13 19:41:25'),(2,'52','Electrical','2026-01-13 19:41:25','2026-01-13 19:41:25'),(3,'53','Hardware','2026-01-13 19:41:25','2026-01-13 19:41:25');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `issue_lines`
--

DROP TABLE IF EXISTS `issue_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `issue_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `issue_id` bigint unsigned NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `specification` text COLLATE utf8mb4_unicode_ci,
  `issue_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `quantity` decimal(15,3) NOT NULL,
  `line_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `issue_lines_item_id_index` (`item_id`),
  KEY `issue_lines_issue_id_item_id_index` (`issue_id`,`item_id`),
  CONSTRAINT `issue_lines_issue_id_foreign` FOREIGN KEY (`issue_id`) REFERENCES `issues` (`id`) ON DELETE CASCADE,
  CONSTRAINT `issue_lines_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `issue_lines`
--

LOCK TABLES `issue_lines` WRITE;
/*!40000 ALTER TABLE `issue_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `issue_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `issues`
--

DROP TABLE IF EXISTS `issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `issues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `issue_date` date NOT NULL,
  `issued_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `issues_created_by_foreign` (`created_by`),
  KEY `issues_issue_date_index` (`issue_date`),
  CONSTRAINT `issues_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `issues`
--

LOCK TABLES `issues` WRITE;
/*!40000 ALTER TABLE `issues` DISABLE KEYS */;
/*!40000 ALTER TABLE `issues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group_id` bigint unsigned NOT NULL,
  `item_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_spec` text COLLATE utf8mb4_unicode_ci,
  `low_stock_threshold` decimal(12,3) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `items_group_id_item_code_unique` (`group_id`,`item_code`),
  KEY `items_item_code_index` (`item_code`),
  CONSTRAINT `items_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (1,1,'ST-001','Steel Rod',NULL,NULL,'2026-01-13 19:41:25','2026-01-13 19:41:25'),(2,1,'ST-002','Steel Mug',NULL,NULL,'2026-01-13 19:41:25','2026-01-13 19:41:25'),(3,1,'ST-003','Steel Sheet',NULL,NULL,'2026-01-13 19:41:25','2026-01-13 19:41:25'),(4,2,'EL-001','Copper Wire',NULL,NULL,'2026-01-13 19:41:25','2026-01-13 19:41:25'),(5,2,'EL-002','Switch',NULL,NULL,'2026-01-13 19:41:25','2026-01-13 19:41:25'),(6,3,'HW-001','Nut Bolt Set',NULL,NULL,'2026-01-13 19:41:25','2026-01-13 19:41:25');
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_01_02_211732_add_role_and_is_active_to_users_table',1),(5,'2026_01_02_211733_create_groups_table',1),(6,'2026_01_02_211733_create_items_table',1),(7,'2026_01_02_211733_create_purchases_table',1),(8,'2026_01_02_211734_create_issues_table',1),(9,'2026_01_02_211735_create_backups_table',1),(10,'2026_01_02_211735_create_issue_lines_table',1),(11,'2026_01_02_211735_create_system_logs_table',1),(12,'2026_01_02_211736_create_purchase_lines_table',1),(13,'2026_01_02_211737_create_stock_ledger_table',1),(14,'2026_01_05_101347_create_return_transactions_table',1),(15,'2026_01_05_101357_create_return_lines_table',1),(16,'2026_01_07_132612_create_backup_settings_table',1),(17,'2026_01_07_182241_add_stock_threshold_to_items_table',1),(18,'2026_01_07_183019_create_app_settings_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_lines`
--

DROP TABLE IF EXISTS `purchase_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint unsigned NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `specification` text COLLATE utf8mb4_unicode_ci,
  `purchase_price` decimal(15,2) NOT NULL,
  `quantity` decimal(15,3) NOT NULL,
  `line_total` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_lines_item_id_index` (`item_id`),
  KEY `purchase_lines_purchase_id_item_id_index` (`purchase_id`,`item_id`),
  CONSTRAINT `purchase_lines_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  CONSTRAINT `purchase_lines_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_lines`
--

LOCK TABLES `purchase_lines` WRITE;
/*!40000 ALTER TABLE `purchase_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_date` date NOT NULL,
  `supplier_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchases_created_by_foreign` (`created_by`),
  KEY `purchases_purchase_date_index` (`purchase_date`),
  CONSTRAINT `purchases_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchases`
--

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `return_lines`
--

DROP TABLE IF EXISTS `return_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `return_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `return_transaction_id` bigint unsigned NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `specification` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `quantity` decimal(12,3) NOT NULL,
  `line_total` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `return_lines_return_transaction_id_foreign` (`return_transaction_id`),
  KEY `return_lines_item_id_foreign` (`item_id`),
  CONSTRAINT `return_lines_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  CONSTRAINT `return_lines_return_transaction_id_foreign` FOREIGN KEY (`return_transaction_id`) REFERENCES `return_transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `return_lines`
--

LOCK TABLES `return_lines` WRITE;
/*!40000 ALTER TABLE `return_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `return_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `return_transactions`
--

DROP TABLE IF EXISTS `return_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `return_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `return_date` date NOT NULL,
  `type` enum('IN','OUT') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `party` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `return_transactions_created_by_foreign` (`created_by`),
  CONSTRAINT `return_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `return_transactions`
--

LOCK TABLES `return_transactions` WRITE;
/*!40000 ALTER TABLE `return_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `return_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_ledger`
--

DROP TABLE IF EXISTS `stock_ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_ledger` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `txn_date` date NOT NULL,
  `txn_type` enum('PURCHASE','ISSUE','RETURN_IN','RETURN_OUT','ADJUSTMENT','OPENING') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_table` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_id` bigint unsigned NOT NULL,
  `ref_line_id` bigint unsigned DEFAULT NULL,
  `item_id` bigint unsigned NOT NULL,
  `qty_in` decimal(15,3) NOT NULL DEFAULT '0.000',
  `qty_out` decimal(15,3) NOT NULL DEFAULT '0.000',
  `unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `specification_snapshot` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_ledger_created_by_foreign` (`created_by`),
  KEY `stock_ledger_item_id_txn_date_index` (`item_id`,`txn_date`),
  KEY `stock_ledger_txn_type_txn_date_index` (`txn_type`,`txn_date`),
  KEY `stock_ledger_ref_table_ref_id_index` (`ref_table`,`ref_id`),
  KEY `stock_ledger_txn_date_index` (`txn_date`),
  CONSTRAINT `stock_ledger_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `stock_ledger_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_ledger`
--

LOCK TABLES `stock_ledger` WRITE;
/*!40000 ALTER TABLE `stock_ledger` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_ledger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_logs`
--

DROP TABLE IF EXISTS `system_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `context_json` json DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_logs_created_by_foreign` (`created_by`),
  KEY `system_logs_level_module_index` (`level`,`module`),
  CONSTRAINT `system_logs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_logs`
--

LOCK TABLES `system_logs` WRITE;
/*!40000 ALTER TABLE `system_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','store_helper') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'store_helper',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Main Desk Admin','admin@local.test','admin',NULL,'$2y$12$9R0RB8nyYhklfF1GLwmVMOIg7gh.bA1X0m.t2k9xw0f0Qbc2goNrC','admin',1,NULL,'2026-01-13 19:41:25','2026-01-13 19:41:25'),(2,'Store Helper','helper@local.test','helper',NULL,'$2y$12$/yffEd.62MfEZRAt4LQuseqDkNLawnRjZftPDTg0hXvD9Nc7USlpS','store_helper',1,NULL,'2026-01-13 19:41:25','2026-01-13 19:41:25');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-14  5:43:06
