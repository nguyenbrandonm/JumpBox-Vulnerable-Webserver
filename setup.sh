#!/usr/bin/env bash
set -euo pipefail

APP_NAME="jumpbox"
WEB_PORT="80"

# Where the app will live on the server (standard practice)
DEPLOY_ROOT="/var/www/${APP_NAME}"

# Resolve repo root (script lives at repo root, or adjust if you move it)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="${SCRIPT_DIR}"

APACHE_SITE="/etc/apache2/sites-available/${APP_NAME}.conf"

require_root() {
  if [[ ${EUID} -ne 0 ]]; then
    echo "[!] Run as root: sudo $0"
    exit 1
  fi
}

pkg_installed() { dpkg -s "$1" >/dev/null 2>&1; }

install_if_missing() {
  local pkg="$1"
  if pkg_installed "$pkg"; then
    echo "[*] ${pkg} already installed"
  else
    echo "[*] Installing ${pkg}..."
    apt-get update -y
    apt-get install -y "$pkg"
  fi
}

detect_docroot() {
  # Prefer /public if it exists (common secure layout)
  if [[ -f "${DEPLOY_ROOT}/public/index.php" ]]; then
    echo "${DEPLOY_ROOT}/public"
    return
  fi

  # Otherwise use deploy root if it contains index.php
  if [[ -f "${DEPLOY_ROOT}/index.php" ]]; then
    echo "${DEPLOY_ROOT}"
    return
  fi

  # Fall back to deploy root
  echo "${DEPLOY_ROOT}"
}

write_site_config() {
  local docroot="$1"

  cat > "${APACHE_SITE}" <<EOF
<VirtualHost *:${WEB_PORT}>
    ServerName ${APP_NAME}.local
    DocumentRoot ${docroot}

    <Directory ${docroot}>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/${APP_NAME}_error.log
    CustomLog \${APACHE_LOG_DIR}/${APP_NAME}_access.log combined
</VirtualHost>
EOF
}

sync_app_files() {
  echo "[*] Deploying files to ${DEPLOY_ROOT}..."

  mkdir -p "${DEPLOY_ROOT}"

  # Use rsync if available (best for repeatable installs); otherwise fallback to cp
  if command -v rsync >/dev/null 2>&1; then
    # Exclude git + github metadata + common junk
    rsync -a --delete \
      --exclude ".git/" \
      --exclude ".github/" \
      --exclude "*.md" \
      --exclude "LICENSE" \
      --exclude "CODE_OF_CONDUCT.md" \
      --exclude "CONTRIBUTING.md" \
      --exclude "SECURITY.md" \
      "${REPO_ROOT}/" "${DEPLOY_ROOT}/"
  else
    echo "[!] rsync not found. Falling back to cp (less clean on updates)."
    rm -rf "${DEPLOY_ROOT:?}"/*
    cp -a "${REPO_ROOT}/." "${DEPLOY_ROOT}/"
    rm -rf "${DEPLOY_ROOT}/.git" "${DEPLOY_ROOT}/.github" || true
  fi
}

set_permissions() {
  echo "[*] Setting ownership and permissions..."

  # Readable by Apache, owned by root (standard)
  chown -R root:root "${DEPLOY_ROOT}"
  find "${DEPLOY_ROOT}" -type d -exec chmod 755 {} \;
  find "${DEPLOY_ROOT}" -type f -exec chmod 644 {} \;

  # Writable directories (adjust based on your repo)
  # You currently have an "uploads/" folder at repo root.
  if [[ -d "${DEPLOY_ROOT}/uploads" ]]; then
    echo "[*] Making uploads/ writable by www-data..."
    chown -R www-data:www-data "${DEPLOY_ROOT}/uploads"
    find "${DEPLOY_ROOT}/uploads" -type d -exec chmod 775 {} \;
    find "${DEPLOY_ROOT}/uploads" -type f -exec chmod 664 {} \;
  fi

  # If you later add app state dirs, handle them similarly (example):
  # for d in tmp cache sessions; do ...
}

main() {
  require_root

  echo "[*] Repo root: ${REPO_ROOT}"

  # 1) Install dependencies (Ubuntu/Debian)
  install_if_missing apache2
  install_if_missing php
  install_if_missing libapache2-mod-php

  # rsync is optional but recommended
  if ! command -v rsync >/dev/null 2>&1; then
    echo "[*] Installing rsync (recommended for clean redeploys)..."
    install_if_missing rsync
  fi

  # 2) Deploy app into /var/www/jumpbox
  sync_app_files

  # 3) Permissions (do NOT chmod your whole repo)
  set_permissions

  # 4) Pick DocumentRoot from deployed path
  DOCROOT="$(detect_docroot)"
  echo "[*] Using DocumentRoot: ${DOCROOT}"

  # 5) Write/refresh site config
  echo "[*] Writing Apache site config: ${APACHE_SITE}"
  write_site_config "${DOCROOT}"

  # 6) Enable modules + site
  echo "[*] Enabling rewrite module..."
  a2enmod rewrite >/dev/null || true

  echo "[*] Enabling site: ${APP_NAME}"
  a2ensite "${APP_NAME}" >/dev/null || true

  # Disable default site to avoid confusion (optional)
  if [[ -e /etc/apache2/sites-enabled/000-default.conf ]]; then
    echo "[*] Disabling default site (000-default)..."
    a2dissite 000-default >/dev/null || true
  fi

  # 7) Reload Apache
  echo "[*] Reloading Apache..."
  systemctl reload apache2

  echo
  echo "[+] JumpBox deployed and live."
  echo "    DocumentRoot:      ${DOCROOT}"
  echo "    Test from server:  curl -I http://localhost:${WEB_PORT}/"
  echo "    Server IP:         hostname -I"
  echo "    Logs:"
  echo "      /var/log/apache2/${APP_NAME}_error.log"
  echo "      /var/log/apache2/${APP_NAME}_access.log"
  echo
  if [[ ! -f "${DOCROOT}/index.php" ]]; then
    echo "[!] Note: index.php was not found in DocumentRoot."
    echo "    Check your repo layout or adjust detect_docroot()."
  fi
}

main "$@"
