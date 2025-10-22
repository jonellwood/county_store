# Friday Pending Requests Reminder - README

> **Automated weekly email reminders for department approvers with pending requests**

---

## 🎯 Purpose

Every Friday morning at 7:00 AM, this cron job automatically sends reminder emails to department heads, assistants, and asset managers who have pending employee requests waiting for approval.

---

## 📦 What's Included

- `cron-pending-requests-reminder.php` - Main cron script
- `test-pending-requests-reminder.php` - Testing/diagnostic tool
- `CRON_SETUP_PENDING_REQUESTS.md` - Complete setup guide
- `QUICK_REFERENCE_CRON.md` - Quick reference card
- `CRON_IMPLEMENTATION_SUMMARY.md` - Implementation overview

---

## ⚡ Quick Start

### 1. Test First

```bash
php test-pending-requests-reminder.php
```

### 2. Create Log Directory

```bash
sudo mkdir -p /var/log/store
sudo chown $(whoami):www-data /var/log/store
sudo chmod 755 /var/log/store
```

### 3. Schedule the Job

```bash
crontab -e
```

Add this line:

```
0 7 * * 5 /usr/bin/php /path/to/store/cron-pending-requests-reminder.php >> /var/log/store/pending-requests-reminder.log 2>&1
```

Save and exit. Done! ✅

---

## 📧 What Recipients See

**Subject Line:**

```
⏰ Pending Requests Reminder - [Department Name] (X items)
```

**Email Content:**

- Professional HTML email with Berkeley County branding
- Summary of total pending requests and dollar amount
- List of approvers (Department Head, Assistant, Asset Manager)
- Detailed table showing:
  - Order number
  - Employee name
  - Request date
  - Product details
  - Amount
- "Review & Approve Requests" button
- Access instructions (network requirement note)

---

## 🛠️ Configuration

### Current Settings

| Setting | Value |
|---------|-------|
| **Schedule** | Friday 7:00 AM |
| **SMTP Server** | 10.50.10.10:25 |
| **From Address** | <noreply@berkeleycountysc.gov> |
| **Database** | 10.50.10.94:3306 (uniform_orders) |
| **Log File** | /var/log/store/pending-requests-reminder.log |

### To Change Schedule

Edit your crontab (`crontab -e`) and modify the timing:

- **Daily at 8 AM:** `0 8 * * *`
- **Weekdays at 7 AM:** `0 7 * * 1-5`
- **Monday only:** `0 7 * * 1`
- **First Friday:** `0 7 1-7 * 5`

---

## 📊 Monitoring

### Check Last Run

```bash
tail -20 /var/log/store/pending-requests-reminder.log
```

### Find Errors

```bash
grep ERROR /var/log/store/pending-requests-reminder.log
```

### Watch Live

```bash
tail -f /var/log/store/pending-requests-reminder.log
```

### Verify Cron Schedule

```bash
crontab -l | grep pending-requests
```

---

## 🧪 Testing

### Dry Run (No Emails Sent)

```bash
php test-pending-requests-reminder.php
```

This shows:

- ✓ Database connectivity
- ✓ Pending requests found
- ✓ Email recipients
- ✓ SMTP server status
- ✓ Sample email content

### Send Test Emails

```bash
php cron-pending-requests-reminder.php
```

**Note:** This sends real emails! Only use when ready.

---

## 🚨 Troubleshooting

### No emails received?

1. **Check spam folders**
2. **Verify SMTP server:**

   ```bash
   telnet 10.50.10.10 25
   ```

3. **Review logs:**

   ```bash
   grep ERROR /var/log/store/pending-requests-reminder.log
   ```

### Script not running?

1. **Check crontab:**

   ```bash
   crontab -l
   ```

2. **Verify permissions:**

   ```bash
   ls -l cron-pending-requests-reminder.php
   ```

3. **Check PHP path:**

   ```bash
   which php
   ```

### Database errors?

1. **Test connection:**

   ```bash
   telnet 10.50.10.94 3306
   ```

2. **Verify credentials in `config.php`**
3. **Check table exists:**

   ```sql
   SELECT COUNT(*) FROM ord_ref WHERE status = 'Pending';
   ```

---

## 🔐 Security

- ✅ Script can only run from command line (not web accessible)
- ✅ Logs contain timestamps and execution details
- ✅ Email addresses generated from database (firstname.lastname format)
- ✅ No passwords or sensitive data in logs
- ⚠️ Log files should have restricted permissions (640)

---

## 📝 How It Works

1. **Query Database** - Find all departments with pending requests
2. **Get Approvers** - Lookup department head, assistant, asset manager
3. **Generate Emails** - Create HTML email for each department
4. **Send Emails** - One per department, to all approvers
5. **Log Results** - Record success/failure and timing

**Key Details:**

- Queries `ord_ref` table for status = 'Pending'
- Joins with `dep_ref` for approver names
- Generates emails from names (<firstname.lastname@berkeleycountysc.gov>)
- Prevents duplicate emails to same person
- Includes direct link to approval portal

---

## 📚 Documentation

- **Quick Reference:** `QUICK_REFERENCE_CRON.md` - One-page cheat sheet
- **Setup Guide:** `CRON_SETUP_PENDING_REQUESTS.md` - Complete installation
- **Implementation:** `CRON_IMPLEMENTATION_SUMMARY.md` - Technical details
- **System Docs:** `TECHNICAL_DOCUMENTATION.md` - Full system overview

---

## 🆘 Support

**For assistance:**

1. Check documentation files listed above
2. Run test script: `php test-pending-requests-reminder.php`
3. Review logs: `tail -50 /var/log/store/pending-requests-reminder.log`
4. Contact: [PLACEHOLDER: Jon Ellwood contact info]

---

## 📅 Maintenance

### Weekly

- No action needed (runs automatically)

### Monthly

- Review logs for errors
- Verify emails being received
- Check log file size

### As Needed

- Update email template (edit `generateOrdersTable()` function)
- Modify schedule (edit crontab)
- Adjust filters (edit SQL queries)

---

## ✅ Checklist for New Deployments

- [ ] Test database connectivity
- [ ] Verify SMTP server is accessible
- [ ] Create log directory
- [ ] Set appropriate permissions
- [ ] Run test script
- [ ] Send test email manually
- [ ] Schedule cron job
- [ ] Monitor first scheduled run
- [ ] Verify emails are received
- [ ] Set up log rotation (optional)
- [ ] Document in disaster recovery plan
- [ ] Train backup administrator

---

**Version:** 1.0  
**Created:** October 20, 2025  
**Author:** Jon Ellwood  
**Organization:** Berkeley County IT Department  

For the latest version and updates, check the git repository.
