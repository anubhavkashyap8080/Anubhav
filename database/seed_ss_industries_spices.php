<?php
/**
 * Seeder: S S Industries Vendor & Kalyani Masala Authentic Spices Products
 * Adds:
 * 1. Category: Spices (मसाले एवं पारंपरिक स्वाद)
 * 2. Vendor: S S Industries (M/S S.S. INDUSTRIES) with full verified profile & login user
 * 3. 19 Authentic Kalyani Masala Products with real pricing, descriptions, images, and B2B wholesale MOQ tiers
 */

function seed_ss_industries_kalyani_spices($pdo) {
    if (!$pdo) return;

    $is_sqlite = false;
    try {
        $is_sqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
    } catch (Throwable $t) {}

    // 1. Ensure 'Spices' Category Exists
    try {
        $stmt_cat = $pdo->prepare("SELECT id FROM categories WHERE slug = 'spices' OR name = 'Spices' LIMIT 1");
        $stmt_cat->execute();
        $cat_id = $stmt_cat->fetchColumn();

        if (!$cat_id) {
            $cat_name = 'Spices';
            $cat_hindi = 'मसाले एवं पारंपरिक स्वाद';
            $cat_slug = 'spices';
            $cat_desc = '100% pure authentic spices, cold-ground culinary blends, whole aromatic seeds, and kitchen essentials by M/S S.S. Industries.';
            $cat_img = '/uploads/products/kalyani_garam_masala.jpeg';
            $cat_sort = 1;

            $ins_cat = $pdo->prepare("INSERT INTO categories (name, hindi_name, slug, description, image, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            $ins_cat->execute([$cat_name, $cat_hindi, $cat_slug, $cat_desc, $cat_img, $cat_sort]);
            $cat_id = $pdo->lastInsertId();
        }
    } catch (Throwable $e) {
        $cat_id = 1;
    }

    // 2. Ensure Vendor User Account Exists in `users`
    $user_id = null;
    try {
        $stmt_u = $pdo->prepare("SELECT id FROM users WHERE username = 'ssindustries' OR email = 'kalyanispices88@gmail.com' LIMIT 1");
        $stmt_u->execute();
        $user_id = $stmt_u->fetchColumn();

        if (!$user_id) {
            $u_code = 'GGH-VND-8801';
            $u_name = 'S S Industries';
            $u_uname = 'ssindustries';
            $u_email = 'kalyanispices88@gmail.com';
            $u_phone = '7897492100';
            $u_pass = password_hash('Kalyani@2026', PASSWORD_BCRYPT);
            $u_role = 'vendor';
            $u_city = 'Hardoi';
            $u_pin = '241126';
            $u_addr = 'Madhoganj Baghauli Road, Newada, Hardoi, Uttar Pradesh - 241126';
            $u_bname = 'M/S S.S. INDUSTRIES';
            $u_gst = '09CHCPK0881J125';

            $ins_u = $pdo->prepare("
                INSERT INTO users (
                    unique_code, name, username, email, phone, password, role,
                    city, pincode, address, business_name, gstin, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            $ins_u->execute([
                $u_code, $u_name, $u_uname, $u_email, $u_phone, $u_pass, $u_role,
                $u_city, $u_pin, $u_addr, $u_bname, $u_gst
            ]);
            $user_id = $pdo->lastInsertId();
        }
    } catch (Throwable $e) {
        $user_id = 1;
    }

    // 3. Ensure 'S S Industries' Vendor Exists in `vendors`
    $vendor_id = null;
    try {
        $stmt_v = $pdo->prepare("SELECT id FROM vendors WHERE slug = 'ss-industries' OR business_name = 'M/S S.S. INDUSTRIES' OR name = 'S S Industries' LIMIT 1");
        $stmt_v->execute();
        $vendor_id = $stmt_v->fetchColumn();

        if (!$vendor_id) {
            $v_name = 'S S Industries';
            $v_bname = 'M/S S.S. INDUSTRIES';
            $v_slug = 'ss-industries';
            $v_type = 'MSME Cluster';
            $v_addr = 'Madhoganj Baghauli Road, Newada, Hardoi, Uttar Pradesh - 241126';
            $v_vil = 'Newada';
            $v_dist = 'Hardoi';
            $v_state = 'Uttar Pradesh';
            $v_pin = '241126';
            $v_phone = '+91 7897492100';
            $v_wa = '917897492100';
            $v_email = 'kalyanispices88@gmail.com';
            $v_story = 'M/S S.S. Industries is the official processing, cold-grinding and packaging enterprise behind Kalyani Masala. Based in Hardoi and Sandila, Uttar Pradesh, the enterprise crafts 100% pure authentic spices, regional culinary blends, and aromatic whole seeds with zero artificial colors or adulteration.';
            $v_gst = '09CHCPK0881J125';
            $v_pan = 'CHCPK0881J';

            $ins_v = $pdo->prepare("
                INSERT INTO vendors (
                    user_id, name, business_name, slug, type, address, village, district, state, pincode,
                    phone, whatsapp, email, story, pan_number, gstin, verified, kyc_status, commission_rate
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, 1, 'Verified', 4.00
                )
            ");
            $ins_v->execute([
                $user_id, $v_name, $v_bname, $v_slug, $v_type, $v_addr, $v_vil, $v_dist, $v_state, $v_pin,
                $v_phone, $v_wa, $v_email, $v_story, $v_pan, $v_gst
            ]);
            $vendor_id = $pdo->lastInsertId();
        } else {
            // Ensure proper business name and details are set
            $pdo->prepare("
                UPDATE vendors SET
                    name = 'S S Industries',
                    business_name = 'M/S S.S. INDUSTRIES',
                    slug = 'ss-industries',
                    type = 'MSME Cluster',
                    phone = '+91 7897492100',
                    email = 'kalyanispices88@gmail.com',
                    village = 'Newada',
                    district = 'Hardoi',
                    state = 'Uttar Pradesh',
                    pincode = '241126',
                    address = 'Madhoganj Baghauli Road, Newada, Hardoi, Uttar Pradesh - 241126',
                    gstin = '09CHCPK0881J125',
                    pan_number = 'CHCPK0881J',
                    kyc_status = 'Verified',
                    verified = 1
                WHERE id = ?
            ")->execute([$vendor_id]);
        }
    } catch (Throwable $e) {
        $vendor_id = 1;
    }

    // 4. Products Array (19 Real Products from Kalyani Spices / kalyanispices.com)
    $products = [
        [
            'code' => 'GGH-SP-001',
            'name' => 'Kalyani Premium Garam Masala',
            'hindi_name' => 'कल्याणी शुद्ध गरम मसाला',
            'slug' => 'kalyani-premium-garam-masala',
            'price' => 48.00,
            'original_price' => 65.00,
            'weight_kg' => 0.10,
            'unit' => '100 gm Pack',
            'image' => '/uploads/products/kalyani_garam_masala.jpeg',
            'desc' => 'Kalyani Garam Masala is a signature blend of royal whole spices, roasted gently and cold-ground to preserve essential aromatic oils. Perfect for vegetable curries, dal tadka, rich gravies, paneer dishes, and royal biryanis.',
            'moq' => 20,
            'bulk_price' => 38.00,
            'tier2_qty' => 50,
            'tier2_price' => 34.00,
            'pkg' => 'Master Box of 20 x 100g Pouches'
        ],
        [
            'code' => 'GGH-SP-002',
            'name' => 'Kalyani Pure Chana Dal Besan',
            'hindi_name' => 'कल्याणी 100% शुद्ध चना दाल बेसन',
            'slug' => 'kalyani-pure-chana-dal-besan',
            'price' => 120.00,
            'original_price' => 150.00,
            'weight_kg' => 1.00,
            'unit' => '1 kg Pack',
            'image' => '/uploads/products/kalyani_besan.jpeg',
            'desc' => '100% pure premium chana dal se bana, bina kisi milawat ke. Stone-ground for the finest silky texture and natural aroma, ideal for crispy pakodas, delicious motichoor laddus, kadhi, and dhokla.',
            'moq' => 10,
            'bulk_price' => 98.00,
            'tier2_qty' => 25,
            'tier2_price' => 90.00,
            'pkg' => 'Heavy Corrugated Carton of 10 x 1kg Bags'
        ],
        [
            'code' => 'GGH-SP-003',
            'name' => 'Kalyani Pure Red Chilli Powder (Lal Mirch)',
            'hindi_name' => 'कल्याणी शुद्ध लाल मिर्च पाउडर',
            'slug' => 'kalyani-pure-red-chilli-powder',
            'price' => 200.00,
            'original_price' => 250.00,
            'weight_kg' => 1.00,
            'unit' => '1 kg Pack',
            'image' => '/uploads/products/kalyani_red_chilli.jpeg',
            'desc' => 'Kalyani Red Chilli Powder is made from the finest sun-dried regional red chilies, carefully selected, destemmed, and hygienically processed using advanced cool-grinding technology for deep natural red color and sharp pungency.',
            'moq' => 10,
            'bulk_price' => 165.00,
            'tier2_qty' => 30,
            'tier2_price' => 150.00,
            'pkg' => 'Airtight Food-Grade 1kg Packs'
        ],
        [
            'code' => 'GGH-SP-004',
            'name' => 'Kalyani Pure Turmeric Powder (Haldi Powder)',
            'hindi_name' => 'कल्याणी शुद्ध हल्दी पाउडर',
            'slug' => 'kalyani-pure-turmeric-powder',
            'price' => 220.00,
            'original_price' => 270.00,
            'weight_kg' => 1.00,
            'unit' => '1 kg Pack',
            'image' => '/uploads/products/kalyani_turmeric.jpeg',
            'desc' => 'Pure organic turmeric powder processed from high-curcumin finger roots. Imparts a bright rich golden hue, earthy fragrance, and superior natural antiseptic and immunity-boosting culinary qualities.',
            'moq' => 10,
            'bulk_price' => 180.00,
            'tier2_qty' => 30,
            'tier2_price' => 165.00,
            'pkg' => 'Moisture-Proof 1kg Pouch Carton'
        ],
        [
            'code' => 'GGH-SP-005',
            'name' => 'Kalyani Fresh Ground Coriander Powder (Dhaniya)',
            'hindi_name' => 'कल्याणी शुद्ध धनिया पाउडर',
            'slug' => 'kalyani-fresh-coriander-powder',
            'price' => 170.00,
            'original_price' => 220.00,
            'weight_kg' => 1.00,
            'unit' => '1 kg Pack',
            'image' => '/uploads/products/kalyani_coriander.jpeg',
            'desc' => 'Fresh ground coriander seeds picked from prime agricultural farms. Features a gentle citrus-green aroma, thickens curries and gravies, and adds quintessential Indian comfort flavor to everyday cooking.',
            'moq' => 10,
            'bulk_price' => 138.00,
            'tier2_qty' => 30,
            'tier2_price' => 125.00,
            'pkg' => 'Laminated Foil 1kg Packets'
        ],
        [
            'code' => 'GGH-SP-006',
            'name' => 'Kalyani Roasted Jeera Powder (Cumin Powder)',
            'hindi_name' => 'कल्याणी भुना जीरा पाउडर',
            'slug' => 'kalyani-roasted-jeera-powder',
            'price' => 350.00,
            'original_price' => 420.00,
            'weight_kg' => 1.00,
            'unit' => '1 kg Pack',
            'image' => '/uploads/products/kalyani_jeera.jpeg',
            'desc' => 'A fragrant roasted spice powder that enhances flavor and aids digestion in every dish. Ideal for buttermilk, curd raitas, jaljeera, roasted vegetable dishes, and street chaat.',
            'moq' => 5,
            'bulk_price' => 285.00,
            'tier2_qty' => 20,
            'tier2_price' => 260.00,
            'pkg' => 'Carton of 5 x 1kg Sealed Packs'
        ],
        [
            'code' => 'GGH-SP-007',
            'name' => 'Kalyani Aromatic Clove Powder (Laung Powder)',
            'hindi_name' => 'कल्याणी शुद्ध लौंग पाउडर',
            'slug' => 'kalyani-aromatic-clove-powder',
            'price' => 100.00,
            'original_price' => 130.00,
            'weight_kg' => 0.10,
            'unit' => '100 gm Pack',
            'image' => '/uploads/products/kalyani_clove.jpeg',
            'desc' => 'Warm, aromatic clove powder processed from whole premium cloves. Renowned for its rich essential eugenol oil content, intense aroma, natural medicinal properties, and bakery/culinary uses.',
            'moq' => 20,
            'bulk_price' => 80.00,
            'tier2_qty' => 50,
            'tier2_price' => 72.00,
            'pkg' => 'Box of 20 x 100g Sealed Pouches'
        ],
        [
            'code' => 'GGH-SP-008',
            'name' => 'Kalyani Refreshing Raita Masala',
            'hindi_name' => 'कल्याणी रायता मसाला',
            'slug' => 'kalyani-refreshing-raita-masala',
            'price' => 40.00,
            'original_price' => 55.00,
            'weight_kg' => 0.10,
            'unit' => '100 gm Pack',
            'image' => '/uploads/products/kalyani_raita_masala.jpeg',
            'desc' => 'Khatte-meethe swad aur masaledar tazgi ka perfect blend jo har raita, boondi raita, dahi bhalla, aur chaat ko banaye laajawab. Features rock salt, roasted cumin, and dehydrated mint.',
            'moq' => 25,
            'bulk_price' => 32.00,
            'tier2_qty' => 60,
            'tier2_price' => 28.00,
            'pkg' => 'Display Carton of 25 Units'
        ],
        [
            'code' => 'GGH-SP-009',
            'name' => 'Kalyani Special Sabji Masala',
            'hindi_name' => 'कल्याणी स्पेशल सब्जी मसाला',
            'slug' => 'kalyani-special-sabji-masala',
            'price' => 44.00,
            'original_price' => 60.00,
            'weight_kg' => 0.10,
            'unit' => '100 gm Pack',
            'image' => '/uploads/products/kalyani_sabji_masala.jpeg',
            'desc' => 'Har sabzi ko de swaad aur khushboo ka laajawab touch. Crafted with over 15 hand-selected whole spices to make everyday vegetable stir-fries and dry subzis rich, appetizing, and flavorful.',
            'moq' => 25,
            'bulk_price' => 35.00,
            'tier2_qty' => 60,
            'tier2_price' => 30.00,
            'pkg' => 'Retail Box of 25 x 100g Packs'
        ],
        [
            'code' => 'GGH-SP-010',
            'name' => 'Kalyani Tangy Amchur Powder (Dry Mango)',
            'hindi_name' => 'कल्याणी शुद्ध अमचूर पाउडर',
            'slug' => 'kalyani-tangy-amchur-powder',
            'price' => 35.00,
            'original_price' => 50.00,
            'weight_kg' => 0.10,
            'unit' => '100 gm Pack',
            'image' => '/uploads/products/kalyani_amchur.jpeg',
            'desc' => 'Authentic sour seasoning made from sun-dried raw green mangoes. Delivers a clean fruity tartness to samosa fillings, chutneys, okra bhindi, and lentil curries without adding moisture.',
            'moq' => 20,
            'bulk_price' => 27.00,
            'tier2_qty' => 50,
            'tier2_price' => 24.00,
            'pkg' => 'Pack of 20 x 100g Pouches'
        ],
        [
            'code' => 'GGH-SP-011',
            'name' => 'Kalyani Shahi Paneer Masala',
            'hindi_name' => 'कल्याणी शाही पनीर मसाला',
            'slug' => 'kalyani-shahi-paneer-masala',
            'price' => 52.00,
            'original_price' => 70.00,
            'weight_kg' => 0.10,
            'unit' => '100 gm Pack',
            'image' => '/uploads/products/kalyani_paneer_masala.jpeg',
            'desc' => 'Richly spiced and creamy Paneer Masala that delights your taste buds with authentic Awadhi & Punjabi flavors. Blended with kasuri methi, cardamom, and nutmeg for velvety gravies.',
            'moq' => 20,
            'bulk_price' => 42.00,
            'tier2_qty' => 50,
            'tier2_price' => 37.00,
            'pkg' => 'Carton of 20 x 100g Pouches'
        ],
        [
            'code' => 'GGH-SP-012',
            'name' => 'Kalyani Chatpata Chaat Masala',
            'hindi_name' => 'कल्याणी चटपटा चाट मसाला',
            'slug' => 'kalyani-chatpata-chaat-masala',
            'price' => 30.00,
            'original_price' => 45.00,
            'weight_kg' => 0.10,
            'unit' => '100 gm Pack',
            'image' => '/uploads/products/kalyani_chaat_masala.jpeg',
            'desc' => 'Ek khatti-meethi aur chatpati masale ki mix jo har dish ka swad badha de. Perfect sprinkling for fresh fruit salads, aloo chaat, pakodas, sprouts, sandwiches, and beverages.',
            'moq' => 25,
            'bulk_price' => 24.00,
            'tier2_qty' => 60,
            'tier2_price' => 21.00,
            'pkg' => 'Display Box of 25 Units'
        ],
        [
            'code' => 'GGH-SP-013',
            'name' => 'Kalyani Amritsari Chhole Masala',
            'hindi_name' => 'कल्याणी अमृतसरी छोले मसाला',
            'slug' => 'kalyani-amritsari-chhole-masala',
            'price' => 72.00,
            'original_price' => 95.00,
            'weight_kg' => 0.10,
            'unit' => '100 gm Pack',
            'image' => '/uploads/products/kalyani_chhole_masala.jpeg',
            'desc' => 'Rich and aromatic Chhole Masala, crafted to add authentic North Indian flavor and tempting dark glaze to bhature chhole, chickpea curries, and savory dry chana.',
            'moq' => 20,
            'bulk_price' => 58.00,
            'tier2_qty' => 50,
            'tier2_price' => 52.00,
            'pkg' => 'Carton of 20 x 100g Units'
        ],
        [
            'code' => 'GGH-SP-014',
            'name' => 'Kalyani Royal Meat Masala (Classic)',
            'hindi_name' => 'कल्याणी रॉयल मीट मसाला (क्लासिक)',
            'slug' => 'kalyani-royal-meat-masala-classic',
            'price' => 80.00,
            'original_price' => 105.00,
            'weight_kg' => 0.10,
            'unit' => '100 gm Pack',
            'image' => '/uploads/products/kalyani_meat_masala_classic.jpeg',
            'desc' => 'Rich and aromatic Meat Masala that perfectly enhances the flavor, tenderness, and aroma of all your mutton, lamb, and braised meat preparations.',
            'moq' => 20,
            'bulk_price' => 64.00,
            'tier2_qty' => 50,
            'tier2_price' => 58.00,
            'pkg' => 'Carton of 20 x 100g Packs'
        ],
        [
            'code' => 'GGH-SP-015',
            'name' => 'Kalyani Special Shahi Meat Masala (Gourmet)',
            'hindi_name' => 'कल्याणी स्पेशल शाही मीट मसाला',
            'slug' => 'kalyani-special-shahi-meat-masala',
            'price' => 85.00,
            'original_price' => 110.00,
            'weight_kg' => 0.10,
            'unit' => '100 gm Pack',
            'image' => '/uploads/products/kalyani_shahi_meat_masala.jpeg',
            'desc' => 'Premium gourmet Meat Masala that enhances dishes with rich, aromatic spices, mace, and whole star anise for celebratory royal non-vegetarian cooking.',
            'moq' => 20,
            'bulk_price' => 68.00,
            'tier2_qty' => 50,
            'tier2_price' => 62.00,
            'pkg' => 'Premium Box of 20 Units'
        ],
        [
            'code' => 'GGH-SP-016',
            'name' => 'Kalyani Mughlai Chicken Masala',
            'hindi_name' => 'कल्याणी मुगलाई चिकन मसाला',
            'slug' => 'kalyani-mughlai-chicken-masala',
            'price' => 88.00,
            'original_price' => 115.00,
            'weight_kg' => 0.10,
            'unit' => '100 gm Pack',
            'image' => '/uploads/products/kalyani_chicken_masala.jpeg',
            'desc' => 'Rich and aromatic Chicken Masala, crafted with a master balance of spices, garlic, ginger, and chillies to elevate your chicken curries, bhuna chicken, and roasts.',
            'moq' => 20,
            'bulk_price' => 70.00,
            'tier2_qty' => 50,
            'tier2_price' => 64.00,
            'pkg' => 'Carton of 20 x 100g Packs'
        ],
        [
            'code' => 'GGH-SP-017',
            'name' => 'Kalyani Premium Green Cardamom (Hari Elaichi)',
            'hindi_name' => 'कल्याणी प्रीमियम हरी इलायची',
            'slug' => 'kalyani-premium-green-cardamom-elaichi',
            'price' => 120.00,
            'original_price' => 160.00,
            'weight_kg' => 0.05,
            'unit' => '50 gm Pack',
            'image' => '/uploads/products/kalyani_elaichi.jpeg',
            'desc' => 'Elaichi (Green Cardamom) is a fragrant queen of spices with a sweet aromatic flavor used to enhance chai, biryanis, puddings, kheer, and traditional Indian sweets.',
            'moq' => 20,
            'bulk_price' => 96.00,
            'tier2_qty' => 50,
            'tier2_price' => 88.00,
            'pkg' => 'Zipper Sealed Aroma-Preserving Pouches'
        ],
        [
            'code' => 'GGH-SP-018',
            'name' => 'Kalyani Desi Ajwain (Carom Seeds)',
            'hindi_name' => 'कल्याणी देसी अजवाइन',
            'slug' => 'kalyani-desi-ajwain-carom-seeds',
            'price' => 20.00,
            'original_price' => 30.00,
            'weight_kg' => 0.10,
            'unit' => '100 gm Pack',
            'image' => '/uploads/products/kalyani_ajwain.jpeg',
            'desc' => 'Aromatic carom seeds known for distinctive sharp flavor and natural digestive and medicinal properties. Essential for parathas, poori, samosa dough, and tadka.',
            'moq' => 30,
            'bulk_price' => 16.00,
            'tier2_qty' => 80,
            'tier2_price' => 14.00,
            'pkg' => 'Retail Pack of 30 x 100g'
        ],
        [
            'code' => 'GGH-SP-019',
            'name' => 'Kalyani High-Protein Soya Chunks',
            'hindi_name' => 'कल्याणी हाई-प्रोटीन सोया चंक्स',
            'slug' => 'kalyani-high-protein-soya-chunks',
            'price' => 40.00,
            'original_price' => 55.00,
            'weight_kg' => 0.20,
            'unit' => '200 gm Pack',
            'image' => '/uploads/products/kalyani_soya_chunks.jpeg',
            'desc' => 'Kalyani Soya Chunks are high-protein (over 52% plant protein), 100% vegetarian nuggets, perfect for quick, healthy, and versatile everyday curries and pulao.',
            'moq' => 25,
            'bulk_price' => 32.00,
            'tier2_qty' => 60,
            'tier2_price' => 28.00,
            'pkg' => 'Master Bag of 25 x 200g Packs'
        ]
    ];

    // 5. Insert / Update each product
    $stmt_check = $pdo->prepare("SELECT id FROM products WHERE code = ? OR slug = ? LIMIT 1");
    
    $sql_ins = "
        INSERT INTO products (
            code, vendor_id, category_id, name, hindi_name, slug, price, original_price,
            discount, weight_kg, length_cm, width_cm, height_cm, volumetric_weight,
            stock, stock_status, rating, review_count, image, unit, description,
            seo_title, seo_description, seo_keywords,
            bulk_available, bulk_moq, bulk_price, bulk_tier2_qty, bulk_tier2_price,
            bulk_packaging_desc, bulk_lead_time_days
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            150, 'In Stock', 4.9, 8, ?, ?, ?,
            ?, ?, ?,
            1, ?, ?, ?, ?,
            ?, 2
        )
    ";
    $stmt_ins = $pdo->prepare($sql_ins);

    $sql_up = "
        UPDATE products SET
            vendor_id = ?, category_id = ?, name = ?, hindi_name = ?,
            price = ?, original_price = ?, discount = ?, weight_kg = ?,
            image = ?, unit = ?, description = ?,
            bulk_available = 1, bulk_moq = ?, bulk_price = ?, bulk_tier2_qty = ?, bulk_tier2_price = ?,
            bulk_packaging_desc = ?, bulk_lead_time_days = 2, stock = 150, stock_status = 'In Stock'
        WHERE id = ?
    ";
    $stmt_up = $pdo->prepare($sql_up);

    $inserted = 0;
    $updated = 0;

    foreach ($products as $p) {
        $disc = round((($p['original_price'] - $p['price']) / $p['original_price']) * 100);
        $len = 10; $wid = 10; $ht = 5;
        $vol_w = round(($len * $wid * $ht) / 5000, 2);
        $seo_t = $p['name'] . ' — Buy Online | GanvGaliHat';
        $seo_d = substr($p['desc'], 0, 155);
        $seo_k = 'kalyani masala, authentic spices, ' . strtolower($p['name']) . ', buy spices online, hardoi spices, pure masala';

        $stmt_check->execute([$p['code'], $p['slug']]);
        $existing_id = $stmt_check->fetchColumn();

        if ($existing_id) {
            $stmt_up->execute([
                $vendor_id, $cat_id, $p['name'], $p['hindi_name'],
                $p['price'], $p['original_price'], $disc, $p['weight_kg'],
                $p['image'], $p['unit'], $p['desc'],
                $p['moq'], $p['bulk_price'], $p['tier2_qty'], $p['tier2_price'],
                $p['pkg'], $existing_id
            ]);
            $updated++;
        } else {
            $stmt_ins->execute([
                $p['code'], $vendor_id, $cat_id, $p['name'], $p['hindi_name'], $p['slug'],
                $p['price'], $p['original_price'], $disc, $p['weight_kg'],
                $len, $wid, $ht, $vol_w,
                $p['image'], $p['unit'], $p['desc'],
                $seo_t, $seo_d, $seo_k,
                $p['moq'], $p['bulk_price'], $p['tier2_qty'], $p['tier2_price'],
                $p['pkg']
            ]);
            $inserted++;
        }
    }

    return [
        'category_id' => $cat_id,
        'vendor_id' => $vendor_id,
        'inserted' => $inserted,
        'updated' => $updated,
        'total' => count($products)
    ];
}
