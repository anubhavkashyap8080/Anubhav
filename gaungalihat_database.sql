-- GaunGaliHat Enterprise E-Commerce Platform
-- MySQL Database Schema & Initial Data
-- Character Set: utf8mb4, Collation: utf8mb4_unicode_ci

-- cPanel Shared Hosting Safe: No root CREATE DATABASE command
-- Tables will be created directly in your selected cPanel database

-- 1. USERS TABLE
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `unique_code` VARCHAR(50) UNIQUE NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `username` VARCHAR(80) UNIQUE NOT NULL,
  `email` VARCHAR(150) UNIQUE NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('customer', 'vendor', 'admin', 'super_admin') NOT NULL DEFAULT 'customer',
  `department` VARCHAR(150) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT 'Hardoi',
  `state` VARCHAR(100) DEFAULT 'Uttar Pradesh',
  `pincode` VARCHAR(20) DEFAULT '241001',
  `address` TEXT DEFAULT NULL,
  `status` ENUM('active', 'pending', 'suspended') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. VENDORS / SELLERS TABLE
CREATE TABLE IF NOT EXISTS `vendors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `name` VARCHAR(200) NOT NULL,
  `business_name` VARCHAR(200) DEFAULT NULL,
  `slug` VARCHAR(200) UNIQUE NOT NULL,
  `type` ENUM('SHG', 'Artisan', 'Farmer', 'Cottage Industry') DEFAULT 'Cottage Industry',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `cover_image` VARCHAR(255) DEFAULT NULL,
  `location` VARCHAR(200) DEFAULT NULL,
  `panchayat` VARCHAR(150) DEFAULT NULL,
  `village` VARCHAR(150) DEFAULT NULL,
  `district` VARCHAR(100) DEFAULT 'Hardoi',
  `state` VARCHAR(100) DEFAULT 'Uttar Pradesh',
  `pincode` VARCHAR(20) DEFAULT '241126',
  `phone` VARCHAR(30) NOT NULL,
  `whatsapp` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(150) NOT NULL,
  `story` TEXT DEFAULT NULL,
  `year_established` INT DEFAULT 2011,
  `member_count` INT DEFAULT 18,
  `verified` TINYINT(1) DEFAULT 1,
  `kyc_status` ENUM('Verified', 'Pending', 'Rejected') DEFAULT 'Verified',
  `rating` DECIMAL(3,2) DEFAULT 4.95,
  `commission_rate` DECIMAL(5,2) DEFAULT 4.00,
  `bank_beneficiary` VARCHAR(150) DEFAULT 'Saraswati Mahila SHG',
  `bank_name` VARCHAR(150) DEFAULT 'State Bank of India',
  `bank_account` VARCHAR(100) DEFAULT '38920194821',
  `bank_ifsc` VARCHAR(50) DEFAULT 'SBIN0001245',
  `bank_branch` VARCHAR(150) DEFAULT 'Madhoganj Branch, Hardoi',
  `upi_id` VARCHAR(100) DEFAULT 'saraswati.shg@sbi',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CATEGORIES TABLE
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `hindi_name` VARCHAR(150) DEFAULT NULL,
  `slug` VARCHAR(150) UNIQUE NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `icon_name` VARCHAR(50) DEFAULT 'Package',
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. PRODUCTS TABLE
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) UNIQUE NOT NULL,
  `vendor_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `hindi_name` VARCHAR(255) DEFAULT NULL,
  `slug` VARCHAR(255) UNIQUE NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `original_price` DECIMAL(10,2) NOT NULL,
  `discount` INT DEFAULT 0,
  `weight_kg` DECIMAL(6,2) DEFAULT 0.50,
  `stock` INT DEFAULT 50,
  `stock_status` ENUM('In Stock', 'Only Few Left', 'Out of Stock') DEFAULT 'In Stock',
  `rating` DECIMAL(3,2) DEFAULT 4.90,
  `review_count` INT DEFAULT 15,
  `image` VARCHAR(255) NOT NULL,
  `gallery` TEXT DEFAULT NULL,
  `unit` VARCHAR(50) DEFAULT 'piece',
  `description` TEXT DEFAULT NULL,
  `specifications` TEXT DEFAULT NULL,
  `is_best_seller` TINYINT(1) DEFAULT 0,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_women_made` TINYINT(1) DEFAULT 0,
  `is_tool` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. ORDERS TABLE
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) UNIQUE NOT NULL,
  `user_id` INT DEFAULT NULL,
  `customer_name` VARCHAR(150) NOT NULL,
  `customer_phone` VARCHAR(30) NOT NULL,
  `customer_email` VARCHAR(150) DEFAULT NULL,
  `shipping_street` TEXT NOT NULL,
  `shipping_city` VARCHAR(100) NOT NULL,
  `shipping_state` VARCHAR(100) NOT NULL,
  `shipping_pincode` VARCHAR(20) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `shipping_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('cod', 'razorpay', 'payu') NOT NULL DEFAULT 'cod',
  `payment_status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  `payment_txn_id` VARCHAR(150) DEFAULT NULL,
  `order_status` ENUM('Order Placed', 'Confirmed', 'Dispatched', 'Out for Delivery', 'Delivered', 'Cancelled') DEFAULT 'Order Placed',
  `tracking_carrier` VARCHAR(100) DEFAULT 'India Post Express',
  `tracking_consignment_no` VARCHAR(100) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. ORDER ITEMS TABLE
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `vendor_id` INT NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `product_code` VARCHAR(50) DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL,
  `total` DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. VENDOR PAYOUTS TABLE
CREATE TABLE IF NOT EXISTS `vendor_payouts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `payout_ref` VARCHAR(50) UNIQUE NOT NULL,
  `vendor_id` INT NOT NULL,
  `gross_amount` DECIMAL(10,2) NOT NULL,
  `commission_amount` DECIMAL(10,2) NOT NULL,
  `net_amount` DECIMAL(10,2) NOT NULL,
  `utr_number` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('Settled to Bank', 'Processing', 'Failed') DEFAULT 'Settled to Bank',
  `settled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. SITE SETTINGS TABLE
CREATE TABLE IF NOT EXISTS `site_settings` (
  `setting_key` VARCHAR(100) PRIMARY KEY,
  `setting_value` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. WISHLIST TABLE
CREATE TABLE IF NOT EXISTS `wishlists` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_product` (`user_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. BLOGS CMS TABLE
CREATE TABLE IF NOT EXISTS `blogs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `hindi_title` VARCHAR(255) DEFAULT NULL,
  `slug` VARCHAR(255) UNIQUE NOT NULL,
  `excerpt` TEXT,
  `content` LONGTEXT,
  `category` VARCHAR(100) DEFAULT 'Rural Craftsmanship',
  `author_name` VARCHAR(100) DEFAULT 'Anubhav Kashyap',
  `image` TEXT,
  `status` ENUM('published', 'draft') DEFAULT 'published',
  `views` INT DEFAULT 120,
  `tags` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. SUPPORT TICKETS TABLE
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_number` VARCHAR(50) UNIQUE NOT NULL,
  `user_id` INT DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `role` ENUM('customer', 'vendor', 'visitor') DEFAULT 'customer',
  `subject` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) DEFAULT 'Order Tracking',
  `priority` ENUM('Low', 'Medium', 'High', 'Urgent') DEFAULT 'Medium',
  `status` ENUM('Open', 'In Progress', 'Resolved', 'Closed') DEFAULT 'Open',
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. TICKET REPLIES TABLE
CREATE TABLE IF NOT EXISTS `ticket_replies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `user_id` INT DEFAULT NULL,
  `sender_name` VARCHAR(100) NOT NULL,
  `sender_role` VARCHAR(50) DEFAULT 'admin',
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- SEED INITIAL DATA
-- Initial Users: Super Admin (anubhav), Admin (sunil_ops), Seller (ramkishan_artisan), Customer (aarav_buyer)
INSERT IGNORE INTO `users` (`unique_code`, `name`, `username`, `email`, `phone`, `password`, `role`, `department`) VALUES
('GGH-USR-01', 'Anubhav Kashyap', 'anubhav', 'anubhav@ssindustries.in', '+91 7897492100', '$2y$10$g2bX5vBfyZObWF81HbhgsOFWClnOg0G48lQ2vcmpDzO8HfNTLrKpW', 'super_admin', 'Executive Governance & Foundry Operations'),
('GGH-USR-02', 'Sunil Sharma', 'sunil_ops', 'sunil.ops@ssindustries.in', '+91 9005756321', '$2y$10$g2bX5vBfyZObWF81HbhgsOFWClnOg0G48lQ2vcmpDzO8HfNTLrKpW', 'admin', 'Factory Dispatch & Regional Warehousing'),
('GGH-USR-03', 'Ramkishan Lohar', 'ramkishan_artisan', 'ramkishan@ganvgalihat.com', '+91 8303578806', '$2y$10$BYm/rcOxjfXGye1e9CKg9O.Nu648DFJCP4EgzoFWZSefcARDa83O.', 'vendor', 'Blacksmith Artisan Partner'),
('GGH-USR-04', 'S S Industries Hardoi', 'ss_industries', 'hardoi@ssindustries.in', '+91 7897492100', '$2y$10$BYm/rcOxjfXGye1e9CKg9O.Nu648DFJCP4EgzoFWZSefcARDa83O.', 'vendor', 'Agricultural Forge & Foundry'),
('GGH-USR-05', 'Aarav Gupta', 'aarav_buyer', 'aarav.buyer@gmail.com', '+91 98112 34567', '$2y$10$BYm/rcOxjfXGye1e9CKg9O.Nu648DFJCP4EgzoFWZSefcARDa83O.', 'customer', 'Retail Shopper');

-- Initial Vendors
INSERT IGNORE INTO `vendors` (`user_id`, `name`, `business_name`, `slug`, `type`, `avatar`, `cover_image`, `location`, `panchayat`, `village`, `district`, `state`, `pincode`, `phone`, `whatsapp`, `email`, `story`, `year_established`, `member_count`, `commission_rate`, `bank_beneficiary`, `bank_name`, `bank_account`, `bank_ifsc`, `upi_id`) VALUES
(4, 'S S Industries Agro & Tool Works', 'S S Industries Unit 1', 'ss-industries-hardoi', 'Cottage Industry', 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=300&q=80', 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=1200&q=80', 'Madhoganj Baghauli Road, Newada', 'Newada Gram Panchayat', 'Dewa', 'Hardoi', 'Uttar Pradesh', '241126', '+91 7897492100', '+91 7897492100', 'hardoi@ssindustries.in', 'Pioneering agricultural and workshop tool manufacturing facility operated by S S Industries in Hardoi. High-carbon steel sickles, garden khurpis, fawdas, hammers, and manual farming equipment designed for rugged durability.', 2011, 45, 4.00, 'S S Industries Hardoi Works', 'State Bank of India', '38920194821', 'SBIN0001245', 'ssindustries@sbi'),
(2, 'S S Industries Sandila Industrial Hub', 'S S Industries Head Office & Works', 'ss-industries-sandila', 'Cottage Industry', 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=300&q=80', 'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=1200&q=80', 'G145 Phase 2 Industrial Area, UPSIDA Sandila', 'UPSIDA Industrial Area', 'Sandila', 'Hardoi', 'Uttar Pradesh', '241204', '+91 9005756321', '+91 9005756321', 'sandila@ssindustries.in', 'Central dispatch, heavy fabrication, and rural artisan coordination center in UPSIDA Sandila Industrial Zone. Coordinates direct logistics from village clusters to pan-India destinations.', 2015, 38, 4.00, 'S S Industries Sandila Works', 'Punjab National Bank', '4120002100918', 'PUNB0412000', 'sandila.works@pnb'),
(3, 'Ramkishan Lohar Traditional Forgery', 'Lohar Hand-Forged Tools', 'ramkishan-lohar-tools', 'Artisan', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80', 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=1200&q=80', 'Baghauli Village, Hardoi', 'Baghauli Gram Panchayat', 'Baghauli', 'Hardoi', 'Uttar Pradesh', '241126', '+91 8303578806', '+91 8303578806', 'ramkishan.tools@ganvgalihat.com', 'Fourth-generation hereditary village blacksmith crafting leaf-spring tempered sickles, heavy fawdas, garden hand tools, and custom iron clamps built to endure decades of farm work.', 1994, 8, 5.00, 'Ramkishan Lohar', 'Bank of Baroda', '2941010001923', 'BARB0BAGHAU', 'ramkishan@bob'),
(NULL, 'Awadh Gramin Dal & Pulses Kisan Samiti', 'Awadh Organic Pulses Producer Co.', 'awadh-pulses-samiti', 'Farmer', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=300&q=80', 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=1200&q=80', 'Mallawan Crop Belt, Hardoi', 'Mallawan Gram Panchayat', 'Mallawan', 'Hardoi', 'Uttar Pradesh', '241303', '+91 94512 88410', '+91 94512 88410', 'awadh.pulses@ganvgalihat.com', 'A cooperative of 34 organic smallholder pulse farmers in Hardoi cultivating indigenous Arhar, Chana, Moong, and Urad dal without chemical polishing or synthetic fertilizers.', 2018, 34, 4.50, 'Awadh Kisan Producer Co', 'State Bank of India', '39102941829', 'SBIN0001290', 'awadh.kisan@sbi'),
(NULL, 'Varanasi Heritage Pickle & Murabba SHG', 'Banarasi Swad Gramin Mahila SHG', 'banarasi-pickle-shg', 'SHG', 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=300&q=80', 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=1200&q=80', 'Kotwa Village, Varanasi', 'Kotwa Panchayat', 'Kotwa', 'Varanasi', 'Uttar Pradesh', '221105', '+91 97920 11450', '+91 97920 11450', 'banaras.achar@ganvgalihat.com', '22 village women preparing ancestral sun-cured mustard oil mango pickles, stuffed red chillies, raw jackfruit (kathal) achars, and honey-dipped wild amla murabbas.', 2016, 22, 5.00, 'Banarasi Swad Mahila Samiti', 'Union Bank of India', '5120020109123', 'UBIN0551201', 'banarasi.achar@unionbank'),
(NULL, 'Awadh Mahila Eco Jute & Cloth SHG', 'Awadh Golden Fibers SHG', 'awadh-mahila-jute-shg', 'SHG', 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=300&q=80', 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=1200&q=80', 'Mallawan Rural Cluster, Hardoi', 'Mallawan Gram Panchayat', 'Mallawan', 'Hardoi', 'Uttar Pradesh', '241303', '+91 94512 88410', '+91 94512 88410', 'awadh.shg@ganvgalihat.com', 'A self-help group of 28 rural women producing double-stitched golden jute conference totes, vegetable jholas, sabai grass baskets, and cotton storage bags replacing single-use plastics.', 2018, 28, 5.00, 'Awadh Mahila Samiti', 'State Bank of India', '37192841920', 'SBIN0001245', 'awadh.jute@sbi'),
(NULL, 'Channapatna Wooden Artisan Guild', 'Channapatna Heritage Woodcraft', 'channapatna-wood-guild', 'Artisan', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80', 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 'Channapatna Craft Town, Ramanagara', 'Channapatna Town', 'Channapatna', 'Ramanagara', 'Karnataka', '562160', '+91 98451 22340', '+91 98451 22340', 'channapatna@ganvgalihat.com', 'Preserving 200-year-old GI-certified lacquerware turning traditions using ivory wood and natural vegetable dyes (turmeric, indigo, kumkum) 100% non-toxic for children.', 2004, 22, 5.00, 'Channapatna Guild', 'Canara Bank', '10920194821', 'CNRB0001092', 'channapatna@canara'),
(NULL, 'Kashi Handloom Weavers Cooperative', 'Kashi Gramin Weavers', 'kashi-handloom-weavers', 'Cottage Industry', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=300&q=80', 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=1200&q=80', 'Kotwa, Varanasi', 'Kotwa', 'Kotwa', 'Varanasi', 'Uttar Pradesh', '221105', '+91 97920 11450', '+91 97920 11450', 'kashi.khadi@ganvgalihat.com', 'Traditional handloom collective weaving breathable pure khadi cotton kurtas, sturdy gamchhas, unbleached dhotis, and natural dyed scarves on pit-looms without industrial machinery.', 1999, 35, 5.00, 'Kashi Handloom Society', 'Bank of India', '89102019284', 'BKID0008910', 'kashi.weavers@boi');

-- Initial Categories
INSERT IGNORE INTO `categories` (`name`, `hindi_name`, `slug`, `description`, `image`, `icon_name`, `sort_order`) VALUES
('Farming & Agricultural Tools', 'à¤•à¥ƒà¤·à¤¿ à¤à¤µà¤‚ à¤–à¥‡à¤¤à¥€ à¤”à¤œà¤¾à¤°', 'farming-traditional-tools', 'High-carbon steel desi hansiya, heavy garden khurpi, durable fawda, and traditional wooden handle agricultural implements forged by S S Industries & village smiths.', 'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=600&q=80', 'Wrench', 1),
('Organic Pulses & Farm Grains', 'à¤¦à¤¾à¤²à¥‡à¤‚ à¤à¤µà¤‚ à¤¦à¥‡à¤¸à¥€ à¤…à¤¨à¤¾à¤œ', 'pulses-organic-grains', '100% unpolished Arhar/Toor dal, organic Chana dal, Moong, Urad, and nutrient-dense desi kala chana directly from Awadh farmer collectives.', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=600&q=80', 'Wheat', 2),
('Authentic Village Pickles & Murabbas', 'à¤¦à¥‡à¤¸à¥€ à¤…à¤šà¤¾à¤° à¤µ à¤®à¥à¤°à¤¬à¥à¤¬à¤¾', 'pickles-village-murabba', 'Sun-cured Banarasi mustard oil mango pickles, stuffed red chilli achar, raw jackfruit (kathal) achar, and wild amla murabbas prepared by rural women SHGs.', 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=600&q=80', 'Utensils', 3),
('Workshop & Iron Implements', 'à¤²à¥‹à¤¹à¤¾à¤° à¤à¤µà¤‚ à¤µà¤°à¥à¤•à¤¶à¥‰à¤ª à¤‰à¤ªà¤•à¤°à¤£', 'hardware-cottage-implements', 'Heavy sledge hammers, anvil stakes, hand wood chisels, leaf-spring cutting tools, and industrial utility hardware manufactured for durable village use.', 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=600&q=80', 'Wrench', 4),
('Eco Jute Bags & Cloth Jholas', 'à¤‡à¤•à¥‹ à¤œà¥‚à¤Ÿ à¤¬à¥ˆà¤— à¤à¤µà¤‚ à¤à¥‹à¤²à¥‡', 'jute-bags-cloth-jholas', '100% biodegradable golden jute totes, village sabai grass storage baskets, heavy-duty grain jholas, and kantha embroidered cotton pouches.', 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80', 'ShoppingBag', 5),
('Village Decor & Wooden Toys', 'à¤—à¥à¤°à¤¾à¤®à¥€à¤£ à¤¸à¤œà¤¾à¤µà¤Ÿ à¤µ à¤–à¤¿à¤²à¥Œà¤¨à¥‡', 'village-home-decor-toys', 'GI-tagged Channapatna natural wooden toys, hand-painted terracotta pots, bell-metal crafts, hand-woven coir door mats, and brass oil lamps.', 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=600&q=80', 'Home', 6),
('Handloom Textiles & Apparel', 'à¤¹à¤¥à¤•à¤°à¤˜à¤¾ à¤µà¤¸à¥à¤¤à¥à¤° à¤µ à¤–à¤¾à¤¦à¥€', 'handmade-apparel-textiles', 'Authentic hand-spun khadi cotton kurtas, traditional gamchhas, cotton dhotis, natural dyed dupattas, and wool shawls from regional handloom weavers.', 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=600&q=80', 'Shirt', 7),
('Rural Utility & Manual Machinery', 'à¤—à¥à¤°à¤¾à¤®à¥€à¤£ à¤‰à¤ªà¤¯à¥‹à¤—à¤¿à¤¤à¤¾ à¤µ à¤®à¤¶à¥€à¤¨à¤°à¥€', 'rural-utility-machinery', 'Manual seed dibblers, rotary chaff cutter blades, hand winnowing supa trays, sugarcane crusher components, and household oil press spare assemblies.', 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=600&q=80', 'Sparkles', 8),
('Handmade by Rural Women', 'à¤—à¥à¤°à¤¾à¤®à¥€à¤£ à¤®à¤¹à¤¿à¤²à¤¾à¤“à¤‚ à¤¦à¥à¤µà¤¾à¤°à¤¾ à¤¨à¤¿à¤°à¥à¤®à¤¿à¤¤', 'handmade-by-rural-women', 'Exclusive artisanal crafts, lac bangles, macrame hangings, and textile organizers produced by rural women self-help groups (SHGs) across Uttar Pradesh & Bharat.', 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=600&q=80', 'Heart', 9);

-- Initial Products (Tools, Pulses, Pickles, Bags, Crafts, Handlooms)
INSERT IGNORE INTO `products` (`code`, `vendor_id`, `category_id`, `name`, `hindi_name`, `slug`, `price`, `original_price`, `discount`, `weight_kg`, `stock`, `stock_status`, `rating`, `review_count`, `image`, `gallery`, `unit`, `description`, `specifications`, `is_best_seller`, `is_featured`, `is_women_made`, `is_tool`) VALUES
('GGH-TL-101', 1, 1, 'Hand-Forged Desi Hansiya (Grass & Paddy Sickle)', 'à¤¦à¥‡à¤¸à¥€ à¤¹à¤‚à¤¸à¤¿à¤¯à¤¾ (à¤®à¤œà¤¬à¥‚à¤¤ à¤§à¤¾à¤°à¤¦à¤¾à¤°)', 'forged-desi-hansiya-sickle', 295.00, 420.00, 30, 0.45, 68, 'In Stock', 4.96, 42, 'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=600&q=80"]', 'piece', 'Heavy tempered high-carbon railroad spring steel hansiya crafted in Madhoganj forge by S S Industries. Razor-sharp micro-serrated blade tailored for fodder cutting, paddy, and wheat harvesting with seasoned shisham wooden grip.', '{"Blade Material": "High-Carbon Leaf Spring Steel", "Handle": "Hardwood Shisham Grip with Iron Ferrule", "Total Length": "14.5 Inches", "Weight": "450g", "Origin": "Hardoi, UP"}', 1, 1, 0, 1),
('GGH-TL-102', 1, 1, 'Hardened High-Carbon Steel Garden & Weeding Khurpi', 'à¤®à¤œà¤¬à¥‚à¤¤ à¤²à¥‹à¤¹à¥‡ à¤•à¥€ à¤–à¥à¤°à¤ªà¥€ (à¤¨à¤¿à¤°à¤¾à¤ˆ-à¤—à¥à¤¡à¤¼à¤¾à¤ˆ)', 'high-carbon-steel-weeding-khurpi', 185.00, 260.00, 29, 0.50, 84, 'In Stock', 4.94, 38, 'https://images.unsplash.com/photo-1617576683096-00fc8eecb3af?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1617576683096-00fc8eecb3af?auto=format&fit=crop&w=600&q=80"]', 'piece', 'Traditional heavy agricultural khurpi forged for weeding vegetable beds, sugarcane furrows, nursery beds, and household kitchen gardens. Flat-forged broad blade welded into sturdy tang that will never bend.', '{"Blade Width": "3 Inches", "Handle Material": "Seasoned Neem Hardwood", "Blade Material": "Drop-forged EN-8 Carbon Steel", "Origin": "Hardoi, UP"}', 1, 1, 0, 1),
('GGH-TL-103', 2, 1, 'Heavy Forged Iron Fawda / Fadawa Spade (Belcha)', 'à¤²à¥‹à¤¹à¥‡ à¤•à¤¾ à¤®à¤œà¤¬à¥‚à¤¤ à¤«à¤¾à¤µà¤¡à¤¼à¤¾ / à¤•à¥à¤¦à¤¾à¤²', 'heavy-forged-iron-fawda-spade', 340.00, 480.00, 29, 1.80, 45, 'In Stock', 4.92, 29, 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=600&q=80"]', 'piece', 'Extra thick 3.5mm agricultural forged fawda engineered for deep earth digging, irrigation channel clearing, canal maintenance, and building foundation earthwork. Heat treated blade rim resists chipping on stones.', '{"Blade Thickness": "3.5mm Heavy Gauge", "Material": "Recycled Locomotive Spring Steel", "Eye Diameter": "1.5 Inches Standard", "Weight": "1.8 kg", "Origin": "Sandila Works"}', 1, 1, 0, 1),
('GGH-TL-104', 1, 1, 'Heavy Forged Iron Woodcutting Kulhadi (Axe with Sal Handle)', 'à¤²à¥‹à¤¹à¥‡ à¤•à¥€ à¤­à¤¾à¤°à¥€ à¤•à¥à¤²à¥à¤¹à¤¾à¤¡à¤¼à¥€ (à¤²à¤•à¤¡à¤¼à¥€ à¤•à¤Ÿà¤¾à¤ˆ)', 'heavy-forged-iron-woodcutting-kulhadi', 460.00, 650.00, 29, 2.10, 32, 'In Stock', 4.98, 31, 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=600&q=80"]', 'piece', 'Tempered village woodcutter axe featuring a razor-sharp wedge profile edge. Cast and hand-beaten in S S Industries foundry with seasoned Himalayan Sal wood handle anchored by two iron safety wedges.', '{"Head Weight": "1.4 kg Forged Steel", "Total Weight": "2.1 kg", "Handle Length": "26 Inches Sal Wood", "Hardness": "54-56 HRC", "Origin": "Hardoi Forge"}', 1, 1, 0, 1),
('GGH-TL-105', 2, 1, 'Heavy UV-Stabilized Crop Protection & Pond Fishing Net', 'à¤–à¥‡à¤¤ à¤µ à¤¤à¤¾à¤²à¤¾à¤¬ à¤¸à¥à¤°à¤•à¥à¤·à¤¾ à¤œà¤¾à¤² (à¤¨à¥‡à¤Ÿ)', 'crop-protection-pond-fishing-net', 380.00, 550.00, 31, 1.20, 50, 'In Stock', 4.90, 24, 'https://images.unsplash.com/photo-1500651230702-0e2d8a49d4ad?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1500651230702-0e2d8a49d4ad?auto=format&fit=crop&w=600&q=80"]', 'piece', 'Multipurpose village net woven from high-tensile braided nylon monofilament with double-knotted selvage edge. Protects vegetable polyhouses, orchards from nilgai/wild boars, and serves as farm pond fishing casting net.', '{"Dimensions": "10 meters x 3 meters", "Mesh Size": "1.25 Inches Diamond", "Material": "UV Protected Virgin HDPE Monofilament", "Tensile Strength": "High Resistance"}', 0, 1, 0, 1),
('GGH-TL-106', 2, 4, 'Hand-Forged Blacksmith Sledge Hammer (3 kg Head)', 'à¤²à¥‹à¤¹à¤¾à¤° à¤¹à¤¥à¥Œà¤¡à¤¼à¤¾ (3 à¤•à¤¿à¤²à¥‹ à¤­à¤¾à¤°à¥€)', 'hand-forged-blacksmith-sledge-hammer', 520.00, 720.00, 28, 3.40, 25, 'In Stock', 4.95, 19, 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=600&q=80"]', 'piece', 'Industrial strength 3kg double-face sledge hammer hand-forged in UPSIDA Sandila manufacturing plant. Machined dual striking faces with bevelled edges to prevent metal flaking under heavy workshop blows.', '{"Head Weight": "3.0 kg Solid Carbon Steel", "Total Length": "30 Inches", "Handle": "Fibre & Hardwood Hybrid Grip", "Origin": "Sandila Heavy Plant"}', 0, 1, 0, 1),

-- NEW PULSES CATEGORY PRODUCTS
('GGH-PL-201', 4, 2, 'Unpolished Desi Organic Arhar / Toor Dal (Awadh Dal)', 'à¤¦à¥‡à¤¸à¥€ à¤…à¤¨à¤ªà¥‰à¤²à¤¿à¤¶ à¤…à¤°à¤¹à¤° à¤¦à¤¾à¤² (1 à¤•à¤¿à¤²à¥‹)', 'desi-organic-arhar-toor-dal-1kg', 185.00, 230.00, 20, 1.05, 120, 'In Stock', 4.98, 54, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=600&q=80"]', '1 kg pack', 'Farm-fresh, zero-chemical, unpolished natural Arhar (Pigeon Pea) dal cultivated in the rich alluvial soils of Awadh. Retains natural dietary fibre, protein, and authentic village aroma when cooked with ghee and cumin.', '{"Weight": "1 kg Air-Sealed Pouch", "Processing": "Traditional Stone Chakki Milled (Unpolished)", "Preservatives": "None (100% Raw Farm Produce)", "Farmer Cluster": "Mallawan Organic Producer Co."}', 1, 1, 1, 0),
('GGH-PL-202', 4, 2, 'Stone-Ground Desi Chana Dal (Unpolished Bengal Gram)', 'à¤¦à¥‡à¤¸à¥€ à¤šà¤¨à¤¾ à¤¦à¤¾à¤² (à¤ªà¤¤à¥à¤¥à¤° à¤šà¤•à¥à¤•à¥€ à¤¦à¤²à¤¿à¤¯à¤¾)', 'stone-ground-desi-chana-dal-1kg', 145.00, 180.00, 19, 1.05, 95, 'In Stock', 4.95, 36, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80"]', '1 kg pack', 'Desi indigenous chana split on village hand chakkis. Golden yellow, aromatic, and rich in slow-burning complex carbs. Perfect for poori-chana, dal tadka, besan sweets, and healthy rural sattu.', '{"Weight": "1 kg", "Grain Type": "Desi Small-Grain Bengal Gram", "Polish": "Zero Polish & Zero Artificial Color", "Dietary": "High Protein & High Fibre"}', 1, 1, 0, 0),
('GGH-PL-203', 4, 2, 'Bundelkhandi Desi Kala Chana (Nutrient Dense Brown Gram)', 'à¤¬à¥à¤‚à¤¦à¥‡à¤²à¤–à¤‚à¤¡à¥€ à¤¦à¥‡à¤¸à¥€ à¤•à¤¾à¤²à¤¾ à¤šà¤¨à¤¾ (1 à¤•à¤¿à¤²à¥‹)', 'bundelkhandi-desi-kala-chana-1kg', 125.00, 160.00, 22, 1.05, 110, 'In Stock', 4.92, 41, 'https://images.unsplash.com/photo-1515543237350-b3eea1ec8082?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1515543237350-b3eea1ec8082?auto=format&fit=crop&w=600&q=80"]', '1 kg pack', 'Sun-dried small grain organic desi kala chana. Naturally high iron and fibre content, ideal for sprouting, authentic tari-wale chane, boiled morning chaat, and fasting prasad.', '{"Weight": "1 kg", "Variety": "Desi Indigenous Kala Chana", "Cultivation": "Rainfed Dryland Organic", "Shelf Life": "12 Months"}', 0, 1, 1, 0),
('GGH-PL-204', 4, 2, 'Organic Yellow Moong Dal (High Protein Dhuli Moong)', 'à¤§à¥à¤²à¥€ à¤®à¥‚à¤‚à¤— à¤¦à¤¾à¤² (à¤†à¤°à¥à¤—à¥‡à¤¨à¤¿à¤• à¤µ à¤¸à¥à¤ªà¤¾à¤šà¥à¤¯)', 'organic-yellow-moong-dal-dhuli-1kg', 165.00, 205.00, 20, 1.05, 80, 'In Stock', 4.96, 28, 'https://images.unsplash.com/photo-1596797038530-2c107229654b?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1596797038530-2c107229654b?auto=format&fit=crop&w=600&q=80"]', '1 kg pack', 'Easily digestible, light yellow moong dal split without chemical agents. Highly recommended for children, convalescents, khichdi, moong dal halwa, and daily nourishing soups.', '{"Weight": "1 kg", "Type": "Split Huskless Yellow Moong", "Purity": "100% Unadulterated", "Origin": "Awadh Farmer Cooperative"}', 0, 0, 1, 0),
('GGH-PL-205', 4, 2, 'Desi Chilka Urad Dal (Black Gram Lentils with Husk)', 'à¤¦à¥‡à¤¸à¥€ à¤›à¤¿à¤²à¤•à¤¾ à¤‰à¤¡à¤¼à¤¦ à¤¦à¤¾à¤² (à¤¦à¤¾à¤² à¤®à¤–à¤¨à¥€ à¤µ à¤¤à¤¡à¤¼à¤•à¤¾)', 'desi-chilka-urad-dal-1kg', 155.00, 195.00, 21, 1.05, 75, 'In Stock', 4.90, 22, 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1585937421612-70a008356fbe?auto=format&fit=crop&w=600&q=80"]', '1 kg pack', 'Rich, earthy split black gram with retained nutritious black husk. Imparts the classic velvety texture and aroma to traditional village dal fry, imarti batter, and dahi bhalle.', '{"Weight": "1 kg", "Cultivation": "Indigenous Desi Seed", "Quality": "Grade A Machine Cleaned by Village SHG"}', 0, 0, 0, 0),

-- NEW PICKLES & MURABBAS CATEGORY PRODUCTS
('GGH-PK-301', 5, 3, 'Banarasi Kachi Ghani Mustard Oil Aam ka Achar', 'à¤¬à¤¨à¤¾à¤°à¤¸à¥€ à¤•à¤šà¥à¤šà¥€ à¤˜à¤¾à¤¨à¥€ à¤†à¤® à¤•à¤¾ à¤¦à¥‡à¤¸à¥€ à¤…à¤šà¤¾à¤°', 'banarasi-kachi-ghani-aam-ka-achar-500g', 240.00, 320.00, 25, 0.75, 90, 'In Stock', 4.99, 68, 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=600&q=80"]', '500g glass jar', 'Handmade sun-cured raw Ramkela mango pickle marinated in pungent wood-pressed (kolhu) kachi ghani sarson oil. Spiced with whole fennel, kalonji, roasted methi, heeng, and mountain rock salt by Varanasi rural women.', '{"Net Weight": "500g Ceramic/Glass Jar", "Oil Base": "100% Pure Cold-Pressed Mustard Oil", "Preservative": "Natural Sunlight & Mustard Oil Only", "SHG Producer": "Banarasi Swad Mahila Samiti"}', 1, 1, 1, 0),
('GGH-PK-302', 5, 3, 'Awadhi Stuffed Banarasi Lal Mirch ka Achar', 'à¤…à¤µà¤§à¥€ à¤¬à¤¨à¤¾à¤°à¤¸à¥€ à¤­à¤°à¤µà¤¾à¤‚ à¤²à¤¾à¤² à¤®à¤¿à¤°à¥à¤š à¤•à¤¾ à¤…à¤šà¤¾à¤°', 'awadhi-bharwa-lal-mirch-achar-400g', 260.00, 340.00, 24, 0.65, 65, 'In Stock', 4.97, 45, 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=600&q=80"]', '400g jar', 'Plump whole red Banarasi chillies deseeded and packed by hand with a secret blend of amchoor, roasted saunf, rai, ajwain, and mustard oil. The ultimate companion for hot parathas and dal chawal.', '{"Net Weight": "400g", "Ingredients": "Sun-Dried Red Chillies, Amchoor, Cold-Pressed Mustard Oil, Spices", "Fermentation": "21 Days Sun Matured"}', 1, 1, 1, 0),
('GGH-PK-303', 5, 3, 'Village Desi Kathal ka Achar (Spicy Raw Jackfruit)', 'à¤—à¤¾à¤‚à¤µ à¤•à¤¾ à¤¦à¥‡à¤¸à¥€ à¤•à¤Ÿà¤¹à¤² à¤•à¤¾ à¤…à¤šà¤¾à¤° (à¤¸à¤°à¤¸à¥‹à¤‚ à¤¤à¥‡à¤²)', 'village-desi-kathal-ka-achar-500g', 250.00, 330.00, 24, 0.75, 48, 'In Stock', 4.91, 33, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80"]', '500g jar', 'Tender young baby kathal cubes parboiled and sun-cured with cracked mustard seeds, red chilli flakes, and roasted cumin in pure mustard oil. Has a rich, meaty texture that melts in the mouth.', '{"Net Weight": "500g", "Key Fruit": "Tender Village Kathal (Raw Jackfruit)", "Oil Content": "Optimal Cold-Pressed Sarson Oil"}', 0, 1, 1, 0),
('GGH-PK-304', 5, 3, 'Wild Forest Desi Amla Murabba (In Raw Khand Syrup)', 'à¤œà¤‚à¤—à¤²à¥€ à¤†à¤‚à¤µà¤²à¤¾ à¤®à¥à¤°à¤¬à¥à¤¬à¤¾ (à¤¦à¥‡à¤¸à¥€ à¤–à¤¾à¤‚à¤¡ à¤®à¥‡à¤‚ à¤¨à¤¿à¤°à¥à¤®à¤¿à¤¤)', 'wild-forest-desi-amla-murabba-1kg', 310.00, 420.00, 26, 1.25, 70, 'In Stock', 4.96, 52, 'https://images.unsplash.com/photo-1599940824399-b87987ceb72a?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1599940824399-b87987ceb72a?auto=format&fit=crop&w=600&q=80"]', '1 kg tub', 'Whole wild Pratapgarh amla gooseberries slow-simmered in unrefined desi sulphur-free sugar syrup (khand) with cardamom and saffron. Highly rejuvenating Ayurvedic daily tonic for immunity and digestion.', '{"Net Weight": "1 kg", "Amla Source": "Organic Orchards of Pratapgarh", "Sweetener": "Desi Raw Khand (No Bleached White Sugar)", "Ayurvedic Benefits": "Rich in Natural Vitamin C & Iron"}', 1, 1, 1, 0),
('GGH-PK-305', 5, 3, 'Digestive Nimbu Adrak Mirch Pickle (Oil-Free)', 'à¤ªà¤¾à¤šà¤• à¤¨à¥€à¤‚à¤¬à¥‚ à¤…à¤¦à¤°à¤• à¤¹à¤°à¥€ à¤®à¤¿à¤°à¥à¤š à¤…à¤šà¤¾à¤° (à¤¬à¤¿à¤¨à¤¾ à¤¤à¥‡à¤²)', 'digestive-nimbu-adrak-mirch-pickle-500g', 210.00, 280.00, 25, 0.75, 55, 'In Stock', 4.88, 26, 'https://images.unsplash.com/photo-1596797038530-2c107229654b?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1596797038530-2c107229654b?auto=format&fit=crop&w=600&q=80"]', '500g jar', 'Traditional digestive Indian pickle made with juicy thin-skinned kagzi lemons, fresh mountain ginger slivers, slit green chillies, and black rock salt. 100% oil-free and naturally aged under direct sunlight.', '{"Net Weight": "500g", "Oil": "Zero Oil (Oil-Free Formulation)", "Specialty": "Excellent for Digestion & Acidity Relief"}', 0, 0, 1, 0),

-- CRAFTS, TEXTILES & JUTE BAGS
('GGH-JT-401', 6, 5, 'Hand-Embroidered Golden Jute Market Bag (Pack of 2)', 'à¤¹à¤¾à¤¥ à¤¸à¥‡ à¤¬à¤¨à¤¾ à¤œà¥‚à¤Ÿ à¤à¥‹à¤²à¤¾ (2 à¤•à¤¾ à¤¸à¥‡à¤Ÿ)', 'hand-embroidered-jute-market-bag-pack-2', 420.00, 580.00, 28, 0.50, 60, 'In Stock', 4.93, 39, 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80"]', 'pack of 2', 'Biodegradable, heavy-duty laminated golden jute tote bag hand-stitched by rural women artisans. Features padded bamboo-reinforced cane handles, zippered main compartment, and traditional Kantha border embroidery.', '{"Dimensions": "15 x 14 x 5 Inches", "Weight Capacity": "Holds up to 15 kg groceries", "Handle": "Padded Cotton Rope Grip", "Fabric": "100% Natural Golden Jute"}', 1, 1, 1, 0),
('GGH-WM-001', 7, 7, 'Pure Khadi Cotton Men Kurta (Handloom Natural White)', 'à¤¹à¤¾à¤¥ à¤¸à¥‡ à¤¬à¥à¤¨à¤¾ à¤¶à¥à¤¦à¥à¤§ à¤–à¤¾à¤¦à¥€ à¤•à¥à¤°à¥à¤¤à¤¾', 'handloom-khadi-cotton-men-kurta', 650.00, 890.00, 27, 0.35, 40, 'In Stock', 4.95, 34, 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=600&q=80"]', 'piece', 'Woven on indigenous wooden pit-looms by traditional weavers in Kashi. Breathable, sweat-absorbent unbleached desi cotton fabric with Mandarin collar and natural wooden coconut-shell buttons.', '{"Sizes": "M (38), L (40), XL (42), XXL (44)", "Weave": "Hand-spun 56s Count Khadi Cotton", "Buttons": "Natural Coconut Shell", "Origin": "Varanasi, UP"}', 1, 1, 0, 0),
('GGH-WM-002', 8, 7, 'Traditional Handloom Cotton Gamchha (Pack of 3)', 'à¤ªà¤¾à¤°à¤‚à¤ªà¤°à¤¿à¤• à¤¸à¥‚à¤¤à¥€ à¤—à¤®à¤›à¤¾ (3 à¤•à¤¾ à¤¸à¥‡à¤Ÿ)', 'traditional-handloom-cotton-gamchha-pack-3', 290.00, 390.00, 26, 0.40, 75, 'In Stock', 4.91, 48, 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=600&q=80"]', 'pack of 3', 'Ultra-absorbent, quick-drying 100% pure cotton rural red and white checkered gamchhas. An indispensable multi-purpose companion for farming, travel, and bathing across rural India.', '{"Pack Size": "3 Pieces (Assorted Checks)", "Length": "1.8 meters x 0.8 meters", "Yarn": "Pure Combed Cotton", "Origin": "Kashi Handloom Society"}', 1, 0, 0, 0),
('GGH-DC-501', 7, 6, 'Handcrafted Natural River Reed Floor Mat (Desi Chatai)', 'à¤¹à¤¾à¤¥ à¤¸à¥‡ à¤¬à¥à¤¨à¥€ à¤ªà¥à¤°à¤¾à¤•à¥ƒà¤¤à¤¿à¤• à¤šà¤Ÿà¤¾à¤ˆ (à¤«à¥à¤²à¥‹à¤° à¤®à¥ˆà¤Ÿ)', 'handcrafted-river-reed-chatai-mat', 480.00, 680.00, 29, 1.40, 35, 'In Stock', 4.94, 27, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=600&q=80"]', 'piece', 'Natural kauna river reed grass woven tight on village ground frames. Remains naturally cool in hot summers and warm in winter. Double-stitched fabric border prevents edge fraying.', '{"Dimensions": "6 feet x 3.5 feet (Full Bed/Floor Size)", "Material": "Natural River Reed Grass & Jute Warp", "Border": "Reinforced Cotton Fabric", "Maintenance": "Wipe with damp cloth and sun dry"}', 1, 1, 1, 0),
('GGH-DC-502', 7, 6, 'Channapatna Wooden Handcrafted Spinning Top & Toy Set', 'à¤šà¤¨à¥à¤¨à¤¾à¤ªà¤Ÿà¤¨à¤¾ à¤²à¤•à¤¡à¤¼à¥€ à¤•à¥‡ à¤ªà¤¾à¤°à¤‚à¤ªà¤°à¤¿à¤• à¤–à¤¿à¤²à¥Œà¤¨à¥‡', 'channapatna-wooden-traditional-toy-set', 360.00, 490.00, 27, 0.40, 50, 'In Stock', 4.96, 31, 'https://images.unsplash.com/photo-1567532939604-b6b5b0db2604?auto=format&fit=crop&w=600&q=80', '["https://images.unsplash.com/photo-1567532939604-b6b5b0db2604?auto=format&fit=crop&w=600&q=80"]', 'set of 4 toys', 'GI certified ivory wood hand-turned toys colored with natural vegetable turmeric, indigo, and kumkum lacquer. 100% lead-free, splinter-free, and safe for toddlers and children.', '{"Included": "2 Latthus (Spinning Tops), 1 Roly-Poly Toy, 1 Wooden Car", "Dyes": "100% Edible Vegetable Extracts", "Wood": "Aale Mara (Wrightia tinctoria)", "Safety": "BIS & ASTM Child Safe Certified"}', 1, 1, 0, 0);

-- Initial Orders
INSERT IGNORE INTO `orders` (`order_number`, `user_id`, `customer_name`, `customer_phone`, `customer_email`, `shipping_street`, `shipping_city`, `shipping_state`, `shipping_pincode`, `subtotal`, `shipping_fee`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `tracking_carrier`, `tracking_consignment_no`) VALUES
('ORD-9821', 5, 'Vikram Joshi', '+91 98450 12345', 'vikram.joshi@gmail.com', 'Plot 44, Indiranagar 100ft Road', 'Bengaluru', 'Karnataka', '560038', 535.00, 0.00, 535.00, 'razorpay', 'completed', 'Order Placed', 'India Post Express', 'EU982147890IN'),
('ORD-9818', 5, 'Suman Devi', '+91 94150 98765', 'suman.devi@example.com', 'Village Madhoganj, Near Post Office', 'Hardoi', 'Uttar Pradesh', '241126', 420.00, 40.00, 460.00, 'cod', 'pending', 'Dispatched', 'India Post Express', 'EU981845120IN'),
('ORD-9804', 5, 'Deepak Sharma', '+91 98111 22334', 'deepak.sharma@gmail.com', 'B-12, Sector 62', 'Noida', 'Uttar Pradesh', '201301', 650.00, 0.00, 650.00, 'payu', 'completed', 'Delivered', 'India Post Express', 'EU980411234IN');

-- Initial Order Items
INSERT IGNORE INTO `order_items` (`order_id`, `product_id`, `vendor_id`, `product_name`, `product_code`, `price`, `quantity`, `total`) VALUES
(1, 1, 1, 'Hand-Forged Desi Hansiya (Grass & Paddy Sickle)', 'GGH-TL-101', 295.00, 1, 295.00),
(1, 11, 5, 'Banarasi Kachi Ghani Mustard Oil Aam ka Achar', 'GGH-PK-301', 240.00, 1, 240.00),
(2, 16, 6, 'Hand-Embroidered Golden Jute Market Bag (Pack of 2)', 'GGH-JT-401', 420.00, 1, 420.00),
(3, 17, 7, 'Pure Khadi Cotton Men Kurta (Handloom Natural White)', 'GGH-WM-001', 650.00, 1, 650.00);

-- Initial Vendor Payouts
INSERT IGNORE INTO `vendor_payouts` (`payout_ref`, `vendor_id`, `gross_amount`, `commission_amount`, `net_amount`, `utr_number`, `status`) VALUES
('PAY-9821', 1, 24500.00, 980.00, 23520.00, 'SBIN26101089214', 'Settled to Bank'),
('PAY-9818', 2, 18500.00, 740.00, 17760.00, 'PUNB26092544102', 'Settled to Bank'),
('PAY-9804', 3, 12400.00, 620.00, 11780.00, 'BARB26091811904', 'Settled to Bank'),
('PAY-9799', 4, 15600.00, 702.00, 14898.00, 'SBIN26091599821', 'Settled to Bank'),
('PAY-9788', 5, 21200.00, 1060.00, 20140.00, 'UBIN26091012894', 'Settled to Bank');

-- Initial Site Settings
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('brand_name', 'GaunGaliHat'),
('company_name', 'S S Industries'),
('brand_tagline', 'S S Industries Enterprise â€¢ Ganv se ghar tak'),
('announcement_text', 'ðŸŒ¾ S S Industries Tools, Awadh Pulses, Banarasi Pickles & Village Crafts: Direct from Rural Artisans | Free Delivery Over â‚¹499 | Cash on Delivery Available'),
('contact_phone', '+91 7897492100'),
('contact_email', 'namaste@ganvgalihat.com'),
('factory_address', 'Works / Factory: Madhoganj Baghauli Road, Newada, Hardoi, Uttar Pradesh - 241126'),
('head_office_address', 'Head Office: G145 Phase 2 Industrial Area, UPSIDA Sandila, Hardoi, Uttar Pradesh'),
('free_shipping_threshold', '499'),
('shipping_base_up', '40'),
('shipping_base_north', '60'),
('shipping_base_other', '80'),
('shipping_heavy_per_kg', '15'),
('razorpay_enabled', '1'),
('razorpay_key_id', 'rzp_test_1DP5mmOlF5G5ag'),
('razorpay_key_secret', 's9F2xKlY02jK3991lmxO92aa'),
('payu_enabled', '1'),
('payu_merchant_key', 'gtKFFx'),
('payu_merchant_salt', 'eCwWELxi'),
('payu_mode', 'test'),
('cod_enabled', '1'),
('commission_rate_default', '4.00');


-- ==========================================================================
-- ENTERPRISE MODULES, RBAC, HERO CAMPAIGNS & SECURITY TABLES
-- ==========================================================================

-- GaunGaliHat Enterprise Architecture Migration
-- Version: 2026_10_24_000001_enterprise_upgrade.sql
-- Supports MySQL 8.x / MariaDB and is translated seamlessly for SQLite

-- 1. ROLES & PERMISSIONS (RBAC)
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(80) UNIQUE NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `is_system` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `module` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(80) UNIQUE NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` INT NOT NULL,
  `permission_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`, `permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_roles` (
  `user_id` INT NOT NULL,
  `role_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. ADMINISTRATIVE AUDIT TRAIL
CREATE TABLE IF NOT EXISTS `admin_audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `user_name` VARCHAR(150) NOT NULL,
  `user_role` VARCHAR(50) NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `entity_type` VARCHAR(50) DEFAULT NULL,
  `entity_id` VARCHAR(50) DEFAULT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. HERO CAMPAIGNS & VERSION HISTORY
CREATE TABLE IF NOT EXISTS `hero_campaigns` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) UNIQUE NOT NULL,
  `campaign_type` VARCHAR(50) DEFAULT 'festival',
  `theme_preset` VARCHAR(50) DEFAULT 'general',
  `theme_color` VARCHAR(50) DEFAULT 'emerald',
  `badge_text` VARCHAR(255) DEFAULT NULL,
  `title_line1` VARCHAR(255) DEFAULT NULL,
  `title_line2` VARCHAR(255) DEFAULT NULL,
  `subtitle` TEXT DEFAULT NULL,
  `image_url` TEXT DEFAULT NULL,
  `btn1_text` VARCHAR(100) DEFAULT NULL,
  `btn1_link` VARCHAR(255) DEFAULT NULL,
  `btn2_text` VARCHAR(100) DEFAULT NULL,
  `btn2_link` VARCHAR(255) DEFAULT NULL,
  `stat1_num` VARCHAR(50) DEFAULT NULL,
  `stat1_label` VARCHAR(100) DEFAULT NULL,
  `stat2_num` VARCHAR(50) DEFAULT NULL,
  `stat2_label` VARCHAR(100) DEFAULT NULL,
  `stat3_num` VARCHAR(50) DEFAULT NULL,
  `stat3_label` VARCHAR(100) DEFAULT NULL,
  `tag1_title` VARCHAR(100) DEFAULT NULL,
  `tag1_subtitle` VARCHAR(100) DEFAULT NULL,
  `tag2_title` VARCHAR(100) DEFAULT NULL,
  `tag2_subtitle` VARCHAR(100) DEFAULT NULL,
  `custom_html` LONGTEXT DEFAULT NULL,
  `custom_css` LONGTEXT DEFAULT NULL,
  `has_custom_code` TINYINT(1) DEFAULT 0,
  `priority` INT DEFAULT 10,
  `start_datetime` DATETIME DEFAULT NULL,
  `end_datetime` DATETIME DEFAULT NULL,
  `status` ENUM('draft', 'active', 'scheduled', 'archived') DEFAULT 'draft',
  `created_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hero_campaign_versions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `campaign_id` INT NOT NULL,
  `version_num` INT NOT NULL,
  `snapshot_data` LONGTEXT NOT NULL,
  `reason` VARCHAR(255) DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. SEO REDIRECTS & 404 ERROR LOGS
CREATE TABLE IF NOT EXISTS `seo_redirects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `source_path` VARCHAR(255) UNIQUE NOT NULL,
  `target_url` VARCHAR(255) NOT NULL,
  `status_code` INT DEFAULT 301,
  `hits` INT DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `seo_404_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `request_path` VARCHAR(255) NOT NULL,
  `referrer` VARCHAR(255) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `hit_count` INT DEFAULT 1,
  `resolved` TINYINT(1) DEFAULT 0,
  `last_occurred_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. SECURITY LOGS & RATE LIMITING
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `identifier` VARCHAR(150) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `success` TINYINT(1) DEFAULT 0,
  `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `security_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_type` VARCHAR(50) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_id` INT DEFAULT NULL,
  `details` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. CENTRAL NOTIFICATIONS QUEUE & TEMPLATES
CREATE TABLE IF NOT EXISTS `notification_templates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) UNIQUE NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body_template` TEXT NOT NULL,
  `channels` VARCHAR(100) DEFAULT 'email,push',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `recipient` VARCHAR(150) NOT NULL,
  `channel` VARCHAR(30) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `status` ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
  `error_message` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `sent_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


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
INSERT IGNORE INTO `coupons` (`code`, `title`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount`, `coupon_type`, `usage_limit`, `expiry_date`, `status`) VALUES
('WELCOME50', 'New User Special (₹50 Off)', 'Flat ₹50 discount on your first village handcrafted order', 'fixed', 50.00, 299.00, 50.00, 'new_user_only', 5000, '2028-12-31', 'active'),
('FESTIVAL20', 'Festive Sale 20% Off', '20% discount on agricultural tools, pulses and Banarasi achar', 'percentage', 20.00, 499.00, 300.00, 'sale_discount', 2000, '2028-12-31', 'active'),
('SPECIAL100', 'Special Customer Discount', 'Flat ₹100 discount on orders above ₹699', 'fixed', 100.00, 699.00, 100.00, 'one_per_user', 1000, '2028-12-31', 'active'),
('DESIKISAN', 'Artisan Forge 15% Off', '15% discount on high-carbon sickles and forged tools', 'percentage', 15.00, 399.00, 250.00, 'sale_discount', 3000, '2028-12-31', 'active')
ON DUPLICATE KEY UPDATE `status` = 'active';
