-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: yatranest_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` int(10) unsigned DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_action` (`action`),
  KEY `idx_audit_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,NULL,'booking_created','booking',1,NULL,'{\"reference\":\"YN-20261013-86BA97\",\"total\":65621.85}','127.0.0.1','Unknown','2026-10-13 16:45:25'),(2,NULL,'booking_cancelled','booking',1,'{\"old_status\":\"pending\"}','{\"new_status\":\"cancelled\",\"reason\":\"Customer schedule change\"}','127.0.0.1','Unknown','2026-10-13 16:45:25'),(3,NULL,'booking_created','booking',2,NULL,'{\"reference\":\"YN-20261013-A6E2E1\",\"total\":65621.85}','127.0.0.1','Unknown','2026-10-13 16:48:31'),(4,NULL,'booking_cancelled','booking',2,'{\"old_status\":\"pending\"}','{\"new_status\":\"cancelled\",\"reason\":\"Customer schedule change\"}','127.0.0.1','Unknown','2026-10-13 16:48:32'),(5,1,'admin_login','user',1,NULL,NULL,'::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-IN) WindowsPowerShell/5.1.26100.9444','2026-10-13 16:52:48'),(6,5,'booking_created','booking',3,NULL,'{\"reference\":\"YN-20261013-F723C1\",\"total\":49872.9}','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-IN) WindowsPowerShell/5.1.26100.9444','2026-10-13 16:53:10'),(7,6,'booking_created','booking',4,NULL,'{\"reference\":\"YN-20261013-675BB1\",\"total\":94500}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-13 17:03:34'),(8,6,'user_logout','user',6,NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-13 17:04:14'),(9,1,'user_login','user',1,NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-13 17:22:06'),(10,1,'settings_updated','site_setting',NULL,NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-13 17:22:55'),(11,NULL,'booking_created','booking',5,NULL,'{\"reference\":\"YN-20261013-BD07A7\",\"total\":65621.85}','127.0.0.1','Unknown','2026-10-13 17:24:54'),(12,NULL,'booking_cancelled','booking',5,'{\"old_status\":\"pending\"}','{\"new_status\":\"cancelled\",\"reason\":\"Customer schedule change\"}','127.0.0.1','Unknown','2026-10-13 17:24:54'),(13,1,'user_logout','user',1,NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-13 17:25:48'),(14,NULL,'booking_created','booking',6,NULL,'{\"reference\":\"YN-20261013-024E39\",\"total\":65621.85}','127.0.0.1','Unknown','2026-10-13 17:32:21'),(15,NULL,'booking_cancelled','booking',6,'{\"old_status\":\"pending\"}','{\"new_status\":\"cancelled\",\"reason\":\"Customer schedule change\"}','127.0.0.1','Unknown','2026-10-13 17:32:21'),(16,NULL,'booking_created','booking',7,NULL,'{\"reference\":\"YN-20261013-4151E2\",\"total\":65621.85}','127.0.0.1','Unknown','2026-10-13 17:38:18'),(17,NULL,'booking_cancelled','booking',7,'{\"old_status\":\"pending\"}','{\"new_status\":\"cancelled\",\"reason\":\"Customer schedule change\"}','127.0.0.1','Unknown','2026-10-13 17:38:19');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_holds`
--

