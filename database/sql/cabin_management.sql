-- Edge Clinic cabin management schema
-- Important: `doctors.id` in this database is INT(11), not BIGINT UNSIGNED.
-- So every cabin table that references doctors must use INT(11) for `doctor_id`.
-- For new installs, run this file directly.
-- For existing installs where cabin tables already exist, add the new columns/tables below carefully:
-- `cabins`: room_number, available_from, operating_start_time, operating_end_time
-- `cabin_bookings`: payment_choice, payment_mode, payment_status, transaction_reference, paid_amount, paid_on
-- plus the new master tables: `cabin_facilities` and `cabin_facility_links`

CREATE TABLE IF NOT EXISTS `cabins` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cabin_code` VARCHAR(50) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `cabin_type` ENUM('consultation','premium','procedure','other') NOT NULL DEFAULT 'consultation',
  `floor_name` VARCHAR(100) DEFAULT NULL,
  `room_number` VARCHAR(50) DEFAULT NULL,
  `capacity` INT NOT NULL DEFAULT 1,
  `booking_mode` ENUM('hourly','monthly','both') NOT NULL DEFAULT 'both',
  `hourly_rate` DECIMAL(10,2) DEFAULT NULL,
  `monthly_rate` DECIMAL(10,2) DEFAULT NULL,
  `status` ENUM('available','occupied','maintenance','inactive') NOT NULL DEFAULT 'available',
  `available_from` DATE DEFAULT NULL,
  `operating_start_time` TIME DEFAULT NULL,
  `operating_end_time` TIME DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cabins_cabin_code_unique` (`cabin_code`),
  UNIQUE KEY `cabins_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cabin_facilities` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `pricing_type` ENUM('free','paid') NOT NULL DEFAULT 'free',
  `rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `charge_label` VARCHAR(100) DEFAULT NULL,
  `payment_note` TEXT DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cabin_facilities_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cabin_facility_links` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cabin_id` BIGINT UNSIGNED NOT NULL,
  `cabin_facility_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cabin_facility_links_unique` (`cabin_id`, `cabin_facility_id`),
  KEY `cabin_facility_links_facility_index` (`cabin_facility_id`),
  CONSTRAINT `cabin_facility_links_cabin_id_foreign` FOREIGN KEY (`cabin_id`) REFERENCES `cabins` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cabin_facility_links_facility_id_foreign` FOREIGN KEY (`cabin_facility_id`) REFERENCES `cabin_facilities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cabin_settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `clinic_open_time` TIME NOT NULL DEFAULT '09:00:00',
  `clinic_close_time` TIME NOT NULL DEFAULT '21:00:00',
  `min_booking_duration_minutes` INT NOT NULL DEFAULT 60,
  `buffer_minutes` INT NOT NULL DEFAULT 15,
  `default_gst_percent` DECIMAL(5,2) NOT NULL DEFAULT 18.00,
  `monthly_invoice_day` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `payment_due_days` TINYINT UNSIGNED NOT NULL DEFAULT 15,
  `invoice_delivery_mode` ENUM('email','whatsapp','both') NOT NULL DEFAULT 'both',
  `clinic_gstin` VARCHAR(30) DEFAULT NULL,
  `standard_hourly_rate` DECIMAL(10,2) NOT NULL DEFAULT 800.00,
  `premium_hourly_rate` DECIMAL(10,2) NOT NULL DEFAULT 1200.00,
  `procedure_hourly_rate` DECIMAL(10,2) NOT NULL DEFAULT 1500.00,
  `standard_monthly_rate` DECIMAL(10,2) NOT NULL DEFAULT 22000.00,
  `premium_monthly_rate` DECIMAL(10,2) NOT NULL DEFAULT 32000.00,
  `procedure_monthly_rate` DECIMAL(10,2) NOT NULL DEFAULT 38000.00,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cabin_settings` (
  `id`,
  `clinic_open_time`,
  `clinic_close_time`,
  `min_booking_duration_minutes`,
  `buffer_minutes`,
  `default_gst_percent`,
  `monthly_invoice_day`,
  `payment_due_days`,
  `invoice_delivery_mode`,
  `clinic_gstin`,
  `standard_hourly_rate`,
  `premium_hourly_rate`,
  `procedure_hourly_rate`,
  `standard_monthly_rate`,
  `premium_monthly_rate`,
  `procedure_monthly_rate`
) VALUES (
  1,
  '09:00:00',
  '21:00:00',
  60,
  15,
  18.00,
  1,
  15,
  'both',
  NULL,
  800.00,
  1200.00,
  1500.00,
  22000.00,
  32000.00,
  38000.00
)
ON DUPLICATE KEY UPDATE `id` = `id`;

