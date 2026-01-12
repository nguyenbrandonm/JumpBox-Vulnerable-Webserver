#!/usr/bin/env bash
set -euo pipefail

APP_NAME="jumpbox"
WEB_PORT="80"

# Resolve repo root (script lives in ./scripts)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

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
  # Prefer a "public/" layout if it exists and contains index.php
  if [[ -f "${REPO_ROOT}/public/index.php" ]]; then
    echo "${REPO_ROOT}/public"
    return
  fi

  # Otherwise use repo root if it contains index.php
  if [[ -f "${REPO_ROOT}/index.php" ]]; then
    echo "${REPO_ROOT}"
    return
  fi

  # Otherwise fall back to repo root (user can adjust)
  echo "${REPO_ROOT}"
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

main() {
  require_root

  echo "[*] Repo root: ${REPO_ROOT}"

  # 1) Install dependencies (Ubuntu/Debian)
  install_if_missing apache2
  install_if_missing php
  install_if_missing libapache2-mod-php

  # 2) Pick DocumentRoot
  DOCROOT="$(detect_docroot)"
  echo "[*] Using DocumentRoot: ${DOCROOT}"

  # 3) Ensure Apache can read the repo files (non-destructive, lab-friendly)
  echo "[*] Ensuring Apache can read files..."
  chmod -R a+rX "${REPO_ROOT}" || true

  # 4) Write/refresh site config
  echo "[*] Writing Apache site config: ${APACHE_SITE}"
  write_site_config "${DOCROOT}"

  # 5) Enable modules + site
  echo "[*] Enabling rewrite module..."
  a2enmod rewrite >/dev/null || true

  echo "[*] Enabling site: ${APP_NAME}"
  a2ensite "${APP_NAME}" >/dev/null || true

  # Disable default site to avoid confusion (optional, but nice)
  if [[ -e /etc/apache2/sites-enabled/000-default.conf ]]; then
    echo "[*] Disabling default site (000-default)..."
    a2dissite 000-default >/dev/null || true
  fi

  # 6) Reload Apache
  echo "[*] Reloading Apache..."
  systemctl reload apache2

  echo
  echo "[+] JumpBox is live."
  echo "    Test from server:  curl -I http://localhost:${WEB_PORT}/"
  echo "    Server IP:         hostname -I"
  echo "    Logs:"
  echo "      /var/log/apache2/${APP_NAME}_error.log"
  echo "      /var/log/apache2/${APP_NAME}_access.log"
  echo
  if [[ ! -f "${DOCROOT}/index.php" ]]; then
    echo "[!] Note: index.php was not found in DocumentRoot."
    echo "    If your app entry is in another folder, update detect_docroot() in scripts/setup.sh."
  fi
}

main "$@"
