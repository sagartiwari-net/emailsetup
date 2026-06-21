# Mail System Project

Self-hosted mail panel for OTP, transactional mail, and gradual promotional campaigns.

## Project Structure

```
email Setup/
├── DEVELOPMENT_PLAN.md      # Full phase-wise plan
├── PROGRESS_TRACKER.md      # Feature completion tracker
├── mail-panel/              # Laravel control panel + API
├── demo-website/            # Pilot OTP site (uses SDK)
├── docs/runbook-*.md        # Server runbooks
├── scripts/                 # Server setup + backup scripts
└── sdk/                     # PHP client for your websites
```

## What Is Built So Far

- Laravel mail panel with admin login
- Domain management
- API key generation
- **Email template manager (CRUD)**
- **Test mail sender (admin panel)**
- **Tenant / friend account management (super admin)**
- Send API (`POST /api/v1/send`)
- Queue-based sending (database queue)
- OTP / welcome / promo templates
- Daily cap (warmup limiter)
- Mail logs dashboard + per-domain stats
- PHP SDK for website integration
- Mailcow server setup scripts
- Bulk list upload + scheduled recurring campaigns
- Unsubscribe page + daily cap on campaigns
- Failed jobs admin page
- Website integration registry (admin → Websites)
- Tenant activity view + suspend/activate
- Campaign bounced/unsubscribed stats
- Health check command (`php artisan mail-panel:health`)
- Demo OTP website (`demo-website/`)
- Runbooks + backup script template

## Local Development

```bash
cd mail-panel
cp .env.example .env   # if needed
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

**Admin login (seeded):**
- Email: `admin@mail.local`
- Password: `password`

Open: http://localhost:8000

**Campaign scheduler (local):**
```bash
php artisan schedule:work
# or manually: php artisan campaigns:process
```

**Health check:**
```bash
php artisan mail-panel:health
```

**Demo website (pilot OTP):**
```bash
cp demo-website/config.example.php demo-website/config.php
# Add your API key from admin panel
cd demo-website && php -S localhost:8080
```

## API Usage

```bash
curl -X POST http://localhost:8000/api/v1/send \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_KEY" \
  -d '{
    "to": "user@example.com",
    "template": "otp",
    "data": {
      "otp": "482910",
      "name": "Rahul",
      "minutes": 10
    }
  }'
```

Or use `sdk/MailPanelClient.php` in your PHP websites.

## Server Setup (aaPanel + Hetzner)

**Subdomain:** `email.sagartiwari.net`  
**Server path:** `/www/wwwroot/sagartiwari.net/email`  
**GitHub:** https://github.com/sagartiwari-net/emailsetup

```bash
# Server par (pehli baar)
cd /www/wwwroot/sagartiwari.net
git clone https://github.com/sagartiwari-net/emailsetup.git email
```

Full guide: **`AAPANEL_DEPLOY.md`**

## Next Steps

1. Run Phase 0 + 1 on dedicated server
2. Configure DNS for first domain
3. Update `mail-panel/.env` with Mailcow SMTP credentials
4. Add your 10 domains in admin panel
5. Generate API keys per website
6. Integrate SDK in each website

See `PROGRESS_TRACKER.md` to mark completed tasks.
