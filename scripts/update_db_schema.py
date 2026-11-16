with open('config/db.php', 'r', encoding='utf-8') as f:
    content = f.read()

target = "    } catch (Throwable $e_dim_mig) {}"

migration_code = """    } catch (Throwable $e_dim_mig) {}

    // Auto-migrate B2B Wholesale & Bulk columns in products table
    try {
        if ($is_sqlite) {
            $cols = $pdo->query("PRAGMA table_info(products)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('bulk_available', $cols)) {
                $pdo->exec("ALTER TABLE products ADD COLUMN bulk_available INTEGER DEFAULT 0");
            }
            if (!in_array('bulk_moq', $cols)) {
                $pdo->exec("ALTER TABLE products ADD COLUMN bulk_moq INTEGER DEFAULT 10");
            }
            if (!in_array('bulk_price', $cols)) {
                $pdo->exec("ALTER TABLE products ADD COLUMN bulk_price REAL DEFAULT NULL");
            }
            if (!in_array('bulk_tier2_qty', $cols)) {
                $pdo->exec("ALTER TABLE products ADD COLUMN bulk_tier2_qty INTEGER DEFAULT NULL");
            }
            if (!in_array('bulk_tier2_price', $cols)) {
                $pdo->exec("ALTER TABLE products ADD COLUMN bulk_tier2_price REAL DEFAULT NULL");
            }
            if (!in_array('bulk_packaging_desc', $cols)) {
                $pdo->exec("ALTER TABLE products ADD COLUMN bulk_packaging_desc TEXT DEFAULT NULL");
            }
            if (!in_array('bulk_lead_time_days', $cols)) {
                $pdo->exec("ALTER TABLE products ADD COLUMN bulk_lead_time_days INTEGER DEFAULT 3");
            }
        } else {
            $col_ba = $pdo->query("SHOW COLUMNS FROM `products` LIKE 'bulk_available'")->fetch();
            if (!$col_ba) {
                $pdo->exec("ALTER TABLE `products` ADD COLUMN `bulk_available` TINYINT(1) DEFAULT 0 COMMENT '1 = B2B Wholesale Enabled'");
            }
            $col_bm = $pdo->query("SHOW COLUMNS FROM `products` LIKE 'bulk_moq'")->fetch();
            if (!$col_bm) {
                $pdo->exec("ALTER TABLE `products` ADD COLUMN `bulk_moq` INT DEFAULT 10 COMMENT 'Minimum Order Quantity for Bulk'");
            }
            $col_bp = $pdo->query("SHOW COLUMNS FROM `products` LIKE 'bulk_price'")->fetch();
            if (!$col_bp) {
                $pdo->exec("ALTER TABLE `products` ADD COLUMN `bulk_price` DECIMAL(10,2) DEFAULT NULL COMMENT 'Wholesale Price per Unit'");
            }
            $col_t2q = $pdo->query("SHOW COLUMNS FROM `products` LIKE 'bulk_tier2_qty'")->fetch();
            if (!$col_t2q) {
                $pdo->exec("ALTER TABLE `products` ADD COLUMN `bulk_tier2_qty` INT DEFAULT NULL COMMENT 'Tier 2 Bulk Quantity'");
            }
            $col_t2p = $pdo->query("SHOW COLUMNS FROM `products` LIKE 'bulk_tier2_price'")->fetch();
            if (!$col_t2p) {
                $pdo->exec("ALTER TABLE `products` ADD COLUMN `bulk_tier2_price` DECIMAL(10,2) DEFAULT NULL COMMENT 'Tier 2 Bulk Price per Unit'");
            }
            $col_bpd = $pdo->query("SHOW COLUMNS FROM `products` LIKE 'bulk_packaging_desc'")->fetch();
            if (!$col_bpd) {
                $pdo->exec("ALTER TABLE `products` ADD COLUMN `bulk_packaging_desc` VARCHAR(255) DEFAULT NULL COMMENT 'Master Carton Packaging Details'");
            }
            $col_blt = $pdo->query("SHOW COLUMNS FROM `products` LIKE 'bulk_lead_time_days'")->fetch();
            if (!$col_blt) {
                $pdo->exec("ALTER TABLE `products` ADD COLUMN `bulk_lead_time_days` INT DEFAULT 3 COMMENT 'Dispatch SLA in Days'");
            }
        }
    } catch (Throwable $e_bulk_mig) {}

    // Auto-migrate Retailer Business columns in users table
    try {
        if ($is_sqlite) {
            $u_cols = $pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('business_name', $u_cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN business_name TEXT DEFAULT NULL");
            }
            if (!in_array('gstin', $u_cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN gstin TEXT DEFAULT NULL");
            }
            if (!in_array('business_type', $u_cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN business_type TEXT DEFAULT NULL");
            }
            if (!in_array('pan_number', $u_cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN pan_number TEXT DEFAULT NULL");
            }
            if (!in_array('business_address', $u_cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN business_address TEXT DEFAULT NULL");
            }
            if (!in_array('business_city', $u_cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN business_city TEXT DEFAULT NULL");
            }
            if (!in_array('business_state', $u_cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN business_state TEXT DEFAULT NULL");
            }
            if (!in_array('business_pincode', $u_cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN business_pincode TEXT DEFAULT NULL");
            }
        } else {
            $col_bn = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'business_name'")->fetch();
            if (!$col_bn) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `business_name` VARCHAR(255) DEFAULT NULL");
            }
            $col_gst = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'gstin'")->fetch();
            if (!$col_gst) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `gstin` VARCHAR(30) DEFAULT NULL");
            }
            $col_bt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'business_type'")->fetch();
            if (!$col_bt) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `business_type` VARCHAR(100) DEFAULT NULL");
            }
            $col_pan = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'pan_number'")->fetch();
            if (!$col_pan) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `pan_number` VARCHAR(30) DEFAULT NULL");
            }
            $col_ba = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'business_address'")->fetch();
            if (!$col_ba) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `business_address` TEXT DEFAULT NULL");
            }
            $col_bc = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'business_city'")->fetch();
            if (!$col_bc) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `business_city` VARCHAR(100) DEFAULT NULL");
            }
            $col_bs = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'business_state'")->fetch();
            if (!$col_bs) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `business_state` VARCHAR(100) DEFAULT NULL");
            }
            $col_bp = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'business_pincode'")->fetch();
            if (!$col_bp) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `business_pincode` VARCHAR(10) DEFAULT NULL");
            }
        }
    } catch (Throwable $e_user_mig) {}

    // Auto-migrate B2B Wholesale columns in orders table
    try {
        if ($is_sqlite) {
            $o_cols = $pdo->query("PRAGMA table_info(orders)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('order_type', $o_cols)) {
                $pdo->exec("ALTER TABLE orders ADD COLUMN order_type TEXT DEFAULT 'retail'");
            }
            if (!in_array('business_name', $o_cols)) {
                $pdo->exec("ALTER TABLE orders ADD COLUMN business_name TEXT DEFAULT NULL");
            }
            if (!in_array('gstin', $o_cols)) {
                $pdo->exec("ALTER TABLE orders ADD COLUMN gstin TEXT DEFAULT NULL");
            }
        } else {
            $col_ot = $pdo->query("SHOW COLUMNS FROM `orders` LIKE 'order_type'")->fetch();
            if (!$col_ot) {
                $pdo->exec("ALTER TABLE `orders` ADD COLUMN `order_type` VARCHAR(30) DEFAULT 'retail' COMMENT 'retail or wholesale_b2b'");
            }
            $col_obn = $pdo->query("SHOW COLUMNS FROM `orders` LIKE 'business_name'")->fetch();
            if (!$col_obn) {
                $pdo->exec("ALTER TABLE `orders` ADD COLUMN `business_name` VARCHAR(255) DEFAULT NULL");
            }
            $col_ogst = $pdo->query("SHOW COLUMNS FROM `orders` LIKE 'gstin'")->fetch();
            if (!$col_ogst) {
                $pdo->exec("ALTER TABLE `orders` ADD COLUMN `gstin` VARCHAR(30) DEFAULT NULL");
            }
        }
    } catch (Throwable $e_ord_mig) {}"""

assert target in content, "target not found in config/db.php"
content = content.replace(target, migration_code, 1)

with open('config/db.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("config/db.php updated successfully")