DROP TABLE IF EXISTS `booking_holds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_holds` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `departure_id` int(10) unsigned NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `seats_held` int(10) unsigned NOT NULL DEFAULT 1,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_holds_dep` (`departure_id`),
  KEY `idx_holds_expires` (`expires_at`),
  KEY `idx_holds_session` (`session_id`),
  CONSTRAINT `fk_holds_dep` FOREIGN KEY (`departure_id`) REFERENCES `tour_departures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_holds`
--

LOCK TABLES `booking_holds` WRITE;
/*!40000 ALTER TABLE `booking_holds` DISABLE KEYS */;
INSERT INTO `booking_holds` VALUES (6,25,'7f3f02a9be043731bbde6cc4ae6ba03a',NULL,2,'2026-10-13 23:11:54','2026-10-13 17:26:54');
/*!40000 ALTER TABLE `booking_holds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_status_history`
--

DROP TABLE IF EXISTS `booking_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_status_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `from_status` varchar(50) DEFAULT NULL,
  `to_status` varchar(50) NOT NULL,
  `changed_by_user_id` int(10) unsigned DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_hist_booking` (`booking_id`),
  CONSTRAINT `fk_hist_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_status_history`
--

LOCK TABLES `booking_status_history` WRITE;
/*!40000 ALTER TABLE `booking_status_history` DISABLE KEYS */;
INSERT INTO `booking_status_history` VALUES (1,1,NULL,'pending',NULL,'Booking initialized via website.','2026-10-13 16:45:25'),(2,1,'pending','cancelled',3,'Booking cancelled: Customer schedule change','2026-10-13 16:45:25'),(3,2,NULL,'pending',NULL,'Booking initialized via website.','2026-10-13 16:48:31'),(4,2,'pending','cancelled',3,'Booking cancelled: Customer schedule change','2026-10-13 16:48:32'),(5,3,NULL,'pending',5,'Booking initialized via website.','2026-10-13 16:53:10'),(6,4,NULL,'awaiting_payment',6,'Booking initialized via website.','2026-10-13 17:03:34'),(7,5,NULL,'pending',NULL,'Booking initialized via website.','2026-10-13 17:24:54'),(8,5,'pending','cancelled',3,'Booking cancelled: Customer schedule change','2026-10-13 17:24:54'),(9,6,NULL,'pending',NULL,'Booking initialized via website.','2026-10-13 17:32:21'),(10,6,'pending','cancelled',3,'Booking cancelled: Customer schedule change','2026-10-13 17:32:21'),(11,7,NULL,'pending',NULL,'Booking initialized via website.','2026-10-13 17:38:18'),(12,7,'pending','cancelled',3,'Booking cancelled: Customer schedule change','2026-10-13 17:38:19');
/*!40000 ALTER TABLE `booking_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_travelers`
--

DROP TABLE IF EXISTS `booking_travelers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_travelers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `age` int(10) unsigned NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `traveler_type` enum('adult','child','infant') NOT NULL DEFAULT 'adult',
  `id_proof_type` varchar(50) DEFAULT NULL,
  `id_proof_number` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_travelers_booking` (`booking_id`),
  CONSTRAINT `fk_travelers_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_travelers`
--

LOCK TABLES `booking_travelers` WRITE;
/*!40000 ALTER TABLE `booking_travelers` DISABLE KEYS */;
INSERT INTO `booking_travelers` VALUES (1,1,'Rahul Sharma',32,'male','adult',NULL,NULL),(2,1,'Ananya Sharma',29,'female','adult',NULL,NULL),(3,1,'Aarav Sharma',6,'male','child',NULL,NULL),(4,2,'Rahul Sharma',32,'male','adult',NULL,NULL),(5,2,'Ananya Sharma',29,'female','adult',NULL,NULL),(6,2,'Aarav Sharma',6,'male','child',NULL,NULL),(7,3,'Meera Gupta',28,'female','adult',NULL,NULL),(8,3,'Aarav Gupta',31,'male','adult',NULL,NULL),(9,4,'Prem Raj Mahto',28,'male','adult',NULL,NULL),(10,4,'rakesh',26,'male','adult',NULL,NULL),(11,5,'Rahul Sharma',32,'male','adult',NULL,NULL),(12,5,'Ananya Sharma',29,'female','adult',NULL,NULL),(13,5,'Aarav Sharma',6,'male','child',NULL,NULL),(14,6,'Rahul Sharma',32,'male','adult',NULL,NULL),(15,6,'Ananya Sharma',29,'female','adult',NULL,NULL),(16,6,'Aarav Sharma',6,'male','child',NULL,NULL),(17,7,'Rahul Sharma',32,'male','adult',NULL,NULL),(18,7,'Ananya Sharma',29,'female','adult',NULL,NULL),(19,7,'Aarav Sharma',6,'male','child',NULL,NULL);
/*!40000 ALTER TABLE `booking_travelers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_reference` varchar(50) NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `package_id` int(10) unsigned NOT NULL,
  `departure_id` int(10) unsigned NOT NULL,
  `adult_count` int(10) unsigned NOT NULL DEFAULT 1,
  `child_count` int(10) unsigned NOT NULL DEFAULT 0,
  `infant_count` int(10) unsigned NOT NULL DEFAULT 0,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 5.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('unpaid','pending','paid','failed','partially_refunded','refunded') NOT NULL DEFAULT 'unpaid',
  `booking_status` enum('pending','awaiting_payment','confirmed','cancelled','completed','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` enum('razorpay','pay_later') NOT NULL DEFAULT 'razorpay',
  `pickup_location` varchar(255) DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `coupon_id` int(10) unsigned DEFAULT NULL,
  `internal_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_reference` (`booking_reference`),
  KEY `idx_booking_ref` (`booking_reference`),
  KEY `idx_booking_customer` (`customer_id`),
  KEY `idx_booking_status` (`booking_status`),
  KEY `idx_booking_payment_status` (`payment_status`),
  KEY `fk_bookings_pkg` (`package_id`),
  KEY `fk_bookings_dep` (`departure_id`),
  KEY `fk_bookings_coupon` (`coupon_id`),
  CONSTRAINT `fk_bookings_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bookings_cust` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_bookings_dep` FOREIGN KEY (`departure_id`) REFERENCES `tour_departures` (`id`),
  CONSTRAINT `fk_bookings_pkg` FOREIGN KEY (`package_id`) REFERENCES `tour_packages` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (1,'YN-20261013-86BA97',3,1,1,2,1,0,64997.00,2500.00,5.00,3124.85,65621.85,'unpaid','cancelled','pay_later','Airport Terminal 2','Vegetarian meal preference',1,'\nCancellation reason: Customer schedule change','2026-10-13 16:45:25','2026-10-13 16:45:25'),(2,'YN-20261013-A6E2E1',3,1,1,2,1,0,64997.00,2500.00,5.00,3124.85,65621.85,'unpaid','cancelled','pay_later','Airport Terminal 2','Vegetarian meal preference',1,'\nCancellation reason: Customer schedule change','2026-10-13 16:48:31','2026-10-13 16:48:32'),(3,'YN-20261013-F723C1',5,1,1,2,0,0,49998.00,2500.00,5.00,2374.90,49872.90,'unpaid','pending','pay_later','Srinagar Airport','Honeymoon decoration on houseboat',1,NULL,'2026-10-13 16:53:10','2026-10-13 16:53:10'),(4,'YN-20261013-675BB1',6,6,31,2,0,0,90000.00,0.00,5.00,4500.00,94500.00,'unpaid','awaiting_payment','razorpay','ranchi','',NULL,NULL,'2026-10-13 17:03:34','2026-10-13 17:03:34'),(5,'YN-20261013-BD07A7',3,1,1,2,1,0,64997.00,2500.00,5.00,3124.85,65621.85,'unpaid','cancelled','pay_later','Airport Terminal 2','Vegetarian meal preference',1,'\nCancellation reason: Customer schedule change','2026-10-13 17:24:54','2026-10-13 17:24:54'),(6,'YN-20261013-024E39',3,1,1,2,1,0,64997.00,2500.00,5.00,3124.85,65621.85,'unpaid','cancelled','pay_later','Airport Terminal 2','Vegetarian meal preference',1,'\nCancellation reason: Customer schedule change','2026-10-13 17:32:21','2026-10-13 17:32:21'),(7,'YN-20261013-4151E2',3,1,1,2,1,0,64997.00,2500.00,5.00,3124.85,65621.85,'unpaid','cancelled','pay_later','Airport Terminal 2','Vegetarian meal preference',1,'\nCancellation reason: Customer schedule change','2026-10-13 17:38:18','2026-10-13 17:38:18');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupon_redemptions`
--

DROP TABLE IF EXISTS `coupon_redemptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon_redemptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` int(10) unsigned NOT NULL,
  `booking_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `discount_applied` decimal(10,2) NOT NULL,
  `redeemed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_redempt_coupon` (`coupon_id`),
  KEY `fk_redempt_booking` (`booking_id`),
  KEY `fk_redempt_user` (`user_id`),
  CONSTRAINT `fk_redempt_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_redempt_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_redempt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupon_redemptions`
--

LOCK TABLES `coupon_redemptions` WRITE;
/*!40000 ALTER TABLE `coupon_redemptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupon_redemptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `min_booking_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `start_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `usage_limit` int(10) unsigned DEFAULT 100,
  `per_user_limit` int(10) unsigned DEFAULT 1,
  `used_count` int(10) unsigned DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_coupon_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
