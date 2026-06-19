-- NEXAutogear B2B Member System Schema
-- Run this once in phpMyAdmin

CREATE TABLE IF NOT EXISTS `b2b_applications` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company`      VARCHAR(200) NOT NULL,
  `contact_name` VARCHAR(100) NOT NULL,
  `email`        VARCHAR(150) NOT NULL UNIQUE,
  `phone`        VARCHAR(50),
  `country`      VARCHAR(100),
  `business_type` VARCHAR(100),
  `products_interest` VARCHAR(200),
  `annual_volume` VARCHAR(50),
  `message`      TEXT,
  `status`       ENUM('pending','approved','rejected') DEFAULT 'pending',
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at`  DATETIME,
  `reviewed_by`  VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `b2b_members` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT UNSIGNED,
  `company`      VARCHAR(200) NOT NULL,
  `contact_name` VARCHAR(100) NOT NULL,
  `email`        VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `country`      VARCHAR(100),
  `account_tier` ENUM('standard','preferred','vip') DEFAULT 'standard',
  `status`       ENUM('active','suspended') DEFAULT 'active',
  `last_login`   DATETIME,
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `b2b_orders` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `member_id`    INT UNSIGNED NOT NULL,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `status`       ENUM('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `total_usd`    DECIMAL(10,2),
  `notes`        TEXT,
  `tracking_number` VARCHAR(100),
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`member_id`) REFERENCES `b2b_members`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `b2b_order_items` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id`     INT UNSIGNED NOT NULL,
  `product_sku`  VARCHAR(100),
  `product_name` VARCHAR(200),
  `qty`          INT,
  `unit_price`   DECIMAL(10,2),
  FOREIGN KEY (`order_id`) REFERENCES `b2b_orders`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `admin_users` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username`     VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `email`        VARCHAR(150),
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin (password: Aegis0901! — change after first login)
INSERT INTO `admin_users` (`username`, `password_hash`, `email`) VALUES
('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uRpZ7yBVy', 'Sales@nexautogear.com');
