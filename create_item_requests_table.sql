-- Item Request System Database Table
-- Created: 2026/02/11
-- Updated: 2026/02/11 - Added support for multiple items per request
-- Purpose: Store employee requests for new items to be added to the store

-- Main request table (one record per request submission)
CREATE TABLE IF NOT EXISTS `item_requests` (
  `request_id` INT(11) NOT NULL AUTO_INCREMENT,
  `emp_number` VARCHAR(50) NOT NULL,
  `emp_name` VARCHAR(255) NOT NULL,
  `emp_email` VARCHAR(255) NOT NULL,
  `dept_name` VARCHAR(255) NOT NULL,
  `dept_number` VARCHAR(50) DEFAULT NULL,
  `reason` TEXT NOT NULL,
  `additional_notes` TEXT DEFAULT NULL,
  `request_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('pending', 'under_review', 'approved', 'denied', 'completed') NOT NULL DEFAULT 'pending',
  `reviewed_by` VARCHAR(255) DEFAULT NULL,
  `review_date` DATETIME DEFAULT NULL,
  `review_notes` TEXT DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`request_id`),
  KEY `idx_status` (`status`),
  KEY `idx_request_date` (`request_date`),
  KEY `idx_emp_email` (`emp_email`),
  KEY `idx_emp_number` (`emp_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Individual items table (multiple items can belong to one request)
CREATE TABLE IF NOT EXISTS `request_items` (
  `item_id` INT(11) NOT NULL AUTO_INCREMENT,
  `request_id` INT(11) NOT NULL,
  `item_category` VARCHAR(100) NOT NULL,
  `product_url` TEXT NOT NULL,
  `item_name` VARCHAR(255) NOT NULL,
  `item_details` TEXT DEFAULT NULL,
  `quantity_estimate` INT(11) DEFAULT 1,
  `priority` ENUM('low', 'medium', 'high') NOT NULL,
  `item_status` ENUM('pending', 'approved', 'denied', 'ordered', 'received') DEFAULT 'pending',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`item_id`),
  KEY `idx_request_id` (`request_id`),
  KEY `idx_item_category` (`item_category`),
  KEY `idx_priority` (`priority`),
  FOREIGN KEY (`request_id`) REFERENCES `item_requests`(`request_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: Create a table for tracking status changes
CREATE TABLE IF NOT EXISTS `item_request_history` (
  `history_id` INT(11) NOT NULL AUTO_INCREMENT,
  `request_id` INT(11) NOT NULL,
  `old_status` VARCHAR(50) DEFAULT NULL,
  `new_status` VARCHAR(50) NOT NULL,
  `changed_by` VARCHAR(255) DEFAULT NULL,
  `change_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` TEXT DEFAULT NULL,
  PRIMARY KEY (`history_id`),
  KEY `idx_request_id` (`request_id`),
  FOREIGN KEY (`request_id`) REFERENCES `item_requests`(`request_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
