#!/bin/bash
# Phase 0: Dedicated server preparation (Ubuntu 22.04)
# Run as root on your mail server: bash phase0-server-setup.sh

set -euo pipefail

echo "==> Updating system..."
apt update && apt upgrade -y

echo "==> Installing base packages..."
apt install -y curl git ufw fail2ban ca-certificates gnupg lsb-release

echo "==> Installing Docker..."
if ! command -v docker >/dev/null 2>&1; then
  install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
  chmod a+r /etc/apt/keyrings/docker.gpg
  echo \
    "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
    $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
    tee /etc/apt/sources.list.d/docker.list > /dev/null
  apt update
  apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
fi

echo "==> Configuring firewall..."
ufw allow 22/tcp
ufw allow 25/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 587/tcp
ufw allow 993/tcp
ufw allow 995/tcp
ufw --force enable

echo "==> Enabling fail2ban..."
systemctl enable fail2ban
systemctl start fail2ban

echo ""
echo "Phase 0 complete."
echo "Next steps:"
echo "1. Verify IP is clean: https://mxtoolbox.com/blacklists.aspx"
echo "2. Request PTR record from hosting provider -> mail.yourdomain.com"
echo "3. Run phase1-mailcow-install.sh"
