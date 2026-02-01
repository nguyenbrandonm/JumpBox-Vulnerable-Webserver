#!/usr/bin/env bash
set -euo pipefail

APP_NAME="jumpbox"
WEB_PORT="80"

DEPLOY_ROOT="/var/www/${APP_NAME}"
APACHE_SITE="/etc/apache2/sites-available/${APP_NAME}.conf"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="${SCRIPT_DIR}"

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

ensure_dirs() {
  # Ensure required runtime dirs exist even if empty in git
  mkdir -p "${DEPLOY_ROOT}/uploads"
  mkdir -p "${DEPLOY_ROOT}/files"
}

sync_app_files() {
  echo "[*] Deploying files to ${DEPLOY_ROOT}..."

  mkdir -p "${DEPLOY_ROOT}"

  if ! command -v rsync >/dev/null 2>&1; then
    install_if_missing rsync
  fi

  # Deploy the app from the repo into /var/www/jumpbox
  # Exclude repo-only files that don't need to be served
  rsync -a --delete \
    --exclude ".git/" \
    --exclude ".github/" \
    --exclude "*.md" \
    --exclude "LICENSE" \
    --exclude "CODE_OF_CONDUCT.md" \
    --exclude "CONTRIBUTING.md" \
    --exclude "SECURITY.md" \
    "${REPO_ROOT}/" "${DEPLOY_ROOT}/"

  # Ensure required dirs exist after rsync (in case they're empty in git)
  ensure_dirs
}

set_permissions() {
  echo "[*] Setting ownership and permissions..."

  # Site files: owned by root, readable by Apache
  chown -R root:root "${DEPLOY_ROOT}"
  find "${DEPLOY_ROOT}" -type d -exec chmod 755 {} \;
  find "${DEPLOY_ROOT}" -type f -exec chmod 644 {} \;

  # uploads/: must be writable for the insecure upload endpoint
  # NOTE: uploads/uploads.php stores uploads directly into /uploads/
  echo "[*] Making uploads/ writable by www-data..."
  chown -R www-data:www-data "${DEPLOY_ROOT}/uploads"
  find "${DEPLOY_ROOT}/uploads" -type d -exec chmod 775 {} \;
  find "${DEPLOY_ROOT}/uploads" -type f -exec chmod 664 {} \;

  # Optional: if you want uploaded PHP to execute (lab realism),
  # Apache needs to be allowed to execute PHP in /uploads.
  # That is controlled by Apache config (Directory settings) and PHP handler.
}

detect_docroot() {
  # If you later add /public, support it automatically
  if [[ -f "${DEPLOY_ROOT}/public/index.php" ]]; then
    echo "${DEPLOY_ROOT}/public"
    return
  fi
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

    # If you want uploads to be *extra* permissive for the lab, keep it inherited.
    # (No special restrictions here intentionally.)

    ErrorLog \${APACHE_LOG_DIR}/${APP_NAME}_error.log
    CustomLog \${APACHE_LOG_DIR}/${APP_NAME}_access.log combined
</VirtualHost>
EOF
}

enable_site() {
  echo "[*] Enabling rewrite module..."
  a2enmod rewrite >/dev/null || true

  echo "[*] Enabling site: ${APP_NAME}"
  a2ensite "${APP_NAME}" >/dev/null || true

  # Disable default site to avoid confusion
  if [[ -e /etc/apache2/sites-enabled/000-default.conf ]]; then
    echo "[*] Disabling default site (000-default)..."
    a2dissite 000-default >/dev/null || true
  fi
}

reload_apache() {
  echo "[*] Reloading Apache..."
  systemctl reload apache2
}

post_install_notes() {
  echo
  echo "[+] JumpBox deployed and live."
  echo "    DocumentRoot:  ${DEPLOY_ROOT}"
  echo "    Test locally:  curl -I http://localhost:${WEB_PORT}/"
  echo "    Browse:        http://<server-ip>/"
  echo
  echo "    Key endpoints (UI routes):"
  echo "      /            (dashboard)"
  echo "      /uploads/     (folder landing, if you add uploads/index.php)"
  echo "      /ping/        (folder landing, if you add ping/index.php)"
  echo "      /dir/         (folder landing, if you add dir/index.php)"
  echo
  echo "    Vulnerable endpoints (deeper paths):"
  echo "      /uploads/uploads.php"
  echo "      /ping/ping.php"
  echo "      /dir/viewer.php"
  echo
  echo "    Logs:"
  echo "      /var/log/apache2/${APP_NAME}_error.log"
  echo "      /var/log/apache2/${APP_NAME}_access.log"
  echo
}

main() {
  require_root

  echo "[*] Repo root: ${REPO_ROOT}"

  # 1) Install deps
  install_if_missing apache2
  install_if_missing php
  install_if_missing libapache2-mod-php

  # 2) Deploy into /var/www/jumpbox
  sync_app_files

  # 3) Permissions (uploads writable)
  set_permissions

  # 4) Apache site config
  DOCROOT="$(detect_docroot)"
  echo "[*] Using DocumentRoot: ${DOCROOT}"
  echo "[*] Writing Apache site config: ${APACHE_SITE}"
  write_site_config "${DOCROOT}"

  # 5) Enable + reload
  enable_site
  reload_apache

  post_install_notes
}

main "$@"
