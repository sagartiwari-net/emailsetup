# Mail System — Progress Tracker

> **Last updated:** June 20, 2026  
> **Overall progress:** 61% (local work complete — server deploy pending)  
> **Current phase:** Ready for Hetzner/Mailcow deploy  
> **Warmup status:** Not started

---

## Overall Progress

```
[████████████░░░░░░░░] 61%

Phase 0  [░░░░░░░░░░] 0%   Server & Infrastructure
Phase 1  [░░░░░░░░░░] 0%   Mailcow Installation
Phase 2  [░░░░░░░░░░] 0%   DNS & Deliverability
Phase 3  [█████████░] 88%  Laravel API + Website Integration
Phase 4  [██████████] 100% Queue, Templates & Dashboard
Phase 5  [██████████] 100% Bulk Campaigns & Scheduling
Phase 6  [███████░░░] 77%  Multi-Tenant Friend Panel
Phase 7  [███░░░░░░░] 23%  Monitoring & Security
```

| Phase | Name | Tasks | Done | Progress |
|-------|------|-------|------|----------|
| 0 | Server & Infrastructure | 10 | 0 | 0% |
| 1 | Mailcow Installation | 12 | 0 | 0% |
| 2 | DNS & Deliverability | 11 | 0 | 0% |
| 3 | Laravel API + Websites | 16 | 14 | 88% |
| 4 | Queue & Templates | 12 | 12 | 100% |
| 5 | Bulk Campaigns | 15 | 15 | 100% |
| 6 | Friend Panel | 13 | 10 | 77% |
| 7 | Monitoring & Security | 13 | 3 | 23% |
| **Total** | | **102** | **62** | **61%** |

> **Note:** Phases 0–2 aur Mailcow SMTP server par hi complete honge. Local development 100% ready hai.

---

## Warmup Tracker

> **Daily Cap** = system maximum | **Actual** = jo sach mein bheje | Actual ko force mat karo

| Week | Daily Cap (Max) | Actual (avg) | Mix | Status |
|------|-----------------|--------------|-----|--------|
| Week 1 | 10–15 | — | OTP + transactional only | ⬜ Not started |
| Week 2 | 25–30 | — | OTP + 5–10 opt-in promo | ⬜ Not started |
| Week 3 | 40–50 | — | Natural + 10–20 promo | ⬜ Not started |
| Week 4 | 60–80 | — | Natural + 20–30 promo | ⬜ Not started |
| Month 2 | 100 → 200 | — | + light campaigns | ⬜ Not started |
| Month 3 | 200 → 400 | — | + scheduled campaigns | ⬜ Not started |

### Daily Send Log (optional — weekly average note karo)

| Date / Week | OTP | Transactional | Promo | Total Actual | Cap | Inbox OK? |
|-------------|-----|---------------|-------|--------------|-----|-----------|
| — | — | — | — | — | — | — |

**Legend:** ⬜ Not started | 🟡 In progress | ✅ Complete | 🔴 Issue/Blocked

---

## Phase 0 — Server & Infrastructure Prep
**Progress: 0/10 — 0%**  
**Note:** Setup scripts ready in `scripts/phase0-server-setup.sh`

- [ ] Verify server specs (4GB RAM, 2 CPU, 50GB SSD)
- [ ] Ubuntu 22.04 LTS installed
- [ ] System updated (`apt update && apt upgrade`)
- [ ] Docker + Docker Compose installed
- [ ] Firewall configured (UFW: 22, 25, 80, 443, 587, 993, 995)
- [ ] IP blacklist check passed (mxtoolbox.com)
- [ ] PTR record requested/set (`mail.yourdomain.com`)
- [ ] Primary mail domain decided
- [ ] Daily backup strategy setup
- [ ] Server credentials documented

---

## Phase 1 — Mailcow Installation
**Progress: 0/12 — 0%**  
**Note:** Install script ready in `scripts/phase1-mailcow-install.sh`

- [ ] Mailcow repo cloned
- [ ] Config generated (`generate_config.sh`)
- [ ] `MAILCOW_HOSTNAME` set in config
- [ ] Docker stack started (`docker compose up -d`)
- [ ] Admin panel accessible via HTTPS
- [ ] Default admin password changed
- [ ] Test mailbox created (`test@yourdomain.com`)
- [ ] Send test via webmail (Roundcube)
- [ ] Receive test from external email (Gmail)
- [ ] SMTP auth tested (port 587, STARTTLS)
- [ ] DKIM key verified in admin
- [ ] All Mailcow containers running healthy

---

## Phase 2 — DNS & Deliverability
**Progress: 0/11 — 0%**

