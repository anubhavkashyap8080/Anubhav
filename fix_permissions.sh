#!/usr/bin/env bash
# ==============================================================================
# GaunGaliHat Platform — Production Permissions Hardening Script
# Use on cPanel / Linux Apache / Nginx servers
# ==============================================================================

set -euo pipefail

TARGET_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
echo "Hardening file and directory permissions in: ${TARGET_DIR}"

# 1. Reset standard directory permissions to 755 (drwxr-xr-x)
find "${TARGET_DIR}" -type d -exec chmod 755 {} +

# 2. Reset standard file permissions to 644 (-rw-r--r--)
find "${TARGET_DIR}" -type f -exec chmod 644 {} +

# 3. Ensure CLI helper scripts are executable
if [ -d "${TARGET_DIR}/scripts" ]; then
    find "${TARGET_DIR}/scripts" -type f -name "*.php" -exec chmod 750 {} + || true
fi
chmod 750 "${TARGET_DIR}/fix_permissions.sh" || true

# 4. Secure configuration files with tighter permissions if supported
if [ -f "${TARGET_DIR}/config/config.local.php" ]; then
    chmod 640 "${TARGET_DIR}/config/config.local.php" || true
fi

# 5. Ensure upload directory is accessible
if [ -d "${TARGET_DIR}/uploads" ]; then
    chmod 755 "${TARGET_DIR}/uploads" || true
fi

echo "Permissions hardening completed successfully."
