-- ==========================================================
-- YatraNest Tour & Travel Booking Platform Database Schema
-- Charset: utf8mb4, Engine: InnoDB
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `phone` VARCHAR(25) NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `status` ENUM('active', 'suspended') DEFAULT 'active',
  `avatar` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_email` (`email`),
  INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Roles table
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `description` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. User Roles mapping
CREATE TABLE IF NOT EXISTS `user_roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_user_role` (`user_id`, `role_id`),
  CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Destinations table
CREATE TABLE IF NOT EXISTS `destinations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(160) NOT NULL UNIQUE,
  `country` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NULL,
  `city` VARCHAR(100) NULL,
  `description` TEXT NULL,
  `image_url` VARCHAR(255) NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_dest_slug` (`slug`),
  INDEX `idx_dest_featured` (`is_featured`),
  INDEX `idx_dest_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tour Categories table
CREATE TABLE IF NOT EXISTS `tour_categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(110) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `icon` VARCHAR(50) DEFAULT 'bi-compass',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tour Packages table
CREATE TABLE IF NOT EXISTS `tour_packages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NOT NULL,
  `destination_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(260) NOT NULL UNIQUE,
  `summary` TEXT NULL,
  `description` LONGTEXT NULL,
  `duration_days` INT UNSIGNED NOT NULL DEFAULT 1,
  `duration_nights` INT UNSIGNED NOT NULL DEFAULT 0,
  `price_adult` DECIMAL(10,2) NOT NULL,
  `price_child` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `price_infant` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `featured_image` VARCHAR(255) NULL,
  `gallery_images` JSON NULL,
  `group_size_max` INT UNSIGNED NOT NULL DEFAULT 20,
  `inclusions` TEXT NULL,
  `exclusions` TEXT NULL,
  `cancellation_policy_text` TEXT NULL,
  `hotel_details` TEXT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_domestic` TINYINT(1) DEFAULT 1,
  `tour_type` VARCHAR(50) DEFAULT 'adventure',
  `status` ENUM('published', 'draft', 'archived') DEFAULT 'published',
  `seo_title` VARCHAR(255) NULL,
  `seo_meta_description` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_packages_slug` (`slug`),
  INDEX `idx_packages_featured` (`is_featured`),
  INDEX `idx_packages_status` (`status`),
  INDEX `idx_packages_price` (`price_adult`),
  CONSTRAINT `fk_packages_cat` FOREIGN KEY (`category_id`) REFERENCES `tour_categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_packages_dest` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tour Images table
CREATE TABLE IF NOT EXISTS `tour_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `package_id` INT UNSIGNED NOT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NULL,
  `sort_order` INT DEFAULT 0,
  CONSTRAINT `fk_images_pkg` FOREIGN KEY (`package_id`) REFERENCES `tour_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tour Itineraries table
CREATE TABLE IF NOT EXISTS `tour_itineraries` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `package_id` INT UNSIGNED NOT NULL,
  `day_number` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `meals_included` VARCHAR(100) NULL,
  `accommodation` VARCHAR(150) NULL,
  CONSTRAINT `fk_itinerary_pkg` FOREIGN KEY (`package_id`) REFERENCES `tour_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Tour Departures table (Date & Seat Availability)
CREATE TABLE IF NOT EXISTS `tour_departures` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `package_id` INT UNSIGNED NOT NULL,
  `departure_date` DATE NOT NULL,
  `return_date` DATE NOT NULL,
  `total_seats` INT UNSIGNED NOT NULL DEFAULT 20,
  `available_seats` INT UNSIGNED NOT NULL DEFAULT 20,
  `adult_price` DECIMAL(10,2) NULL,
  `child_price` DECIMAL(10,2) NULL,
  `status` ENUM('open', 'closed', 'blocked') DEFAULT 'open',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_dep_date` (`departure_date`),
  INDEX `idx_dep_status` (`status`),
  CONSTRAINT `fk_departures_pkg` FOREIGN KEY (`package_id`) REFERENCES `tour_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Temporary Seat Holds (Protection against race conditions)