CREATE TABLE IF NOT EXISTS `cabin_bookings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cabin_id` BIGINT UNSIGNED NOT NULL,
  `doctor_id` INT(11) NOT NULL,
  `booking_type` ENUM('hourly','half_day','full_day') NOT NULL DEFAULT 'hourly',
  `booking_date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `total_hours` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `base_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `gst_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `gst_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_choice` ENUM('pay_now','pay_later','free_booking','no_payment_required') NOT NULL DEFAULT 'pay_later',
  `payment_mode` VARCHAR(30) DEFAULT NULL,
  `payment_status` VARCHAR(50) DEFAULT NULL,
  `transaction_reference` VARCHAR(100) DEFAULT NULL,
  `paid_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `paid_on` DATETIME DEFAULT NULL,
  `status` ENUM('booked','completed','cancelled') NOT NULL DEFAULT 'booked',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cabin_bookings_cabin_id_index` (`cabin_id`),
  KEY `cabin_bookings_doctor_id_index` (`doctor_id`),
  KEY `cabin_bookings_booking_date_index` (`booking_date`),
  CONSTRAINT `cabin_bookings_cabin_id_foreign` FOREIGN KEY (`cabin_id`) REFERENCES `cabins` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cabin_bookings_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Existing database upgrade: run these ALTER statements one time only before the facility seed below.
-- If a column already exists in your database, skip that specific ALTER statement.

ALTER TABLE `cabins`
  ADD COLUMN `room_number` VARCHAR(50) DEFAULT NULL AFTER `floor_name`,
  ADD COLUMN `available_from` DATE DEFAULT NULL AFTER `status`,
  ADD COLUMN `operating_start_time` TIME DEFAULT NULL AFTER `available_from`,
  ADD COLUMN `operating_end_time` TIME DEFAULT NULL AFTER `operating_start_time`;

ALTER TABLE `cabins`
  ADD UNIQUE KEY `cabins_name_unique` (`name`);

ALTER TABLE `cabin_bookings`
  ADD COLUMN `payment_choice` ENUM('pay_now','pay_later','free_booking','no_payment_required') NOT NULL DEFAULT 'pay_later' AFTER `total_amount`,
  ADD COLUMN `payment_mode` VARCHAR(30) DEFAULT NULL AFTER `payment_choice`,
  ADD COLUMN `payment_status` VARCHAR(50) DEFAULT NULL AFTER `payment_mode`,
  ADD COLUMN `transaction_reference` VARCHAR(100) DEFAULT NULL AFTER `payment_status`,
  ADD COLUMN `paid_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `transaction_reference`,
  ADD COLUMN `paid_on` DATETIME DEFAULT NULL AFTER `paid_amount`;

ALTER TABLE `cabin_subscriptions`
  ADD COLUMN `subscription_start_time` TIME DEFAULT NULL AFTER `end_date`,
  ADD COLUMN `subscription_end_time` TIME DEFAULT NULL AFTER `subscription_start_time`;

