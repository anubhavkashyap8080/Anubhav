# -*- coding: utf-8 -*-
sql_append = """
-- 7. COUPONS & PROMOTIONAL DISCOUNTS
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) UNIQUE NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `discount_type` ENUM('percentage', 'fixed', 'free_shipping') DEFAULT 'percentage',
  `discount_value` DECIMAL(10,2) NOT NULL,
  `min_order_amount` DECIMAL(10,2) DEFAULT 0,
  `max_discount` DECIMAL(10,2) DEFAULT 0,
  `coupon_type` ENUM('one_per_user', 'new_user_only', 'sale_discount') DEFAULT 'sale_discount',
  `usage_limit` INT DEFAULT 1000,
  `used_count` INT DEFAULT 0,
  `start_date` DATE DEFAULT NULL,
  `expiry_date` DATE DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `coupon_usages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `coupon_id` INT NOT NULL,
  `user_id` INT DEFAULT NULL,
  `order_id` INT DEFAULT NULL,
  `customer_email` VARCHAR(150) DEFAULT NULL,
  `customer_phone` VARCHAR(50) DEFAULT NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0,
  `used_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. LIVE CUSTOMER & ARTISAN CHAT
CREATE TABLE IF NOT EXISTS `chat_sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `session_token` VARCHAR(100) UNIQUE NOT NULL,
  `user_id` INT DEFAULT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  `user_email` VARCHAR(150) DEFAULT NULL,
  `user_phone` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('active', 'resolved', 'closed') DEFAULT 'active',
  `last_message` TEXT,
  `unread_admin` INT DEFAULT 0,
  `unread_user` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `session_id` INT NOT NULL,
  `sender_type` ENUM('user', 'admin', 'support') NOT NULL,
  `sender_name` VARCHAR(100) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Default Active Coupons
INSERT INTO `coupons` (`code`, `title`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount`, `coupon_type`, `usage_limit`, `expiry_date`, `status`) VALUES
('WELCOME50', 'New User Special (₹50 Off)', 'Flat ₹50 discount on your first village handcrafted order', 'fixed', 50.00, 299.00, 50.00, 'new_user_only', 5000, '2028-12-31', 'active'),
('FESTIVAL20', 'Festive Sale 20% Off', '20% discount on agricultural tools, pulses and Banarasi achar', 'percentage', 20.00, 499.00, 300.00, 'sale_discount', 2000, '2028-12-31', 'active'),
('SPECIAL100', 'Special Customer Discount', 'Flat ₹100 discount on orders above ₹699', 'fixed', 100.00, 699.00, 100.00, 'one_per_user', 1000, '2028-12-31', 'active'),
('DESIKISAN', 'Artisan Forge 15% Off', '15% discount on high-carbon sickles and forged tools', 'percentage', 15.00, 399.00, 250.00, 'sale_discount', 3000, '2028-12-31', 'active')
ON DUPLICATE KEY UPDATE `status` = 'active';
"""

targets = [
    r'C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\database\schema.sql',
    r'C:\Users\HP\.gemini\antigravity\scratch\gaungalihat_database.sql'
]

for p in targets:
    with open(p, 'r', encoding='utf-8') as f:
        content = f.read()
    if 'CREATE TABLE IF NOT EXISTS `coupons`' not in content:
        with open(p, 'a', encoding='utf-8') as f:
            f.write(sql_append)
        print('Updated:', p)
    else:
        print('Already present in:', p)
