# Pending Requests Reminder - Cron Job Setup

## Overview

This cron job sends weekly email reminders to department approvers (heads, assistants, and asset managers) who have pending user requests awaiting their review.

**Schedule:** Every Friday at 7:00 AM  
**Script:** `cron-pending-requests-reminder.php`

---

## Features

✅ **Automated Weekly Reminders** - Runs every Friday morning  
✅ **Smart Recipient Detection** - Emails department heads, assistants, and asset managers  
✅ **Duplicate Prevention** - Avoids sending multiple emails to the same person  
✅ **Detailed Order Information** - Shows product details, quantities, and amounts  
✅ **Professional HTML Emails** - Formatted tables with Berkeley County branding  
✅ **Comprehensive Logging** - Detailed logs for monitoring and troubleshooting  
✅ **Error Handling** - Graceful handling of database and email errors  

---

## Installation

### 1. Make the Script Executable

```bash
chmod +x /path/to/store/cron-pending-requests-reminder.php
```

### 2. Test the Script Manually

Before setting up the cron job, test the script:

```bash
# Run the script
php /path/to/store/cron-pending-requests-reminder.php

# Or if you made it executable:
/path/to/store/cron-pending-requests-reminder.php
```

### 3. Set Up the Cron Job

Edit your crontab:

```bash
crontab -e
```

Add one of the following lines:

#### Option A: Friday at 7:00 AM (Recommended)

```bash
0 7 * * 5 /usr/bin/php /path/to/store/cron-pending-requests-reminder.php >> /var/log/store/pending-requests-reminder.log 2>&1
```

#### Option B: Every Day at 8:00 AM (if you want daily reminders)

```bash
0 8 * * * /usr/bin/php /path/to/store/cron-pending-requests-reminder.php >> /var/log/store/pending-requests-reminder.log 2>&1
```

#### Option C: Friday at 7:00 AM with separate error log

```bash
0 7 * * 5 /usr/bin/php /path/to/store/cron-pending-requests-reminder.php >> /var/log/store/pending-requests-reminder.log 2>> /var/log/store/pending-requests-errors.log
```

**Cron Schedule Breakdown:**

- `0` - Minute (0 = on the hour)
- `7` - Hour (7 = 7:00 AM)
- `*` - Day of month (any day)
- `*` - Month (any month)
- `5` - Day of week (5 = Friday, 0 = Sunday)

### 4. Create Log Directory

```bash
# Create log directory if it doesn't exist
sudo mkdir -p /var/log/store

# Set appropriate permissions
sudo chown [your-user]:www-data /var/log/store
sudo chmod 755 /var/log/store
```

---

## Testing

### Test Script

A test script is provided: `test-pending-requests-reminder.php`

Run it to verify everything works:

```bash
php test-pending-requests-reminder.php
```

This will:

- ✓ Check database connection
- ✓ Query for pending requests
- ✓ Show what emails would be sent (without actually sending)
- ✓ Display sample email HTML

### Manual Test Run

To do a real test (sends actual emails):

```bash
# Test on a Friday
php cron-pending-requests-reminder.php

# Or force it to run on any day by temporarily commenting out the day check
# (Currently the script runs any day - add a day check if needed)
```

---

## Log Files

### Log Location

Default: `/var/log/store/pending-requests-reminder.log`

### Log Format

```
[2025-10-20 07:00:01] Starting Pending Requests Reminder Job
[2025-10-20 07:00:01] Database connection successful
[2025-10-20 07:00:01] Found 3 department(s) with pending requests
[2025-10-20 07:00:02] Processing department: Public Works (ID: 15)
[2025-10-20 07:00:02] Found 5 pending order(s) for Public Works
[2025-10-20 07:00:02] Adding recipient: John Doe <john.doe@berkeleycountysc.gov>
[2025-10-20 07:00:03] SUCCESS: Email sent to 1 recipient(s) for Public Works
============================================================
[2025-10-20 07:00:15] Job Complete
Departments processed: 3
Emails sent successfully: 3
Emails failed: 0
Execution time: 14.23 seconds
============================================================
```

### Log Rotation

Set up log rotation to prevent logs from growing too large:

Create `/etc/logrotate.d/store-cron`:

```
/var/log/store/*.log {
    daily
    rotate 30
    compress
    delaycompress
    notifempty
    create 0640 [your-user] www-data
    sharedscripts
}
```

---

## Monitoring

### Check if Cron Job is Scheduled

```bash
crontab -l | grep pending-requests
```

### View Recent Logs

```bash
tail -f /var/log/store/pending-requests-reminder.log
```

### Check Last Execution

```bash
# View last 50 lines
tail -50 /var/log/store/pending-requests-reminder.log

# Search for errors
grep ERROR /var/log/store/pending-requests-reminder.log

# Check success count for today
grep "Job Complete" /var/log/store/pending-requests-reminder.log | tail -1
```

### Email Test

