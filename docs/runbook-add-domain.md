# Runbook: Add New Domain / Website / Friend Account

## Add a new sending domain

### On server (after Mailcow is installed)

1. Add domain in Mailcow admin → Configuration → Domains
2. Copy DNS records (MX, SPF, DKIM, DMARC) from Mailcow
3. Add DNS records at your registrar
4. Wait for propagation (up to 24h)
5. Test with https://www.mail-tester.com (target: 9/10+)
6. Update `PROGRESS_TRACKER.md` domain table

### In Mail Panel admin

1. **Domains** → Add domain name + from email
2. Assign to tenant (if friend account)
3. **API Keys** → Generate key for that domain
4. **Websites** → Register site, link domain + API key
5. Send test mail from **Test Mail** page

---

## Connect a website (OTP)

1. Copy `sdk/MailPanelClient.php` to your website
2. Generate API key in admin for the site's domain
3. Integrate:

```php
require_once 'MailPanelClient.php';
$client = new MailPanelClient('https://mail-api.yourdomain.com', 'mk_your_key');
$client->sendOtp($userEmail, $otpCode, $userName);
```

4. See `demo-website/` for a working pilot example
5. Mark website status as **Testing** → send test OTP → mark **Connected**

---

## Add a friend account (tenant)

1. **Admin → Tenants → Add Account**
2. Set name, daily cap, owner email/password
3. Assign domain(s) to tenant: **Domains → Assign**
4. Friend logs in at `https://mail-api.yourdomain.com/login`
5. Friend can manage: API keys, templates, campaigns, subscribers, logs
6. To suspend: **Tenants → Edit → Status: Suspended** (blocks login immediately)

---

## Warmup for new domain

| Week | Daily Cap | Allowed mail |
|------|-----------|--------------|
| 1 | 10–15 | OTP + transactional only |
| 2 | 25–30 | + 5–10 opt-in promo |
| 3 | 40–50 | + 10–20 promo |
| 4+ | Gradual increase | Monitor mail-tester |

Set tenant daily limit in **Tenants → Edit → Daily Limit**.

---

## Checklist after adding anything new

- [ ] DNS records live and verified
- [ ] mail-tester score 9/10+
- [ ] Test OTP sent successfully
- [ ] Website registered in admin panel
- [ ] PROGRESS_TRACKER.md updated
