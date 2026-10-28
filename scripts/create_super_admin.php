<?php
/**
 * GaunGaliHat Initial Super Administrator Provisioning CLI Script
 * Run from terminal / SSH:
 * php scripts/create_super_admin.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Error: This administrative script can only be executed via the command line (CLI).\n");
}

require_once __DIR__ . '/../config/db.php';

echo "======================================================\n";
echo "  GaunGaliHat Super Administrator Provisioning Tool\n";
echo "======================================================\n\n";

if (!$pdo) {
    echo "[-] Error: Database connection failed. Please verify config.php / config.local.php.\n";
    exit(1);
}

// Ensure platform schema and security columns exist
ensure_platform_schema($pdo);

// Check if a super admin already exists
$super_count = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'super_admin'")->fetchColumn();
if ($super_count > 0) {
    echo "[!] Notice: System already has {$super_count} super administrator account(s).\n";
    echo "Do you want to create an additional super administrator? (y/N): ";
    $confirm = trim(fgets(STDIN));
    if (strtolower($confirm) !== 'y') {
        echo "Aborted.\n";
        exit(0);
    }
}

// Prompt for Details
echo "Full Name: ";
$name = trim(fgets(STDIN));
if (empty($name)) {
    echo "[-] Error: Name cannot be empty.\n";
    exit(1);
}

echo "Username (alphanumeric & underscores, min 3 chars): ";
$username = strtolower(trim(fgets(STDIN)));
if (!preg_match('/^[a-z0-9_]{3,30}$/', $username)) {
    echo "[-] Error: Invalid username format.\n";
    exit(1);
}

// Check username uniqueness
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    echo "[-] Error: Username '{$username}' already exists in database.\n";
    exit(1);
}

echo "Email Address: ";
$email = strtolower(trim(fgets(STDIN)));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "[-] Error: Invalid email address.\n";
    exit(1);
}

// Check email uniqueness
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo "[-] Error: Email '{$email}' is already registered.\n";
    exit(1);
}

echo "Contact Phone: ";
$phone = trim(fgets(STDIN));
if (empty($phone)) {
    $phone = '+91 0000000000';
}

echo "Enter Strong Password (min 10 chars, upper, lower, digit, symbol): ";
$password = trim(fgets(STDIN));

// Validate Password Strength (Min 10 chars, upper, lower, digit, symbol)
if (strlen($password) < 10) {
    echo "[-] Error: Password must be at least 10 characters long.\n";
    exit(1);
}
if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[\W_]/', $password)) {
    echo "[-] Error: Password must contain uppercase, lowercase, numbers, and symbols.\n";
    exit(1);
}

// Hash password securely with Bcrypt
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
$unique_code = 'GGH-SA-' . bin2hex(random_bytes(3));

try {
    $ins = $pdo->prepare("
        INSERT INTO users (
            unique_code, name, username, email, phone, password, role, department, status, email_verified_at, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, 'super_admin', 'Executive Governance', 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
        )
    ");
    $ins->execute([$unique_code, $name, $username, $email, $phone, $hash]);

    echo "\n[+] SUCCESS! Super Administrator created successfully!\n";
    echo "    ID: " . $pdo->lastInsertId() . "\n";
    echo "    Username: {$username}\n";
    echo "    Role: super_admin\n";
    echo "    Email: {$email}\n\n";

} catch (Exception $e) {
    echo "[-] Database Error: " . $e->getMessage() . "\n";
    exit(1);
}
