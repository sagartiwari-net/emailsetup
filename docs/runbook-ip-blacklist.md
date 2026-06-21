# Runbook: IP Blacklist Recovery

If your mail server IP gets blacklisted, follow these steps.

## 1. Confirm the listing

```bash
# Check major blacklists
curl -s "https://mxtoolbox.com/api/v1/Lookup/blacklist/YOUR.SERVER.IP" 
# Or use: https://multirbl.valli.org/lookup/YOUR.SERVER.IP.html
```

## 2. Stop bulk sending immediately

- Pause all running campaigns in Mail Panel admin
- Reduce daily cap to OTP-only (10–15/day) in tenant settings
- Do not send promotional mail until delisted

## 3. Identify the cause

Common causes:
- Sudden volume spike (warmup skipped)
- Invalid/bought email lists
- Missing SPF/DKIM/DMARC
- Wrong PTR record
- Compromised account sending spam

Check Mail Panel logs: **Admin → Mail Logs** and **Failed Jobs**

## 4. Request delisting

Each blacklist has its own removal form:
- **Spamhaus**: https://www.spamhaus.org/query/ip/YOUR.IP
- **Barracuda**: https://www.barracudacentral.org/rbl/removal-request
- **SORBS**: http://www.sorbs.net/lookup.shtml

Provide:
- Your server IP
- Proof you fixed the issue (SPF/DKIM screenshots, warmup plan)
- Statement that you are a legitimate sender

## 5. Fix root cause before resuming

- [ ] SPF, DKIM, DMARC verified for all domains
- [ ] PTR matches `mail.yourdomain.com`
- [ ] Warmup caps enforced in Mail Panel
- [ ] Only opt-in subscribers in lists
- [ ] fail2ban active on server

## 6. Gradual recovery

1. Week 1: OTP/transactional only (10–15/day)
2. Week 2: Add 5–10 opt-in promo/day
3. Monitor mail-tester.com score weekly
4. Increase cap only if inbox placement is good

## 7. Prevention

- Run `php artisan mail-panel:health` daily (cron)
- Weekly IP blacklist check
- Never exceed warmup daily cap
- Keep bounce rate under 2%
