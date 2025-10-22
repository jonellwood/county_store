# Pending Requests Reminder Cron Job - Implementation Summary

## ✅ What Was Created

### 1. Main Cron Script

**File:** `cron-pending-requests-reminder.php`

A production-ready PHP script that:

- Runs every Friday at 7:00 AM
- Finds all departments with pending requests
- Sends formatted HTML emails to department approvers
- Includes comprehensive logging and error handling
- Can be run manually or scheduled via cron

**Key Features:**

- ✅ CLI-only execution (prevents web access)
- ✅ Database error handling
- ✅ Email recipient deduplication
- ✅ Professional HTML email templates
- ✅ Detailed logging with timestamps
- ✅ Execution summary with timing stats

### 2. Test Script

**File:** `test-pending-requests-reminder.php`

A diagnostic tool that:

- Tests database connectivity
- Shows what emails would be sent (without sending)
- Displays pending requests by department
- Checks SMTP server availability
- Validates PHPMailer configuration
- Provides sample email content

### 3. Setup Documentation

**File:** `CRON_SETUP_PENDING_REQUESTS.md`

Comprehensive guide covering:

- Installation instructions
- Cron job configuration options
- Log file management
- Monitoring and troubleshooting
- Security considerations
- Customization options
- Maintenance procedures

### 4. Quick Reference

**File:** `QUICK_REFERENCE_CRON.md`

One-page cheat sheet with:

- Common commands
- Quick start instructions
- Monitoring tips
- Troubleshooting checklist
- Contact information

### 5. Updated Technical Documentation

**File:** `TECHNICAL_DOCUMENTATION.md` (updated)

Added complete section on:

- Active cron jobs
- Scheduling details
- Dependencies
- Monitoring procedures

---

## 📧 How It Works

### Workflow

1. **Friday 7:00 AM** - Cron triggers the script
2. **Database Query** - Finds departments with pending requests:

   ```sql
   SELECT DISTINCT 
       ord_ref.department, 
       dep_ref.dep_head_empName, 
       dep_ref.dep_assist_empName, 
       dep_ref.dep_asset_mgr_empName, 
       dep_ref.dep_name
   FROM ord_ref
   JOIN dep_ref ON dep_ref.dep_num = ord_ref.department
   WHERE ord_ref.status = 'Pending'
   ```

