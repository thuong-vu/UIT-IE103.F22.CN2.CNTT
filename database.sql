-- =============================================================
-- Job Recruitment Website — Full Database Schema (v2)
-- 1. CREATE DATABASE recruitment_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- 2. USE recruitment_db;
-- 3. SOURCE database.sql;
-- NOTE: Run  SET GLOBAL event_scheduler = ON;  once on the server
--       to activate daily maintenance events.
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- ----------------------------
-- Table: users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`         VARCHAR(180) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role`          ENUM('employer','jobseeker') NOT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login`    DATETIME NULL DEFAULT NULL,
  `status`        ENUM('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: employer_profiles
-- ----------------------------
DROP TABLE IF EXISTS `employer_profiles`;
CREATE TABLE `employer_profiles` (
  `id`                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`                  INT UNSIGNED NOT NULL,
  `company_name`             VARCHAR(200) NOT NULL DEFAULT '',
  `description`              TEXT,
  `logo_path`                VARCHAR(255) DEFAULT NULL,
  `website`                  VARCHAR(255) DEFAULT NULL,
  `address`                  VARCHAR(255) DEFAULT NULL,
  `no_employees`             INT UNSIGNED NULL COMMENT 'Number of employees',
  `business_registration_no` VARCHAR(50)  NULL COMMENT 'Số đăng ký kinh doanh',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ep_user` (`user_id`),
  CONSTRAINT `fk_ep_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: jobseeker_profiles
-- ----------------------------
DROP TABLE IF EXISTS `jobseeker_profiles`;
CREATE TABLE `jobseeker_profiles` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED NOT NULL,
  `fullname`    VARCHAR(150) NOT NULL DEFAULT '',
  `phone`       VARCHAR(30)  DEFAULT NULL,
  `bio`         TEXT,
  `cv_path`     VARCHAR(255) DEFAULT NULL,
  `avatar_path` VARCHAR(255) DEFAULT NULL,
  `last_update` DATETIME     NULL DEFAULT NULL,
  `skills`      TEXT         NULL COMMENT 'Comma-separated list of skills',
  `education`   VARCHAR(255) NULL,
  `location`    VARCHAR(255) NULL,
  `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_jsp_user` (`user_id`),
  CONSTRAINT `fk_jsp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: job_posts
-- ----------------------------
DROP TABLE IF EXISTS `job_posts`;
CREATE TABLE `job_posts` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employer_id`  INT UNSIGNED NOT NULL,
  `title`        VARCHAR(200) NOT NULL,
  `description`  TEXT NOT NULL,
  `requirements` TEXT,
  `salary`       VARCHAR(100) NULL COMMENT 'DEPRECATED — use salary_min/salary_max',
  `salary_min`   ENUM('below_10M','10M_15M','15M_20M','20M_30M','30M_50M','above_50M') NULL,
  `salary_max`   ENUM('below_10M','10M_15M','15M_20M','20M_30M','30M_50M','above_50M') NULL,
  `currency`     ENUM('VND','USD','EUR') NOT NULL DEFAULT 'VND',
  `end_date`     DATE NULL COMMENT 'Application deadline',
  `recruit_type` ENUM('fulltime','parttime','online','offline') NOT NULL DEFAULT 'fulltime',
  `location`     VARCHAR(150) DEFAULT NULL,
  `category`     VARCHAR(100) DEFAULT NULL,
  `status`       ENUM('open','closed') NOT NULL DEFAULT 'open',
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_jp_employer` (`employer_id`),
  KEY `idx_jp_status`   (`status`),
  KEY `idx_jp_created`  (`created_at`),
  KEY `idx_jp_end_date` (`end_date`),
  CONSTRAINT `fk_jp_employer` FOREIGN KEY (`employer_id`) REFERENCES `employer_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: applications
-- ----------------------------
DROP TABLE IF EXISTS `applications`;
CREATE TABLE `applications` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id`       INT UNSIGNED NOT NULL,
  `jobseeker_id` INT UNSIGNED NOT NULL,
  `cover_letter` TEXT,
  `status`       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `applied_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_jobseeker_employer_active` (`job_id`, `jobseeker_id`),
  KEY `idx_app_jobseeker` (`jobseeker_id`),
  CONSTRAINT `fk_app_job`    FOREIGN KEY (`job_id`)       REFERENCES `job_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_app_seeker` FOREIGN KEY (`jobseeker_id`) REFERENCES `jobseeker_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: saved_jobs