- [ ] Primary domain added in Mailcow
- [ ] DNS configured for primary domain (MX, SPF, DKIM, DMARC)
- [ ] DNS propagation verified
- [ ] mail-tester.com score 9/10+ (primary domain)
- [ ] All mail-tester issues fixed
- [ ] 9 remaining domains added in Mailcow
- [ ] DNS configured for all 10 domains
- [ ] mail-tester score 9/10+ (all domains)
- [ ] PTR record confirmed
- [ ] DNS records documented
- [ ] Warmup started (5–10 OTP/day)

### Domain DNS Status

| # | Domain | MX | SPF | DKIM | DMARC | mail-tester | Status |
|---|--------|----|-----|------|-------|-------------|--------|
| 1 | — | ⬜ | ⬜ | ⬜ | ⬜ | — | Not started |
| 2 | — | ⬜ | ⬜ | ⬜ | ⬜ | — | Not started |
| 3 | — | ⬜ | ⬜ | ⬜ | ⬜ | — | Not started |
| 4 | — | ⬜ | ⬜ | ⬜ | ⬜ | — | Not started |
| 5 | — | ⬜ | ⬜ | ⬜ | ⬜ | — | Not started |
| 6 | — | ⬜ | ⬜ | ⬜ | ⬜ | — | Not started |
| 7 | — | ⬜ | ⬜ | ⬜ | ⬜ | — | Not started |
| 8 | — | ⬜ | ⬜ | ⬜ | ⬜ | — | Not started |
| 9 | — | ⬜ | ⬜ | ⬜ | ⬜ | — | Not started |
| 10 | — | ⬜ | ⬜ | ⬜ | ⬜ | — | Not started |

---

## Phase 3 — Laravel API + Website Integration
**Progress: 14/16 — 88%**

- [x] Laravel 11 project created (`mail-panel`)
- [x] `.env` configured (DB, Redis, SMTP)
- [x] Database migrations run
- [x] `POST /api/v1/send` endpoint built
- [x] API key authentication middleware
- [ ] Mailcow SMTP connection working (needs server)
- [x] OTP email template created
- [x] Welcome/details email template created
- [x] Rate limiter per API key (warmup limits)
- [x] Mail send logging (success/fail)
- [x] Admin login page
- [x] Admin: view send logs
- [x] Admin: generate/revoke API keys
- [x] PHP SDK/helper for websites
- [x] Website registry in admin panel
- [x] Demo pilot website (`demo-website/`)
- [ ] All 10 websites connected and OTP working (needs server + DNS)

### Website Integration Status

| # | Website | Domain | API Key | OTP Working | Status |
|---|---------|--------|---------|-------------|--------|
| 1 | Demo Shop | Register in admin | ⬜ | ⬜ | Pilot ready |
| 2 | — | — | ⬜ | ⬜ | Not started |
| 3 | — | — | ⬜ | ⬜ | Not started |
| 4 | — | — | ⬜ | ⬜ | Not started |
| 5 | — | — | ⬜ | ⬜ | Not started |
| 6 | — | — | ⬜ | ⬜ | Not started |
| 7 | — | — | ⬜ | ⬜ | Not started |
| 8 | — | — | ⬜ | ⬜ | Not started |
| 9 | — | — | ⬜ | ⬜ | Not started |
| 10 | — | — | ⬜ | ⬜ | Not started |

---

## Phase 4 — Queue, Templates & Dashboard
**Progress: 12/12 — 100%**

- [x] Database queue driver configured (default)
- [x] Mail sending moved to queued jobs
- [x] Retry logic (3 attempts, backoff) — job class ready
- [x] Template manager in admin (CRUD)
- [x] Template variables (`{{otp}}`, `{{name}}`, etc.)
- [x] Dashboard: today's sends + success rate
- [x] Dashboard: per-website/domain statistics
- [x] Daily send counter (warmup limit enforced)
- [x] Bounce handling (mark invalid emails)
- [x] Failed job monitoring (admin page)
- [x] API returns `message_id`
- [x] `GET /api/v1/status/{message_id}` endpoint

---

## Phase 5 — Bulk Campaigns & Scheduling
**Progress: 15/15 — 100%**

- [x] Subscriber list model + CSV upload
- [x] CSV validation (format, dedup)
- [x] Campaign create form
- [x] Campaign preview + test send
- [x] Send now option
- [x] One-time schedule (future date/time)
- [x] Recurring schedule (every X days)
- [x] Campaign queue processor (cron every 5 min)
- [x] Batch sending (50/batch, 5 min gap)
- [x] Daily warmup limit enforced in campaigns
- [x] Unsubscribe link in promo templates
- [x] Unsubscribe endpoint working
- [x] Campaign stats (sent/failed/skipped)
- [x] Bounced/unsubscribed counts in campaign UI
- [x] Pause / resume / cancel campaign
- [x] Campaign history log