CREATE TABLE IF NOT EXISTS `booking_holds` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `departure_id` INT UNSIGNED NOT NULL,
  `session_id` VARCHAR(100) NOT NULL,
  `user_id` INT UNSIGNED NULL,
  `seats_held` INT UNSIGNED NOT NULL DEFAULT 1,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_holds_dep` (`departure_id`),
  INDEX `idx_holds_expires` (`expires_at`),
  INDEX `idx_holds_session` (`session_id`),
  CONSTRAINT `fk_holds_dep` FOREIGN KEY (`departure_id`) REFERENCES `tour_departures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Coupons table
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `discount_type` ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` DECIMAL(10,2) NOT NULL,
  `min_booking_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `max_discount` DECIMAL(10,2) NULL,
  `start_date` DATE NOT NULL,
  `expiry_date` DATE NOT NULL,
  `usage_limit` INT UNSIGNED DEFAULT 100,
  `per_user_limit` INT UNSIGNED DEFAULT 1,
  `used_count` INT UNSIGNED DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_coupon_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Bookings table
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `booking_reference` VARCHAR(50) NOT NULL UNIQUE,
  `customer_id` INT UNSIGNED NOT NULL,
  `package_id` INT UNSIGNED NOT NULL,
  `departure_id` INT UNSIGNED NOT NULL,
  `adult_count` INT UNSIGNED NOT NULL DEFAULT 1,
  `child_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `infant_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `tax_rate` DECIMAL(5,2) NOT NULL DEFAULT 5.00,
  `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_status` ENUM('unpaid', 'pending', 'paid', 'failed', 'partially_refunded', 'refunded') NOT NULL DEFAULT 'unpaid',
  `booking_status` ENUM('pending', 'awaiting_payment', 'confirmed', 'cancelled', 'completed', 'refunded') NOT NULL DEFAULT 'pending',
  `payment_method` ENUM('razorpay', 'pay_later') NOT NULL DEFAULT 'razorpay',
  `pickup_location` VARCHAR(255) NULL,
  `special_requests` TEXT NULL,
  `coupon_id` INT UNSIGNED NULL,
  `internal_notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_booking_ref` (`booking_reference`),
  INDEX `idx_booking_customer` (`customer_id`),
  INDEX `idx_booking_status` (`booking_status`),
  INDEX `idx_booking_payment_status` (`payment_status`),
  CONSTRAINT `fk_bookings_cust` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_bookings_pkg` FOREIGN KEY (`package_id`) REFERENCES `tour_packages` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_bookings_dep` FOREIGN KEY (`departure_id`) REFERENCES `tour_departures` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_bookings_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Booking Travelers Manifest
CREATE TABLE IF NOT EXISTS `booking_travelers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT UNSIGNED NOT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `age` INT UNSIGNED NOT NULL,
  `gender` ENUM('male', 'female', 'other') NOT NULL,
  `traveler_type` ENUM('adult', 'child', 'infant') NOT NULL DEFAULT 'adult',
  `id_proof_type` VARCHAR(50) NULL,
  `id_proof_number` VARCHAR(100) NULL,
  CONSTRAINT `fk_travelers_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Booking Status History (Audit Trail)
CREATE TABLE IF NOT EXISTS `booking_status_history` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT UNSIGNED NOT NULL,
  `from_status` VARCHAR(50) NULL,
  `to_status` VARCHAR(50) NOT NULL,
  `changed_by_user_id` INT UNSIGNED NULL,
  `note` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_hist_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Coupon Redemptions
CREATE TABLE IF NOT EXISTS `coupon_redemptions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `coupon_id` INT UNSIGNED NOT NULL,
  `booking_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `discount_applied` DECIMAL(10,2) NOT NULL,
  `redeemed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_redempt_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_redempt_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_redempt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Payments table
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT UNSIGNED NOT NULL,
  `transaction_reference` VARCHAR(100) NOT NULL UNIQUE,
  `gateway` VARCHAR(50) NOT NULL DEFAULT 'razorpay',
  `gateway_order_id` VARCHAR(100) NULL,
  `gateway_payment_id` VARCHAR(100) NULL,
  `gateway_signature` VARCHAR(255) NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
  `status` ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
  `gateway_response` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_pay_booking` (`booking_id`),
  INDEX `idx_pay_order` (`gateway_order_id`),
  INDEX `idx_pay_payment` (`gateway_payment_id`),
  CONSTRAINT `fk_payments_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Payment Webhook Events (Idempotency Tracking)
CREATE TABLE IF NOT EXISTS `payment_webhook_events` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `event_id` VARCHAR(100) NOT NULL UNIQUE,
  `event_type` VARCHAR(100) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `status` ENUM('processed', 'failed', 'ignored') NOT NULL DEFAULT 'processed',
  `processed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_wh_event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Refunds table
CREATE TABLE IF NOT EXISTS `refunds` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT UNSIGNED NOT NULL,
  `payment_id` INT UNSIGNED NULL,
  `refund_reference` VARCHAR(100) NOT NULL UNIQUE,
  `amount` DECIMAL(10,2) NOT NULL,
  `reason` TEXT NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
  `gateway_refund_id` VARCHAR(100) NULL,
  `created_by_user_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_refunds_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_refunds_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Reviews table
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `package_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `booking_id` INT UNSIGNED NULL,
  `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `review_title` VARCHAR(255) NULL,
  `review_text` TEXT NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_reviews_pkg` (`package_id`),
  INDEX `idx_reviews_status` (`status`),
  CONSTRAINT `fk_reviews_pkg` FOREIGN KEY (`package_id`) REFERENCES `tour_packages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. Contact Messages table
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `phone` VARCHAR(25) NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('new', 'read', 'replied') DEFAULT 'new',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. Newsletter Subscribers table
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. Password Reset Tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(191) NOT NULL,
  `token` VARCHAR(100) NOT NULL UNIQUE,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_prt_token` (`token`),
  INDEX `idx_prt_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. Site Settings table
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `setting_group` VARCHAR(50) DEFAULT 'general',
  `description` VARCHAR(255) NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. Audit Logs table
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(100) NULL,
  `entity_id` INT UNSIGNED NULL,
  `old_values` JSON NULL,
  `new_values` JSON NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_audit_action` (`action`),
  INDEX `idx_audit_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