3. **For Each Department** - Gets pending order details
4. **Generate Email** - Creates HTML email with:
   - Department name and approver names
   - Total pending requests and amount
   - Detailed order table (Order #, Requested For, Date, Product, etc.)
   - Direct link to approval portal
5. **Send Email** - To all approvers:
   - Department Head
   - Department Assistant (if different from head)
   - Asset Manager (if different from others)
6. **Log Results** - Records success/failure and timing

### Email Example

**Subject:** ⏰ Pending Requests Reminder - Public Works (5 items)

**Body:** Professional HTML email with:

- Summary box showing total requests and amount
- Info box with approver names
- Formatted table with all pending orders
- "Review & Approve Requests" button
- Footer with access instructions

---

## 🚀 Installation

### Quick Setup (3 steps)

```bash
# 1. Test the script
php test-pending-requests-reminder.php

# 2. Create log directory
sudo mkdir -p /var/log/store
sudo chown [your-user]:www-data /var/log/store

# 3. Schedule the cron job
crontab -e
# Add this line:
0 7 * * 5 /usr/bin/php /path/to/store/cron-pending-requests-reminder.php >> /var/log/store/pending-requests-reminder.log 2>&1
```

### What You Need

**Already in place:**

- ✅ Database connection (config.php)
- ✅ PHPMailer library (vendor/)
- ✅ Database tables (ord_ref, dep_ref)
- ✅ SMTP server (10.50.10.10:25)

**New requirements:**

- Log directory: `/var/log/store/`
- Cron access on the server
- Write permissions for log files

---

## 📊 Expected Output

### Console/Log Output

```
[2025-10-20 07:00:01] Starting Pending Requests Reminder Job
[2025-10-20 07:00:01] Database connection successful
[2025-10-20 07:00:01] Found 3 department(s) with pending requests
[2025-10-20 07:00:02] Processing department: Public Works (ID: 15)
[2025-10-20 07:00:02] Found 5 pending order(s) for Public Works
[2025-10-20 07:00:02] Adding recipient: john.doe@berkeleycountysc.gov
[2025-10-20 07:00:03] SUCCESS: Email sent to 1 recipient(s) for Public Works
============================================================
[2025-10-20 07:00:15] Job Complete
Departments processed: 3
Emails sent successfully: 3
Emails failed: 0
Execution time: 14.23 seconds
============================================================
```

---

## 🔧 Customization Options

### Change Schedule

Modify the cron expression:

- **Daily 8 AM:** `0 8 * * *`
- **Weekdays 7 AM:** `0 7 * * 1-5`
- **Monday 7 AM:** `0 7 * * 1`

### Modify Email Appearance

Edit `generateOrdersTable()` function:

- Colors and styling (CSS in the function)
- Table columns and data
- Button text and URL
- Footer content

### Add Recipients

Always CC someone:

```php
$mail->addCC('admin@berkeleycountysc.gov', 'Store Admin');
```

### Filter by Age

Only remind about old requests:

```php
WHERE ord_ref.status = 'Pending'
AND ord_ref.created <= DATE_SUB(CURDATE(), INTERVAL 3 DAY)
```

---

## 🎯 Testing

### Before Going Live

1. **Test database connection:**

   ```bash
   php test-pending-requests-reminder.php
   ```

2. **Verify pending requests exist:**

   ```sql
   SELECT COUNT(*) FROM ord_ref WHERE status = 'Pending';
   ```

3. **Send test email manually:**

   ```bash
   php cron-pending-requests-reminder.php
   ```

4. **Check email delivery:**
   - Verify emails arrive
   - Check spam folders
   - Confirm formatting looks good

5. **Monitor first scheduled run:**

   ```bash
   tail -f /var/log/store/pending-requests-reminder.log
   ```

---

## 📋 Monitoring & Maintenance

### Daily Checks (Optional)

- No action needed - runs automatically

### Weekly Checks

```bash
# Check if cron job ran
grep "Job Complete" /var/log/store/pending-requests-reminder.log | tail -1

# Check for errors
grep ERROR /var/log/store/pending-requests-reminder.log | tail -10
```

### Monthly Tasks

- Review logs for any patterns
- Verify emails are being received
- Check log file size (set up rotation if needed)

### Troubleshooting

See `CRON_SETUP_PENDING_REQUESTS.md` for detailed troubleshooting steps.

---

## 🔒 Security Notes

1. **Script is CLI-only** - Cannot be accessed via web browser
2. **Logs may contain sensitive data** - Restrict permissions (chmod 640)
3. **Email contains employee info** - Ensure SMTP connection is internal
4. **Database credentials** - Stored in config.php (not in version control)

---

## 📞 Support

### If Something Goes Wrong

1. **Check the logs:**

   ```bash
   tail -50 /var/log/store/pending-requests-reminder.log
   ```

2. **Run the test script:**

   ```bash
   php test-pending-requests-reminder.php
   ```

3. **Verify cron is scheduled:**

   ```bash
   crontab -l | grep pending-requests
   ```

4. **Check common issues:**
   - Database connectivity (telnet 10.50.10.94 3306)
   - SMTP server (telnet 10.50.10.10 25)
   - File permissions (ls -l cron-pending-requests-reminder.php)
   - Log directory (ls -ld /var/log/store)

### Contact Information

- **Script Author:** Jon Ellwood - [PLACEHOLDER: Email/Phone]
- **SMTP Issues:** [PLACEHOLDER: Mail Admin Contact]
- **Database Issues:** [PLACEHOLDER: DBA Contact]

---

## 📚 Documentation Files

| File | Purpose |
|------|---------|
| `cron-pending-requests-reminder.php` | Main script |
| `test-pending-requests-reminder.php` | Test/diagnostic tool |
| `CRON_SETUP_PENDING_REQUESTS.md` | Complete setup guide |
| `QUICK_REFERENCE_CRON.md` | Quick reference card |
| `TECHNICAL_DOCUMENTATION.md` | Full system documentation |
| This file | Implementation summary |

---

## ✅ Next Steps

1. [ ] Review all scripts and documentation
2. [ ] Fill in placeholder contact information
3. [ ] Test on development/staging server
4. [ ] Verify email formatting and delivery
5. [ ] Set up log directory on production server
6. [ ] Schedule cron job on production server
7. [ ] Monitor first few runs
8. [ ] Set up log rotation
9. [ ] Add to disaster recovery documentation
10. [ ] Train backup personnel on monitoring

---

## 📝 Notes

- The script uses the existing `ord_ref` and `dep_ref` tables
- Email addresses are generated from names (<firstname.lastname@berkeleycountysc.gov>)
- No database changes required - works with existing schema
- Backwards compatible - doesn't affect existing functionality
- Can run manually anytime: `php cron-pending-requests-reminder.php`
- Logs are append-only - won't overwrite previous runs

---

**Created:** October 20, 2025  
**Author:** GitHub Copilot (for Jon Ellwood)  
**Version:** 1.0  