---

## Phase 6 — Multi-Tenant Friend Panel
**Progress: 10/13 — 77%**

- [x] User roles (`super_admin`, `tenant`)
- [x] Super admin: create tenant manually
- [x] Super admin: assign domains to tenant
- [x] Super admin: set daily limit per tenant
- [x] Tenant isolated dashboard (data scoped by tenant_id)
- [ ] Tenant: create mailboxes (Mailcow API — needs server)
- [x] Tenant: view/generate API key
- [x] Tenant: manage campaigns
- [x] Tenant: view send logs
- [x] Tenant: manage templates
- [x] Tenant: manage subscriber lists
- [x] Super admin: view all tenant activity
- [x] Super admin: suspend tenant (blocks login)

### Friend Accounts

| # | Name | Domain | Daily Limit | Panel Ready | Status |
|---|------|--------|-------------|-------------|--------|
| 1 | — | — | — | ⬜ | Not created |

---

## Phase 7 — Monitoring & Security
**Progress: 3/13 — 23%**

- [ ] Fail2ban installed and configured
- [ ] UFW firewall verified
- [ ] SSL auto-renewal working
- [x] Daily backup script template (`scripts/backup.sh`)
- [ ] Weekly backup restore test
- [ ] Weekly IP blacklist check
- [ ] bi-weekly mail-tester score check
- [ ] Monthly security updates
- [ ] Log rotation configured
- [ ] Disk usage alert (> 80%)
- [ ] Queue stuck alert
- [x] Runbook: IP blacklist recovery (`docs/runbook-ip-blacklist.md`)
- [x] Runbook: add new domain / website / friend (`docs/runbook-add-domain.md`)
- [x] Health check command (`php artisan mail-panel:health`)

---

## Milestones

| Milestone | Target Date | Actual Date | Status |
|-----------|-------------|-------------|--------|
| Laravel panel built locally | 2026-06-20 | 2026-06-20 | ✅ |
| Local features complete | 2026-06-20 | 2026-06-20 | ✅ |
| Server ready | — | — | ⬜ |
| Mailcow running | — | — | ⬜ |
| First OTP sent (production) | — | — | ⬜ |
| All 10 domains DNS ready | — | — | ⬜ |
| All 10 websites OTP working | — | — | ⬜ |
| mail-tester 9/10+ all domains | — | — | ⬜ |
| Warmup cap 60–80/day set | — | — | ⬜ |
| First opt-in promo sent (Week 2) | — | — | ⬜ |
| First campaign sent | — | — | ⬜ |
| First friend account created | — | — | ⬜ |
| Warmup cap 400/day set | — | — | ⬜ |

---

## Issues & Notes Log

| Date | Phase | Issue / Note | Resolution | Status |
|------|-------|--------------|------------|--------|
| 2026-06-20 | 3 | Laravel panel + API built on local Mac | Test with `php artisan serve` | ✅ |
| 2026-06-20 | 5 | Bulk campaigns, subscribers, unsubscribe, scheduler | Local ready | ✅ |
| 2026-06-20 | 6 | Tenants, websites registry, suspend, activity view | Local ready | ✅ |
| 2026-06-20 | 7 | Runbooks + health command + backup script | Local ready | ✅ |
| 2026-06-20 | Deploy | aaPanel deploy guide created | After automation ends | 🟡 |

---

## How to Update This Tracker

1. Jab koi task complete ho → `[ ]` ko `[x]` karo
2. Phase ke done count update karo (e.g. `3/10`)
3. Progress bar update karo (e.g. `30%`)
4. Overall table mein total done update karo
5. Overall progress bar recalculate karo: `(total done / 102) * 100`
6. Milestone complete ho to Actual Date + ✅ lagao
7. Koi issue aaye to Issues & Notes Log mein entry add karo
8. Warmup tracker mein Actual column update karo har week

### Progress Bar Guide

```
0%   [░░░░░░░░░░]
10%  [█░░░░░░░░░]
20%  [██░░░░░░░░]
30%  [███░░░░░░░]
40%  [████░░░░░░]
50%  [█████░░░░░]
60%  [██████░░░░]
70%  [███████░░░]
80%  [████████░░]
90%  [█████████░]
100% [██████████]
```

---

*Full plan details: `DEVELOPMENT_PLAN.md`*
