# -*- coding: utf-8 -*-
"""
Append pages table to gaungalihat_database.sql
"""
import os

sql_path = r'C:\Users\HP\.gemini\antigravity\scratch\gaungalihat_database.sql'

pages_sql = """

-- 9. DYNAMIC CMS & BILINGUAL POLICY PAGES
CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) UNIQUE NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `hindi_title` VARCHAR(255) DEFAULT '',
  `content_en` MEDIUMTEXT,
  `content_hi` MEDIUMTEXT,
  `meta_description` VARCHAR(500) DEFAULT '',
  `is_active` TINYINT(1) DEFAULT 1,
  `is_system` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pages` (`slug`, `title`, `hindi_title`, `meta_description`, `is_active`, `is_system`, `content_en`, `content_hi`) VALUES
('about-us', 'About Us — India\\'s Dedicated Rural Marketplace', 'हमारे बारे में — भारत का समर्पित ग्रामीण मार्केटप्लेस', 'GanvGaliHat is India\\'s dedicated rural marketplace bridging ODOP artisans, Women SHGs, and local village makers directly to every home across India.', 1, 1, 'About Us Content', 'हमारे बारे में'),
('privacy-policy', 'Privacy Policy — GanvGaliHat', 'गोपनीयता नीति (Privacy Policy) — GanvGaliHat', 'Official Privacy Policy of GanvGaliHat. Details on data protection, customer security, OTP verification, and secure payments under Indian DPDP Act.', 1, 1, 'Privacy Policy Content', 'गोपनीयता नीति'),
('terms-conditions', 'Terms & Conditions — GanvGaliHat', 'नियम एवं शर्तें (Terms & Conditions) — GanvGaliHat', 'Official Terms of Service for GanvGaliHat rural marketplace. Transparent consumer rights, pricing, and seller guarantees.', 1, 1, 'Terms and Conditions Content', 'नियम एवं शर्तें'),
('shipping-policy', 'Shipping & Return Policy — GanvGaliHat', 'शिपिंग एवं वापसी नीति (Shipping & Return Policy) — GanvGaliHat', 'Pan-India delivery across 19,000+ PIN codes via India Post Speed Post. 24-48h dispatch, 7-day hassle-free replacement policy.', 1, 1, 'Shipping Policy Content', 'शिपिंग नीति'),
('terms-of-supply', 'Terms of Supply & Quality Pledge — GanvGaliHat', 'आपूर्ति के नियम एवं गुणवत्ता वचन (Terms of Supply) — GanvGaliHat', 'GanvGaliHat Terms of Supply: Direct village sourcing standards, 55 HRC steel hardness, 100% unadulterated cold-pressed oils.', 1, 1, 'Terms of Supply Content', 'आपूर्ति के नियम'),
('support', 'Customer Support & Help Desk — GanvGaliHat', 'ग्राहक सहायता एवं संपर्क केंद्र — GanvGaliHat', 'Dedicated customer support desk for GanvGaliHat. Order assistance, artisan inquiries, wholesale inquiries, and live help.', 1, 1, 'Customer Support Content', 'ग्राहक सहायता')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;
"""

if os.path.exists(sql_path):
    with open(sql_path, 'r', encoding='utf-8', errors='ignore') as f:
        existing = f.read()
    if 'CREATE TABLE IF NOT EXISTS `pages`' not in existing:
        with open(sql_path, 'a', encoding='utf-8') as f:
            f.write(pages_sql)
        print("Appended pages table to gaungalihat_database.sql")
    else:
        print("pages table already in gaungalihat_database.sql")
