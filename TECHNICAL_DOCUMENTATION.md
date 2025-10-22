# Technical Documentation: Berkeley County Store Application

**Document Version:** 1.0  
**Last Updated:** October 17, 2025  
**Primary Developer:** Jon Ellwood  
**Project:** county_store (Internal County Store)  
**Repository:** <https://github.com/bc-jonellwood/store>

---

## CRITICAL CONTACT INFORMATION

### Emergency Contacts

- **Primary Developer:** Jon Ellwood - (843) 719-5132 / <jon.ellwood@berkeleycountysc.gov>
- **Project Manager:** Jerri Christmas - [PLACEHOLDER: Email/Phone]
- **Backend Developer:** James Troy - [PLACEHOLDER: Email/Phone]
- **Support:** David Kornahrens - [PLACEHOLDER: Email/Phone]
- **IT Department:** Berkeley County IT Department - [PLACEHOLDER: Phone]

### Business Hours Support

- **Main IT Help Desk:** [PLACEHOLDER: Phone/Email]
- **On-Call Contact:** [PLACEHOLDER: Phone/Email]

---

## EXECUTIVE SUMMARY

The Berkeley County Store is an internal web application that allows county employees to request approved uniform items and supplies. The system manages the complete lifecycle from employee requests through department head approval, ordering, inventory management, and fulfillment.

**Key Purpose:** Interface for employees to request approved items be ordered on their behalf  
**Current Version:** 2.0.6 (per package.json)  
**Organization:** Berkeley County IT Department

---

## INFRASTRUCTURE & HOSTING

### Server Information

#### Production Server

- **Server Name/IP:** [PLACEHOLDER: Production Server Name/IP]
- **Server Type:** [PLACEHOLDER: Physical/VM/Cloud Provider]
- **OS:** [PLACEHOLDER: Operating System & Version]
- **Access Method:** [PLACEHOLDER: SSH/RDP/Other]
- **Server Admin:** [PLACEHOLDER: Admin Name/Contact]

#### Development/Staging Server

- **Server Name/IP:** [PLACEHOLDER: Dev Server Name/IP]
- **Purpose:** Testing and development
- **Access:** Local development uses PHP built-in server (see start-dev-server.sh)

### Web Server Configuration

#### Web Server Details

- **Web Server:** [PLACEHOLDER: Apache/Nginx/IIS]
- **Version:** [PLACEHOLDER: Version Number]
- **Document Root:** [PLACEHOLDER: /var/www/html/store or similar]
- **PHP Version Required:** [PLACEHOLDER: Current PHP Version]
- **PHP Extensions Required:**
  - mysqli (MySQL database connectivity)
  - ldap (LDAP authentication)
  - mbstring (Multi-byte string support)
  - json
  - session
  - curl (for external API calls)

#### URL Endpoints

