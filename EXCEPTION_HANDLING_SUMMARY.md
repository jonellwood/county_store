# Exception Handling - Quick Summary

## What Was Added

✅ **Automatic exception reports** sent to `store@berkeleycountysc.gov` when departments have pending requests but no valid email recipients.

---

## The Problem (Before)

When a department had pending requests but no assigned approvers (department head, assistant, or asset manager), the system would:

- Log a warning message
- Skip sending any email
- **Nobody would know about the pending requests** ⚠️

---

## The Solution (After)

Now when this happens, the system:

1. Logs the warning (same as before)
2. **Sends a detailed exception report to <store@berkeleycountysc.gov>** ✅
3. Continues processing other departments
4. Tracks exception reports in the job summary

---

## Exception Email Contains

📧 **Subject:** ⚠️ EXCEPTION: No Recipients for Pending Requests - [Department Name]

**Content:**

- ⚠️ Red alert header indicating exception
- Department ID and name
- All approver fields (showing "NOT ASSIGNED" for missing ones)
- Complete table of pending requests with:
  - Order numbers
  - Employee names
  - Products requested
  - Amounts
  - Dates
- Total count and dollar amount
- **5 recommended actions** to fix the issue
- Timestamp and script information

---

## Real-World Example

From test run on October 20, 2025:

### Department Found with No Recipients

```
Department: BCWS Solid Waste Landfill Gas (ID: 48045)
  Department Head: Not Assigned
  Department Assistant: Not Assigned  
  Asset Manager: Not Assigned
  Pending Orders: 2 items
  Total Amount: $48.07
```

### What Happens

1. ❌ Normal reminder email **cannot be sent** (no recipients)
2. ✅ Exception report **sent to <store@berkeleycountysc.gov>**
3. 📝 Logged as: "Exception reports sent: 1"
4. 👤 Store admin receives alert with all order details
5. 🔧 Admin can fix department assignments and notify manually if urgent

---

## How to Monitor

### Check for Exceptions in Logs

```bash
grep "EXCEPTION REPORT" /var/log/store/pending-requests-reminder.log
```

### View Last Run Summary

```bash
tail -20 /var/log/store/pending-requests-reminder.log
```

Look for:

```
Exception reports sent: 2
```

### Test Before Friday

```bash
php test-pending-requests-reminder.php
```

Shows which departments would trigger exceptions.

---

## What Store Admins Should Do

### 1. When You Receive an Exception Report

📧 Check your <store@berkeleycountysc.gov> inbox Friday mornings

### 2. Review the Details

- Department name and ID
- What's pending (products, amounts, employees)
- How urgent it is (check dates)

### 3. Fix the Database

Update the `dep_ref` table with proper approvers:

```sql
UPDATE dep_ref
SET dep_head_empName = 'John Doe',
    dep_assist_empName = 'Jane Smith'
WHERE dep_num = 48045;
```

### 4. Manually Notify (If Urgent)

- Call the department directly
- Email them about pending requests
- Provide link: <https://store.berkeleycountysc.gov/storeadmin/pages/sign-in.php>

### 5. Verify Fix Next Friday

- Department should receive normal reminder
- No exception report for that department

---

## Files Modified

1. ✅ `cron-pending-requests-reminder.php` - Added `sendExceptionReport()` function
2. ✅ `test-pending-requests-reminder.php` - Shows exception warnings

---

## Test Results

**Test Date:** October 20, 2025

**Departments Found:**

- 8 total departments with pending requests
- 6 have valid recipients → will get reminder emails
- 2 have NO recipients → will trigger exception reports

**Exception Reports That Would Be Sent:**

1. BCWS Solid Waste Landfill Gas (2 orders, $48.07)
2. Victim Witness-Magistrate (5 orders, $175.85)

Both will send detailed reports to <store@berkeleycountysc.gov> ✅

---

## Benefits

✅ **Zero Lost Requests** - Every pending request gets attention  
✅ **Immediate Alert** - Store admin knows right away  
✅ **Complete Details** - All order info in exception report  
✅ **Actionable** - Clear steps to fix the problem  
✅ **Tracked** - Counted in job summary logs  
✅ **Self-Documenting** - Exception email explains everything  

---

## Questions?

See full documentation:

- `EXCEPTION_REPORTING_IMPLEMENTATION.md` - Complete technical details
- `CRON_SETUP_PENDING_REQUESTS.md` - Setup and troubleshooting
- `QUICK_REFERENCE_CRON.md` - Quick commands

---

**Ready to Use!** ✅

The exception handling is fully implemented and tested. No additional setup needed - it will work automatically when the cron job runs next Friday.
