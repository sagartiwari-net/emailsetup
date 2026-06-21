#!/bin/bash
# Phase 1: Mailcow installation
# Usage: MAIL_HOSTNAME=mail.yourdomain.com bash phase1-mailcow-install.sh

set -euo pipefail

MAIL_HOSTNAME="${MAIL_HOSTNAME:-}"

if [ -z "$MAIL_HOSTNAME" ]; then
  echo "Error: Set MAIL_HOSTNAME first."
  echo "Example: MAIL_HOSTNAME=mail.yourdomain.com bash phase1-mailcow-install.sh"
  exit 1
fi

INSTALL_DIR="/opt/mailcow-dockerized"

echo "==> Installing Mailcow at $INSTALL_DIR ..."
if [ ! -d "$INSTALL_DIR" ]; then
  git clone https://github.com/mailcow/mailcow-dockerized "$INSTALL_DIR"
fi

cd "$INSTALL_DIR"

if [ ! -f mailcow.conf ]; then
  ./generate_config.sh
fi

sed -i "s/^MAILCOW_HOSTNAME=.*/MAILCOW_HOSTNAME=${MAIL_HOSTNAME}/" mailcow.conf

echo "==> Pulling and starting containers..."
docker compose pull
docker compose up -d

echo ""
echo "Phase 1 complete."
echo "Open: https://${MAIL_HOSTNAME}"
echo "Default login: admin / moohoo (change immediately)"
echo "Next: Configure DNS records from DEVELOPMENT_PLAN.md Section 8"
