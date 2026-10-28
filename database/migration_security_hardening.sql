-- ==============================================================================
-- GaunGaliHat Security Hardening Migration
-- Safe to execute repeatedly (Idempotent Migration)
-- Compatible with MySQL 5.7+ / 8.0+ / MariaDB 10.3+
-- ==============================================================================

-- 1. Create password_resets Table
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `token_hash` VARCHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_pr_token` (`token_hash`),
  INDEX `idx_pr_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create login_attempts Table (Throttling & IP Rate Limiting)
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `identifier` VARCHAR(150) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `success` TINYINT(1) DEFAULT 0,
  `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_la_ip` (`ip_address`, `attempted_at`),
  INDEX `idx_la_ident` (`identifier`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2b. Create rate_limits Table (Action & IP/Phone Rate Limiter)
CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `action` VARCHAR(50) NOT NULL,
  `identifier` VARCHAR(100) NOT NULL,
  `hit_count` INT NOT NULL DEFAULT 1,
  `first_hit_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_hit_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_action_identifier` (`action`, `identifier`),
  INDEX `idx_rate_limit_action` (`action`, `last_hit_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create coupon_usages Table (Atomic Tracking & Per-User Limits)
CREATE TABLE IF NOT EXISTS `coupon_usages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `coupon_id` INT NOT NULL,
  `user_id` INT DEFAULT NULL,
  `order_id` INT DEFAULT NULL,
  `customer_email` VARCHAR(150) DEFAULT NULL,
  `customer_phone` VARCHAR(30) DEFAULT NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `used_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_cu_coupon_user` (`coupon_id`, `user_id`),
  INDEX `idx_cu_coupon_email` (`coupon_id`, `customer_email`),
  INDEX `idx_cu_coupon_phone` (`coupon_id`, `customer_phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Dynamic Column Additions (Safe & Idempotent via Stored Procedure)
DROP PROCEDURE IF EXISTS `AddSecurityColumnsIfNotExist`;
DELIMITER //
CREATE PROCEDURE `AddSecurityColumnsIfNotExist`()
BEGIN
  -- USERS: email_verified_at
  IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email_verified_at'
  ) THEN
    ALTER TABLE `users` ADD COLUMN `email_verified_at` DATETIME DEFAULT NULL;
  END IF;

  -- USERS: email_verification_token
  IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email_verification_token'
  ) THEN
    ALTER TABLE `users` ADD COLUMN `email_verification_token` VARCHAR(100) DEFAULT NULL;
  END IF;

  -- USERS: email_verification_expires
  IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email_verification_expires'
  ) THEN
    ALTER TABLE `users` ADD COLUMN `email_verification_expires` DATETIME DEFAULT NULL;
  END IF;

  -- USERS: force_password_change
  IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'force_password_change'
  ) THEN
    ALTER TABLE `users` ADD COLUMN `force_password_change` TINYINT(1) DEFAULT 0;
  END IF;

  -- USERS: two_factor_secret
  IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'two_factor_secret'
  ) THEN
    ALTER TABLE `users` ADD COLUMN `two_factor_secret` VARCHAR(100) DEFAULT NULL;
  END IF;

  -- USERS: two_factor_enabled
  IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'two_factor_enabled'
  ) THEN
    ALTER TABLE `users` ADD COLUMN `two_factor_enabled` TINYINT(1) DEFAULT 0;
  END IF;

  -- USERS: failed_login_attempts
  IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'failed_login_attempts'
  ) THEN
    ALTER TABLE `users` ADD COLUMN `failed_login_attempts` INT DEFAULT 0;
  END IF;

  -- USERS: lockout_until
  IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'lockout_until'
  ) THEN
    ALTER TABLE `users` ADD COLUMN `lockout_until` DATETIME DEFAULT NULL;
  END IF;

  -- ORDERS: order_access_token
  IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'order_access_token'
  ) THEN
    ALTER TABLE `orders` ADD COLUMN `order_access_token` VARCHAR(64) DEFAULT NULL;
  END IF;

  -- ORDERS: gateway_order_id
  IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'gateway_order_id'
  ) THEN
    ALTER TABLE `orders` ADD COLUMN `gateway_order_id` VARCHAR(100) DEFAULT NULL;
  END IF;

  -- ORDER_ITEMS: item_status (for individual vendor fulfillment)
  IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_items' AND COLUMN_NAME = 'item_status'
  ) THEN
    ALTER TABLE `order_items` ADD COLUMN `item_status` VARCHAR(50) DEFAULT 'Order Placed';
  END IF;

END //
DELIMITER ;

CALL `AddSecurityColumnsIfNotExist`();
DROP PROCEDURE IF EXISTS `AddSecurityColumnsIfNotExist`;
