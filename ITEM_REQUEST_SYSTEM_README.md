# Item Request System Documentation

## Overview

The Item Request System allows Berkeley County employees to submit requests for new items to be added to the store. This system includes:

- Public-facing request form
- Email notifications
- Database tracking
- Admin management interface

## Files Created

### Public Files

1. **request-items.php** - Main form where employees submit item requests
2. **submit-item-request.php** - Backend processor for form submissions
3. **support.php** - Updated with new "Request Items" link

### Admin Files

1. **manage-item-requests.php** - Admin dashboard for viewing/managing requests
2. **get-item-request-details.php** - AJAX endpoint for loading request details

### Database Files

1. **create_item_requests_table.sql** - Database schema for storing requests

## Installation Instructions

### Step 1: Create Database Tables

Run the SQL file to create the necessary database tables:

```bash
mysql -u [username] -p [database_name] < create_item_requests_table.sql
```

Or import via phpMyAdmin or your preferred database tool.

### Step 2: Configure Email Settings

Update the email addresses in `submit-item-request.php`:

```php
$to = "store@berkeleycountysc.gov"; // Change to your store management email
```

### Step 3: Add Icon (Optional)

If you want to use a custom icon for the Request Items link, ensure the icon file exists at:

```
assets/icons/add.svg
```

### Step 4: Configure Admin Access

The admin management page (`manage-item-requests.php`) should be protected with authentication.

Update the authentication check in both:

- `manage-item-requests.php`
- `get-item-request-details.php`

Example authentication:

```php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin/signin/signin.php');
    exit();
}
```

## Features

### Employee Features

- **Easy Request Submission**: Clean, intuitive form
- **Multiple Categories**: Shirts, Outerwear, Hats, Boots, Accessories, etc.
- **Priority Levels**: Low, Medium, High
- **Automatic Confirmations**: Email confirmation sent immediately
- **FAQ Section**: Answers common questions

### Admin Features

- **Dashboard View**: See all requests in one place
- **Status Filters**: Filter by status (pending, under review, approved, denied, completed)
- **Priority Filters**: Filter by urgency level
- **Detailed View**: Modal popup with full request details
- **Status Updates**: Change request status with notes
- **Email Tracking**: See requester contact information

## Database Schema

### item_requests Table

| Column | Type | Description |
|--------|------|-------------|
| request_id | INT | Primary key, auto-increment |
| emp_name | VARCHAR(255) | Employee name |
| emp_email | VARCHAR(255) | Employee email |
| emp_id | VARCHAR(50) | Employee ID (optional) |
| department | VARCHAR(255) | Department name |
| item_category | VARCHAR(100) | Category of requested item |
| item_name | VARCHAR(255) | Name/description of item |
| item_details | TEXT | Detailed item description |
| quantity_estimate | VARCHAR(50) | Estimated quantity needed |
| priority | ENUM | low, medium, high |
| reason | TEXT | Reason for the request |
| additional_notes | TEXT | Any additional notes |
| request_date | DATETIME | When request was submitted |
| status | ENUM | pending, under_review, approved, denied, completed |
| reviewed_by | VARCHAR(255) | Admin who reviewed |
| review_date | DATETIME | When it was reviewed |
| review_notes | TEXT | Review notes |
| updated_at | DATETIME | Last update timestamp |

### item_request_history Table (Optional)

Tracks all status changes for audit purposes.

## Usage

### For Employees

1. Navigate to the Support page
2. Click "Request Items" in the Quick Links menu
3. Fill out the request form with all required information
4. Submit the form
5. Receive email confirmation
6. Wait 5-7 business days for review

### For Administrators

1. Navigate to `manage-item-requests.php`
2. View all pending requests
3. Use filters to find specific requests
4. Click "View" to see full details
5. Update status and add review notes
6. Submit changes
7. Employee receives email notification (when implemented)

## Email Notifications

### Employee Confirmation Email

Sent immediately upon form submission, includes:

- Request confirmation
- Item name
- Expected timeline
- Contact information

### Admin Notification Email

Sent to store management, includes:

- Complete request details
- Employee information
- Priority level (color-coded)
- Direct reply-to address

## Future Enhancements

### Recommended Additions

1. **Email Notifications on Status Changes**
   - Notify employees when status changes
   - Include next steps in notification

2. **File Uploads**
   - Allow employees to attach images or specification sheets
   - Store in uploads directory

3. **Admin Dashboard Statistics**
   - Total requests by status
   - Average response time
   - Most requested categories

4. **Request Comments/Discussion**
   - Allow back-and-forth communication
   - Store in separate comments table

5. **Integration with Inventory System**
   - Link approved items to inventory
   - Track from request to procurement

6. **Employee Request History**
   - Allow employees to view their past requests
   - Track request status

## Customization

### Changing Categories

Edit the category dropdown in `request-items.php`:

```php
<select id="item_category" name="item_category" class="form-control form-select" required>
    <option value="">-- Select Category --</option>
    <option value="your_category">Your Category Name</option>
    <!-- Add more categories as needed -->
</select>
```

### Changing Email Template

Edit the HTML email templates in `submit-item-request.php`:

- Search for the `$message` variable (admin email)
- Search for the `$confirmation_message` variable (employee email)

### Styling

Custom styles are included inline in each PHP file. Main CSS variables are defined in:

- `style/global-variables.css`
- `style/storeLux.css`

## Troubleshooting

### Form Not Submitting

- Check that all required fields are filled
- Verify database connection in `config.php`
- Check PHP error logs

### Emails Not Sending

- Verify mail server is configured
- Check email addresses are valid
- Consider using PHPMailer for better reliability

### Database Errors

- Ensure tables are created correctly
- Check column names match code
- Verify user permissions

## Support

For issues or questions, contact:

- Berkeley County IT Department
- <store@berkeleycountysc.gov>

## Version History

- **v1.0** (2026-02-11): Initial release
  - Request form
  - Email notifications
  - Admin dashboard
  - Database schema
