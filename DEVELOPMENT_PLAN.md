# Mail System — Development Plan

> **Project:** Self-hosted mail system for ~10 personal websites  
> **Purpose:** OTP, transactional details, gradual promotional mail (no selling)  
> **Future:** Optional friend accounts with isolated panel access  
> **Last updated:** June 20, 2026

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Email Warmup Strategy](#2-email-warmup-strategy)
3. [Architecture](#3-architecture)
4. [Tech Stack](#4-tech-stack)
5. [Development Phases](#5-development-phases)
6. [Database Schema (Overview)](#6-database-schema-overview)
7. [API Endpoints (Planned)](#7-api-endpoints-planned)
8. [DNS Checklist (Per Domain)](#8-dns-checklist-per-domain)
9. [Security Rules](#9-security-rules)
10. [What NOT to Build](#10-what-not-to-build)
11. [Timeline Summary](#11-timeline-summary)

---

## 1. Project Overview

### Goals

| Priority | Use Case | When |
|----------|----------|------|
| P0 | OTP send from all websites | Phase 3 |
| P1 | Transactional details (welcome, order info, etc.) | Phase 3–4 |
| P2 | Promotional mail (small batches) | After warmup (Month 2+) |
| P3 | Bulk list upload + scheduled recurring campaigns | Phase 5 |
| P4 | Friend accounts with own panel | Phase 6 |

### Scope

- **In scope:** Own use + optional friends (manual account creation)
- **Out of scope:** Public SaaS, payment/billing, selling as product

### Success Criteria

- [ ] All 10 websites sending OTP via central API
- [ ] mail-tester.com score 9/10+ on every domain
- [ ] Mail delivery logs visible in admin panel
- [ ] Warmup plan followed without IP blacklist
- [ ] Bulk campaigns working with schedule (Phase 5)
- [ ] Friend tenant panel working (Phase 6)

---

## 2. Email Warmup Strategy

> **Important:** Daily numbers = **maximum allowed cap**, NOT a target you must hit.  
> Agar aaj sirf 8 OTP gaye, to 8 hi sahi hain — **fake volume mat bhejo**.

### Reality Check

10 websites par OTP/transactional se 100 mail/day tabhi possible hai jab **bahut active users** hon.  
Shuru mein realistic volume:

| Period | Realistic natural volume (OTP + transactional) |
|--------|------------------------------------------------|
| Week 1–2 | 5–20/day |
| Week 3–4 | 15–40/day |
| Month 2 | 30–80/day (traffic badhne par) |

Isliye warmup = **ceiling badhao**, volume **force mat karo**.  
Gap bharna ho to **planned promo** (opt-in list) se — Week 2 se chhota start.

---

### Two Types of Limits

| Term | Meaning |
|------|---------|
| **Daily Cap (Max)** | System aaj itne se zyada mail nahi bhejega — safety limit |
| **Actual Sends** | Jo aaj sach mein gaye (OTP + transactional + promo) — jitna ho utna |

**Rule:** Actual ≤ Daily Cap. Cap badhao jab deliverability stable ho, actual ko force mat karo.

---

### Volume Schedule (Daily Cap — Maximum Allowed)

| Period | Daily Cap (Max) | What to send | Notes |
|--------|-----------------|--------------|-------|
| Week 1 | 10–15 | OTP + transactional (jo naturally aaye) | DNS + mail-tester 9/10 pe focus |
| Week 2 | 25–30 | OTP + transactional + **5–10 promo** (opt-in) | Pehli chhoti promo allowed |
| Week 3 | 40–50 | Natural + **10–20 promo** | Inbox placement check |
| Week 4 | 60–80 | Natural + **20–30 promo** | Cap 100 tab tak mat badhao jab tak stable na ho |
| Month 2 | 100 → 200 | Natural + promo campaigns | Dheere cap badhao |
| Month 3 | 200 → 400 | + scheduled campaigns | Unsubscribe mandatory |

> **Pehle wala plan (100/day Week 4 sirf OTP se)** unrealistic tha — ye revised plan use karo.

---

### Jab Natural OTP Kam Ho — Kya Karein

Volume force karne ki zaroorat nahi. Reputation build karne ke liye ye safe options:

#### Option 1: Opt-in promo (recommended — Week 2 se)
```
- Apni existing genuine list (jinhone subscribe kiya ho)
- Week 2: 5–10 promo mail/day
- Week 3: 10–20 promo mail/day
- Har mail mein unsubscribe link
```

#### Option 2: Seed mailboxes (testing + reputation)
```
- Apne Gmail, Yahoo, Outlook accounts par test mail bhejo
- Mail kholo, kabhi reply karo, spam se "Not spam" mark karo
- Week 1: 2–3 seed mails/day (optional)
- Fake customers ko mail mat bhejo
```

#### Option 3: Internal / friend list (with permission)
```
- Dost/family jinhone explicitly kaha ho "meri mail bhej sakte ho"
- Chhota weekly update ya newsletter
- Max 10–15 log — trusted list only
```

#### Option 4: Cap badhao, volume force mat karo (best default)
```
- Agar aaj 12 OTP gaye aur cap 30 hai → 12 hi perfect
- Cap tab badhao jab:
  ✅ mail-tester 9/10+
  ✅ 2+ weeks koi blacklist issue nahi
  ✅ Bounce rate < 5%
  ✅ Promo inbox mein ja rahi hai (spam nahi)
```

---

### Recommended Mix (Example — Week 3)

| Source | Count | Type |
|--------|-------|------|
| OTP (natural) | ~15 | User-triggered |
| Transactional | ~5 | Welcome, details |
| Promo (opt-in list) | ~15 | Scheduled campaign |
| **Total Actual** | **~35** | Cap 50 ke andar |

---

### Warmup Rules

```
✅ DO
- Daily cap = maximum, actual = jitna real ho
- OTP pehle priority — user-triggered best signal
- Week 2 se chhoti opt-in promo (5–10/day) — OK hai
- Consistent sending (roz kuch na kuch, even if 5 mails)
- Remove bounced emails immediately
- Bounce rate < 5% rakho
- mail-tester.com har 2 hafte
- IP blacklist weekly check

❌ DON'T
- 100 fake OTP bhejna sirf cap hit karne ke liye
- Purchased / scraped email lists
- Week 1 mein bulk promo
- Ek din cap double karna
- Unsubscribe link ke bina promo
- Bounces ignore karna
```

### If Something Goes Wrong

| Problem | Action |
|---------|--------|
| Mails going to spam | Promo pause 1 week, sirf OTP, DNS re-check |
| IP blacklisted | Sab sending stop, delist request, cause dhundho |
| High bounce rate (>5%) | List clean karo, invalid emails hatao |
| Gmail blocking | Volume 50% kam, SPF/DKIM/DMARC verify |
| Natural volume bahut kam | Koi problem nahi — cap mat badhao, consistency rakho |

---

## 3. Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                 LARAVEL CONTROL PANEL (PHP)                  │
│   Admin Dashboard │ Friend Tenants │ Campaign Manager        │
└──────────────────────────┬──────────────────────────────────┘
                           │ REST API
┌──────────────────────────▼──────────────────────────────────┐
│                    SENDING SERVICE (PHP/Laravel)               │
│   API Keys │ Queue (Redis) │ Templates │ Rate Limiter        │
└──────────────────────────┬──────────────────────────────────┘
                           │ SMTP
┌──────────────────────────▼──────────────────────────────────┐
│                  MAILCOW (Docker on Dedicated Server)        │
│        Postfix │ Dovecot │ Rspamd │ OpenDKIM │ Roundcube     │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│                         DNS LAYER                            │
│              MX │ SPF │ DKIM │ DMARC │ PTR                   │
└─────────────────────────────────────────────────────────────┘

   Website 1 ──API──┐
   Website 2 ──API──┤
   ...             ├──→ Sending Service ──→ Mailcow ──→ Internet
   Website 10 ──API─┘
```

---

## 4. Tech Stack

| Layer | Technology | Reason |
|-------|------------|--------|
| Mail server | Mailcow (Docker) | Production-ready, auto DKIM |
| Control panel | PHP Laravel 11 | Familiar stack, fast development |
| Queue | Redis + Laravel Queue | Reliable async sending |
| Database | MySQL / MariaDB | Users, logs, campaigns |
| Cache | Redis | Rate limiting, sessions |
| Frontend | Laravel Blade + Livewire | Simple admin UI without separate SPA |
| Webmail | Roundcube (via Mailcow) | Already included |
| Website SDK | PHP (Guzzle HTTP client) | Connect 10 websites |
| Scheduler | Laravel Scheduler + Cron | Recurring campaigns |
| SSL | Let's Encrypt (via Mailcow) | Free HTTPS |
| Server OS | Ubuntu 22.04 LTS | Stable, well documented |

---

## 5. Development Phases

---

### Phase 0 — Server & Infrastructure Prep
**Duration:** Week 1 (Days 1–2)  
**Depends on:** Nothing

#### Tasks

- [ ] Verify dedicated server specs (min 4GB RAM, 2 CPU, 50GB SSD)
- [ ] Install Ubuntu 22.04 LTS (if not already)
- [ ] Update system: `apt update && apt upgrade -y`
- [ ] Install Docker + Docker Compose
- [ ] Configure firewall (UFW): ports 22, 25, 80, 443, 587, 993, 995
- [ ] Verify dedicated IP is clean (mxtoolbox.com blacklist check)
- [ ] Request PTR (reverse DNS) from hosting provider → `mail.yourdomain.com`
- [ ] Choose primary domain for mail hostname (e.g. `mail.yourdomain.com`)
- [ ] Setup daily server backup (rsync or provider snapshot)
- [ ] Document server IP, root access, domain credentials

#### Deliverable
Server ready, clean IP confirmed, PTR requested.

---

### Phase 1 — Mailcow Installation
**Duration:** Week 1 (Days 3–5)  
**Depends on:** Phase 0

#### Tasks

- [ ] Clone Mailcow: `git clone https://github.com/mailcow/mailcow-dockerized`
- [ ] Generate config: `./generate_config.sh`
- [ ] Set `MAILCOW_HOSTNAME=mail.yourdomain.com` in `mailcow.conf`
- [ ] Start stack: `docker compose pull && docker compose up -d`
- [ ] Access admin panel: `https://mail.yourdomain.com`
- [ ] Change default admin password
- [ ] Create first test mailbox: `test@yourdomain.com`
- [ ] Test send via webmail (Roundcube)
- [ ] Test receive from external email (Gmail → your mailbox)
- [ ] Test SMTP auth from mail client (port 587, STARTTLS)
- [ ] Verify DKIM key generated in Mailcow admin

#### Deliverable
Send + receive working. Webmail accessible. SMTP auth working.

---

### Phase 2 — DNS & Deliverability
**Duration:** Week 1–2  
**Depends on:** Phase 1

#### Tasks

- [ ] Add primary domain in Mailcow admin
- [ ] Configure DNS for primary domain (see [Section 8](#8-dns-checklist-per-domain))
- [ ] Verify DKIM, SPF, DMARC, MX records propagated
- [ ] Send test mail to mail-tester.com → target score 9/10+
- [ ] Fix any issues flagged by mail-tester
- [ ] Add remaining 9 domains in Mailcow (one by one)
- [ ] Configure DNS for each of the 10 domains
- [ ] Verify mail-tester score for each domain
- [ ] Confirm PTR record points to `mail.yourdomain.com`
- [ ] Document all DNS records in a reference sheet
- [ ] Begin warmup: natural OTP only, daily cap 10–15

#### Deliverable
All 10 domains DNS-ready. mail-tester 9/10+. Warmup started.

---

### Phase 3 — Laravel Project + Sending API
**Duration:** Week 2–3  
**Depends on:** Phase 2

#### Tasks

- [ ] Create Laravel 11 project: `mail-panel`
- [ ] Setup `.env` with DB, Redis, Mailcow SMTP credentials
- [ ] Run database migrations (see [Section 6](#6-database-schema-overview))
- [ ] Build `POST /api/v1/send` endpoint
- [ ] Implement API key authentication middleware
- [ ] Connect to Mailcow SMTP for actual sending
- [ ] Create OTP email template (HTML + plain text)
- [ ] Create welcome/details email template
- [ ] Add rate limiter per API key (respect warmup daily limits)
- [ ] Log every send attempt (success/fail/bounce)
- [ ] Build simple admin login (email + password)
- [ ] Admin: view send logs, filter by website/date/status
- [ ] Admin: generate/revoke API keys per website
- [ ] Create PHP SDK/helper for websites to call API easily
- [ ] Connect Website 1 (pilot) — test OTP end-to-end
- [ ] Connect remaining 9 websites one by one
- [ ] Verify OTP delivery on all 10 sites

#### Deliverable
All 10 websites sending OTP via central API. Logs visible in admin.

---

### Phase 4 — Queue, Templates & Reliability
**Duration:** Week 3–4  
**Depends on:** Phase 3

#### Tasks

- [ ] Setup Redis queue driver in Laravel
- [ ] Move mail sending to queued jobs (async)
- [ ] Implement retry logic (3 attempts, exponential backoff)
- [ ] Template manager in admin panel (create/edit/delete templates)
- [ ] Template variables support: `{{otp}}`, `{{name}}`, `{{link}}`, etc.
- [ ] Admin dashboard: today's sends, success rate, fail count
- [ ] Admin dashboard: per-website send statistics
- [ ] Daily send counter (enforce warmup limits automatically)
- [ ] Bounce handling: mark email as invalid after hard bounce
- [ ] Failed job monitoring + admin alert
- [ ] API response: return `message_id` for tracking
- [ ] API endpoint: `GET /api/v1/status/{message_id}` — check delivery status

#### Deliverable
Reliable queued sending. Template system. Dashboard with stats.

---

### Phase 5 — Bulk Campaigns & Scheduling
**Duration:** Month 2 (after deliverability stable)  
**Depends on:** Phase 4 + mail-tester 9/10+ + at least 2 weeks clean sending

#### Tasks

- [ ] Subscriber list model (upload CSV, manual add, remove)
- [ ] CSV upload: validate email format, skip duplicates
- [ ] Campaign create form (name, from, subject, template, list)
- [ ] Campaign preview + send test to own email
- [ ] Send now option (respects daily limit)
- [ ] Schedule option: one-time future date/time
- [ ] Recurring schedule: every X days at specific time
- [ ] Campaign queue processor (Laravel Scheduler, every 5 min)
- [ ] Batch sending: 50 mails per batch, 5 min gap between batches
- [ ] Auto-enforce daily warmup limit in campaign processor
- [ ] Unsubscribe link in every promo template (mandatory)
- [ ] Unsubscribe endpoint: `GET /unsubscribe/{token}`
- [ ] Campaign stats: sent, failed, bounced, unsubscribed
- [ ] Pause / resume / cancel campaign
- [ ] Campaign history log

#### Deliverable
Upload list → schedule recurring campaign → auto-send with limits.

---

### Phase 6 — Multi-Tenant Friend Panel
**Duration:** Month 2–3  
**Depends on:** Phase 5 stable

#### Tasks

- [ ] User roles: `super_admin`, `tenant`
- [ ] Super admin: create tenant account manually
- [ ] Super admin: assign domain(s) to tenant
- [ ] Super admin: set daily send limit per tenant
- [ ] Tenant login → isolated dashboard (sees only own data)
- [ ] Tenant: create mailboxes for own domain (via Mailcow API)
- [ ] Tenant: view/generate own API key
- [ ] Tenant: create and manage own campaigns
- [ ] Tenant: view own send logs and stats
- [ ] Tenant: manage own email templates
- [ ] Tenant: manage own subscriber lists
- [ ] Super admin: view all tenants activity
- [ ] Super admin: suspend tenant account
- [ ] Rate limit per tenant (prevent one friend from affecting IP)

#### Deliverable
Friend gets account → manages own domain, mailboxes, campaigns independently.

---

### Phase 7 — Monitoring, Security & Maintenance
**Duration:** Ongoing from Phase 1  
**Depends on:** All phases

#### Tasks

- [ ] Fail2ban installed and configured
- [ ] UFW firewall rules verified
- [ ] SSL auto-renewal working (Let's Encrypt)
- [ ] Daily MySQL + Mailcow data backup script
- [ ] Weekly backup restore test
- [ ] Weekly IP blacklist check (automated script or manual)
- [ ] bi-weekly mail-tester score check
- [ ] Monthly server security updates
- [ ] Log rotation configured (prevent disk full)
- [ ] Disk usage alert (email when > 80%)
- [ ] Queue stuck alert
- [ ] Document runbook: what to do if IP blacklisted
- [ ] Document runbook: how to add new domain
- [ ] Document runbook: how to add new website/friend

#### Deliverable
Stable, secure, monitored system with documented procedures.

---

## 6. Database Schema (Overview)

```sql
-- Core
users               (id, name, email, password, role, tenant_id, created_at)
tenants             (id, name, owner_user_id, daily_limit, status, created_at)
domains             (id, tenant_id, domain_name, dkim_verified, created_at)
api_keys            (id, tenant_id, domain_id, key_hash, label, is_active, created_at)

-- Sending
mail_logs           (id, tenant_id, domain_id, to_email, subject, template,
                     status, message_id, error, sent_at, created_at)
templates           (id, tenant_id, name, subject, html_body, text_body, type, created_at)
daily_send_counts   (id, tenant_id, date, count)

-- Campaigns (Phase 5)
subscriber_lists    (id, tenant_id, name, created_at)
subscribers         (id, list_id, email, status, unsubscribed_at, created_at)
campaigns           (id, tenant_id, list_id, template_id, name, from_email,
                     subject, schedule_type, schedule_config, status,
                     started_at, completed_at, created_at)
campaign_logs       (id, campaign_id, subscriber_id, mail_log_id, status, created_at)

-- Mailboxes (Phase 6)
mailboxes           (id, tenant_id, domain_id, email, mailcow_id, created_at)
```

---

## 7. API Endpoints (Planned)

### Sending API (for websites)

```
POST   /api/v1/send                  Send single mail (OTP, details)
GET    /api/v1/status/{message_id}   Check delivery status
GET    /api/v1/stats/today           Today's send count for this API key
```

**Send request example:**
```json
POST /api/v1/send
Headers: X-API-Key: your_api_key_here

{
  "to": "user@example.com",
  "template": "otp",
  "data": {
    "otp": "482910",
    "name": "Rahul"
  }
}
```

### Admin API (panel internal)

```
GET    /admin/dashboard              Stats overview
GET    /admin/logs                   Mail logs (paginated, filterable)
POST   /admin/api-keys               Generate new API key
DELETE /admin/api-keys/{id}          Revoke API key
CRUD   /admin/templates              Manage templates
CRUD   /admin/campaigns              Manage campaigns
CRUD   /admin/subscribers            Manage lists
CRUD   /admin/tenants                Manage friend accounts (Phase 6)
```

---

## 8. DNS Checklist (Per Domain)

Copy this for each of the 10 domains:

```
# Replace YOUR_DOMAIN and YOUR_SERVER_IP

MX      @    10    mail.yourmaindomain.com
A       mail       YOUR_SERVER_IP
TXT     @          "v=spf1 mx ip4:YOUR_SERVER_IP -all"
TXT     dkim._domainkey  "v=DKIM1; k=rsa; p=KEY_FROM_MAILCOW"
TXT     _dmarc         "v=DMARC1; p=quarantine; rua=mailto:dmarc@YOUR_DOMAIN"
```

**Verification commands:**
```bash
dig MX YOUR_DOMAIN
dig TXT YOUR_DOMAIN
dig TXT dkim._domainkey.YOUR_DOMAIN
dig TXT _dmarc.YOUR_DOMAIN
```

**PTR (request from hosting provider):**
```
YOUR_SERVER_IP  →  mail.yourmaindomain.com
```

---

## 9. Security Rules

| Rule | Detail |
|------|--------|
| API keys | Hashed in DB, shown once on creation |
| SMTP | Auth required, no open relay |
| Rate limits | Enforced per API key + daily warmup cap |
| Friend accounts | Manual creation only, daily limit set by admin |
| Admin panel | HTTPS only, strong password, optional 2FA |
| Server | SSH key auth, disable root password login |
| Fail2ban | Block brute force on SMTP and admin |
| Secrets | All in `.env`, never in git |

---

## 10. What NOT to Build

| Skip | Reason |
|------|--------|
| Custom SMTP server | Mailcow handles this |
| Custom IMAP server | Dovecot via Mailcow |
| Custom webmail | Roundcube included |
| Payment/billing system | Not selling |
| Public signup page | Friends added manually |
| Mobile app | Web panel is enough |
| Complex analytics | Basic sent/failed/bounced is enough |
| Email warmup service | Manual schedule in this plan |

---

## 11. Timeline Summary

```
Month 1
├── Week 1: Phase 0 + 1 + 2 start (server, Mailcow, DNS primary domain)
├── Week 2: Phase 2 complete (all 10 domains DNS) + Phase 3 start
│            Warmup cap 25–30 | Week 2 se chhoti opt-in promo (5–10/day)
├── Week 3: Phase 3 complete (all websites OTP working)
└── Week 4: Phase 4 (queue, templates, dashboard)
             Warmup cap 60–80 (actual = jitna natural + promo ho)

Month 2
├── Phase 5 start (campaigns) — jab 2+ weeks stable sending ho
├── Phase 6 start (friend panel)
└── Warmup cap: 100 → 200 (actual force mat karo)

Month 3
├── Phase 5 + 6 complete
├── Phase 7 ongoing
└── Warmup cap: 200 → 400 (scheduled campaigns)
```

---

## Quick Reference

| Item | Value |
|------|-------|
| Mail server | Mailcow (Docker) |
| Panel | Laravel 11 + Livewire |
| Server | Ubuntu 22.04, 4GB+ RAM |
| Mail hostname | mail.yourmaindomain.com |
| SMTP port | 587 (STARTTLS) |
| IMAP port | 993 (SSL) |
| Warmup start | Cap 10–15/day (actual = natural OTP) |
| Warmup Month 1 end | Cap 60–80/day |
| Warmup Month 3 end | Cap 400/day |
| mail-tester target | 9/10+ |
| Tracker file | `PROGRESS_TRACKER.md` |

---

*See `PROGRESS_TRACKER.md` for live feature completion status.*