ALTER TABLE `cabin_facilities`
  ADD COLUMN `pricing_type` ENUM('free','paid') NOT NULL DEFAULT 'free' AFTER `description`,
  ADD COLUMN `rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `pricing_type`,
  ADD COLUMN `charge_label` VARCHAR(100) DEFAULT NULL AFTER `rate`,
  ADD COLUMN `payment_note` TEXT DEFAULT NULL AFTER `charge_label`;

INSERT INTO `cabin_facilities` (`name`, `slug`, `description`, `pricing_type`, `rate`, `charge_label`, `payment_note`, `status`, `sort_order`) VALUES
('Air Conditioning', 'air-conditioning', 'Air conditioned cabin', 'free', 0.00, NULL, NULL, 'active', 1),
('Examination Table', 'examination-table', 'Examination table available', 'free', 0.00, NULL, NULL, 'active', 2),
('Hand Wash Basin', 'hand-wash-basin', 'Dedicated hand wash basin', 'free', 0.00, NULL, NULL, 'active', 3),
('Attached Restroom', 'attached-restroom', 'Restroom attached to the cabin', 'free', 0.00, NULL, NULL, 'active', 4),
('WiFi / LAN', 'wifi-lan', 'Internet connectivity available', 'free', 0.00, NULL, NULL, 'active', 5),
('X-Ray Viewer', 'x-ray-viewer', 'X-ray viewer / illuminator', 'paid', 350.00, 'Per Use', 'Charge extra when used during session.', 'active', 6),
('Procedure Light', 'procedure-light', 'Procedure light installed', 'paid', 500.00, 'Per Use', 'Applicable for special procedure support.', 'active', 7),
('Oxygen Supply', 'oxygen-supply', 'Oxygen line available', 'paid', 750.00, 'Per Use', 'Add only when oxygen support is consumed.', 'active', 8),
('Waiting Area', 'waiting-area', 'Shared waiting area support', 'free', 0.00, NULL, NULL, 'active', 9),
('ECG Machine Access', 'ecg-machine-access', 'ECG machine support inside cabin', 'paid', 600.00, 'Per Use', 'Collect payment if ECG setup is used.', 'active', 10),
('Ultrasound Assistance', 'ultrasound-assistance', 'Ultrasound equipment or support staff', 'paid', 1200.00, 'Per Use', 'Charge separately for assisted diagnostics.', 'active', 11),
('Sterilization Kit', 'sterilization-kit', 'Consumable sterile kit support', 'paid', 450.00, 'Per Kit', 'Add per consumable kit issued.', 'active', 12)
ON DUPLICATE KEY UPDATE
`name` = VALUES(`name`),
`description` = VALUES(`description`),
`pricing_type` = VALUES(`pricing_type`),
`rate` = VALUES(`rate`),
`charge_label` = VALUES(`charge_label`),
`payment_note` = VALUES(`payment_note`),
`status` = VALUES(`status`),
`sort_order` = VALUES(`sort_order`);

CREATE TABLE IF NOT EXISTS `cabin_subscriptions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cabin_id` BIGINT UNSIGNED NOT NULL,
  `doctor_id` INT(11) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `subscription_start_time` TIME DEFAULT NULL,
  `subscription_end_time` TIME DEFAULT NULL,
  `monthly_rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `gst_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `gst_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `invoice_day` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `status` ENUM('active','expired','cancelled') NOT NULL DEFAULT 'active',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cabin_subscriptions_cabin_id_index` (`cabin_id`),
  KEY `cabin_subscriptions_doctor_id_index` (`doctor_id`),
  KEY `cabin_subscriptions_start_date_index` (`start_date`),
  KEY `cabin_subscriptions_end_date_index` (`end_date`),
  CONSTRAINT `cabin_subscriptions_cabin_id_foreign` FOREIGN KEY (`cabin_id`) REFERENCES `cabins` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cabin_subscriptions_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cabin_invoices` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number` VARCHAR(50) NOT NULL,
  `doctor_id` INT(11) NOT NULL,
  `cabin_id` BIGINT UNSIGNED DEFAULT NULL,
  `billing_type` ENUM('hourly','monthly') NOT NULL DEFAULT 'hourly',
  `period_start` DATE NOT NULL,
  `period_end` DATE NOT NULL,
  `invoice_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `gst_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `gst_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('draft','sent','paid','overdue') NOT NULL DEFAULT 'draft',
  `sent_via` ENUM('email','whatsapp','both') DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cabin_invoices_invoice_number_unique` (`invoice_number`),
  KEY `cabin_invoices_doctor_id_index` (`doctor_id`),
  KEY `cabin_invoices_cabin_id_index` (`cabin_id`),
  CONSTRAINT `cabin_invoices_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `cabin_invoices_cabin_id_foreign` FOREIGN KEY (`cabin_id`) REFERENCES `cabins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cabin_invoice_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cabin_invoice_id` BIGINT UNSIGNED NOT NULL,
  `description` TEXT NOT NULL,
  `reference_type` ENUM('booking','subscription','manual') DEFAULT NULL,
  `reference_id` BIGINT UNSIGNED DEFAULT NULL,
  `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  `unit_rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `line_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cabin_invoice_items_invoice_id_index` (`cabin_invoice_id`),
  CONSTRAINT `cabin_invoice_items_invoice_id_foreign` FOREIGN KEY (`cabin_invoice_id`) REFERENCES `cabin_invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- Upgrade SQL for already existing databases
-- Run these only if your old cabin tables were already created before this update.
-- IMPORTANT:
-- 1. Run the ALTER statements one time only.
-- 2. After that, run the facility seed INSERT block.
-- ==========================================
-- Weekday-specific subscriptions and configurable booking shifts.
ALTER TABLE `cabin_subscriptions` ADD COLUMN `subscription_days` JSON NULL AFTER `subscription_end_time`;
ALTER TABLE `cabin_bookings` ADD COLUMN `shift_key` VARCHAR(30) NULL AFTER `booking_type`;
ALTER TABLE `cabin_settings` ADD COLUMN `booking_shifts` JSON NULL AFTER `clinic_close_time`;
