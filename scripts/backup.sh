#!/usr/bin/env bash
# Daily backup script for Mail Panel + Mailcow (run on Hetzner server)
# Usage: ./scripts/backup.sh
# Cron: 0 3 * * * /opt/mail-system/scripts/backup.sh >> /var/log/mail-backup.log 2>&1

set -euo pipefail

BACKUP_DIR="${BACKUP_DIR:-/opt/backups/mail-system}"
DATE=$(date +%Y-%m-%d_%H-%M)
MAIL_PANEL_PATH="${MAIL_PANEL_PATH:-/www/wwwroot/mail-api.yourdomain.com}"
MAILCOW_PATH="${MAILCOW_PATH:-/opt/mailcow-dockerized}"
RETAIN_DAYS="${RETAIN_DAYS:-14}"

mkdir -p "$BACKUP_DIR"

echo "[$DATE] Starting backup..."

# Mail Panel: SQLite/MySQL dump
if [ -f "$MAIL_PANEL_PATH/.env" ]; then
    DB_CONNECTION=$(grep ^DB_CONNECTION= "$MAIL_PANEL_PATH/.env" | cut -d= -f2)
    if [ "$DB_CONNECTION" = "sqlite" ]; then
        cp "$MAIL_PANEL_PATH/database/database.sqlite" "$BACKUP_DIR/mail-panel-$DATE.sqlite"
    else
        DB_DATABASE=$(grep ^DB_DATABASE= "$MAIL_PANEL_PATH/.env" | cut -d= -f2)
        DB_USERNAME=$(grep ^DB_USERNAME= "$MAIL_PANEL_PATH/.env" | cut -d= -f2)
        DB_PASSWORD=$(grep ^DB_PASSWORD= "$MAIL_PANEL_PATH/.env" | cut -d= -f2)
        mysqldump -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" | gzip > "$BACKUP_DIR/mail-panel-$DATE.sql.gz"
    fi
    tar -czf "$BACKUP_DIR/mail-panel-env-$DATE.tar.gz" -C "$MAIL_PANEL_PATH" .env 2>/dev/null || true
    echo "Mail Panel database backed up."
fi

# Mailcow data directory
if [ -d "$MAILCOW_PATH" ]; then
    tar -czf "$BACKUP_DIR/mailcow-data-$DATE.tar.gz" \
        -C "$MAILCOW_PATH" mailcow.conf data/conf 2>/dev/null || true
    echo "Mailcow config backed up."
fi

# Remove old backups
find "$BACKUP_DIR" -type f -mtime +"$RETAIN_DAYS" -delete
echo "[$DATE] Backup complete. Retained $RETAIN_DAYS days."