INSERT INTO `coupons` VALUES (1,'WELCOME10','percentage',10.00,10000.00,2500.00,'2026-09-13','2027-04-13',500,2,12,1,'2026-10-13 17:21:07'),(2,'FLAT1000','fixed',1000.00,15000.00,1000.00,'2026-09-13','2027-04-13',200,1,5,1,'2026-10-13 17:21:07'),(3,'EARLYBIRD','percentage',15.00,20000.00,3500.00,'2026-09-13','2027-04-13',100,1,8,1,'2026-10-13 17:21:07');
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `destinations`
--

DROP TABLE IF EXISTS `destinations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `destinations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `country` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_dest_slug` (`slug`),
  KEY `idx_dest_featured` (`is_featured`),
  KEY `idx_dest_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `destinations`
--

LOCK TABLES `destinations` WRITE;
/*!40000 ALTER TABLE `destinations` DISABLE KEYS */;
INSERT INTO `destinations` VALUES (1,'Kashmir','kashmir','India','Jammu and Kashmir','Srinagar','Referred to as Heaven on Earth, Kashmir enchants with snow-capped peaks, shikara rides on Dal Lake, and lush meadows in Gulmarg.','https://images.unsplash.com/photo-1595815771614-ade9d652a65d?auto=format&fit=crop&w=1200&q=80',1,'active','2026-10-13 17:21:06','2026-10-13 17:21:06'),(2,'Kerala','kerala','India','Kerala','Kochi & Munnar','God\'s Own Country famous for serene backwaters, tea gardens in Munnar, Ayurvedic therapies, and Alleppey houseboats.','https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1200&q=80',1,'active','2026-10-13 17:21:06','2026-10-13 17:21:06'),(3,'Himachal Pradesh','himachal-pradesh','India','Himachal Pradesh','Manali & Shimla','Picturesque hill stations, pine forests, adventure sports in Solang Valley, and scenic mountain passes.','https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1200&q=80',1,'active','2026-10-13 17:21:06','2026-10-13 17:21:06'),(4,'Goa','goa','India','Goa','Panaji','Golden sand beaches, vibrant nightlife, Portuguese colonial architecture, and delicious coastal seafood.','https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=1200&q=80',1,'active','2026-10-13 17:21:06','2026-10-13 17:21:06'),(5,'Rajasthan','rajasthan','India','Rajasthan','Jaipur & Udaipur','Land of kings, historic forts, grand royal palaces, Thar desert safaris, and vibrant folk cultural celebrations.','https://images.unsplash.com/photo-1477587458883-47145ed94245?auto=format&fit=crop&w=1200&q=80',1,'active','2026-10-13 17:21:06','2026-10-13 17:21:06'),(6,'Dubai','dubai','United Arab Emirates','Dubai','Dubai','A futuristic global oasis with towering skyscrapers, world-class luxury shopping, desert dunes, and futuristic entertainment.','https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1200&q=80',1,'active','2026-10-13 17:21:06','2026-10-13 17:21:06'),(7,'Bali','bali','Indonesia','Bali','Denpasar & Ubud','Tropical paradise with lush emerald rice terraces, sacred Hindu water temples, dramatic sea cliffs, and surfing beaches.','https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1200&q=80',1,'active','2026-10-13 17:21:06','2026-10-13 17:21:06'),(8,'Switzerland','switzerland','Switzerland','Zurich & Lucerne','Lucerne','Alpine grandeur with pristine turquoise lakes, snow-clad Alps, scenic cogwheel trains, and charming historic towns.','https://images.unsplash.com/photo-1530122037265-a5f1f91d3b99?auto=format&fit=crop&w=1200&q=80',1,'active','2026-10-13 17:21:06','2026-10-13 17:21:06');
/*!40000 ALTER TABLE `destinations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletter_subscribers`
--

DROP TABLE IF EXISTS `newsletter_subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletter_subscribers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletter_subscribers`
--

LOCK TABLES `newsletter_subscribers` WRITE;
/*!40000 ALTER TABLE `newsletter_subscribers` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletter_subscribers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_prt_token` (`token`),
  KEY `idx_prt_email` (`email`)
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
-- Table structure for table `payment_webhook_events`
--

DROP TABLE IF EXISTS `payment_webhook_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_webhook_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` varchar(100) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `payload` longtext NOT NULL,
  `status` enum('processed','failed','ignored') NOT NULL DEFAULT 'processed',
  `processed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_id` (`event_id`),
  KEY `idx_wh_event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_webhook_events`
--

LOCK TABLES `payment_webhook_events` WRITE;
/*!40000 ALTER TABLE `payment_webhook_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_webhook_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `transaction_reference` varchar(100) NOT NULL,
  `gateway` varchar(50) NOT NULL DEFAULT 'razorpay',
  `gateway_order_id` varchar(100) DEFAULT NULL,
  `gateway_payment_id` varchar(100) DEFAULT NULL,
  `gateway_signature` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_response`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_reference` (`transaction_reference`),
  KEY `idx_pay_booking` (`booking_id`),
  KEY `idx_pay_order` (`gateway_order_id`),
  KEY `idx_pay_payment` (`gateway_payment_id`),
  CONSTRAINT `fk_payments_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refunds`
--

DROP TABLE IF EXISTS `refunds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refunds` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `payment_id` int(10) unsigned DEFAULT NULL,
  `refund_reference` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `gateway_refund_id` varchar(100) DEFAULT NULL,
  `created_by_user_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `refund_reference` (`refund_reference`),
  KEY `fk_refunds_booking` (`booking_id`),
  KEY `fk_refunds_payment` (`payment_id`),
  CONSTRAINT `fk_refunds_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_refunds_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refunds`
--

LOCK TABLES `refunds` WRITE;
/*!40000 ALTER TABLE `refunds` DISABLE KEYS */;
/*!40000 ALTER TABLE `refunds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `package_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `booking_id` int(10) unsigned DEFAULT NULL,
  `rating` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `review_title` varchar(255) DEFAULT NULL,
  `review_text` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reviews_pkg` (`package_id`),
  KEY `idx_reviews_status` (`status`),
  KEY `fk_reviews_user` (`user_id`),
  CONSTRAINT `fk_reviews_pkg` FOREIGN KEY (`package_id`) REFERENCES `tour_packages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,1,3,NULL,5,'Unbelievable experience in Kashmir!','Our trip with YatraNest was seamless from start to finish. The houseboat stay in Dal Lake was like a dream, and our driver was courteous and knowledgeable.','approved','2026-10-13 17:21:07'),(2,2,4,NULL,5,'Most romantic Kerala honeymoon trip','The Alleppey houseboat was clean and the freshly prepared Karimeen fish dinner was delicious. Munnar tea estates were breathtaking.','approved','2026-10-13 17:21:07'),(3,3,3,NULL,5,'Thrilling adventure in Solang Valley','Loved the paragliding and the trip through Atal Tunnel. Good hotels, friendly guides, and delicious buffet breakfasts throughout.','approved','2026-10-13 17:21:07'),(4,5,4,NULL,5,'Truly royal treatment across Rajasthan','The heritage properties selected by YatraNest made us feel like royalty. The sunset boat ride on Lake Pichola in Udaipur was magical.','approved','2026-10-13 17:21:07'),(5,4,3,NULL,5,'Relaxing and vibrant Goa holiday','The beach resort was fantastic and the private Mandovi river sunset cruise was unforgettable. Everything was punctual and hassle-free.','approved','2026-10-13 17:21:07'),(6,6,4,NULL,5,'Glitz, thrill, and luxury in Dubai','Desert safari dune bashing followed by dinner under the stars was an adrenaline rush! The Burj Khalifa 124th-floor views were breathtaking.','approved','2026-10-13 17:21:07');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super_admin','Full administrative access and settings'),(2,'booking_manager','Manage tour departures, seats, and customer bookings'),(3,'content_manager','Manage destinations, packages, blogs, and reviews'),(4,'customer','Registered traveler customer account');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_settings_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_settings`
--

LOCK TABLES `site_settings` WRITE;
/*!40000 ALTER TABLE `site_settings` DISABLE KEYS */;
INSERT INTO `site_settings` VALUES (1,'site_name','YatraNest','general','Website Brand Name','2026-10-13 17:21:07'),(2,'site_tagline','Crafting Unforgettable Journeys Across The Globe','general','Tagline displayed on header/hero','2026-10-13 17:21:07'),(3,'company_name','YatraNest Holidays Pvt Ltd','general','Legal registered company name','2026-10-13 17:21:07'),(4,'company_address','Guwahati, India','general','Official physical address','2026-10-13 17:22:55'),(5,'company_phone','+91 98765 43210','general','Customer support telephone','2026-10-13 17:21:07'),(6,'company_email','support@yatranest.com','general','Primary public contact email','2026-10-13 17:21:07'),(7,'company_gstin','29ABCDE1234F1Z5','general','GST Registration Number','2026-10-13 17:21:07'),(8,'tax_rate','5.00','booking','Applicable GST / VAT tax rate percentage','2026-10-13 17:21:07'),(9,'currency_code','INR','booking','Base currency ISO code','2026-10-13 17:21:07'),(10,'currency_symbol','₹','booking','Base currency display symbol','2026-10-13 17:21:07'),(11,'hold_timeout_minutes','15','booking','Seat checkout hold reservation expiration in minutes','2026-10-13 17:21:07'),(12,'razorpay_mode','test','payment','Payment Gateway Mode (test or live)','2026-10-13 17:21:07'),(13,'razorpay_key_id','rzp_test_sampleKey12345','payment','Razorpay Public Key ID','2026-10-13 17:21:07'),(14,'google_maps_api_key','','integrations','Google Maps JavaScript API key (optional)','2026-10-13 17:21:07'),(15,'razorpay_key_secret','Admin@12345','payment',NULL,'2026-10-13 17:22:55'),(16,'mail_host','smtp.mailtrap.io','mail',NULL,'2026-10-13 17:22:55'),(17,'mail_port','2525','mail',NULL,'2026-10-13 17:22:55'),(18,'mail_username','admin@yatranest.com','mail',NULL,'2026-10-13 17:22:55');
/*!40000 ALTER TABLE `site_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tour_categories`
--

DROP TABLE IF EXISTS `tour_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tour_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(110) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'bi-compass',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tour_categories`
--

LOCK TABLES `tour_categories` WRITE;
/*!40000 ALTER TABLE `tour_categories` DISABLE KEYS */;
INSERT INTO `tour_categories` VALUES (1,'Adventure','adventure','Trekking, rafting, safaris, and thrill-seeking expeditions.','bi-compass',1,'2026-10-13 17:21:06'),(2,'Family Holiday','family-holiday','Comfortable, well-paced tours ideal for travelers of all ages.','bi-people',1,'2026-10-13 17:21:06'),(3,'Honeymoon Special','honeymoon-special','Romantic escapes, candle-light dinners, and scenic luxury stays.','bi-heart',1,'2026-10-13 17:21:06'),(4,'Pilgrimage & Spiritual','pilgrimage','Sacred shrines, temple tours, and serene spiritual journeys.','bi-peace',1,'2026-10-13 17:21:06'),(5,'Weekend Getaway','weekend-getaway','Quick refreshing short escapes from city life.','bi-calendar-check',1,'2026-10-13 17:21:06'),(6,'Luxury & Heritage','luxury-heritage','Palaces, five-star resorts, and curated bespoke experiences.','bi-gem',1,'2026-10-13 17:21:06');
/*!40000 ALTER TABLE `tour_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tour_departures`
--

DROP TABLE IF EXISTS `tour_departures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tour_departures` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `package_id` int(10) unsigned NOT NULL,
  `departure_date` date NOT NULL,
  `return_date` date NOT NULL,
  `total_seats` int(10) unsigned NOT NULL DEFAULT 20,
  `available_seats` int(10) unsigned NOT NULL DEFAULT 20,
  `adult_price` decimal(10,2) DEFAULT NULL,
  `child_price` decimal(10,2) DEFAULT NULL,
  `status` enum('open','closed','blocked') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_dep_date` (`departure_date`),
  KEY `idx_dep_status` (`status`),
  KEY `fk_departures_pkg` (`package_id`),
  CONSTRAINT `fk_departures_pkg` FOREIGN KEY (`package_id`) REFERENCES `tour_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tour_departures`
--

LOCK TABLES `tour_departures` WRITE;
/*!40000 ALTER TABLE `tour_departures` DISABLE KEYS */;
INSERT INTO `tour_departures` VALUES (1,1,'2026-10-23','2026-10-29',20,18,24999.00,14999.00,'open','2026-10-13 17:21:07','2026-10-13 17:38:18'),(2,1,'2026-11-02','2026-11-08',20,18,24999.00,14999.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(3,1,'2026-11-12','2026-11-18',20,18,24999.00,14999.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(4,1,'2026-11-27','2026-12-03',20,18,24999.00,14999.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(5,1,'2026-12-12','2026-12-18',20,18,24999.00,14999.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(6,1,'2026-12-27','2027-01-02',20,18,24999.00,14999.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(7,2,'2026-10-23','2026-10-28',20,18,18500.00,9999.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(8,2,'2026-11-02','2026-11-07',20,18,18500.00,9999.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(9,2,'2026-11-12','2026-11-17',20,18,18500.00,9999.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(10,2,'2026-11-27','2026-12-02',20,18,18500.00,9999.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(11,2,'2026-12-12','2026-12-17',20,18,18500.00,9999.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(12,2,'2026-12-27','2027-01-01',20,18,18500.00,9999.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(13,3,'2026-10-23','2026-10-29',20,18,16999.00,9500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(14,3,'2026-11-02','2026-11-08',20,18,16999.00,9500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(15,3,'2026-11-12','2026-11-18',20,18,16999.00,9500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(16,3,'2026-11-27','2026-12-03',20,18,16999.00,9500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(17,3,'2026-12-12','2026-12-18',20,18,16999.00,9500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(18,3,'2026-12-27','2027-01-02',20,18,16999.00,9500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(19,4,'2026-10-23','2026-10-27',20,18,12499.00,6500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(20,4,'2026-11-02','2026-11-06',20,18,12499.00,6500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(21,4,'2026-11-12','2026-11-16',20,18,12499.00,6500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(22,4,'2026-11-27','2026-12-01',20,18,12499.00,6500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(23,4,'2026-12-12','2026-12-16',20,18,12499.00,6500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(24,4,'2026-12-27','2026-12-31',20,18,12499.00,6500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(25,5,'2026-10-23','2026-10-30',20,18,28999.00,16500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(26,5,'2026-11-02','2026-11-09',20,18,28999.00,16500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(27,5,'2026-11-12','2026-11-19',20,18,28999.00,16500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(28,5,'2026-11-27','2026-12-04',20,18,28999.00,16500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(29,5,'2026-12-12','2026-12-19',20,18,28999.00,16500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(30,5,'2026-12-27','2027-01-03',20,18,28999.00,16500.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(31,6,'2026-10-23','2026-10-28',20,18,45000.00,28000.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(32,6,'2026-11-02','2026-11-07',20,18,45000.00,28000.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(33,6,'2026-11-12','2026-11-17',20,18,45000.00,28000.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(34,6,'2026-11-27','2026-12-02',20,18,45000.00,28000.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(35,6,'2026-12-12','2026-12-17',20,18,45000.00,28000.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07'),(36,6,'2026-12-27','2027-01-01',20,18,45000.00,28000.00,'open','2026-10-13 17:21:07','2026-10-13 17:21:07');
/*!40000 ALTER TABLE `tour_departures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tour_images`
--

DROP TABLE IF EXISTS `tour_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tour_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `package_id` int(10) unsigned NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_images_pkg` (`package_id`),
  CONSTRAINT `fk_images_pkg` FOREIGN KEY (`package_id`) REFERENCES `tour_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tour_images`
--

LOCK TABLES `tour_images` WRITE;
/*!40000 ALTER TABLE `tour_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `tour_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tour_itineraries`
--

DROP TABLE IF EXISTS `tour_itineraries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tour_itineraries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `package_id` int(10) unsigned NOT NULL,
  `day_number` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `meals_included` varchar(100) DEFAULT NULL,
  `accommodation` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_itinerary_pkg` (`package_id`),
  CONSTRAINT `fk_itinerary_pkg` FOREIGN KEY (`package_id`) REFERENCES `tour_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tour_itineraries`
--

LOCK TABLES `tour_itineraries` WRITE;
/*!40000 ALTER TABLE `tour_itineraries` DISABLE KEYS */;
INSERT INTO `tour_itineraries` VALUES (1,1,1,'Arrival in Srinagar & Dal Lake Shikara Ride','Arrive at Srinagar airport. Meet our executive, transfer to your hotel. Evening 1-hour relaxing Shikara ride across Dal Lake witnessing floating vegetable gardens and sunset.','Dinner','Hotel Grand Mumtaz, Srinagar'),(2,1,2,'Srinagar Mughal Gardens Sightseeing','Visit famous Nishat Bagh (Garden of Pleasure), Shalimar Bagh, Chashme Shahi, and Shankaracharya Temple perched on a scenic hill overlooking the valley.','Breakfast, Dinner','Hotel Grand Mumtaz, Srinagar'),(3,1,3,'Excursion to Gulmarg (Meadow of Flowers)','Drive through scenic willow countryside to Gulmarg (2,730m). Enjoy the world-famous Gulmarg Gondola cable car ride up to Apharwat Peak for snow activities.','Breakfast, Dinner','Hotel Grand Mumtaz, Srinagar'),(4,1,4,'Srinagar to Pahalgam (Valley of Shepherds)','Drive through saffron fields in Pampore and Awantipora ruins to picturesque Pahalgam. Stroll along the Lidder riverbank and visit Betaab Valley.','Breakfast, Dinner','Pine N Peak Resort, Pahalgam'),(5,1,5,'Pahalgam to Srinagar Deluxe Houseboat','Morning leisure walk in Aru Valley. Post lunch, drive back to Srinagar and check in to a traditional handcrafted wooden deluxe houseboat on Dal Lake.','Breakfast, Dinner','Royal Heritage Houseboat, Dal Lake'),(6,1,6,'Departure from Srinagar','After breakfast, transfer to Srinagar Airport for your onward flight with unforgettable memories of Kashmir.','Breakfast','Tour Ends'),(7,2,1,'Arrival in Kochi & Drive to Munnar','Arrive at Cochin Airport. Drive through the mist-laden Western Ghats, stopping at Cheeyappara and Valara waterfalls. Check-in at Munnar resort.','Dinner','Tea County Resort, Munnar'),(8,2,2,'Munnar Tea Gardens & Eravikulam National Park','Visit Eravikulam National Park (home to the endangered Nilgiri Tahr), Tata Tea Museum, Mattupetty Dam, and Echo Point.','Breakfast, Dinner','Tea County Resort, Munnar'),(9,2,3,'Munnar to Thekkady (Periyar Wildlife Sanctuary)','Drive through cardamom hills to Thekkady. Take a guided walking tour of spice plantations (cardamom, cinnamon, vanilla). Optional boating in Periyar Lake.','Breakfast, Dinner','Spice Village, Thekkady'),(10,2,4,'Thekkady to Alleppey Houseboat Cruise','Drive to Alleppey and board your traditional AC private houseboat. Cruise through narrow canals, paddy fields, and enjoy freshly cooked authentic Kerala meals.','Breakfast, Lunch, Dinner','Private Houseboat, Alleppey'),(11,2,5,'Departure from Cochin','Disembark after breakfast and transfer to Cochin International Airport or Railway Station for departure.','Breakfast','Tour Ends'),(12,3,1,'Chandigarh / Delhi to Shimla','Pick up and scenic drive uphill to Shimla (2,200m). Evening stroll on Ridge and Mall Road.','Dinner','The Grand White, Shimla'),(13,3,2,'Shimla & Kufri Excursion','Excursion to Kufri for horse riding and panoramic views of Himalayan ranges. Visit Jakhoo Temple.','Breakfast, Dinner','The Grand White, Shimla'),(14,3,3,'Shimla to Manali via Kullu Valley','Drive along the Beas river through Pandoh Dam, Kullu Shawl factories, and river rafting points to Manali.','Breakfast, Dinner','Apple Country Resort, Manali'),(15,3,4,'Manali Local Sightseeing','Visit Hadimba Devi Temple, Vashisht Hot Sulphur Springs, Tibetan Monastery, and Club House.','Breakfast, Dinner','Apple Country Resort, Manali'),(16,3,5,'Solang Valley & Atal Tunnel Adventure','Full-day excursion to Solang Valley for paragliding, snow activities, and transit through the engineering marvel of Atal Tunnel.','Breakfast, Dinner','Apple Country Resort, Manali'),(17,3,6,'Manali to Chandigarh / Delhi Departure','After breakfast, drive down to Chandigarh / Delhi for return departure.','Breakfast','Tour Ends'),(18,4,1,'Arrival in Goa & Beach Leisure','Arrival transfer from airport/railway station. Evening free to relax on Calangute and Baga beach.','Welcome Drink','Lemon Tree Resort, Goa'),(19,4,2,'North Goa Forts & Water Sports','Visit historic Fort Aguada, Chapora Fort, Anjuna flea market beach, and water sports at Calangute.','Breakfast','Lemon Tree Resort, Goa'),(20,4,3,'South Goa Heritage & Mandovi Cruise','Explore Basilica of Bom Jesus, Se Cathedral, Mangueshi Temple, and enjoy a lively sunset river cruise.','Breakfast','Lemon Tree Resort, Goa'),(21,4,4,'Departure from Goa','Breakfast and check out. Airport transfer for flight back home.','Breakfast','Tour Ends'),(22,5,1,'Arrival in Jaipur (The Pink City)','Pickup from Jaipur airport. Visit Birla Temple and historic Chokhi Dhani for Rajasthani dinner.','Dinner','Mandawa Haveli, Jaipur'),(23,5,2,'Jaipur Forts & Palaces','Ascend to Amer Fort with jeep ride. Tour Hawa Mahal, City Palace, and Jantar Mantar.','Breakfast, Dinner','Mandawa Haveli, Jaipur'),(24,5,3,'Jaipur to Jodhpur (The Blue City)','Drive to Jodhpur via Ajmer Dargah and Pushkar Brahma Temple. Evening check-in at heritage palace.','Breakfast, Dinner','Ajit Bhawan, Jodhpur'),(25,5,4,'Jodhpur Mehrangarh Fort & Jaswant Thada','Explore the majestic Mehrangarh Fort, Jaswant Thada marble cenotaph, and Umaid Bhawan Palace museum.','Breakfast, Dinner','Ajit Bhawan, Jodhpur'),(26,5,5,'Jodhpur to Udaipur via Ranakpur','Scenic drive to Udaipur stopping at Ranakpur Jain Temple renowned for 1,444 intricately carved pillars.','Breakfast, Dinner','Trident, Udaipur'),(27,5,6,'Udaipur City of Lakes Tour','City Palace, Saheliyon-ki-Bari, Jagdish Temple, and romantic sunset boat ride on Lake Pichola.','Breakfast, Dinner','Trident, Udaipur'),(28,5,7,'Departure from Udaipur','Breakfast and transfer to Udaipur Airport for return flight.','Breakfast','Tour Ends'),(29,6,1,'Arrival in Dubai & Marina Dhow Cruise','Arrive at DXB Airport. Transfer to hotel. Evening 2-hour luxury Marina Dhow Cruise with international buffet dinner.','Dinner','Millennium Central, Dubai'),(30,6,2,'Half-Day Dubai City Tour & Burj Khalifa','Drive past Dubai Frame, Jumeirah Mosque, and Burj Al Arab. Evening visit to Dubai Mall and 124th floor of Burj Khalifa.','Breakfast','Millennium Central, Dubai'),(31,6,3,'Morning Leisure & Desert Safari with BBQ','Morning free for shopping at Gold and Spice Souk. Afternoon 4x4 desert safari, dune bashing, sand boarding, tanoura dance, and BBQ dinner under the stars.','Breakfast, Dinner','Millennium Central, Dubai'),(32,6,4,'Miracle Garden & Global Village','Explore the millions of blooming flowers at Dubai Miracle Garden, followed by an evening immersing in cultures at Global Village.','Breakfast','Millennium Central, Dubai'),(33,6,5,'Departure from Dubai','Breakfast, check out, and transfer to Dubai International Airport for departure.','Breakfast','Tour Ends');
/*!40000 ALTER TABLE `tour_itineraries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tour_packages`
--

DROP TABLE IF EXISTS `tour_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tour_packages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `destination_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(260) NOT NULL,
  `summary` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `duration_days` int(10) unsigned NOT NULL DEFAULT 1,
  `duration_nights` int(10) unsigned NOT NULL DEFAULT 0,
  `price_adult` decimal(10,2) NOT NULL,
  `price_child` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_infant` decimal(10,2) NOT NULL DEFAULT 0.00,
  `featured_image` varchar(255) DEFAULT NULL,
  `gallery_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery_images`)),
  `group_size_max` int(10) unsigned NOT NULL DEFAULT 20,
  `inclusions` text DEFAULT NULL,
  `exclusions` text DEFAULT NULL,
  `cancellation_policy_text` text DEFAULT NULL,
  `hotel_details` text DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_domestic` tinyint(1) DEFAULT 1,
  `tour_type` varchar(50) DEFAULT 'adventure',
  `status` enum('published','draft','archived') DEFAULT 'published',
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_meta_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_packages_slug` (`slug`),
  KEY `idx_packages_featured` (`is_featured`),
  KEY `idx_packages_status` (`status`),
  KEY `idx_packages_price` (`price_adult`),
  KEY `fk_packages_cat` (`category_id`),
  KEY `fk_packages_dest` (`destination_id`),
  CONSTRAINT `fk_packages_cat` FOREIGN KEY (`category_id`) REFERENCES `tour_categories` (`id`),
  CONSTRAINT `fk_packages_dest` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tour_packages`
--

LOCK TABLES `tour_packages` WRITE;
/*!40000 ALTER TABLE `tour_packages` DISABLE KEYS */;
INSERT INTO `tour_packages` VALUES (1,2,1,'Kashmir Paradise: Srinagar, Gulmarg & Pahalgam','kashmir-paradise-srinagar-gulmarg-pahalgam','Experience the magic of Dal Lake Shikara ride, Gulmarg snow gondola, and the valley of shepherds in Pahalgam.','Immerse yourself in Kashmir\'s natural majesty. This comprehensive 6-day package covers Srinagar\'s serene Mughal Gardens, traditional houseboat stay on Dal Lake, snow-clad slopes and Gondola ride in Gulmarg, and lush pine valleys along the Lidder River in Pahalgam.',6,5,24999.00,14999.00,0.00,'https://images.unsplash.com/photo-1595815771614-ade9d652a65d?auto=format&fit=crop&w=1000&q=80',NULL,20,'• 5 Nights Premium Accommodation (including 1 Night Luxury Houseboat)\n• Daily Buffet Breakfast & Dinner\n• All transfers & sightseeing in AC private vehicle\n• 1-hour Shikara ride on Dal Lake\n• Toll taxes, parking, driver allowances\n• 24/7 dedicated tour manager assistance','• Airfare or Train tickets\n• Gulmarg Gondola cable car tickets (Phase 1 & 2)\n• Pony / Horse rides\n• Personal expenses, laundry, tips\n• Travel insurance','• Cancellation 30+ days prior to departure: 90% refund\n• Cancellation 15-29 days prior: 50% refund\n• Cancellation within 14 days: No refund','Srinagar: Grand Mumtaz (4 Star) | Houseboat: Royal Dal Houseboat | Pahalgam: Pine N Peak Resort',1,1,'family','published','Kashmir Paradise: Srinagar, Gulmarg & Pahalgam | YatraNest','Experience the magic of Dal Lake Shikara ride, Gulmarg snow gondola, and the valley of shepherds in Pahalgam.','2026-10-13 17:21:06','2026-10-13 17:21:06'),(2,3,2,'Magical Kerala: Backwaters, Munnar & Alleppey Houseboat','magical-kerala-backwaters-munnar-alleppey','Romantic mist-kissed Munnar tea gardens, spice plantations, and private luxury houseboat cruise in Alleppey.','Discover the timeless romantic beauty of Kerala. From winding scenic ghats lined with cascading waterfalls in Munnar to serene spice plantations in Thekkady, capped by a magical overnight stay on an authentic private houseboat gliding along the palm-fringed backwaters of Alleppey.',5,4,18500.00,9999.00,0.00,'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1000&q=80',NULL,15,'• 4 Nights Accommodation (2N Munnar, 1N Thekkady, 1N AC Deluxe Houseboat)\n• Daily Breakfast, Lunch & Dinner on Houseboat\n• Private AC vehicle for transfers and sightseeing\n• Spice plantation guided walking tour\n• Driver allowance, tolls, and inter-state permits','• Airfare / Train fare to Kochi\n• Periyar boat safari tickets\n• Kathakali / Kalaripayattu performance tickets\n• Personal expenses & tips','• 30+ days: 90% refund\n• 15-29 days: 50% refund\n• Within 14 days: Non-refundable','Munnar: Tea County Resort | Thekkady: Spice Village | Alleppey: Deluxe AC Backwater Cruise',1,1,'honeymoon','published','Magical Kerala: Backwaters, Munnar & Alleppey Houseboat | YatraNest','Romantic mist-kissed Munnar tea gardens, spice plantations, and private luxury houseboat cruise in Alleppey.','2026-10-13 17:21:07','2026-10-13 17:21:07'),(3,1,3,'Himachal Highs: Shimla, Manali & Rohtang Pass','himachal-highs-shimla-manali-rohtang','Colonial charm in Shimla, adrenaline adventures in Solang Valley, and breathtaking Himalayan views.','The quintessential mountain journey through Himachal Pradesh. Wander through heritage British colonial streets along Shimla\'s Mall Road, experience paragliding and zorbing in Solang Valley, marvel at the snowy slopes of Rohtang Pass, and visit ancient wooden temples in Old Manali.',6,5,16999.00,9500.00,0.00,'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1000&q=80',NULL,25,'• 5 Nights Hotel Accommodation (2N Shimla, 3N Manali)\n• Daily Buffet Breakfast & Dinner\n• Individual AC Cab from Delhi / Chandigarh pickup\n• Solang Valley and Kufri excursion\n• Driver charges, parking, and toll taxes','• Rohtang Pass NGT National Green Tribunal permit charges\n• Paragliding, river rafting, and adventure activity fees\n• Entry tickets to monuments','• 30+ days: 85% refund\n• 15-29 days: 50% refund\n• Within 14 days: No refund','Shimla: The Grand White (4 Star) | Manali: Apple Country Resort',1,1,'adventure','published','Himachal Highs: Shimla, Manali & Rohtang Pass | YatraNest','Colonial charm in Shimla, adrenaline adventures in Solang Valley, and breathtaking Himalayan views.','2026-10-13 17:21:07','2026-10-13 17:21:07'),(4,5,4,'Goa Beach Bliss: Sun, Sand & Heritage','goa-beach-bliss-sun-sand-heritage','Relax on Baga Beach, explore Old Goa UNESCO churches, and experience a vibrant Mandovi river cruise.','Unwind in India\'s coastal vacation capital. Stay near lively beaches, explore 16th-century Portuguese cathedrals, relax on golden sands, and sail into sunset on the Mandovi River.',4,3,12499.00,6500.00,0.00,'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=1000&q=80',NULL,20,'• 3 Nights Stay in 4-Star Beach Resort\n• Daily Buffet Breakfast\n• North & South Goa guided sightseeing by AC coach\n• 1-Hour Mandovi River Sunset Cruise\n• Airport / Thivim Station pick-up & drop','• Water sports (Jet ski, parasailing, banana ride)\n• Alcoholic beverages and nightclub entries\n• Personal expenses','• 21+ days: 90% refund\n• 10-20 days: 50% refund\n• Under 10 days: Non-refundable','North Goa: Lemon Tree Amarante Beach Resort (4 Star)',1,1,'weekend','published','Goa Beach Bliss: Sun, Sand & Heritage | YatraNest','Relax on Baga Beach, explore Old Goa UNESCO churches, and experience a vibrant Mandovi river cruise.','2026-10-13 17:21:07','2026-10-13 17:21:07'),(5,6,5,'Royal Rajasthan: Jaipur, Jodhpur & Udaipur Heritage','royal-rajasthan-jaipur-jodhpur-udaipur','Live like royalty across Rajasthan palaces, the Pink City, Mehrangarh Fort, and Lake Pichola.','Journey through the golden history of the Rajputs. Tour the imposing Amer Fort and City Palace in Jaipur, the colossal Mehrangarh Fort overlooking Jodhpur\'s blue city, and the enchanting city of lakes in Udaipur.',7,6,28999.00,16500.00,0.00,'https://images.unsplash.com/photo-1477587458883-47145ed94245?auto=format&fit=crop&w=1000&q=80',NULL,18,'• 6 Nights Heritage Hotel Accommodation (2N Jaipur, 2N Jodhpur, 2N Udaipur)\n• Daily Royal Breakfast & Dinner\n• Private Chauffeur AC Sedan for entire tour\n• Boat ride on Lake Pichola, Udaipur\n• Elephant / Jeep ride at Amer Fort','• Monument entry tickets and camera fees\n• Guide charges\n• Air / Train fares','• 30+ days: 90% refund\n• 15-29 days: 50% refund\n• Under 15 days: Non-refundable','Jaipur: Mandawa Haveli | Jodhpur: Ajit Bhawan | Udaipur: Trident Udaipur',1,1,'luxury','published','Royal Rajasthan: Jaipur, Jodhpur & Udaipur Heritage | YatraNest','Live like royalty across Rajasthan palaces, the Pink City, Mehrangarh Fort, and Lake Pichola.','2026-10-13 17:21:07','2026-10-13 17:21:07'),(6,6,6,'Dazzling Dubai: Desert Safari, Marina & Burj Khalifa','dazzling-dubai-desert-safari-burj-khalifa','124th floor Burj Khalifa view, 4x4 dune bashing desert safari with BBQ, and Dubai Marina dhow cruise.','Experience the glitz and glamour of Dubai. Marvel at the city skyline from the 124th floor of Burj Khalifa, experience thrilling dune bashing and belly dancing in the Arabian desert, sail the Marina on a glass dhow, and shop at the Gold Souk.',5,4,45000.00,28000.00,5000.00,'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1000&q=80',NULL,20,'• 4 Nights in 4-Star Downtown Hotel\n• Daily International Buffet Breakfast\n• Burj Khalifa 124th Floor Off-Peak Entry Ticket\n• 4x4 Desert Safari with Dune Bashing, Camel Ride, & BBQ Dinner\n• Dubai Marina Luxury Dhow Cruise with Dinner\n• Return Airport Transfers by Private Vehicle','• International Flights & UAE Tourist Visa\n• Tourism Dirham Fee payable at hotel\n• Personal expenses & optional activities','• 30+ days: 85% refund\n• 15-29 days: 40% refund\n• Within 14 days: No refund','Dubai: Millennium Central Downtown (4 Star)',1,0,'luxury','published','Dazzling Dubai: Desert Safari, Marina & Burj Khalifa | YatraNest','124th floor Burj Khalifa view, 4x4 dune bashing desert safari with BBQ, and Dubai Marina dhow cruise.','2026-10-13 17:21:07','2026-10-13 17:21:07');
/*!40000 ALTER TABLE `tour_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_role` (`user_id`,`role_id`),
  KEY `fk_user_roles_role` (`role_id`),
  CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (1,1,1,'2026-10-13 17:21:05'),(2,2,2,'2026-10-13 17:21:05'),(3,3,4,'2026-10-13 17:21:06'),(4,4,4,'2026-10-13 17:21:06');
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('active','suspended') DEFAULT 'active',
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_email` (`email`),
  KEY `idx_users_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Super Administrator','admin@yatranest.com','+91 98765 00001','$2y$12$xYS9bGaCJgaZ5kZjWYtnpeiEBe63jDL9T/hK7dHMeZlFX4BUEVhna','active',NULL,'2026-10-13 17:21:05','2026-10-13 17:21:05'),(2,'Booking Operations Lead','manager@yatranest.com','+91 98765 00002','$2y$12$L5MrOd14GF3YJ8yOMblKg.vKM5OuErhlHi6gVlFMeP30Cq.qTpGVS','active',NULL,'2026-10-13 17:21:05','2026-10-13 17:21:05'),(3,'Rahul Sharma','customer@example.com','+91 98765 43210','$2y$12$dX.bNpwW0Oc6NUuzHSh2GuQOZ4ihN4fhf2hACXaYAX5YBKbEDtnQq','active',NULL,'2026-10-13 17:21:06','2026-10-13 17:21:06'),(4,'Priya Patel','priya@example.com','+91 98765 43211','$2y$12$ZPeIBsFrjtnHGAucks4gUuYxYzuc6gQtFTByU4djUIm1NxCGPg.p.','active',NULL,'2026-10-13 17:21:06','2026-10-13 17:21:06');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-13 23:19:50
