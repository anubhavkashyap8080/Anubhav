# GaunGaliHat Security Hardening Audit & Remediation Log (Items 1–32)

**Project:** GaunGaliHat Rural Artisan Marketplace  
**Domain:** `https://gaungalihat.womenvission.in`  
**Deployment Environment:** cPanel (Apache 2.4+, PHP 8.1+, MySQL 5.7+ / 8.0+)  
**Audit Scope:** Full codebase security audit and production hardening across 5 phases (32 numbered vulnerability remediations).

---

## Executive Summary

A comprehensive security hardening was performed on the GaunGaliHat platform. All 32 security issues identified in the audit were resolved in code without regressions to features, existing database records, or user-facing Hindi/English bilingual UI text.

Before beginning code modifications, a complete project backup was created at `../ganvgalihat_backup_pre_security`.

---

## Detailed Remediation Log by Phase

### Phase 1 — Critical Vulnerabilities (Items 1–8)

1. **Google OAuth Token Cryptographic Signature Verification (`auth/google-callback.php`)**
   - **Vulnerability:** Google ID token was decoded via base64 without cryptographic signature verification, enabling forged logins for arbitrary email addresses.
   - **Fix:** Integrated full verification via Google's official tokeninfo endpoint (`https://oauth2.googleapis.com/tokeninfo`). Verifies digital signature, expiry (`exp`), issuer (`iss` matches `accounts.google.com` or `https://accounts.google.com`), and audience (`aud` matches configured `google_client_id`). Requires `email_verified == true`.
   - **Role Restriction:** Strictly restricted Google authentication to the `'customer'` role. Disallowed administrative, super admin, and vendor accounts from signing in via Google, preventing OAuth-based privilege escalation. Rotates session IDs upon login.

2. **Razorpay Signature & Order Verification (`payments/razorpay_verify.php`, `payments/process.php`)**
   - **Vulnerability:** Payment verification trusted client-supplied IDs without cryptographic HMAC signature validation, order ownership checks, or amount validation.
   - **Fix:** Implemented RFC 2104 HMAC-SHA256 signature verification over `razorpay_order_id . "|" . razorpay_payment_id` against `RAZORPAY_KEY_SECRET`. Enforced strict ownership matching the logged-in user or active session order ID. Recomputes and validates the exact payment amount. Rejects replay attempts against already-paid orders. Creates the Razorpay order server-side via Razorpay REST API and stores `gateway_order_id`.

3. **PayU Response Hash Verification (`payments/payu_callback.php`, `payments/process.php`)**
   - **Vulnerability:** Accepted untrusted GET/POST callbacks without reverse SHA-512 hash verification, and used `rand()` for transaction IDs.
   - **Fix:** Restricts PayU callback strictly to POST requests. Computes and validates the reverse SHA-512 hash: `hash('sha512', "$salt|$status|||||||||||$email|$firstname|$productinfo|$amount|$txnid|$key")`. Verifies received amount matches the order in the database. Replaced `rand()` with `random_int()` and `random_bytes()`.

