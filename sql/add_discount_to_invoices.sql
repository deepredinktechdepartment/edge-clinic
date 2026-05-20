ALTER TABLE `invoices`
    ADD COLUMN `discount_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER `sub_total`,
    ADD COLUMN `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `discount_percentage`;
ALTER TABLE `payments`
    ADD COLUMN `source_id` BIGINT UNSIGNED NULL AFTER `doctor_id`,
    ADD COLUMN `discount_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER `registration_fee`,
    ADD COLUMN `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER `discount_percentage`;