- **Production URL:** [PLACEHOLDER: https://store.berkeleycountysc.gov or similar]
- **Admin Portal:** [PLACEHOLDER: https://store.berkeleycountysc.gov/admin]
- **Inventory Management:** [PLACEHOLDER: https://store.berkeleycountysc.gov/inventory]
- **Product Admin:** [PLACEHOLDER: https://store.berkeleycountysc.gov/product-admin]

---

## DATABASE INFORMATION

### Primary Database

#### Connection Details

- **Database Type:** MySQL/MariaDB
- **Database Server:** 10.50.10.94
- **Port:** 3306
- **Database Name:** uniform_orders
- **Application User:** EmpOrderForm
- **Password:** FwpIXaIf1jGCpjS5Banp ⚠️ **CHANGE IN PRODUCTION DOCUMENT**
- **Connection File:** `/config.php`

#### Database Server Access

- **Database Admin Contact:** [PLACEHOLDER: DBA Name/Contact]
- **Admin Tool:** [PLACEHOLDER: phpMyAdmin URL or other admin tool]
- **Backup Schedule:** [PLACEHOLDER: Daily/Weekly/etc.]
- **Backup Location:** [PLACEHOLDER: Backup server/path]
- **Recovery Procedure:** [PLACEHOLDER: Link to DR procedure]

### Key Database Tables

#### Core Tables

- **products_new** - Product catalog with codes, names, descriptions, images
- **colors** - Available product colors with hex codes
- **sizes_new** - Size options for products
- **products_colors** - Junction table linking products to available colors
- **products_sizes_new** - Junction table linking products to available sizes
- **prices** - Product pricing information
- **orders** - Customer orders (header information)
- **order_details** - Individual line items for orders
- **customers** - Customer/employee information
- **departments** - Department reference data
- **emp_ref** / **curr_emp_ref** - Employee reference data
- **user_ref** - User authentication and roles
- **roles** - User role definitions
- **user_roles** - Junction table for user-role assignments

#### Database Schema Files

Located in: `/admin/pages/edit-products/data/`

- products_new.sql
- colors.sql
- sizes_new.sql
- products_colors.sql
- products_sizes_new.sql
- prices.sql
- producttypes.sql

User/Role Schema: `/admin/pages/edit-users/`

- create-roles-system.sql
- create-user-roles-table.sql

---

## AUTHENTICATION & AUTHORIZATION

### LDAP Authentication

#### LDAP Server

- **LDAP Server IP:** 10.11.20.43
- **Protocol:** LDAP (not LDAPS based on code review)
- **Authentication Method:** Direct bind with username/password
- **Domain:** [PLACEHOLDER: @domain.local or similar]
- **Implementation:** Multiple login files use LDAP
  - `/login-ldap.php` (main application)
  - `/comm-login-ldap.php` (communications section)
  - `/inventory/login-ldap.php` (inventory management)
  - `/product-admin/login-ldap.php` (product admin)
  - `/admin/signin/func.php` (admin signin)

#### LDAP Troubleshooting

- **LDAP Admin Contact:** [PLACEHOLDER: AD Admin Name/Contact]
- **Test Account:** [PLACEHOLDER: Test account credentials]
- **Common Issues:**
  - Server unreachable (check network connectivity to 10.11.20.43)
  - Incorrect domain suffix
  - Account locked/disabled in Active Directory

### User Roles

Based on role hierarchy (higher = more authority):

- **Administrator** (Level 100) - Full system access
- **Department Head with Logo** (Level 55) - Department management + logo permissions
- **Department Head** (Level 50) - Department management
- **Assistant with Logo** (Level 30) - Limited department access + logo permissions
- **Assistant** (Level 25) - Limited department access

---

## EMAIL SYSTEM

### Email Configuration

#### Mail Server

- **Method:** PHPMailer library (Composer dependency)
- **Version:** 6.7+ (per composer.json)
- **SMTP Server:** [PLACEHOLDER: Internal SMTP server address]
- **SMTP Port:** [PLACEHOLDER: 25/587/465]
- **Authentication:** [PLACEHOLDER: Yes/No]
- **From Address:** [PLACEHOLDER: store@berkeleycountysc.gov]

#### Email Functionality

- **Order Confirmations:** `newSendOrderEmail.php`, `sendOrderEmail.php`
- **Department Head Notifications:** `sendDepHeadEmail.php`
- **Support Requests:** `supportMail.php`
- **Spending Notifications:** `my-spending-sendmail.php`

### Email Troubleshooting

- **Mail Server Admin:** [PLACEHOLDER: Mail Admin Contact]
- **Mail Logs Location:** [PLACEHOLDER: Log file path]
- **Test Email Command:** [PLACEHOLDER: Test procedure]

---

## APPLICATION ARCHITECTURE

### Technology Stack

#### Frontend

- **HTML5/CSS3**
- **JavaScript (ES6+)**
- **Bootstrap** (custom dark theme: berkstrap-dark.css)
- **Font Awesome Icons**
- **jQuery** (likely, based on common patterns)

#### Backend

- **PHP** (Version: [PLACEHOLDER])
- **MySQL/MariaDB**
- **Session Management** (PHP native sessions)

#### Package Management

- **Composer** (PHP dependencies)
  - phpmailer/phpmailer ^6.7
- **npm** (JavaScript dependencies)
  - markdown-it ^14.1.0

### Application Structure

```
/store/
├── index.php                    # Main entry point
├── config.php                   # Database configuration
├── Cart.class.php              # Shopping cart class
├── login-ldap.php              # Authentication
├── checkout.php                # Order processing
├── /admin/                     # Administrative interface
│   ├── index.php
│   ├── /pages/
│   │   ├── /edit-products/    # Product management
│   │   ├── /edit-users/       # User management
│   │   ├── /tools/            # Utility tools & scrapers
│   │   └── /reports/          # Reporting interface
├── /inventory/                 # Inventory management system
│   ├── index.php
│   ├── login-ldap.php
│   └── dept-inv.php
├── /product-admin/            # Product administration
├── /reports/                  # Reporting modules
├── /API/                      # API endpoints
│   ├── fetchAllProductData.php
│   ├── fetchTopProducts.php
│   ├── getTeam.php
│   └── placeOrder.php
├── /components/               # Reusable UI components
├── /assets/                   # Static assets
├── /vendor/                   # Composer dependencies
└── /utils/                    # Utility scripts
```

### Key Components

#### 1. Employee Front-End

- Product browsing and filtering
- Shopping cart functionality
- Order submission
- Spending tracking
- Personal order history

#### 2. Department Head Back-End (/admin/)

- Order approval/denial workflow
- Department spending reports
- Employee order review
- Budget tracking
- Fiscal year reporting (July 1 - June 30)

#### 3. Inventory Management (/inventory/)

- Inventory tracking
- Item assignment to employees
- Returns processing
- Destruction/disposal tracking
- Department inventory views

#### 4. Product Administration (/product-admin/)

- Product catalog management
- Color and size management
- Pricing management
- Product status (active/inactive)
- Featured product selection

---

## KEY FEATURES & WORKFLOWS

### Order Processing Workflow

1. Employee browses catalog and adds items to cart
2. Employee submits order for approval
3. Department Head receives notification
4. Department Head approves/denies order
5. Approved orders marked for ordering
6. Items ordered from vendor
7. Items received and logged
8. Items assigned to employees
9. Order status updated throughout process

### Fiscal Year Management

- **Fiscal Year:** July 1 - June 30
- **Special Logic:** Store closes in June (see `checkMonthAndRedirect()` in index.php)
- **Budget Tracking:** By fiscal year, not calendar year
- **Calculation:** See `MyDateTime::fiscalYear()` in newSendOrderEmail.php

### Shopping Cart

- **Class:** Cart.class.php
- **Storage:** PHP sessions + localStorage (browser-based persistence)
- **Features:**
  - Add/remove items
  - Quantity management
  - Size and color selection
  - Logo fees
  - Department patch placement
  - Line item comments

---

## EXTERNAL INTEGRATIONS

### Vendor Product Scrapers

Located in: `/admin/pages/tools/`

#### Company Casuals Scraper

- **Files:**
  - `company-casuals-scraper.php` (UI)
  - `company-casuals-scraper-service.php` (Service layer)
  - `company-casuals-batch-runner.php` (CLI batch processor)
- **Purpose:** Scrape product prices and availability from Company Casuals website
- **Run Method:** Can be run via UI or command line
- **Output:** JSON files in `/admin/pages/tools/downloads/company_casuals/`

#### SanMar Product Scraper

- **File:** `product-scraper.php`
- **Purpose:** Scrape product data from SanMar
- **Dev Server:** Use `start-dev-server.sh` for local testing
- **Images:** Saved to `/admin/pages/tools/downloads/`

### Third-Party Services

- **LDAP/Active Directory:** Employee authentication (10.11.20.43)
- **SMTP Server:** Email notifications [PLACEHOLDER: Server details]
- **Vendor Websites:**
  - Company Casuals: [PLACEHOLDER: URL]
  - SanMar: [PLACEHOLDER: URL]

---

## FILE STORAGE & UPLOADS

### Product Images

- **Location:** `/product-images/`
- **Format:** [PLACEHOLDER: JPG/PNG]
- **Naming Convention:** [PLACEHOLDER: Convention]
- **Upload Process:** Product admin interface
- **Storage Limit:** [PLACEHOLDER: Size/count limits]

### Department Logos

- **Location:** `/dept_logos/`
- **Purpose:** Custom department patches/logos
- **Format:** [PLACEHOLDER: File types]

### Specification Sheets

- **Location:** `/spec-sheets/`
- **Purpose:** Product specification documents

### Temporary Files

- **Location:** `/tmp/`
- **Purpose:** Temporary processing files
- **Cleanup:** [PLACEHOLDER: Cleanup schedule]

### Logs

- **PHP Error Log:** `/php_errors.log`
- **Admin Logs:** `/admin/logs/`
- **Inventory Logs:** `/inventory/BCG/` (appears to be Berkeley County Government specific)
- **General Logs:** `/logs/`

---

## SECURITY CONSIDERATIONS

### Sensitive Files

⚠️ **These files contain sensitive information and should be secured:**

- `/config.php` - Database credentials
- `/admin/config.php` - Admin database config
- `/rootConfig.php` - Root configuration
- Any `.env` files if present

### Access Control

- **Admin Area:** Requires authentication + Admin role
- **Inventory System:** Requires authentication + Inventory role
- **Product Admin:** Requires authentication + Product Admin role
- **Department Head Portal:** Requires authentication + Dept Head role

### Session Management

- PHP native sessions
- Session files location: [PLACEHOLDER: /var/lib/php/sessions or similar]
- Session timeout: [PLACEHOLDER: Duration]
- Session cleanup: [PLACEHOLDER: Cleanup method]

### Recommended Security Audits

- [ ] Review all SQL queries for injection vulnerabilities
- [ ] Implement prepared statements consistently (some files use them, others don't)
- [ ] Move database credentials to environment variables
- [ ] Implement LDAPS instead of LDAP
- [ ] Add CSRF protection to forms
- [ ] Review file upload validation
- [ ] Implement rate limiting on login attempts

---

## DEPLOYMENT & MAINTENANCE

### Deployment Process

[PLACEHOLDER: Document your deployment process]

1. Test changes locally using `php -S localhost:8080`
2. Commit to git repository
3. [PLACEHOLDER: Push to staging server]
4. [PLACEHOLDER: QA testing procedure]
5. [PLACEHOLDER: Production deployment steps]
6. [PLACEHOLDER: Post-deployment verification]

### Version Control

- **Repository:** <https://github.com/jonellwood/county_store>
- **Original Repo:** <https://github.com/bc-jonellwood/store>
- **Branch:** main
- **Commit Guidelines:** [PLACEHOLDER: Commit message format]

### Backup Procedures

- **Database Backups:**
  - Schedule: [PLACEHOLDER]
  - Location: [PLACEHOLDER]
  - Retention: [PLACEHOLDER]
- **File Backups:**
  - Schedule: [PLACEHOLDER]
  - Location: [PLACEHOLDER]
  - Retention: [PLACEHOLDER]

### Monitoring

- **Uptime Monitoring:** [PLACEHOLDER: Monitoring service/tool]
- **Error Monitoring:** [PLACEHOLDER: How errors are tracked]
- **Performance Monitoring:** [PLACEHOLDER: APM tool if any]
- **Log Rotation:** [PLACEHOLDER: Log rotation schedule]

---

## DEPENDENCIES & LIBRARIES

### PHP Dependencies (Composer)

```json
{
  "phpmailer/phpmailer": "^6.7"
}
```

### JavaScript Dependencies (npm)

```json
{
  "markdown-it": "^14.1.0"
}
```

### External Libraries (CDN)

- Font Awesome Icons
- Bootstrap (custom berkstrap-dark.css theme)
- [PLACEHOLDER: Other CDN resources]

### Browser Requirements

- Modern browsers with JavaScript enabled
- LocalStorage support (for cart persistence)
- Cookies enabled (for sessions)

---

## TROUBLESHOOTING GUIDE

### Common Issues

#### 1. Cannot Connect to Database

**Symptoms:** Application shows database connection error  
**Check:**

- Database server is running (10.50.10.94:3306)
- Network connectivity to database server
- Credentials in config.php are correct
- MySQL user has proper permissions

**Resolution:**

```bash
# Test connection to database server
telnet 10.50.10.94 3306

# Check MySQL logs
[PLACEHOLDER: Log location]
```

#### 2. LDAP Authentication Failing

**Symptoms:** Users cannot log in  
**Check:**

- LDAP server is reachable (10.11.20.43)
- User account is active in Active Directory
- Domain suffix is correct
- Network connectivity

**Resolution:**

```bash
# Test LDAP connectivity
telnet 10.11.20.43 389

# Check LDAP logs
[PLACEHOLDER: Log location]
```

#### 3. Emails Not Sending

**Symptoms:** Order confirmations not received  
**Check:**

- SMTP server is reachable
- PHPMailer configuration is correct
- Mail server allows relay from application server
- Check spam folders

**Resolution:**

- Review logs in newSendOrderEmail.php execution
- Check SMTP server logs [PLACEHOLDER: Location]
- Test with: [PLACEHOLDER: Test procedure]

#### 4. Shopping Cart Not Persisting

**Symptoms:** Cart clears unexpectedly  
**Check:**

- Browser cookies enabled
- Session files writable
- LocalStorage enabled in browser

**Resolution:**

- Clear browser cache and cookies
- Check session path permissions
- Review Cart.class.php logic

#### 5. Product Images Not Displaying

**Symptoms:** Broken images in catalog  
**Check:**

- Images exist in /product-images/
- File permissions are correct
- Web server can serve static files
- Image paths in database are correct

---

## SCHEDULED TASKS / CRON JOBS

### Active Cron Jobs

#### 1. Pending Requests Reminder Email

- **Script:** `cron-pending-requests-reminder.php`
- **Schedule:** Every Friday at 7:00 AM
- **Purpose:** Send email notifications to department approvers (heads, assistants, asset managers) who have pending user requests
- **Cron Expression:** `0 7 * * 5 /usr/bin/php /path/to/store/cron-pending-requests-reminder.php >> /var/log/store/pending-requests-reminder.log 2>&1`
- **Log Location:** `/var/log/store/pending-requests-reminder.log`
- **Test Script:** `test-pending-requests-reminder.php`
- **Documentation:** `CRON_SETUP_PENDING_REQUESTS.md`
- **Quick Reference:** `QUICK_REFERENCE_CRON.md`
- **What it does:**
  - Queries database for all pending requests in `ord_ref` table
  - Groups requests by department
  - Sends one email per department to all approvers
  - Includes detailed order table with product info and amounts
  - Provides direct link to approval portal
- **Email Recipients:**
  - Department Head (from `dep_ref.dep_head_empName`)
  - Department Assistant (from `dep_ref.dep_assist_empName`)
  - Asset Manager (from `dep_ref.dep_asset_mgr_empName`)
- **Dependencies:**
  - PHPMailer library
  - Database connection (10.50.10.94)
  - SMTP server (10.50.10.10:25)
  - Tables: `ord_ref`, `dep_ref`

### Known Batch Processes

- **Company Casuals Scraper:** Can be run as batch via `company-casuals-batch-runner.php`
- **Usage:** `php company-casuals-batch-runner.php [options]`
- **Schedule:** [PLACEHOLDER: If automated]

### Recommended Scheduled Tasks

- [ ] Database backup: [PLACEHOLDER: Schedule]
- [ ] Log rotation: [PLACEHOLDER: Schedule]
- [ ] Session cleanup: [PLACEHOLDER: Schedule]
- [ ] Product price updates: [PLACEHOLDER: Schedule]
- [ ] Fiscal year rollover: [PLACEHOLDER: Procedure]

### Cron Job Monitoring

- **Check scheduled jobs:** `crontab -l`
- **View cron logs:** `/var/log/cron` or `/var/log/syslog`
- **Monitor job execution:** Check individual log files in `/var/log/store/`

---

## DISASTER RECOVERY

### Critical Recovery Steps

#### If Database Server Fails

1. Contact Database Administrator: [PLACEHOLDER: Contact]
2. Restore from backup: [PLACEHOLDER: Backup location]
3. Update config.php if server IP changes
4. Verify connection with: [PLACEHOLDER: Test procedure]

#### If Web Server Fails

1. Contact Server Administrator: [PLACEHOLDER: Contact]
2. Deploy to backup server: [PLACEHOLDER: Backup server]
3. Update DNS if needed: [PLACEHOLDER: DNS contact]
4. Restore from git repository: `git clone https://github.com/jonellwood/county_store.git`
5. Restore /vendor/ directory: `composer install`
6. Restore config.php with proper credentials
7. Set file permissions: [PLACEHOLDER: Permission settings]

#### If LDAP Server Fails

1. Contact Active Directory Administrator: [PLACEHOLDER: Contact]
2. Update LDAP server IP in all login files if server changes
3. Files to update:
   - login-ldap.php
   - comm-login-ldap.php
   - inventory/login-ldap.php
   - product-admin/login-ldap.php
   - admin/signin/func.php

### Recovery Time Objectives (RTO)

- **Critical:** [PLACEHOLDER: Hours/Minutes]
- **High Priority:** [PLACEHOLDER: Hours]
- **Normal:** [PLACEHOLDER: Hours/Days]

### Data Backup Locations

- **Database:** [PLACEHOLDER: Backup server/path]
- **Files:** [PLACEHOLDER: Backup server/path]
- **Configuration:** [PLACEHOLDER: Backup server/path]

---

## API ENDPOINTS

### Internal APIs

Located in: `/API/`

- **fetchAllProductData.php** - Returns all product catalog data
- **fetchTopProducts.php** - Returns popular/featured products
- **getSeparatedEmps.php** - Employee data retrieval
- **getTeam.php** - Team information
- **placeOrder.php** - Process order submission

### Other API-like Endpoints

- **fetchFilteredProducts.php** - Product filtering with gender
- **fetchFilteredProductsNoGender.php** - Product filtering without gender
- **fetchFilteredProductsBySize.php** - Size-based filtering
- **fetchFilters.php** - Available filter options
- **fetchProductDetails.php** - Individual product details

---

## DEVELOPMENT ENVIRONMENT SETUP

### Prerequisites

- PHP [PLACEHOLDER: Version] or higher
- MySQL client
- Composer
- Node.js and npm
- Git
- Text editor / IDE

### Setup Steps

1. Clone repository:

   ```bash
   git clone https://github.com/jonellwood/county_store.git
   cd store
   ```

2. Install PHP dependencies:

   ```bash
   composer install
   ```

3. Install JavaScript dependencies:

   ```bash
   npm install
   ```

4. Configure database:
   - Copy config.php.example to config.php (if exists)
   - Update database credentials
   - Import database schema: [PLACEHOLDER: Schema file]

5. Start development server:

   ```bash
   php -S localhost:8080
   # OR use the provided script:
   ./start-dev-server.sh
   ```

6. Access application:
   - Main app: <http://localhost:8080>
   - Admin: <http://localhost:8080/admin>
   - Product scraper: <http://localhost:8080/admin/pages/tools/product-scraper.php>

### Development Tools

- **Local Server Script:** `start-dev-server.sh`
- **Changelog Viewer:** `changelogView.php`
- **Changelog:** `changelog.md` (rendered to changelog.html)

---

## KNOWN ISSUES & TECHNICAL DEBT

### Current Issues

- Multiple database connection patterns (some with prepared statements, some without)
- Inconsistent error handling across modules
- Some files named with "OLD_" prefix still in use
- Session management could be more robust
- SQL injection vulnerabilities in some legacy code

### Future Improvements

- [ ] Migrate to PDO for all database connections
- [ ] Implement comprehensive error logging
- [ ] Add unit tests
- [ ] Refactor legacy code in /admin/pages/
- [ ] Implement API authentication
- [ ] Move to environment variables for configuration
- [ ] Add comprehensive input validation
- [ ] Implement CSRF protection
- [ ] Update to LDAPS for secure authentication

---

## CHANGELOG & VERSION HISTORY

See: `changelog.md` for detailed version history

**Current Version:** 2.0.6  
**Previous Major Version:** 1.5.18 (per README)

View changelog in browser: `changelogView.php`

---

## ADDITIONAL RESOURCES

### Documentation Files

- **README.md** - Basic project information
- **changelog.md** - Version history
- **utils/README.md** - Utility scripts documentation

### Support Resources

- **GitHub Issues:** <https://github.com/bc-jonellwood/store/issues>
- **Internal Wiki:** [PLACEHOLDER: Internal documentation link]
- **Training Materials:** [PLACEHOLDER: Training location]

### Related Systems

- **Employee Directory:** [PLACEHOLDER: Link]
- **Budget System:** [PLACEHOLDER: Link]
- **Vendor Portals:** [PLACEHOLDER: Links]

---

## APPENDIX

### A. Database Schema Diagram

[PLACEHOLDER: Include or link to ERD diagram]

### B. Network Diagram

[PLACEHOLDER: Include or link to network architecture]

### C. User Role Matrix

[PLACEHOLDER: Table showing role permissions]

### D. Server Specifications

[PLACEHOLDER: Hardware/VM specifications]

### E. SSL/TLS Certificate Information

- **Certificate Authority:** [PLACEHOLDER]
- **Expiration Date:** [PLACEHOLDER]
- **Renewal Process:** [PLACEHOLDER]
- **Contact:** [PLACEHOLDER]

### F. Firewall Rules

[PLACEHOLDER: Document required firewall rules]

- Application Server → Database Server (10.50.10.94:3306)
- Application Server → LDAP Server (10.11.20.43:389)
- Application Server → SMTP Server ([PLACEHOLDER]:25/587)

### G. DNS Records

[PLACEHOLDER: Document DNS entries for the application]

---

## DOCUMENT REVISION HISTORY

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2025-10-17 | GitHub Copilot | Initial documentation creation |
| | | | |

---

## SIGN-OFF

**Developer Acknowledgment:**

- Name: Jon Ellwood
- Date: [PLACEHOLDER]
- Signature: [PLACEHOLDER]

**Manager Approval:**

- Name: [PLACEHOLDER: Manager Name]
- Date: [PLACEHOLDER]
- Signature: [PLACEHOLDER]

---

**END OF DOCUMENT**

*This document should be reviewed and updated at least quarterly or whenever significant changes are made to the application.*