Verify emails are being received:

1. Check spam folders
2. Verify SMTP server logs: [PLACEHOLDER: SMTP log location]
3. Check recipient email addresses are correct

---

## Troubleshooting

### Script Doesn't Run

**Problem:** Cron job doesn't execute  
**Solutions:**

- Verify cron service is running: `sudo service cron status`
- Check crontab is properly formatted: `crontab -l`
- Check system logs: `grep CRON /var/log/syslog`
- Verify PHP path: `which php`

### Database Connection Fails

**Problem:** "Database connection failed"  
**Solutions:**

- Verify database server is running: `telnet 10.50.10.94 3306`
- Check credentials in `config.php`
- Verify network connectivity
- Check database server logs

### Emails Not Sending

**Problem:** Script runs but emails aren't received  
**Solutions:**

- Verify SMTP server is reachable: `telnet 10.50.10.10 25`
- Check spam/junk folders
- Verify email addresses are correctly formatted
- Check SMTP server logs
- Review script logs for "ERROR" messages

### No Pending Requests Found

**Problem:** "No pending requests found"  
**Solutions:**

- Verify there are actually pending requests in database
- Check that `ord_ref.status = 'Pending'` matches your database
- Run query manually:

  ```sql
  SELECT COUNT(*) FROM ord_ref WHERE status = 'Pending';
  ```

### Permission Denied

**Problem:** "Permission denied" when running script  
**Solutions:**

- Make script executable: `chmod +x cron-pending-requests-reminder.php`
- Check file ownership: `ls -l cron-pending-requests-reminder.php`
- Verify log directory permissions: `ls -ld /var/log/store/`

---

## Customization

### Change Email Schedule

Edit the crontab timing:

- **Daily at 8 AM:** `0 8 * * *`
- **Monday at 7 AM:** `0 7 * * 1`
- **Weekdays at 7 AM:** `0 7 * * 1-5`
- **First Friday at 7 AM:** `0 7 1-7 * 5`

### Modify Email Content

Edit the `generateOrdersTable()` function in `cron-pending-requests-reminder.php`:

- Change colors/styling in the `<style>` section
- Modify table columns
- Update footer text
- Change button URL

### Add Additional Recipients

To always CC certain people (like IT admin):

```php
// In sendReminderEmail() function, after setFrom():
$mail->addCC('admin@berkeleycountysc.gov', 'Store Admin');
```

### Filter by Days Pending

To only remind about requests older than X days, modify the SQL in `getDepartmentsWithPendingRequests()`:

```php
WHERE ord_ref.status = 'Pending'
AND ord_ref.created <= DATE_SUB(CURDATE(), INTERVAL 3 DAY)  -- 3+ days old
```

---

## Maintenance

### Regular Tasks

- **Weekly:** Review logs for errors
- **Monthly:** Verify emails are being received
- **Quarterly:** Test script manually
- **Annually:** Review and update email content/styling

### Disabling Temporarily

To temporarily disable the cron job without deleting it:

```bash
crontab -e
# Add # at the start of the line to comment it out:
# 0 7 * * 5 /usr/bin/php /path/to/store/cron-pending-requests-reminder.php >> /var/log/store/pending-requests-reminder.log 2>&1
```

### Updating the Script

```bash
# Backup current version
cp cron-pending-requests-reminder.php cron-pending-requests-reminder.php.backup

# Pull latest changes
git pull

# Test the updated script
php cron-pending-requests-reminder.php

# Monitor first scheduled run
tail -f /var/log/store/pending-requests-reminder.log
```

---

## Security Considerations

⚠️ **Important Security Notes:**

1. **File Permissions:** Ensure the script is only readable by necessary users

   ```bash
   chmod 750 cron-pending-requests-reminder.php
   ```

2. **Log Security:** Protect log files from unauthorized access

   ```bash
   chmod 640 /var/log/store/*.log
   ```

3. **Email Privacy:** Emails contain employee and order information
   - Ensure SMTP connection is secure
   - Consider encrypting sensitive data in emails
   - Use SMTP authentication if available

4. **Database Credentials:** Never commit `config.php` to version control
   - Add to `.gitignore`
   - Use environment variables for production

---

## Support

### Getting Help

- **Documentation:** This file and `TECHNICAL_DOCUMENTATION.md`
- **Script Author:** Jon Ellwood - [PLACEHOLDER: Email]
- **IT Support:** [PLACEHOLDER: Phone/Email]

### Reporting Issues

When reporting problems, include:

1. Last 50 lines of log file
2. Exact error message
3. When the issue started
4. Any recent changes made

---

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2025-10-20 | Initial creation of cron job script |

---

**Next Steps:**

1. ✅ Review and test the script
2. ✅ Set up log directory
3. ✅ Configure crontab
4. ✅ Test email delivery
5. ✅ Monitor first few runs
6. ✅ Document in `TECHNICAL_DOCUMENTATION.md`
