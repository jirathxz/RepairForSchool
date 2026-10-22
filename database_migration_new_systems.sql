-- ==========================================================
-- Migration: Systems A, B, C, E
-- Database: repair_system_db (MariaDB 11.4 / MySQL 8+)
-- ==========================================================

USE `repair_system_db`;

-- 1. Table: assets (System A: ทะเบียนครุภัณฑ์)
CREATE TABLE IF NOT EXISTS `assets` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_code`        VARCHAR(50)  NOT NULL UNIQUE COMMENT 'รหัสครุภัณฑ์ เช่น ศธ 0401/67-001',
  `name`              VARCHAR(150) NOT NULL COMMENT 'ชื่อครุภัณฑ์/อุปกรณ์',
  `equipment_type_id` INT UNSIGNED NOT NULL,
  `brand`             VARCHAR(100) DEFAULT NULL,
  `model`             VARCHAR(100) DEFAULT NULL,
  `serial_number`     VARCHAR(100) DEFAULT NULL,
  `building`          VARCHAR(100) NOT NULL,
  `room`              VARCHAR(100) NOT NULL,
  `status`            ENUM('active', 'maintenance', 'damaged', 'disposed') NOT NULL DEFAULT 'active',
  `purchase_date`     DATE DEFAULT NULL,
  `warranty_expiry`   DATE DEFAULT NULL,
  `price`             DECIMAL(10,2) DEFAULT 0.00,
  `qr_token`          VARCHAR(64)  NOT NULL UNIQUE,
  `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_asset_equip` (`equipment_type_id`),
  KEY `idx_asset_status` (`status`),
  KEY `idx_asset_building` (`building`),
  CONSTRAINT `fk_asset_equip` FOREIGN KEY (`equipment_type_id`) REFERENCES `equipment_types` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add asset_id to repair_tickets if not exists
SET @existAssetId := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='repair_tickets' AND COLUMN_NAME='asset_id');
SET @sqlAssetId := IF(@existAssetId = 0, 'ALTER TABLE `repair_tickets` ADD COLUMN `asset_id` INT UNSIGNED DEFAULT NULL AFTER `equipment_type_id`, ADD CONSTRAINT `fk_ticket_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL;', 'SELECT 1;');
PREPARE stmtAssetId FROM @sqlAssetId;
EXECUTE stmtAssetId;
DEALLOCATE PREPARE stmtAssetId;

-- 2. System B: Columns for Work Acceptance & Rating in repair_tickets
SET @existRating := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='repair_tickets' AND COLUMN_NAME='rating');
SET @sqlRating := IF(@existRating = 0, 'ALTER TABLE `repair_tickets` ADD COLUMN `rating` TINYINT UNSIGNED DEFAULT NULL COMMENT "คะแนนความพึงพอใจ 1-5" AFTER `cost_description`, ADD COLUMN `rating_comment` TEXT DEFAULT NULL AFTER `rating`, ADD COLUMN `accepted_at` DATETIME DEFAULT NULL AFTER `rating_comment`, ADD COLUMN `accepted_by_user_id` INT UNSIGNED DEFAULT NULL AFTER `accepted_at`, ADD COLUMN `rework_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `accepted_by_user_id`, ADD CONSTRAINT `fk_ticket_acceptor` FOREIGN KEY (`accepted_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;', 'SELECT 1;');
PREPARE stmtRating FROM @sqlRating;
EXECUTE stmtRating;
DEALLOCATE PREPARE stmtRating;

-- 3. System C: Spare Parts & Inventory (ตารางอะไหล่และการตัดสต็อก)
CREATE TABLE IF NOT EXISTS `spare_parts` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `part_code`         VARCHAR(50)  NOT NULL UNIQUE COMMENT 'รหัสอะไหล่ เช่น SP-001',
  `name`              VARCHAR(150) NOT NULL COMMENT 'ชื่ออะไหล่/วัสดุ',
  `unit`              VARCHAR(30)  NOT NULL DEFAULT 'ชิ้น' COMMENT 'หน่วยนับ',
  `unit_cost`         DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'ราคาต่อหน่วย (บาท)',
  `stock_quantity`    INT NOT NULL DEFAULT 0 COMMENT 'จำนวนคงเหลือในคลัง',
  `min_quantity`      INT NOT NULL DEFAULT 5 COMMENT 'จุดเตือนสั่งซื้อขั้นต่ำ',
  `equipment_type_id` INT UNSIGNED DEFAULT NULL COMMENT 'หมวดหมู่ที่เกี่ยวข้อง',
  `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_part_equip` (`equipment_type_id`),
  CONSTRAINT `fk_part_equip` FOREIGN KEY (`equipment_type_id`) REFERENCES `equipment_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ticket_parts_used` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id`     INT UNSIGNED NOT NULL,
  `spare_part_id` INT UNSIGNED NOT NULL,
  `quantity`      INT NOT NULL DEFAULT 1,
  `unit_cost`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_cost`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_used_ticket` (`ticket_id`),
  KEY `fk_used_part`   (`spare_part_id`),
  CONSTRAINT `fk_used_ticket` FOREIGN KEY (`ticket_id`)     REFERENCES `repair_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_used_part`   FOREIGN KEY (`spare_part_id`) REFERENCES `spare_parts` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. System E: Preventive Maintenance Schedules (แผนบำรุงรักษาเชิงป้องกันตามรอบ)
CREATE TABLE IF NOT EXISTS `pm_schedules` (
  `id`                     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`                  VARCHAR(150) NOT NULL COMMENT 'หัวข้อแผนบำรุงรักษา',
  `description`            TEXT DEFAULT NULL,
  `equipment_type_id`      INT UNSIGNED NOT NULL,
  `asset_id`               INT UNSIGNED DEFAULT NULL,
  `building`               VARCHAR(100) NOT NULL,
  `room`                   VARCHAR(100) DEFAULT NULL,
  `frequency_days`         INT NOT NULL DEFAULT 90 COMMENT 'ความถี่รอบวัน เช่น 30, 90, 180, 365',
  `last_run_at`            DATE DEFAULT NULL COMMENT 'วันที่ดำเนินการล่าสุด',
  `next_due_date`          DATE NOT NULL COMMENT 'วันที่ครบกำหนดรอบถัดไป',
  `assigned_technician_id` INT UNSIGNED DEFAULT NULL,
  `is_active`              TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_pm_equip`  (`equipment_type_id`),
  KEY `fk_pm_asset`  (`asset_id`),
  KEY `fk_pm_tech`   (`assigned_technician_id`),
  KEY `idx_pm_due`   (`next_due_date`),
  CONSTRAINT `fk_pm_equip` FOREIGN KEY (`equipment_type_id`)      REFERENCES `equipment_types` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_pm_asset` FOREIGN KEY (`asset_id`)               REFERENCES `assets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pm_tech`  FOREIGN KEY (`assigned_technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
