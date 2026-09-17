-- MySQL / MariaDB: add the doctor's registration number column safely.
ALTER TABLE `doctors`
    ADD COLUMN IF NOT EXISTS `registration_number` VARCHAR(100) NULL AFTER `name`;
