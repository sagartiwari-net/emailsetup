# aaPanel Deployment Guide — email.sagartiwari.net

> **Subdomain:** `email.sagartiwari.net`  
> **Server path:** `/www/wwwroot/sagartiwari.net/email`  
> **GitHub:** https://github.com/sagartiwari-net/emailsetup  
> **Strategy:** Pehle Laravel Mail Panel deploy. Mailcow baad mein alag subdomain par.

---

## Phase A — GitHub se server par pull (recommended)

### Local Mac (already done once)
```bash
cd "/Users/sagartiwari/Desktop/email Setup"
git add .
git commit -m "your message"
git push origin main
```

### Server par (SSH)
```bash
cd /www/wwwroot/sagartiwari.net

# Pehli baar clone
git clone https://github.com/sagartiwari-net/emailsetup.git email

# Baad mein update
cd /www/wwwroot/sagartiwari.net/email
git pull origin main
```

---

## Phase B — aaPanel mein site banao

1. **aaPanel → Website → Add site**
2. Domain: `email.sagartiwari.net`
3. Root path: `/www/wwwroot/sagartiwari.net/email/mail-panel/public`
4. PHP version: **8.2+**
5. Database: **MySQL** create karo (aaPanel → Database)
6. SSL: Let's Encrypt enable karo

> Laravel app folder: `/www/wwwroot/sagartiwari.net/email/mail-panel`  
> Document root **must** be `mail-panel/public`

---

## Phase C — Laravel setup (server par SSH)

```bash
cd /www/wwwroot/sagartiwari.net/email/mail-panel

composer install --no-dev --optimize-autoloader

cp .env.example .env
php artisan key:generate
```

### `.env` production values
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://email.sagartiwari.net

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mail_panel
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

QUEUE_CONNECTION=database

# Mailcow ready hone tak log use kar sakte ho
MAIL_MAILER=log

# Mailcow ke baad:
# MAIL_MAILER=smtp
# MAIL_HOST=mail.sagartiwari.net
# MAIL_PORT=587
# MAIL_USERNAME=noreply@sagartiwari.net
# MAIL_PASSWORD=your_smtp_password
# MAIL_ENCRYPTION=tls

MAIL_SYSTEM_DAILY_CAP=15
```

```bash
php artisan migrate --seed
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www:www storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

**Admin login (seeded):** `admin@mail.local` / `password` — turant change karo!

---

## Phase D — Nginx document root (aaPanel)

aaPanel site settings:
- **Site path / Document root:** `/www/wwwroot/sagartiwari.net/email/mail-panel/public`
- **SSL:** Let's Encrypt for `email.sagartiwari.net`
- **PHP:** 8.2+

Nginx rewrite (Laravel — usually aaPanel default OK):
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

---

## Phase E — Queue worker + cron

### aaPanel → Cron (every minute)
```bash
* * * * * cd /www/wwwroot/sagartiwari.net/email/mail-panel && php artisan schedule:run >> /dev/null 2>&1
```

### Supervisor queue worker
```ini
[program:mail-panel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /www/wwwroot/sagartiwari.net/email/mail-panel/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www
numprocs=1
redirect_stderr=true
stdout_logfile=/www/wwwroot/sagartiwari.net/email/mail-panel/storage/logs/worker.log
```

---

## Phase F — Mailcow (baad mein)

1. Automation task complete hone do
2. Subdomain: `mail.sagartiwari.net` — Mailcow ke liye alag
3. `scripts/phase1-mailcow-install.sh` run karo
4. DNS: MX, SPF, DKIM, DMARC
5. SMTP credentials → `mail-panel/.env` mein daalo

```bash
ss -tlnp | grep -E ':25|:587|:993'
```

---

## Phase G — Website integration

Har website mein SDK use karo:

```php
require_once '/path/to/MailPanelClient.php';
$client = new MailPanelClient(
    'https://email.sagartiwari.net',
    'mk_your_api_key_from_panel'
);
$client->sendOtp('user@email.com', '482910', 'Name');
```

SDK repo mein hai: `sdk/MailPanelClient.php`

---

## Safe rollout order

```
1. ✅ Local build + GitHub push
2. ⬜ Server par git clone → /www/wwwroot/sagartiwari.net/email
3. ⬜ aaPanel site email.sagartiwari.net + SSL
4. ⬜ composer install + migrate + admin login test
5. ⬜ Domain + API key add karo panel mein
6. ⬜ 1 website se OTP test (MAIL_MAILER=log)
7. ⬜ Mailcow install (mail.sagartiwari.net)
8. ⬜ SMTP connect → real mail + warmup start
```

---

## Server par update workflow

```bash
cd /www/wwwroot/sagartiwari.net/email
git pull origin main
cd mail-panel
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
# Supervisor worker restart if needed
```

---

*Local test: `php artisan serve` → http://localhost:8000*