-- ----------------------------
DROP TABLE IF EXISTS `saved_jobs`;
CREATE TABLE `saved_jobs` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `jobseeker_id` INT UNSIGNED NOT NULL,
  `job_id`       INT UNSIGNED NOT NULL,
  `saved_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sj_seeker_job` (`jobseeker_id`, `job_id`),
  CONSTRAINT `fk_sj_seeker` FOREIGN KEY (`jobseeker_id`) REFERENCES `jobseeker_profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sj_job`    FOREIGN KEY (`job_id`)       REFERENCES `job_posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: followed_companies
-- ----------------------------
DROP TABLE IF EXISTS `followed_companies`;
CREATE TABLE `followed_companies` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `jobseeker_id` INT UNSIGNED NOT NULL,
  `employer_id`  INT UNSIGNED NOT NULL,
  `followed_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fc_seeker_employer` (`jobseeker_id`, `employer_id`),
  CONSTRAINT `fk_fc_seeker`   FOREIGN KEY (`jobseeker_id`) REFERENCES `jobseeker_profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fc_employer` FOREIGN KEY (`employer_id`)  REFERENCES `employer_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- STORED FUNCTION
-- ================================================================

DELIMITER $$

CREATE FUNCTION `fn_jobseeker_has_active_application`(
    p_jobseeker_id INT,
    p_employer_id  INT
) RETURNS TINYINT(1)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_count INT DEFAULT 0;
    SELECT COUNT(*) INTO v_count
    FROM   applications a
    JOIN   job_posts    jp ON jp.id = a.job_id
    WHERE  a.jobseeker_id = p_jobseeker_id
      AND  jp.employer_id = p_employer_id
      AND  a.status IN ('pending', 'approved');
    RETURN v_count > 0;
END$$

-- ================================================================
-- TRIGGERS
-- ================================================================

-- Trigger: flag user inactive if last_login is set to a stale date
CREATE TRIGGER `trg_check_user_last_login`
BEFORE UPDATE ON `users`
FOR EACH ROW
BEGIN
    IF NEW.last_login IS NOT NULL AND DATEDIFF(NOW(), NEW.last_login) > 1095 THEN
        SET NEW.status = 'inactive';
    END IF;
END$$

-- Trigger: block duplicate active application with same employer
CREATE TRIGGER `trg_prevent_duplicate_application`
BEFORE INSERT ON `applications`
FOR EACH ROW
BEGIN
    DECLARE v_employer_id INT;
    SELECT employer_id INTO v_employer_id
    FROM   job_posts WHERE id = NEW.job_id;

    IF fn_jobseeker_has_active_application(NEW.jobseeker_id, v_employer_id) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'You already have an active application with this employer.';
    END IF;
END$$

-- Trigger: auto-set last_update; deactivate user if profile stale > 5 years
CREATE TRIGGER `trg_jobseeker_last_update`
BEFORE UPDATE ON `jobseeker_profiles`
FOR EACH ROW
BEGIN
    SET NEW.last_update = NOW();
    IF OLD.last_update IS NOT NULL
       AND DATEDIFF(NOW(), OLD.last_update) > 1825 THEN
        UPDATE `users` SET `status` = 'inactive' WHERE `id` = NEW.user_id;
    END IF;
END$$

DELIMITER ;

-- ================================================================
-- EVENTS  (require SET GLOBAL event_scheduler = ON;)
-- ================================================================

CREATE EVENT `evt_deactivate_inactive_users`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
  UPDATE `users`
  SET    `status` = 'inactive'
  WHERE  `last_login` < DATE_SUB(NOW(), INTERVAL 3 YEAR)
    AND  `status`     = 'active';

CREATE EVENT `evt_deactivate_stale_jobseekers`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
  UPDATE `users` u
  JOIN   `jobseeker_profiles` jsp ON jsp.user_id = u.id
  SET    u.`status` = 'inactive'
  WHERE  jsp.`last_update` < DATE_SUB(NOW(), INTERVAL 5 YEAR)
    AND  u.`status` = 'active';

SET FOREIGN_KEY_CHECKS = 1;
