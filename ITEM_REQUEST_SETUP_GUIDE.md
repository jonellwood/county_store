# Item Request System - Setup & Testing Guide

## ✅ What's Been Implemented

### 1. **Employee Validation System**

- **File:** `validate-employee.php`
- **Function:** Validates employee against `uniform_orders.emp_sync` table
- **Validates:** Last name + Employee number
- **Returns:** Full name, email, department name & number

### 2. **Updated Request Form**

- **File:** `request-items.php`
- **Features:**
  - Step 1: Employee validation (last name + emp number)
  - Shows validated employee info (read-only)
  - Email handling:
    - If email exists in emp_sync → uses it automatically (not editable)
    - If no email → shows input field for user to enter one
  - Updated categories dropdown:
    - Pants
    - Shirts
    - Hats
    - Outerwear
    - Sweatshirts
    - Boots
    - Accessories
  - **Required product URL** field for each item
  - **Multiple items support** - Add/remove items dynamically
  - Each item has: category, product URL, name, details, quantity, priority

### 3. **Updated Submission Handler**

- **File:** `submit-item-request.php`
- **Features:**
  - Validates all employee and item data
  - Inserts into TWO tables:
    - `item_requests` (parent - one per submission)
    - `request_items` (children - multiple items per request)
  - Sends HTML emails to:
    - Admin (with all items listed)
    - Employee (confirmation with request #)

### 4. **Updated Database Schema**

- **File:** `create_item_requests_table.sql`
- **Tables:**
  - `item_requests` - Main request (employee info, reason, notes)
  - `request_items` - Individual items (multiple per request)
  - Supports tracking status for both request and individual items

### 5. **New Validation Endpoint**

- **File:** `validate-employee.php`
- **API:** POST endpoint that queries emp_sync table
- **Returns:** JSON with employee data or error message

## 🔧 Setup Instructions

### Step 1: Run SQL

```bash
mysql -u [username] -p [database_name] < create_item_requests_table.sql
```

**OR** manually run the SQL in phpMyAdmin/MySQL Workbench

### Step 2: Test Employee Validation

1. Go to `request-items.php`
2. Enter a valid last name and employee number from `uniform_orders.emp_sync`
3. Click "Verify Information"
4. Should show validated employee info

### Step 3: Test Form Submission

1. After validation, form should appear
2. Add item details:
   - Select category
   - **Enter product URL** (required!)
   - Enter item name
   - Fill other fields
3. Click "Add Another Item" to test multiple items
4. Fill in reason for request
5. Submit

### Step 4: Check Database

After submission, verify:

```sql
SELECT * FROM item_requests ORDER BY request_id DESC LIMIT 1;
SELECT * FROM request_items WHERE request_id = [last_id];
```

### Step 5: Check Emails

- Admin should receive email at `store@berkeleycountysc.gov`
- Employee should receive confirmation

## 🧪 Testing Scenarios

### Test 1: Valid Employee with Email

- Use employee that HAS email in emp_sync
- Email field should NOT be editable
- Should use emp_sync email automatically

### Test 2: Valid Employee without Email

- Use employee that has NO email in emp_sync
- Should show email input field
- User must enter email manually
- Email NOT saved to emp_sync (as requested)

### Test 3: Invalid Employee

- Enter wrong last name or emp number
- Should show error message
- Should not proceed to form

### Test 4: Single Item Request

- Validate employee
- Fill out 1 item
- Submit
- Check both database tables

### Test 5: Multiple Items Request

- Validate employee
- Click "Add Another Item"
- Fill out 2-3 items
- Submit
- Verify all items saved in request_items table

### Test 6: Required Product URL

- Try to submit without product URL
- Should show validation error
- Must include valid URL format

## 📋 Database Structure

### item_requests table

```
- request_id (PK)
- emp_number
- emp_name
- emp_email
- dept_name
- dept_number
- reason
- additional_notes
- request_date
- status (pending/under_review/approved/denied/completed)
- reviewed_by
- review_date
- review_notes
```

### request_items table

```
- item_id (PK)
- request_id (FK)
- item_category
- product_url ← NEW REQUIRED FIELD
- item_name
- item_details
- quantity_estimate
- priority
- item_status
```

## ⚠️ Important Notes

1. **Email Logic:**
   - If emp_sync has email → MUST use it (not editable)
   - If emp_sync has NO email → user enters one (not saved to emp_sync)

2. **Product URLs:**
   - Required for EVERY item
   - Must be valid URL format
   - No restrictions on domain (for now)

3. **Multiple Items:**
   - Can add unlimited items
   - Each item tracked separately in request_items table
   - Can have different priorities per item

4. **Categories:**
   - Fixed list (per your requirements):
     - Pants, Shirts, Hats, Outerwear, Sweatshirts, Boots, Accessories

## 🐛 Troubleshooting

### Employee Validation Fails

- Check emp_sync table has data
- Verify column names match: `empNum`, `lastName`, `firstName`, `email`, `deptName`, `deptNumber`
- Check database connection in config.php

### Form Doesn't Appear After Validation

- Check browser console for JavaScript errors
- Verify AJAX response in Network tab

### Database Insert Fails

- Run SQL file to create tables
- Check table names and column names
- Verify foreign key constraint

### Emails Not Sending

- Check PHP mail configuration
- Update email address in `submit-item-request.php` line 131
- Consider using PHPMailer for better reliability

## 📝 TODO / Future Enhancements

1. Update admin dashboard (`manage-item-requests.php`) to:
   - Display multiple items per request
   - Show product URLs as clickable links
   - Update to work with new database structure

2. Add ability to approve/deny individual items (not just entire request)

3. Add file upload for product images/specs

4. Send notification emails when status changes

5. Employee portal to view their request history

## 🎯 What You Need to Test

1. **Try to break the validation** - wrong names, numbers
2. **Test the email field logic** - with/without email in emp_sync
3. **Test multiple items** - add 5+ items, remove some, submit
4. **Test required fields** - try to skip product URL, category, etc.
5. **Check emails** - formatting, all data appears correct
6. **Verify database** - all data stored correctly in both tables

Let me know what you find!
