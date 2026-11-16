-- Migration: 002_add_ss_industries_kalyani_spices.sql
-- S S Industries (M/S S.S. INDUSTRIES) Vendor and Kalyani Masala Authentic Spices

-- 1. Create Spices Category if not exists
INSERT INTO categories (name, hindi_name, slug, description, image, sort_order)
SELECT 'Spices', 'मसाले एवं पारंपरिक स्वाद', 'spices', '100% pure authentic spices, cold-ground culinary blends, whole aromatic seeds, and kitchen essentials by M/S S.S. Industries.', '/uploads/products/kalyani_garam_masala.jpeg', 1
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'spices' OR name = 'Spices');

-- 2. Create Vendor User in users table if not exists
INSERT INTO users (unique_code, name, username, email, phone, password, role, city, pincode, address, business_name, gstin, status)
SELECT 'GGH-VND-8801', 'S S Industries', 'ssindustries', 'kalyanispices88@gmail.com', '7897492100', '$2y$10$wT8m9M3N8P7W7Y6X5V4U3.eY9Z8X7W6V5U4T3S2R1Q0P9O8N7M6L', 'vendor', 'Hardoi', '241126', 'Madhoganj Baghauli Road, Newada, Hardoi, Uttar Pradesh - 241126', 'M/S S.S. INDUSTRIES', '09CHCPK0881J125', 'active'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'ssindustries' OR email = 'kalyanispices88@gmail.com');

-- 3. Create S S Industries Vendor in vendors table if not exists
INSERT INTO vendors (user_id, name, business_name, slug, type, address, village, district, state, pincode, phone, whatsapp, email, story, pan_number, gstin, verified, kyc_status, commission_rate)
SELECT (SELECT id FROM users WHERE username = 'ssindustries' LIMIT 1), 'S S Industries', 'M/S S.S. INDUSTRIES', 'ss-industries', 'MSME Cluster', 'Madhoganj Baghauli Road, Newada, Hardoi, Uttar Pradesh - 241126', 'Newada', 'Hardoi', 'Uttar Pradesh', '241126', '+91 7897492100', '917897492100', 'kalyanispices88@gmail.com', 'M/S S.S. Industries is the official processing, cold-grinding and packaging enterprise behind Kalyani Masala. Based in Hardoi and Sandila, Uttar Pradesh, the enterprise crafts 100% pure authentic spices, regional culinary blends, and aromatic whole seeds with zero artificial colors or adulteration.', 'CHCPK0881J', '09CHCPK0881J125', 1, 'Verified', 4.00
WHERE NOT EXISTS (SELECT 1 FROM vendors WHERE slug = 'ss-industries' OR business_name = 'M/S S.S. INDUSTRIES');
