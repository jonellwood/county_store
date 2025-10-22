# Pending Requests Reminder - Quick Reference

## 🚀 Quick Start

### Test the Script

```bash
php test-pending-requests-reminder.php
```

### Run Manually

```bash
php cron-pending-requests-reminder.php
```

### Schedule (Friday 7 AM)

```bash
crontab -e
# Add this line:
0 7 * * 5 /usr/bin/php /path/to/store/cron-pending-requests-reminder.php >> /var/log/store/pending-requests-reminder.log 2>&1
```

---

## 📋 What It Does

✅ Runs every **Friday morning at 7:00 AM**  
✅ Finds all departments with **pending user requests**  
✅ Sends **one email per department** to:

- Department Head
- Department Assistant  
- Asset Manager  
✅ Includes **order details** in a formatted table  
✅ Provides **direct link** to approval portal  

---

## 📁 Files

| File | Purpose |
|------|---------|
| `cron-pending-requests-reminder.php` | Main cron job script |
| `test-pending-requests-reminder.php` | Test script (no emails sent) |
| `CRON_SETUP_PENDING_REQUESTS.md` | Full documentation |

---

## 🔍 Monitoring

### Check Logs

```bash
tail -f /var/log/store/pending-requests-reminder.log
```

### Last Run Status

```bash
tail -20 /var/log/store/pending-requests-reminder.log
```

### Find Errors

```bash
grep ERROR /var/log/store/pending-requests-reminder.log
```

---

## 🛠️ Common Tasks

### Verify Cron is Scheduled

```bash
crontab -l | grep pending-requests
```

### Run Test (No Emails)

```bash
php test-pending-requests-reminder.php
```

### Send Emails Now

```bash
php cron-pending-requests-reminder.php
```

### Temporarily Disable

```bash
crontab -e
# Add # to comment out:
# 0 7 * * 5 /usr/bin/php ...
```

---

## 🐛 Troubleshooting

### No emails received?

1. Check spam/junk folders
2. Verify SMTP: `telnet 10.50.10.10 25`
3. Review logs: `grep ERROR /var/log/store/pending-requests-reminder.log`

### Script not running?

1. Check cron: `crontab -l`
2. Verify executable: `ls -l cron-pending-requests-reminder.php`
3. Check PHP path: `which php`

### Database errors?

1. Test connection: `telnet 10.50.10.94 3306`
2. Verify credentials in `config.php`
3. Check permissions: `SELECT * FROM ord_ref LIMIT 1;`

---

## 📧 Email Details

**From:** <noreply@berkeleycountysc.gov>  
**Subject:** ⏰ Pending Requests Reminder - [Department] (X items)  
**Link:** <https://store.berkeleycountysc.gov/storeadmin/pages/sign-in.php>  

**Recipients per department:**

- Department Head (if assigned)
- Department Assistant (if assigned & different from head)
- Asset Manager (if assigned & different from others)

---

## 📅 Schedule Options

| When | Cron Expression |
|------|----------------|
| Friday 7 AM | `0 7 * * 5` |
| Daily 8 AM | `0 8 * * *` |
| Weekdays 7 AM | `0 7 * * 1-5` |
| Monday 7 AM | `0 7 * * 1` |

---

## 📞 Support

**Script Issues:** Jon Ellwood - [PLACEHOLDER: Contact]  
**Email Issues:** [PLACEHOLDER: SMTP Admin]  
**Database Issues:** [PLACEHOLDER: DBA Contact]  

**Full Documentation:** See `CRON_SETUP_PENDING_REQUESTS.md`