4. **Credential Isolation & Diagnostic Error Suppression (`config/db.php`, `config/settings.php`)**
   - **Vulnerability:** Database credentials, fallback root accounts without passwords, and test payment gateway keys were hardcoded in web-accessible scripts. Raw PDO connection exceptions leaked server paths and credentials.
   - **Fix:** Replaced hardcoded credentials with `load_platform_config()` which reads credentials outside the web root (`/home/<user>/gaungalihat_config.php`), from `config/config.local.php` (git-ignored), or from environment variables (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`). Created `config.sample.php` and `config/config.sample.php`. Eliminated all root fallback accounts and default API keys. Replaced database connection error dumps with a generic, user-safe HTTP 500 error page (`GGH-DB-ERR-500`) and logged errors to server logs.

5. **Elimination of Auto-Installation & Schema Seeding on Web Requests (`install.php`, `config/db.php`)**
   - **Vulnerability:** `install.php` remained in the web root, and `config/db.php` automatically ran schema and seed scripts during runtime web requests.
   - **Fix:** Completely removed `install.php` from the codebase. Disabled runtime schema execution and auto-seeding in `config/db.php`.

6. **Default Account & Seeded Credential Removal (`database/schema.sql`, `gaungalihat_database.sql`, `admin/import_users.php`, `scripts/create_super_admin.php`)**
   - **Vulnerability:** Seeded default user accounts (`anubhav`, `sunil_ops`, etc.) with predictable passwords existed in database schemas.
   - **Fix:** Purged all default user accounts and hardcoded credentials from `database/schema.sql` and `gaungalihat_database.sql`. Built a standalone CLI utility `scripts/create_super_admin.php` for initial provisioning. Updated `admin/import_users.php` to generate unique cryptographically random passwords (`random_bytes()`) per user and enforce `force_password_change = 1` on first sign-in.

7. **Secure Impersonation Architecture (`admin/impersonate.php`, `includes/functions.php`, headers)**
   - **Vulnerability:** Impersonation allowed GET requests, lacked audit logging, allowed non-super admins to impersonate, and lacked visual indicators.
   - **Fix:** Restricted impersonation strictly to `super_admin`. Converted start and exit actions to POST + CSRF token. Prohibited impersonation of another `super_admin`. Logs every impersonation start and stop to `security_logs` and administrative audit logs. Injected persistent sticky impersonation warning banner (`render_impersonation_banner()`) across customer, admin, and seller portals with a one-click exit button.

8. **User & Role Access Boundaries (`admin/users.php`)**
   - **Vulnerability:** Admins could manipulate super admin accounts. State-changing actions (deletion, status toggle, password reset) accepted GET requests.
   - **Fix:** Role assignment validated against strict whitelists (`customer`, `vendor` for regular admin; `admin`, `super_admin` restricted to super admin). Regular admins cannot view, edit, suspend, or delete super admins. Converted all user deletions, status toggles, and password resets to POST + CSRF token. Added safety check preventing deletion or suspension of the last remaining super admin account. Enforced `is_strong_password()` on password resets.

---

### Phase 2 — Access Control and Sessions (Items 9–15)

9. **Live Support Chat Authorization (`ajax/chat_api.php`)**
   - **Vulnerability:** Support privileges were checked by searching for the substring `'support'` in the user's department text, allowing vendors with store names containing "support" to access chat desks.
   - **Fix:** Restricted administrative and support access strictly to `in_array($curr_user['role'], ['super_admin', 'admin', 'support'], true)`. Removed all departmental string matching. Added CSRF token validation to `admin_reply` and `admin_toggle_status`.

10. **Email Verification & Cryptographic Password Reset Flow (`register.php`, `seller-register.php`, `account.php`, `verify-email.php`, `forgot-password.php`, `reset-password.php`)**
    - **Vulnerability:** Unverified accounts could see orders and tickets matched by email. Password reset lacked expiring single-use cryptographic tokens.
    - **Fix:** Implemented `send_email_verification_link()` triggered upon registration for customers and sellers. Created `verify-email.php` to validate tokens and record `email_verified_at`. In `account.php`, orders and tickets match strictly by `user_id`, or by email *only if* `email_verified_at IS NOT NULL`. Built `forgot-password.php` and `reset-password.php` with 32-byte secure random tokens hashed via SHA-256 into `password_resets` table (1-hour expiry, single-use, rate-limited to 3 requests per 15 minutes, constant-time responses).

11. **Order Access Control & Cryptographic Identifiers (`order-success.php`, `payments/process.php`)**
    - **Vulnerability:** Order success pages were accessible by guessing auto-increment order IDs. Insecure `rand()` was used for tracking numbers and orders.
    - **Fix:** Added `order_access_token` (48-character cryptographically secure hex). Access to `order-success.php` requires active login matching the order's `user_id`, recent session checkout verification, administrative role, or the valid `order_access_token` query parameter. Replaced all occurrences of `rand()` with `random_int()` and `random_bytes()` across order numbers, India Post consignment tracking numbers, and ticket numbers.

12. **Seller Order Scope & Status Integrity (`seller/orders.php`)**
    - **Vulnerability:** Vendors could alter global order statuses and update order items belonging to other sellers.
    - **Fix:** Enforced strict status whitelist (`['Confirmed', 'Processing', 'Dispatched', 'Delivered', 'Cancelled']`). Sellers can only update the `item_status` in `order_items` for items associated with their own `vendor_id`. The global order status only transitions to Delivered or Cancelled if all vendor items within that order reach that status.

13. **Session Hardening & Immediate Privilege Invalidation (`config/db.php`, `includes/auth_check.php`, `includes/functions.php`)**
    - **Vulnerability:** Role changes or account suspensions did not take effect until re-login. Session cookies lacked secure flags. Admin sessions had no idle timeout.
    - **Fix:** Configured secure cookie attributes before `session_start()`: `httponly=1`, `secure=($is_https)`, `samesite=Lax`, `use_strict_mode=1`, `use_only_cookies=1`. `get_logged_in_user()` queries the database on each request; if a user is suspended or deleted, their session is destroyed immediately. Implemented 30-minute idle session timeout for admin panels (`check_admin_idle_timeout()`). Rotates session IDs via `session_regenerate_id(true)` upon login, registration, and privilege escalation.

14. **Trusted Reverse Proxy IP Resolution (`includes/functions.php`)**
    - **Vulnerability:** `get_client_ip()` trusted `HTTP_CF_CONNECTING_IP` and `HTTP_X_FORWARDED_FOR` from any client, allowing IP spoofing.
    - **Fix:** Configured `get_client_ip()` to use `REMOTE_ADDR` by default. Only trusts `HTTP_CF_CONNECTING_IP` if `REMOTE_ADDR` belongs to Cloudflare's published official IPv4 (`173.245.48.0/20`, `103.21.244.0/22`, `104.16.0.0/13`, etc.) and IPv6 CIDR blocks via CIDR subnet matching.

15. **Authentication Throttling & Optional Two-Factor Authentication (`login.php`, `includes/functions.php`)**
    - **Vulnerability:** Login endpoints lacked CSRF protection and account/IP throttling.
    - **Fix:** Added CSRF protection to all customer, seller, and admin login forms. Created `login_attempts` table tracking failed logins by identifier and IP. Implemented progressive exponential delay throttling (blocks after 5 failures in 15 minutes). Implemented native RFC 6238 TOTP two-factor authentication verification (`verify_totp_code()`) for `admin` and `super_admin` accounts. Triggers owner security alerts upon 3+ failed admin login attempts.

---

### Phase 3 — CSRF, Input and Output (Items 16–23)

16. **Centralized CSRF Architecture (`admin/admin_header.php`, `seller/seller_header.php`, `includes/header.php`, `assets/js/main.js`, AJAX handlers)**
    - **Vulnerability:** CSRF protection was inconsistently applied. AJAX requests and administrative actions were susceptible to cross-origin forgery.
    - **Fix:** Embedded `<meta name="csrf-token" content="...">` centrally in all layout headers. Enforced automatic `csrf_protect()` on all POST requests in `admin_header.php` and `seller_header.php`. Enhanced `csrf_protect()` to return HTTP 403 JSON on AJAX/fetch requests and same-origin redirects for browser submissions (blocking open-redirects via HTTP Referer). Updated `main.js`, `cart_actions.php`, `wishlist_actions.php`, and live chat AJAX scripts to include `X-CSRF-Token` headers and `csrf_token` form fields.

17. **Hardened File Uploads (`includes/functions.php`, `uploads/.htaccess`)**
    - **Vulnerability:** SVG files were accepted (risk of Stored XSS via embedded `<script>` or `<svg onload>`), uploads were not re-encoded, and `.htaccess` lacked Apache 2.4 authorization directives.
    - **Fix:** Removed SVG from allowed file types (allowed: JPEG, PNG, WebP, GIF). Enforced server-side image re-encoding via PHP GD (`imagecreatefromjpeg`/`png`/`webp`/`gif`), stripping all EXIF metadata, ICC profiles, and malicious comments. Enforced 4000x4000 dimension limits. Preserved randomized file naming (`bin2hex(random_bytes(16))`). Updated `uploads/.htaccess` with Apache 2.4 directives (`<IfModule mod_authz_core.c> Require all denied </IfModule>`) blocking execution of all PHP scripts (`.php`, `.phtml`, `.phar`, `.inc`, `.cgi`) and disabled the PHP engine (`php_flag engine off`).

18. **Strict DOMDocument HTML Sanitizer (`includes/functions.php`)**
    - **Vulnerability:** `sanitize_custom_html()` used regex replacements that could be bypassed with nested tags, unknown tags, or malformed attributes.
    - **Fix:** Rewrote `sanitize_custom_html()` using PHP's native `DOMDocument` parser with a strict element and attribute allowlist. Strips `<script>`, `<style>`, `<base>`, `<form>`, `<svg>`, `<math>`, `<iframe>`, `<object>`, `<embed>`, etc. Validates URL attributes (`href`, `src`) against `http:`, `https:`, `mailto:`, and `tel:` protocols, strictly stripping `javascript:`, `vbscript:`, and `data:` schemes (including whitespace/entity-encoded variants like `jav&#x09;ascript:`). Removes all `on*` inline event handlers.

19. **Frontend XSS Prevention (`assets/js/main.js`)**
    - **Vulnerability:** Live search and UI components injected raw server response strings via `innerHTML`.
    - **Fix:** Replaced `innerHTML` manipulations in `main.js` with safe DOM element construction (`document.createElement`, `textContent`) and strict `escapeHtml()` helper calls. Validated search result URLs using `sanitizeUrl()` ensuring they start with `/` and do not start with `//` (preventing protocol-relative redirects). Protected search input echo from reflected XSS.

20. **Seller Product Input & Image URL Validation (`seller/products.php`, `includes/functions.php`)**
    - **Vulnerability:** Product inputs lacked numeric boundary checks, and image URLs allowed arbitrary domains.
    - **Fix:** Added `is_valid_product_image_url()` in `includes/functions.php`. Enforces that product images either originate from local `/uploads/` (checked against traversal attacks) or belong to an HTTPS allowlist (`images.unsplash.com`, `res.cloudinary.com`, `i.imgur.com`, `cdn.pixabay.com`, or the official site host). Clamped numeric fields: `price >= 0`, `original_price >= price`, `stock >= 0`, `weight_kg >= 0.01`, and `discount` strictly clamped between 0 and 100%. Converted stock toggling and product deletions from GET to POST + CSRF.

21. **CSV Formula Injection Neutralization (`admin/export_users.php`, `admin/export_products.php`, `seller/products.php`, `admin/import_users.php`)**
    - **Vulnerability:** Exported user and product data could execute arbitrary formulas (`=cmd|...`, `@SUM...`) when opened in Excel or LibreOffice Calc. Import scripts lacked strict role whitelisting.
    - **Fix:** Applied `escape_csv_cell()` to every cell in `export_users.php`, `export_products.php`, and `seller/products.php`. Prefixes cells starting with `=`, `+`, `-`, `@`, `\t`, or `\r` with a single quote (`'`). Validated CSV user import roles against allowed lists (regular admin can only import customers and vendors; super admin can import staff). Validated email formats.

22. **Password Policy Enforcement (`includes/functions.php`, registration, user management, and password resets)**
    - **Vulnerability:** Short and common passwords were accepted.
    - **Fix:** Standardized `is_strong_password()` requiring a minimum length of 10 characters and rejecting common passwords/patterns (`password123`, `admin@12345`, `qwertyuiop`, etc.) including normalized alphanumeric variants. Applied across customer registration, seller registration, admin user creation/edits, and password reset forms.

23. **Input Sanitization vs. Output Escaping (`includes/functions.php`, templates)**
    - **Vulnerability:** Storing HTML entities in the database led to double-encoding issues and data corruption.
    - **Fix:** Refactored `clean_input()` to perform `trim()` and string normalization without applying `htmlspecialchars()` at input time. Added `e()` helper: `htmlspecialchars($data, ENT_QUOTES, 'UTF-8', false)` (`$double_encode = false`) to escape raw characters on output while preventing double-encoding on legacy stored records. Audited templates across customer, seller, and admin portals.

---

### Phase 4 — Orders, Payments, Business Logic (Items 24–27)

24. **Checkout Atomic Stock Verification & Rate Limiting (`payments/process.php`, `checkout.php`, `contact.php`, `register.php`, `seller-register.php`, `ajax/chat_api.php`, `includes/functions.php`)**
    - **Vulnerability:** Product prices were calculated from client session state without re-verifying against the database. Stock was decremented without row-level locking (`SELECT ... FOR UPDATE`), permitting race conditions and overselling. `add_to_cart()` treated stock 0 as 1. Checkout and public forms lacked CAPTCHA and rate limits.
    - **Fix:**
      - Whitelisted allowed payment methods: `['cod', 'razorpay', 'payu']`.
      - In `payments/process.php`, moved all price calculations and subtotal computations strictly inside `$pdo->beginTransaction()`.
      - Locked each product row using `SELECT ... FOR UPDATE`, validated stock availability, and atomically decremented stock. Orders fail and roll back cleanly if inventory is insufficient.
      - Fixed `add_to_cart()` and `update_cart_qty()` in `includes/functions.php` to immediately reject adding or updating out-of-stock items (stock <= 0).
      - Added persistent action rate limiting (`check_rate_limit()`) backed by MySQL `rate_limits` table:
        - Checkout: max 10 attempts per IP / 5 attempts per phone per 15 minutes.
        - Contact form: max 5 submissions per IP / 3 per phone per 15 minutes.
        - Customer & Seller registration: max 5 registrations per IP / 3 per phone per hour.
        - Chat initialization: max 10 chat session creations per IP per 15 minutes.
      - Integrated Cloudflare Turnstile CAPTCHA verification (`verify_turnstile_captcha()` and `render_turnstile_widget()`) across checkout, contact form, and registration pages.

25. **Atomic Coupon Usage & Per-User Constraints (`payments/process.php`, `includes/functions.php`, `database/migration_security_hardening.sql`)**
    - **Vulnerability:** Coupon usage updates were vulnerable to race conditions (could exceed `usage_limit`), per-user limits were not enforced at checkout, and coupons were not re-validated against current subtotal inside the transaction.
    - **Fix:**
      - Re-validates coupons inside the database transaction with `SELECT ... FOR UPDATE`.
      - Checks expiry date, minimum order amount against freshly computed DB subtotal, and total usage count.
      - Enforces per-user constraints (`one_per_user`, `new_user_only`) against the `coupon_usages` table by `user_id`, `customer_email`, and `customer_phone`.
      - Atomically updates coupon usage with concurrency guard: `UPDATE coupons SET used_count = used_count + 1 WHERE id = ? AND used_count < usage_limit`.
      - Logs coupon usage records including `customer_email`, `customer_phone`, and `discount_amount`.

26. **Canonical Domain Enforcement & Hostname Sanitization (`config/settings.php`, `robots.php`, `sitemap.php`, `admin/merchant_feed.php`, `admin/social_auth.php`, `feeds/google_merchant.php`)**
    - **Vulnerability:** Reliance on `$_SERVER['HTTP_HOST']` enabled HTTP Host header attacks and cache poisoning.
    - **Fix:** Audited and replaced unvalidated instances of `$_SERVER['HTTP_HOST']` across robots, sitemaps, social auth, and merchant feeds with canonical `SITE_URL`. Enforced automatic HTTP 301 Permanent Redirect to `SITE_URL` in `config/settings.php` if an incoming request arrives with a mismatched host (while allowing `localhost` in local development).

27. **Production Error Handling & PHP Configuration (`.user.ini`, `.htaccess`, `config/db.php`)**
    - **Vulnerability:** Default PHP settings could expose notices and stack traces to visitors.
    - **Fix:** Created `.user.ini` and configured root `.htaccess` with `display_errors = Off`, `display_startup_errors = Off`, and `log_errors = On`. Generic 500 error page (`GGH-DB-ERR-500`) handles unhandled database exceptions without exposing connection details.

---

### Phase 5 — Server & Deployment Hardening (Items 28–32)

28. **Root `.htaccess` Enterprise Hardening (`.htaccess`)**
    - **Vulnerability:** Internal directories and configuration files were not uniformly protected across Apache 2.2 and 2.4 syntax. Modern security headers were missing.
    - **Fix:**
      - Blocked direct web access to internal directories via rewrite rules: `/config`, `/includes`, `/database`, `/data`, `/functions`, `/src`, `/scripts`, and `/.git`.
      - Denied access to sensitive file patterns: `*.sql`, `*.sqlite`, `*.db`, `*.env`, `*.log`, `*.md`, `*.json`, `*.ts`, `*.tsx`, `*.lock`, `*.ini`, and `*.sample`.
      - Configured modern HTTP security response headers:
        - `Strict-Transport-Security: max-age=31536000; includeSubDomains`
        - `X-Content-Type-Options: nosniff`
        - `X-Frame-Options: SAMEORIGIN`
        - `Referrer-Policy: strict-origin-when-cross-origin`
        - `Permissions-Policy: camera=(), microphone=(), payment=(self)`
        - Removed deprecated `X-XSS-Protection` header.
        - Implemented Content Security Policy (`CSP`) restricting scripts, frames, and connect endpoints.

29. **Production Repository Cleanup**
    - **Vulnerability:** Unused development artifacts from past React/Vite/Pages experiments remained in the web root.
    - **Fix:** Removed unused folders: `src/`, `functions/`, `public/`, `database/migrations/`, and development database `data/gaungalihat.sqlite`. (All files are preserved in the backup directory).

30. **API Secrets Protection (`admin/integrations.php`, `payments/process.php`)**
    - **Vulnerability:** Gateway API secrets could be exposed in client markup or overwritten by masked asterisks.
    - **Fix:** Key secrets (`razorpay_key_secret`, `payu_merchant_salt`) are masked in admin UI inputs. If an administrator saves the form without changing the masked value, the stored secret remains untouched. Secret keys are never returned in client JSON or HTML responses.

31. **Robots.txt & XML Sitemap Domain Alignment (`robots.php`, `sitemap.php`)**
    - **Vulnerability:** Fallback domains defaulted to `'ganvgalihat.com'` instead of the active production domain.
    - **Fix:** Aligned `robots.php` and `sitemap.php` with `SITE_URL` (`https://gaungalihat.womenvission.in`).

32. **Security Audit Logging & Real-time Owner Alerts (`includes/functions.php`, `admin/users.php`, `login.php`)**
    - **Vulnerability:** Administrative privilege escalation and brute force attacks did not alert system owners.
    - **Fix:** Connected `send_owner_security_alert()` to:
      - Administrative role escalation: fires an alert whenever an existing user's role is upgraded to `admin` or `super_admin`.
      - Administrative account creation: fires an alert whenever a new `admin` or `super_admin` is created.
      - Brute-force attacks: fires an alert on 3 or more consecutive failed login attempts on any admin/super_admin account.

---

## Complete List of Modified & Created Files

### Modified Files:
- `.gitignore` — Ignore production credentials and config files.
- `.htaccess` — Hardened directory blocks, file protections, HSTS, CSP, and PHP flags.
- `account.php` — Enforced verified email order/ticket matching and password policy.
- `admin/admin_header.php` — Injected CSRF meta tag, impersonation banner, and automatic POST CSRF protection.
- `admin/export_products.php` — CSV formula injection neutralization with `escape_csv_cell()`.
- `admin/export_users.php` — CSV formula injection neutralization with `escape_csv_cell()`.
- `admin/impersonate.php` — Restricted to super admin, POST + CSRF required, audit logging, blocked super admin impersonation.
- `admin/import_users.php` — Unique random credentials, force password change, role whitelisting.
- `admin/live_chat.php` — Added CSRF token in AJAX headers.
- `admin/merchant_feed.php` — Replaced `$_SERVER['HTTP_HOST']` with `SITE_URL`.
- `admin/social_auth.php` — Replaced `$_SERVER['HTTP_HOST']` with `SITE_URL`.
- `admin/users.php` — Role whitelisting, POST + CSRF conversions, last super admin guard, owner alerts on escalation.
- `ajax/cart_actions.php` — Central CSRF protection and stock validation.
- `ajax/chat_api.php` — Role verification, CSRF protection, rate limiting on session start.
- `ajax/wishlist_actions.php` — Central CSRF protection.
- `assets/js/main.js` — XSS prevention in search results, CSRF token header in AJAX requests.
- `auth/google-callback.php` — Google ID token cryptographic signature verification, customer-only role restriction.
- `checkout.php` — Injected CSRF token and Turnstile CAPTCHA widget.
- `config/db.php` — Externalized credentials, suppressed DB diagnostics, generic 500 error page, session security parameters.
- `config/settings.php` — Removed hardcoded keys, enforced canonical domain 301 redirect.
- `contact.php` — Added CSRF check, IP/phone rate limiting, Turnstile verification, and widget.
- `database/schema.sql` — Purged default accounts, added `rate_limits` and `coupon_usages` tables.
- `feeds/google_merchant.php` — Replaced `$_SERVER['HTTP_HOST']` with `SITE_URL`.
- `gaungalihat_database.sql` — Purged default accounts and passwords.
- `includes/auth_check.php` — Re-read user status on each request, immediate suspension enforcement, 30-min admin idle timeout.
- `includes/functions.php` — Added `check_rate_limit()`, `verify_turnstile_captcha()`, `render_turnstile_widget()`, `is_valid_product_image_url()`, `escape_csv_cell()`, fixed `add_to_cart()` stock bug, DOMDocument HTML sanitizer, Cloudflare IP range check.
- `includes/header.php` — Injected CSRF meta tag and impersonation banner.
- `includes/live_chat_widget.php` — Injected CSRF token in chat AJAX requests.
- `login.php` — CSRF protection, IP/account throttling, TOTP 2FA, failed login owner alerts.
- `order-success.php` — Strict authorization checks via session, login ownership, or `order_access_token`.
- `payments/payu_callback.php` — POST-only restriction, SHA-512 reverse hash verification, amount check.
- `payments/process.php` — Whitelisted payment methods, atomic stock lock & decrement (`SELECT ... FOR UPDATE`), coupon atomic usage, rate limiting, Turnstile verification.
- `payments/razorpay_verify.php` — HMAC-SHA256 signature verification, user/session ownership check, amount match.
- `product.php` — Safe JSON encoding in analytics scripts, output escaping.
- `register.php` — CSRF protection, password policy, rate limiting, Turnstile verification, email verification dispatch.
- `robots.php` — Aligned with `SITE_URL`.
- `seller-register.php` — CSRF protection, password policy, rate limiting, Turnstile verification, email verification dispatch.
- `seller/orders.php` — Status whitelisting, seller item-status scoping, CSV formula injection protection.
- `seller/products.php` — Numeric field validation, image URL whitelisting, converted stock toggle and delete to POST + CSRF, CSV export neutralization.
- `seller/seller_header.php` — Injected CSRF meta tag, impersonation banner, and automatic POST CSRF protection.
- `sitemap.php` — Aligned with `SITE_URL`.
- `uploads/.htaccess` — Apache 2.4 deny directives, disabled PHP execution.

### Newly Created Files:
- `.user.ini` — Production PHP configuration (`display_errors = Off`, `log_errors = On`, cookie security).
- `config.sample.php` & `config/config.sample.php` — Clean template for credentials outside web root.
- `database/migration_security_hardening.sql` — Idempotent migration script for cPanel MySQL.
- `forgot-password.php` — Rate-limited password reset request endpoint with SHA-256 hashed tokens.
- `reset-password.php` — Token consumption endpoint enforcing strong password policy.
- `verify-email.php` — Email verification link handler.
- `scripts/create_super_admin.php` — Secure CLI script to provision the first super admin.
- `tests/test_security_hardening.php` — Standalone test suite verifying security controls (100% pass rate).

### Deleted Files:
- `install.php` — Removed installer.
- `src/` (entire folder) — Unused React/Vite development code.
- `functions/` (entire folder) — Unused Cloudflare Pages functions.
- `public/` (entire folder) — Unused Vite build directory.
- `database/migrations/` (entire folder) — Development migrations.
- `data/gaungalihat.sqlite` — Stale development SQLite database.

---

## Database Migration Instructions

Execute the idempotent migration script on your cPanel MySQL database:

1. Log into **cPanel** > **phpMyAdmin** (or connect via MySQL CLI).
2. Select your database (e.g., `womenvis_gaungalihat`).
3. Import or execute the contents of:  
   `database/migration_security_hardening.sql`

This script safely creates the necessary tables (`password_resets`, `login_attempts`, `rate_limits`, `coupon_usages`) and adds missing columns (`email_verified_at`, `email_verification_token`, `two_factor_secret`, `order_access_token`, `gateway_order_id`, `item_status`, etc.) if they do not already exist.

---

## User Action Checklist

To complete production setup after deploying this hardened codebase:

1. **Rotate Credentials & Move Config Outside Web Root:**
   - Copy `config.sample.php` to your user home directory on cPanel (e.g. `/home/username/gaungalihat_config.php`).
   - Fill in your database name, user, and strong database password.
   - Delete any local credentials from `config/config.local.php`.

2. **Configure API Keys:**
   - Update Razorpay Key ID and Secret in `/home/username/gaungalihat_config.php` or in Admin Integrations.
   - Update PayU Key and Salt if PayU is used.
   - Set Cloudflare Turnstile Site Key & Secret Key in environment variables or in Admin Site Settings.

3. **Provision Initial Super Admin:**
   - Run via SSH/CLI:
     ```bash
     php scripts/create_super_admin.php
     ```
   - Enter your desired admin email, username, and strong password.

4. **Invalidate Existing Sessions & Reset Passwords:**
   - Ask existing vendors/staff to change their passwords using the new "Forgot Password" link.
   - If migrating from old seeded accounts, remove any lingering test accounts from phpMyAdmin.

---

## Follow-Up Production Hardening (Items 1–12)

### 1. Payments Fail-Closed Architecture
- **`payments/process.php`**: If Razorpay server-side order creation fails or returns no id, checkout immediately halts: marks pending order as cancelled, restores reserved inventory, logs the failure, and displays a user-safe error message. Never proceeds without a valid `gateway_order_id`.
- **`payments/razorpay_verify.php`**: Strictly requires both `gateway_order_id` and `razorpay_signature`. Computes HMAC-SHA256 signature using `hash_equals()`. Fails closed if the Razorpay REST API verification fails, returns non-200 HTTP status, or confirms status other than `captured` or `authorized`. Amount must be `>= order.total_amount`.
- **Replay Protection & Secret Log Stripping**: Both `razorpay_verify.php` and `payu_callback.php` perform conditional SQL updates (`WHERE id = ? AND payment_status != 'completed'`) and check `rowCount() === 0` to block transaction replays. Stripped all "Expected" and "Calculated" secret signatures and hashes from audit logs and error logs.

### 2. Universal CSRF Tokens in Admin & Seller Forms
- Embedded `<?= csrf_field() ?>` inside all POST `<form>` tags across:
  - `admin/blogs.php`, `admin/categories.php`, `admin/coupons.php`, `admin/merchant_feed.php`, `admin/orders.php`, `admin/products.php`, `admin/seo_settings.php`, `admin/site_customizer.php`, `admin/site_settings.php`, `admin/social_auth.php`, `admin/tickets.php`
  - `seller/profile.php`
  - `cart.php`
- Enforced `X-CSRF-Token` headers on all fetch and XHR calls via `<meta name="csrf-token">`.
- Preserved automatic `csrf_protect()` in `admin_header.php` and `seller_header.php`.

### 3. Elimination of State-Changing GET Endpoints
- Converted all state mutations to POST + CSRF token + JavaScript confirmation:
  - `admin/products.php`: `delete_id`, `toggle_featured`
  - `admin/coupons.php`: `delete_id`, `toggle_status`
  - `admin/blogs.php`: `delete_id`, `toggle_status`
  - `admin/categories.php`: `delete_id`
  - `admin/tickets.php`: `set_status`
  - `admin/seo_redirects.php`: `delete_redirect`, `resolve_404`
  - `seller/products.php`: `toggle_stock`, `delete_product`
- Added explicit HTTP 405 Method Not Allowed blocks for any incoming GET requests targeting these actions.
- Added comprehensive audit log entries for every deletion action.

### 4. Deploy Cleanup & One-Time Setup Admin Script
- Removed `data/backups/*.sqlite`, `gaungalihat_database.sql`, `database/schema.sql`, and `database/migration_*.sql` from the deployable repository into a private archive outside the web root (`ganvgalihat_private_archive`).
- Removed SQLite fallback block in `config/db.php` so production database connectivity fails securely without exposing diagnostics or creating local file databases.
- Hardened `setup_admin.php`: Enforces a 30-minute validity window based on `filemtime(__FILE__)`, requires `SETUP_SECRET` via `?key=` compared with `hash_equals()`, locks immediately if a super admin already exists, and self-deletes (`@unlink(__FILE__)`) upon successful account creation.

### 5. HTTPS Enforcement, Security Headers & Inline Script Roadmap
- **`.htaccess`**:
  - Enforced 301 HTTPS redirect using canonical `SITE_URL` host:
    ```apache
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://gaungalihat.womenvission.in%{REQUEST_URI} [L,R=301]
    ```
  - Strengthened Content-Security-Policy (CSP): Added `frame-ancestors 'self'` and `form-action 'self' https://secure.payu.in https://test.payu.in`.
  - Removed `cdn.tailwindcss.com` from CSP.
- **Roadmap for Removing `unsafe-inline` and `unsafe-eval`**:
  - *Current Blocking Elements:* Inline onclick handlers in modals (`toggleModal()`), dynamic chart initialization in `admin/index.php`, and dynamic search filter handlers in `assets/js/main.js`.
  - *Planned Migration Phase:*
    1. Implement PHP nonces generated per request (`bin2hex(random_bytes(16))`) and injected into the CSP header (`script-src 'self' 'nonce-...'`).
    2. Move all inline JavaScript block logic into external versioned bundles (`/assets/js/admin_dashboard.js`, `/assets/js/modals.js`).
    3. Replace inline `onclick="func()"` attributes with `addEventListener` attached to data attributes (`data-action="open-modal"`).

### 6. Domain Alignment & Email Deliverability (SPF / DKIM / DMARC)
- Replaced all legacy hardcoded references to `ganvgalihat.com` with the canonical host resolved from `SITE_URL` across:
  - `includes/functions.php`, `forgot-password.php`, `robots.php`, `robots.txt`, `sitemap.php`, `includes/header.php`, `admin/seo_settings.php`, `admin/site_settings.php`, `admin/site_customizer.php`.
- **DNS Records Guidance for `gaungalihat.womenvission.in`:**
  - **SPF Record (TXT):**
    ```
    v=spf1 include:_spf.google.com include:relay.mailchannels.net ~all
    ```
    *(Adjust according to your cPanel hosting SMTP provider or Google Workspace).*
  - **DKIM Record (TXT):**
    Generate a 2048-bit DKIM key inside **cPanel > Email Deliverability** > click **Manage** next to `womenvission.in`, and publish the provided TXT record under `default._domainkey.gaungalihat.womenvission.in`.
  - **DMARC Record (TXT):**
    ```
    _dmarc.gaungalihat.womenvission.in TXT "v=DMARC1; p=quarantine; sp=quarantine; rua=mailto:dmarc-reports@womenvission.in; pct=100"
    ```

### 7. Self-Hosted Assets & GSC Token Sanitization
- Removed runtime dependency on `cdn.tailwindcss.com`. Built a static, minified production CSS bundle (`/assets/css/tailwind.css`, ~71 KB) compiled via Tailwind CLI with all scanned templates and custom brand color configurations.
- Self-hosted pinned third-party vendor libraries in `/assets/vendor/`:
  - `assets/vendor/lucide.min.js` (pinned v0.469.0)
  - `assets/vendor/chart.umd.min.js` (pinned v4.4.7)
- In `includes/header.php`, Google Search Console verification tag is strictly regex-validated (`/^[A-Za-z0-9_-]{20,100}$/`) and escaped with `htmlspecialchars()`. Clamped mobile (20–80px) and desktop (24–120px) logo heights to prevent layout breaks.

### 8. Secure Avatar Uploads & URL Validation
- Replaced custom avatar file upload code in `account.php` and `seller/profile.php` with `secure_upload_image()`, ensuring server-side image re-encoding, EXIF stripping, and safe file extensions.
- Restricted `avatar_url_input` and seller product image URLs using `is_valid_product_image_url()` (allowing only local `/uploads/` paths or whitelisted HTTPS CDNs like Unsplash/Cloudinary).

### 9. Robots.txt Security Alignment
- Removed all `Disallow` directives revealing internal directories (`/admin/`, `/seller/`, `/data/`, `/database/`, `/includes/`) and removed `Allow: /uploads/`.
- Access to sensitive administrative and system paths is strictly blocked at the web server level via `.htaccess` (returning HTTP 403) rather than disclosed via `robots.txt`.

### 10. Secrets Priority, Field Encryption & Session Invalidation
- **Secrets Priority (`config/settings.php`):**
  - Config and environment variables take strict precedence over `site_settings` for `RAZORPAY_KEY_SECRET`, `PAYU_MERCHANT_SALT`, and `GOOGLE_CLIENT_SECRET`.
  - Plaintext secrets are no longer persisted to the database. The admin integrations hub and social auth settings display only masked values (`mask_secret()`).
- **Vendor Banking Details Encryption:**
  - Encrypted vendor `bank_account`, `bank_ifsc`, and `upi_id` fields using Libsodium authenticated symmetric encryption (`sodium_crypto_secretbox`) with random nonces.
  - Masked values in seller and admin forms (`mask_sensitive_value()`).
- **Session Invalidation on Password Change:**
  - Upon user or vendor password change/reset, `password_changed_at` is updated to `CURRENT_TIMESTAMP`, the active session ID is regenerated (`session_regenerate_id(true)`), and all older active sessions for that account are invalidated.
- **Hashed Email Verification Tokens:**
  - Stored `email_verification_token` as a SHA-256 hash in the database and validated incoming tokens using constant-time `hash_equals()`.

### 11. Hardened CSV Imports
- Implemented file size limit (max 1 MB) and maximum row limit (max 500 rows) in `seller/products.php`, `admin/import_products.php`, and `admin/import_users.php`.
- Added strict numeric validation (`is_numeric()`) for price, original price, weight, and stock quantities.
- Whitelisted categories and roles against database tables before insertion.
- Neutralized spreadsheet formula injection attacks (`=`, `+`, `-`, `@`, `\t`, `\r`) using `neutralize_csv_formula()` on all text fields.

### 12. Permissions Hardening Script
- Added `fix_permissions.sh` script to set standard cPanel file permissions (644 for files, 755 for directories, 640 for local configs, 750 for CLI tools).

