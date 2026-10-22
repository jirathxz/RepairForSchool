-- ==========================================================
-- RepairForSchool - Complete Database Schema & Seed Data
-- Database: repair_system_db (MariaDB 11.4 / MySQL 8+)
-- Character Set: utf8mb4 / Collation: utf8mb4_unicode_ci
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `repair_system_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `repair_system_db`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `ticket_parts_used`;
DROP TABLE IF EXISTS `spare_parts`;
DROP TABLE IF EXISTS `pm_schedules`;
DROP TABLE IF EXISTS `ticket_comments`;
DROP TABLE IF EXISTS `ticket_images`;
DROP TABLE IF EXISTS `repair_status_logs`;
DROP TABLE IF EXISTS `repair_tickets`;
DROP TABLE IF EXISTS `assets`;
DROP TABLE IF EXISTS `equipment_types`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------
-- 1. Table: users
-- Roles: admin (ผู้ดูแลระบบ), teacher (ครู/บุคลากร), technician (เจ้าหน้าที่ซ่อม)
-- ----------------------------------------------------------
CREATE TABLE `users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(50)  NOT NULL UNIQUE,
  `email`         VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name`     VARCHAR(100) NOT NULL,
  `role`          ENUM('admin', 'teacher', 'technician') NOT NULL DEFAULT 'teacher',
  `department`    VARCHAR(100) DEFAULT NULL COMMENT 'กลุ่มสาระฯ / ฝ่ายงาน',
  `phone`         VARCHAR(20)  DEFAULT NULL,
  `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_role` (`role`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 2. Table: equipment_types (หมวดหมู่อุปกรณ์/งานซ่อม)
-- ----------------------------------------------------------
CREATE TABLE `equipment_types` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100) NOT NULL UNIQUE,
  `icon`        VARCHAR(50)  NOT NULL DEFAULT 'fa-solid fa-wrench',
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 3. Table: assets (System A: ทะเบียนครุภัณฑ์และ QR Code)
-- ----------------------------------------------------------
CREATE TABLE `assets` (
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

-- ----------------------------------------------------------
-- 4. Table: repair_tickets (ใบแจ้งซ่อม)
-- Status: pending (รอดำเนินการ) -> in_process (กำลังซ่อม) -> completed (เสร็จสิ้น)
-- ----------------------------------------------------------
CREATE TABLE `repair_tickets` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_no`           VARCHAR(30)  NOT NULL UNIQUE,
  `member_id`           INT UNSIGNED NOT NULL COMMENT 'ครู/บุคลากรผู้แจ้ง',
  `technician_id`       INT UNSIGNED DEFAULT NULL COMMENT 'ช่างผู้รับผิดชอบ (Admin กำหนด)',
  `equipment_type_id`   INT UNSIGNED NOT NULL COMMENT 'ประเภทอุปกรณ์/ปัญหา',
  `asset_id`            INT UNSIGNED DEFAULT NULL COMMENT 'ครุภัณฑ์ที่เชื่อมโยง (System A)',
  `title`               VARCHAR(150) NOT NULL COMMENT 'หัวข้อปัญหา',
  `description`         TEXT         NOT NULL COMMENT 'รายละเอียดปัญหา',
  `building`            VARCHAR(100) NOT NULL COMMENT 'อาคาร',
  `room`                VARCHAR(80)  NOT NULL COMMENT 'ห้อง/จุดติดตั้ง',
  `status`              ENUM('pending', 'in_process', 'completed') NOT NULL DEFAULT 'pending',
  `priority`            ENUM('low', 'normal', 'high', 'urgent')    NOT NULL DEFAULT 'normal',
  `due_date`            DATE         DEFAULT NULL COMMENT 'กำหนดวันเสร็จ (Admin ระบุ)',
  `technician_note`     TEXT         DEFAULT NULL COMMENT 'บันทึกผลการแก้ไขของช่าง',
  `repair_cost`         DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'ค่าใช้จ่ายในการซ่อม (บาท)',
  `cost_description`    TEXT         DEFAULT NULL COMMENT 'รายละเอียดค่าใช้จ่าย/รายการอะไหล่',
  `rating`              TINYINT UNSIGNED DEFAULT NULL COMMENT 'คะแนนความพึงพอใจ 1-5 (System B)',
  `rating_comment`      TEXT         DEFAULT NULL,
  `accepted_at`         DATETIME     DEFAULT NULL,
  `accepted_by_user_id` INT UNSIGNED DEFAULT NULL,
  `rework_count`        INT UNSIGNED NOT NULL DEFAULT 0,
  `assigned_at`         DATETIME     DEFAULT NULL,
  `completed_at`        DATETIME     DEFAULT NULL,
  `created_at`          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ticket_member`     (`member_id`),
  KEY `fk_ticket_technician` (`technician_id`),
  KEY `fk_ticket_equip`      (`equipment_type_id`),
  KEY `fk_ticket_asset`      (`asset_id`),
  KEY `fk_ticket_acceptor`   (`accepted_by_user_id`),
  KEY `idx_status`           (`status`),
  KEY `idx_priority`         (`priority`),
  KEY `idx_due_date`         (`due_date`),
  CONSTRAINT `fk_ticket_member`     FOREIGN KEY (`member_id`)           REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_ticket_technician` FOREIGN KEY (`technician_id`)       REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ticket_equip`      FOREIGN KEY (`equipment_type_id`)   REFERENCES `equipment_types` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_ticket_asset`      FOREIGN KEY (`asset_id`)            REFERENCES `assets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ticket_acceptor`   FOREIGN KEY (`accepted_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 5. Table: ticket_images (ภาพถ่ายก่อน-หลังซ่อม)
-- ----------------------------------------------------------
CREATE TABLE `ticket_images` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id`           INT UNSIGNED NOT NULL,
  `uploaded_by_user_id` INT UNSIGNED NOT NULL,
  `type`                ENUM('before', 'after') NOT NULL COMMENT 'before=ก่อนซ่อม, after=หลังซ่อม',
  `file_name`           VARCHAR(255) NOT NULL,
  `original_name`       VARCHAR(255) DEFAULT NULL,
  `file_size`           INT UNSIGNED DEFAULT NULL,
  `mime_type`           VARCHAR(50)  DEFAULT NULL,
  `caption`             VARCHAR(255) DEFAULT NULL,
  `created_at`          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_img_ticket` (`ticket_id`),
  KEY `fk_img_user`   (`uploaded_by_user_id`),
  CONSTRAINT `fk_img_ticket` FOREIGN KEY (`ticket_id`)           REFERENCES `repair_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_img_user`   FOREIGN KEY (`uploaded_by_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 6. Table: ticket_comments (ความคิดเห็น/ข้อความสอบถามในใบแจ้งซ่อม)
-- ----------------------------------------------------------
CREATE TABLE `ticket_comments` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id`  INT UNSIGNED NOT NULL,
  `user_id`    INT UNSIGNED NOT NULL,
  `comment`    TEXT         NOT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_comment_ticket` (`ticket_id`),
  KEY `fk_comment_user`   (`user_id`),
  CONSTRAINT `fk_comment_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `repair_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comment_user`   FOREIGN KEY (`user_id`)   REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 7. Table: repair_status_logs (ประวัติการเปลี่ยนสถานะ)
-- ----------------------------------------------------------
CREATE TABLE `repair_status_logs` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id`          INT UNSIGNED NOT NULL,
  `changed_by_user_id` INT UNSIGNED DEFAULT NULL,
  `previous_status`    VARCHAR(30)  DEFAULT NULL,
  `new_status`         VARCHAR(30)  NOT NULL,
  `notes`              VARCHAR(255) DEFAULT NULL,
  `created_at`         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_log_ticket` (`ticket_id`),
  KEY `fk_log_user`   (`changed_by_user_id`),
  CONSTRAINT `fk_log_ticket` FOREIGN KEY (`ticket_id`)          REFERENCES `repair_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_log_user`   FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 8. Table: spare_parts (System C: คลังอะไหล่และอุปกรณ์)
-- ----------------------------------------------------------
CREATE TABLE `spare_parts` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `part_code`         VARCHAR(50)  NOT NULL UNIQUE COMMENT 'รหัสอะไหล่ เช่น SP-001',
  `name`              VARCHAR(150) NOT NULL COMMENT 'ชื่ออะไหล่/วัสดุ',
  `unit`              VARCHAR(30)  NOT NULL DEFAULT 'ชิ้น' COMMENT 'หน่วยนับ',
  `unit_cost`         DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'ราคาต่อหน่วย (บาท)',
  `category`          VARCHAR(50)  DEFAULT 'General',
  `cost_price`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `sell_price`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock_quantity`    INT          NOT NULL DEFAULT 0 COMMENT 'จำนวนคงเหลือในคลัง',
  `min_quantity`      INT          NOT NULL DEFAULT 5 COMMENT 'จุดเตือนสั่งซื้อขั้นต่ำ',
  `equipment_type_id` INT UNSIGNED DEFAULT NULL COMMENT 'หมวดหมู่ที่เกี่ยวข้อง',
  `min_stock_alert`   INT          NOT NULL DEFAULT 2,
  `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_part_equip` (`equipment_type_id`),
  CONSTRAINT `fk_part_equip` FOREIGN KEY (`equipment_type_id`) REFERENCES `equipment_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 9. Table: ticket_parts_used (System C: บันทึกการใช้อะไหล่ในใบงาน)
-- ----------------------------------------------------------
CREATE TABLE `ticket_parts_used` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id`     INT UNSIGNED NOT NULL,
  `spare_part_id` INT UNSIGNED NOT NULL,
  `quantity`      INT          NOT NULL DEFAULT 1,
  `unit_cost`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_cost`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_used_ticket` (`ticket_id`),
  KEY `fk_used_part`   (`spare_part_id`),
  CONSTRAINT `fk_used_ticket` FOREIGN KEY (`ticket_id`)     REFERENCES `repair_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_used_part`   FOREIGN KEY (`spare_part_id`) REFERENCES `spare_parts` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 10. Table: pm_schedules (System E: แผนบำรุงรักษาเชิงป้องกันตามรอบ)
-- ----------------------------------------------------------
CREATE TABLE `pm_schedules` (
  `id`                     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`                  VARCHAR(150) NOT NULL COMMENT 'หัวข้อแผนบำรุงรักษา',
  `description`            TEXT         DEFAULT NULL,
  `equipment_type_id`      INT UNSIGNED NOT NULL,
  `asset_id`               INT UNSIGNED DEFAULT NULL,
  `building`               VARCHAR(100) NOT NULL,
  `room`                   VARCHAR(100) DEFAULT NULL,
  `frequency_days`         INT          NOT NULL DEFAULT 90 COMMENT 'ความถี่รอบวัน เช่น 30, 90, 180, 365',
  `last_run_at`            DATE         DEFAULT NULL COMMENT 'วันที่ดำเนินการล่าสุด',
  `next_due_date`          DATE         NOT NULL COMMENT 'วันที่ครบกำหนดรอบถัดไป',
  `assigned_technician_id` INT UNSIGNED DEFAULT NULL,
  `is_active`              TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_pm_equip`  (`equipment_type_id`),
  KEY `fk_pm_asset`  (`asset_id`),
  KEY `fk_pm_tech`   (`assigned_technician_id`),
  KEY `idx_pm_due`   (`next_due_date`),
  CONSTRAINT `fk_pm_equip` FOREIGN KEY (`equipment_type_id`)      REFERENCES `equipment_types` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_pm_asset` FOREIGN KEY (`asset_id`)               REFERENCES `assets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pm_tech`  FOREIGN KEY (`assigned_technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 11. Table: notifications (ระบบการแจ้งเตือนภายใน)
-- ----------------------------------------------------------
CREATE TABLE `notifications` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `ticket_id`  INT UNSIGNED DEFAULT NULL,
  `title`      VARCHAR(150) NOT NULL,
  `message`    VARCHAR(255) NOT NULL,
  `type`       ENUM('info', 'success', 'warning', 'danger') NOT NULL DEFAULT 'info',
  `link`       VARCHAR(255) DEFAULT NULL,
  `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_notif_user`   (`user_id`),
  KEY `fk_notif_ticket` (`ticket_id`),
  KEY `idx_notif_read`  (`user_id`, `is_read`),
  CONSTRAINT `fk_notif_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `repair_tickets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_notif_user`   FOREIGN KEY (`user_id`)   REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- SEED DATA
-- Default password for all seed accounts: "password123"
-- BCRYPT Hash: $2y$10$pIwCkgJj.tPnOuQZV/Sh9eJa1YDnsXmSF/y6d.jepXbSxfGRgkTGW
-- ==========================================================

-- 1. Users
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `department`, `phone`) VALUES
(1, 'admin', 'admin@school.ac.th', '$2y$10$pIwCkgJj.tPnOuQZV/Sh9eJa1YDnsXmSF/y6d.jepXbSxfGRgkTGW', 'อ.สมเกียรติ บริหารงาน (ผู้ดูแลระบบ)', 'admin', 'ฝ่ายบริหารงานทั่วไปและอาคารสถานที่', '081-234-5678'),
(2, 'tech01', 'tech01@school.ac.th', '$2y$10$pIwCkgJj.tPnOuQZV/Sh9eJa1YDnsXmSF/y6d.jepXbSxfGRgkTGW', 'ช่างสมชาย มั่นคง (ช่างเทคนิค/ไฟฟ้า)', 'technician', 'งานซ่อมบำรุงและโสตทัศนูปกรณ์', '089-876-5432'),
(3, 'tech02', 'tech02@school.ac.th', '$2y$10$pIwCkgJj.tPnOuQZV/Sh9eJa1YDnsXmSF/y6d.jepXbSxfGRgkTGW', 'ช่างวิชัย ว่องไว (ช่างคอมพิวเตอร์/ระบบ)', 'technician', 'ศูนย์เทคโนโลยีสารสนเทศ', '085-555-4321'),
(4, 'teacher01', 'teacher01@school.ac.th', '$2y$10$pIwCkgJj.tPnOuQZV/Sh9eJa1YDnsXmSF/y6d.jepXbSxfGRgkTGW', 'ครูสุดา วงศ์ไทย', 'teacher', 'กลุ่มสาระการเรียนรู้วิทยาศาสตร์และเทคโนโลยี', '086-111-2233'),
(5, 'teacher02', 'teacher02@school.ac.th', '$2y$10$pIwCkgJj.tPnOuQZV/Sh9eJa1YDnsXmSF/y6d.jepXbSxfGRgkTGW', 'ครูอนุชา กิตติสุข', 'teacher', 'กลุ่มสาระการเรียนรู้คณิตศาสตร์', '087-222-3344'),
(6, 'staff01', 'staff01@school.ac.th', '$2y$10$pIwCkgJj.tPnOuQZV/Sh9eJa1YDnsXmSF/y6d.jepXbSxfGRgkTGW', 'นางวิภา เจ้าหน้าที่ธุรการ', 'teacher', 'ฝ่ายธุรการและการเงิน', '084-333-7788');

-- 2. Equipment Types
INSERT INTO `equipment_types` (`id`, `name`, `icon`, `description`) VALUES
(1, 'เครื่องคอมพิวเตอร์และอุปกรณ์ต่อพ่วง', 'fa-solid fa-desktop', 'PC, Notebook, จอภาพ, คีย์บอร์ด, เมาส์'),
(2, 'ระบบโสตทัศนูปกรณ์และโปรเจกเตอร์', 'fa-solid fa-video', 'Projector, จอรับภาพ, เครื่องขยายเสียง, ไมโครโฟน'),
(3, 'เครื่องปรับอากาศและพัดลม', 'fa-solid fa-snowflake', 'แอร์ติดผนัง, แอร์ตั้งแขวน, พัดลมเพดาน, พัดลมดูดอากาศ'),
(4, 'ระบบไฟฟ้าและสวิตช์ปลั๊ก', 'fa-solid fa-bolt', 'หลอดไฟ LED, สวิตช์ไฟ, เต้ารับ, เบรกเกอร์'),
(5, 'ระบบสุขาภิบาลและประปา', 'fa-solid fa-faucet-drip', 'ก๊อกน้ำ, ท่อน้ำรั่ว, ชักโครก, อ่างล้างหน้า'),
(6, 'เครื่องพิมพ์และสแกนเนอร์', 'fa-solid fa-print', 'Laser Printer, InkTank, Copier, Scanner'),
(7, 'ระบบเครือข่ายและอินเทอร์เน็ต', 'fa-solid fa-wifi', 'สาย LAN, Access Point, Switch, Router'),
(8, 'ประตู หน้าต่าง โต๊ะเก้าอี้ ครุภัณฑ์', 'fa-solid fa-door-closed', 'ลูกบิดประตู, บานพับ, โต๊ะเรียน, เก้าอี้ครู');

-- 3. Assets (ครุภัณฑ์โรงเรียน)
INSERT INTO `assets` (`id`, `asset_code`, `name`, `equipment_type_id`, `brand`, `model`, `serial_number`, `building`, `room`, `status`, `purchase_date`, `warranty_expiry`, `price`, `qr_token`) VALUES
(1, 'ศธ-0401/67-001', 'เครื่องคอมพิวเตอร์ All-in-One ห้องปฏิบัติการ 204', 1, 'Dell', 'OptiPlex 7400', 'SN-DELL-99214', 'อาคาร 2 วิทยาศาสตร์', 'ห้องปฏิบัติการคอมพิวเตอร์ 204', 'active', '2024-05-15', '2027-05-15', 32000.00, 'ASSET-TOKEN-001'),
(2, 'ศธ-0401/66-002', 'เครื่องปรับอากาศระบบอินเวอร์เตอร์ 24,000 BTU', 3, 'Daikin', 'FTKQ24WV2S', 'DK-24K-8812', 'อาคาร 1 เฉลิมพระเกียรติ', 'ห้องพักครู 101', 'active', '2023-03-10', '2028-03-10', 38900.00, 'ASSET-TOKEN-002'),
(3, 'ศธ-0401/67-003', 'เครื่องฉายภาพโปรเจกเตอร์ความคมชัดสูง Full HD', 2, 'Epson', 'EB-FH52', 'EP-PROJ-3341', 'อาคาร 1 เฉลิมพระเกียรติ', 'ห้องเรียน 103', 'active', '2024-01-20', '2027-01-20', 26500.00, 'ASSET-TOKEN-003'),
(4, 'ศธ-0401/67-004', 'ชุดตู้ทำน้ำเย็นสแตนเลส 4 หัวก๊อก', 5, 'Lucky Flame', 'WD-404', 'LF-7721-04', 'อาคารอเนกประสงค์ และโรงอาหาร', 'ลานน้ำดื่มชั้น 1', 'active', '2024-02-10', '2025-02-10', 14500.00, 'ASSET-TOKEN-004'),
(5, 'ศธ-0401/65-005', 'พัดลมเพดานโคจร 16 นิ้ว', 3, 'Mitsubishi', 'CY16-ST', 'MB-FAN-0112', 'อาคาร 3 มัธยมศึกษา', 'ห้องเรียน 302', 'active', '2022-08-01', '2024-08-01', 1450.00, 'ASSET-TOKEN-005');

-- 4. Repair Tickets
INSERT INTO `repair_tickets` 
  (`id`, `ticket_no`, `member_id`, `technician_id`, `equipment_type_id`, `asset_id`, `title`, `description`, `building`, `room`, `status`, `priority`, `due_date`, `technician_note`, `repair_cost`, `cost_description`, `assigned_at`, `completed_at`, `created_at`) 
VALUES
(1, 'REP-202609-0001', 4, 3, 1, 1,
 'คอมพิวเตอร์ห้องปฏิบัติการเปิดไม่ติด', 
 'เปิดสวิตช์เคสแล้วพัดลมหมุนเบาๆ ไม่มีภาพขึ้นจอ มีเสียงบี๊บยาว 1 ครั้ง สั้น 2 ครั้ง', 
 'อาคาร 2 วิทยาศาสตร์', 'ห้องปฏิบัติการคอมพิวเตอร์ 204', 
 'in_process', 'high', '2026-09-23', 
 'เข้าตรวจสอบเบื้องต้นแล้ว พบ RAM หลวมและมีฝุ่นสะสม กำลังทำความสะอาด slot และทดสอบเปิดเครื่อง', 
 0.00, NULL, 
 '2026-09-20 09:30:00', NULL, '2026-09-20 08:15:00'),

(2, 'REP-202609-0002', 5, NULL, 2, 3,
 'โปรเจกเตอร์ภาพกระพริบและติดเป็นแถบสีเหลือง', 
 'เปิดใช้งานสอนคาบเช้า ภาพฉายกระพริบตลอดเวลาและมีแถบสีเหลืองทับหน้าจอ ทำให้นักเรียนมองสไลด์ไม่ชัด', 
 'อาคาร 1 เฉลิมพระเกียรติ', 'ห้องเรียน 103 ชั้น 1', 
 'pending', 'urgent', NULL, 
 NULL, 
 0.00, NULL, 
 NULL, NULL, '2026-09-21 07:45:00'),

(3, 'REP-202609-0003', 6, 2, 3, 2,
 'เครื่องปรับอากาศมีน้ำหยดลงบนโต๊ะเอกสาร', 
 'เปิดแอร์ได้ประมาณ 30 นาทีแล้วมีน้ำไหลหยดจากตัวเครื่องด้านขวา ท่อน้ำทิ้งอาจจะตัน', 
 'อาคารบริหารและธุรการ', 'ห้องธุรการและการเงิน ชั้น 1', 
 'completed', 'high', '2026-09-20', 
 'ทำการล้างแผงคอยล์เย็น เป่าล้างท่อน้ำทิ้งที่อุดตันด้วยตะไคร่น้ำ ตรวจวัดแรงดันน้ำยาแอร์ปกติ ใช้งานได้ดีแล้ว', 
 450.00, 'ค่าน้ำยาล้างคอยล์และอุปกรณ์เป่าท่อ', 
 '2026-09-19 10:00:00', '2026-09-19 14:30:00', '2026-09-19 09:10:00'),

(4, 'REP-202609-0004', 4, 3, 7, NULL,
 'สัญญาณ Wi-Fi หลุดบ่อย เชื่อมต่อได้แต่ใช้งานอินเทอร์เน็ตไม่ได้', 
 'ครูและนักเรียนไม่สามารถเชื่อมต่อสัญญาณ Wi-Fi Access Point ประจำชั้น 3 ได้ ไฟสถานะขึ้นสีส้มกระพริบ', 
 'อาคาร 2 วิทยาศาสตร์', 'โถงทางเดินชั้น 3 หน้าห้องพักครู', 
 'completed', 'normal', '2026-09-19', 
 'ตรวจสอบพบหัวต่อ RJ-45 ต้นทางหลวม ทำการตัดเข้าหัวสาย LAN ใหม่ และรีบูต PoE Switch ระบบกลับมาออนไลน์ปกติ', 
 80.00, 'หัวแลน RJ-45 Cat6 จำนวน 2 ตัว', 
 '2026-09-18 13:00:00', '2026-09-18 16:00:00', '2026-09-18 11:20:00'),

(5, 'REP-202609-0005', 5, NULL, 4, NULL,
 'หลอดไฟ LED เพดานกระพริบถี่ และสวิตช์ไฟมีเสียงดังผิดปกติ', 
 'หลอดไฟแถวกลางห้องกระพริบตลอดเวลาเมื่อเปิด และสวิตช์ไฟแถวริมหน้าต่างเปิดแล้วมีเสียงจี่ เกรงว่าจะลัดวงจร', 
 'อาคาร 1 เฉลิมพระเกียรติ', 'ห้องเรียน 205 ชั้น 2', 
 'pending', 'urgent', NULL, 
 NULL, 
 0.00, NULL, 
 NULL, NULL, '2026-09-21 08:30:00'),

(6, 'REP-202609-0006', 6, 2, 6, NULL,
 'เครื่องพิมพ์เอกสารดึงกระดาษซ้อนกันและติดขัด', 
 'เครื่องพิมพ์ Laser ห้องธุรการ พิมพ์งานแล้วลูกกลิ้งดึงกระดาษทีละหลายแผ่นจนติดขัดข้างใน', 
 'อาคารบริหารและธุรการ', 'ห้องพิมพ์เอกสารข้อสอบ ชั้น 2', 
 'in_process', 'normal', '2026-09-22', 
 'สั่งซื้อลูกยางดึงกระดาษ (Pickup Roller) ตัวใหม่เรียบร้อยแล้ว อยู่ระหว่างรอจัดส่งอะไหล่เข้าโรงเรียน', 
 350.00, 'ลูกยางดึงกระดาษ Canon LBP (อะไหล่แท้)', 
 '2026-09-20 11:00:00', NULL, '2026-09-20 10:15:00');

-- 5. Ticket Comments
INSERT INTO `ticket_comments` (`id`, `ticket_id`, `user_id`, `comment`, `created_at`) VALUES
(1, 1, 4, 'เครื่องนี้จำเป็นต้องใช้สอบเก็บคะแนนวันศุกร์นี้ครับ รบกวนช่างตรวจสอบให้ด้วยครับ', '2026-09-20 08:20:00'),
(2, 1, 1, 'รับเรื่องแล้วครับ มอบหมายช่างวิชัยดูแลให้ด่วน กำหนดเสร็จภายในวันที่ 23 นี้', '2026-09-20 09:30:00'),
(3, 1, 3, 'รับทราบครับ กำลังเช็คแผงบอร์ดและแรม หากมีชิ้นส่วนต้องเบิกจะแจ้งเพิ่มเติมครับ', '2026-09-20 10:15:00'),
(4, 2, 5, 'อาการเป็นช่วงก่อนเริ่มสอน คาดว่าอาจเป็นที่สายสัญญาณ HDMI หรือหลอดภาพครับ', '2026-09-21 07:50:00'),
(5, 3, 6, 'ขอบคุณช่างสมชายมากค่ะ ล้างแอร์เสร็จเรียบร้อย ไม่มีน้ำหยดแล้ว เย็นฉ่ำดีมากค่ะ', '2026-09-19 15:00:00'),
(6, 6, 2, 'เช็คลูกยางเดิมสึกหมดสภาพแล้วครับ ได้แจ้งฝ่ายจัดซื้อสั่งอะไหล่แล้ว คาดว่าจะถึงพรุ่งนี้ครับ', '2026-09-20 14:00:00');

-- 6. Status Logs
INSERT INTO `repair_status_logs` (`ticket_id`, `changed_by_user_id`, `previous_status`, `new_status`, `notes`) VALUES
(1, 4, NULL, 'pending', 'ครูสุดา ส่งคำขอแจ้งซ่อม'),
(1, 1, 'pending', 'in_process', 'ผู้ดูแลระบบมอบหมายงานให้ ช่างวิชัย (กำหนดเสร็จ 2026-09-23)'),
(2, 5, NULL, 'pending', 'ครูอนุชา ส่งคำขอแจ้งซ่อม (เร่งด่วน)'),
(3, 6, NULL, 'pending', 'เจ้าหน้าที่ธุรการ ส่งคำขอแจ้งซ่อม'),
(3, 1, 'pending', 'in_process', 'ผู้ดูแลระบบมอบหมายงานให้ ช่างสมชาย'),
(3, 2, 'in_process', 'completed', 'ช่างสมชาย ดำเนินการล้างแผงคอยล์และท่อน้ำทิ้งเสร็จสิ้น ค่าใช้จ่าย 450 บาท'),
(4, 4, NULL, 'pending', 'ครูสุดา ส่งคำขอแจ้งซ่อม'),
(4, 1, 'pending', 'in_process', 'ผู้ดูแลระบบมอบหมายงานให้ ช่างวิชัย'),
(4, 3, 'in_process', 'completed', 'ช่างวิชัย เข้าหัวสาย LAN ใหม่ และทดสอบเชื่อมต่อสำเร็จ ค่าใช้จ่าย 80 บาท'),
(5, 5, NULL, 'pending', 'ครูอนุชา ส่งคำขอแจ้งซ่อม (เร่งด่วน)'),
(6, 6, NULL, 'pending', 'เจ้าหน้าที่ธุรการ ส่งคำขอแจ้งซ่อม'),
(6, 1, 'pending', 'in_process', 'ผู้ดูแลระบบมอบหมายงานให้ ช่างสมชาย');

-- 7. Spare Parts (คลังอะไหล่)
INSERT INTO `spare_parts` (`id`, `part_code`, `name`, `unit`, `unit_cost`, `category`, `cost_price`, `sell_price`, `stock_quantity`, `min_quantity`, `equipment_type_id`, `min_stock_alert`) VALUES
(1, 'PART-SSD-NVME-1TB', 'Kingston NV2 1TB PCIe 4.0 NVMe SSD', 'ชิ้น', 1800.00, 'Storage', 1800.00, 2400.00, 15, 3, 1, 3),
(2, 'PART-RAM-DDR4-16G', 'Kingston Fury Beast 16GB DDR4 3200MHz', 'ชิ้น', 1100.00, 'Memory', 1100.00, 1500.00, 8, 2, 1, 2),
(3, 'PART-RAM-DDR5-16G', 'Crucial 16GB DDR5 4800MHz Laptop SO-DIMM', 'ชิ้น', 1600.00, 'Memory', 1600.00, 2200.00, 6, 2, 1, 2),
(4, 'PART-THERMAL-PASTE', 'Thermal Grizzly Kryonaut 1g', 'ชิ้น', 250.00, 'Thermal', 250.00, 450.00, 25, 5, 1, 5),
(5, 'PART-PSU-750W-GOLD', 'Corsair RM750e 750W 80 Plus Gold', 'ชิ้น', 2900.00, 'Power', 2900.00, 3800.00, 4, 1, 1, 1),
(6, 'PART-FAN-120MM-ARGB', 'Cooler Master SickleFlow 120 ARGB', 'ชิ้น', 250.00, 'Cooling', 250.00, 400.00, 10, 4, 1, 4);

-- 8. PM Schedules (แผนบำรุงรักษาเชิงป้องกัน)
INSERT INTO `pm_schedules` (`id`, `title`, `description`, `equipment_type_id`, `asset_id`, `building`, `room`, `frequency_days`, `last_run_at`, `next_due_date`, `assigned_technician_id`, `is_active`) VALUES
(1, 'ล้างทำความสะอาดแผ่นกรองแอร์ห้องพักครู', 'ตรวจเช็คน้ำยาแอร์ ล้างฟิลเตอร์กรองฝุ่น และทำความสะอาดคอยล์เย็น', 3, 2, 'อาคาร 1 เฉลิมพระเกียรติ', 'ห้องพักครู 101', 90, '2026-08-23', '2026-11-21', 3, 1),
(2, 'ตรวจเช็คระบบไฟฟ้าและไฟฉุกเฉินประจำเดือน', 'ทดสอบสวิตช์ตัดตอนอัตโนมัติ ตรวจสอบแบตเตอรี่ไฟฉุกเฉินทางเดิน', 4, NULL, 'อาคาร 2 วิทยาศาสตร์', 'ทุกชั้น', 30, '2026-10-22', '2026-11-21', 3, 1),
(3, 'ล้างทำความสะอาดตู้ทำน้ำเย็นและตรวจไส้กรอง', 'ล้างถังพักน้ำ สเตอริไลซ์หัวก๊อก และตรวจวัดค่าน้ำดื่ม', 5, 4, 'อาคารอเนกประสงค์ และโรงอาหาร', 'ลานน้ำดื่มชั้น 1', 60, '2026-09-02', '2026-11-01', 3, 1);

-- 9. Notifications (การแจ้งเตือนเริ่มต้น)
INSERT INTO `notifications` (`id`, `user_id`, `ticket_id`, `title`, `message`, `type`, `link`, `is_read`, `created_at`) VALUES
(1, 1, 1, 'ยินดีต้อนรับสู่ RepairForSchool', 'ระบบบริหารจัดการงานซ่อมบำรุงโรงเรียนเวอร์ชันใหม่พร้อมใช้งาน', 'success', 'index.php?page=dashboard', 0, NOW()),
(2, 2, 3, 'มอบหมายงานซ่อมบำรุง', 'ท่านได้รับมอบหมายงานซ่อมเครื่องปรับอากาศ #REP-202609-0003', 'info', 'index.php?page=tickets-show&id=3', 1, NOW()),
(3, 3, 1, 'มอบหมายงานซ่อมบำรุง', 'ท่านได้รับมอบหมายงานซ่อมคอมพิวเตอร์ #REP-202609-0001', 'info', 'index.php?page=tickets-show&id=1', 0, NOW());
