-- =============================================================
-- migration.sql — Apply to an EXISTING database without data loss
-- Run: mysql -u root -p recruitment_db < migration.sql
-- NOTE: Run SET GLOBAL event_scheduler = ON; once on the server
--       before importing to activate scheduled events.
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = '';

-- ---------------------------------------------------------------
-- 1. users — add last_login, status
-- ---------------------------------------------------------------
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `last_login` DATETIME NULL DEFAULT NULL AFTER `created_at`,
    ADD COLUMN IF NOT EXISTS `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active' AFTER `last_login`;

-- ---------------------------------------------------------------
-- 2. employer_profiles — add no_employees, business_registration_no
-- ---------------------------------------------------------------
ALTER TABLE `employer_profiles`
    ADD COLUMN IF NOT EXISTS `no_employees`              INT UNSIGNED NULL COMMENT 'Number of employees' AFTER `address`,
    ADD COLUMN IF NOT EXISTS `business_registration_no`  VARCHAR(50)  NULL COMMENT 'Số đăng ký kinh doanh' AFTER `no_employees`;

-- ---------------------------------------------------------------
-- 3. job_posts — keep salary (deprecated), add new salary columns
--    + end_date, recruit_type
-- ---------------------------------------------------------------
ALTER TABLE `job_posts`
    MODIFY COLUMN `salary` VARCHAR(100) NULL COMMENT 'DEPRECATED — use salary_min/salary_max',
    ADD COLUMN IF NOT EXISTS `salary_min`    ENUM('below_10M','10M_15M','15M_20M','20M_30M','30M_50M','above_50M') NULL AFTER `salary`,
    ADD COLUMN IF NOT EXISTS `salary_max`    ENUM('below_10M','10M_15M','15M_20M','20M_30M','30M_50M','above_50M') NULL AFTER `salary_min`,
    ADD COLUMN IF NOT EXISTS `currency`      ENUM('VND','USD','EUR') NOT NULL DEFAULT 'VND' AFTER `salary_max`,
    ADD COLUMN IF NOT EXISTS `end_date`      DATE NULL COMMENT 'Application deadline' AFTER `currency`,
    ADD COLUMN IF NOT EXISTS `recruit_type`  ENUM('fulltime','parttime','online','offline') NOT NULL DEFAULT 'fulltime' AFTER `end_date`;

-- ---------------------------------------------------------------
-- 4. jobseeker_profiles — add last_update, skills, education,
--    location, status
-- ---------------------------------------------------------------
ALTER TABLE `jobseeker_profiles`
    ADD COLUMN IF NOT EXISTS `last_update` DATETIME    NULL DEFAULT NULL AFTER `avatar_path`,
    ADD COLUMN IF NOT EXISTS `skills`      TEXT         NULL COMMENT 'Comma-separated list of skills' AFTER `last_update`,
    ADD COLUMN IF NOT EXISTS `education`   VARCHAR(255) NULL AFTER `skills`,
    ADD COLUMN IF NOT EXISTS `location`    VARCHAR(255) NULL AFTER `education`,
    ADD COLUMN IF NOT EXISTS `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active' AFTER `location`;

-- ---------------------------------------------------------------
-- 5. applications — rename existing unique key (same constraint)
--    The original uq_app_job_seeker already covers (job_id, jobseeker_id).
--    Rename it to match new spec without touching data.
-- ---------------------------------------------------------------
ALTER TABLE `applications`
    DROP KEY IF EXISTS `uq_app_job_seeker`,
    DROP INDEX IF EXISTS `uq_jobseeker_employer_active`,
    ADD UNIQUE KEY `uq_jobseeker_employer_active` (`job_id`, `jobseeker_id`);

-- ---------------------------------------------------------------
-- 6. MySQL FUNCTION — fn_jobseeker_has_active_application
-- ---------------------------------------------------------------
DROP FUNCTION IF EXISTS `fn_jobseeker_has_active_application`;

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
DELIMITER ;

-- ---------------------------------------------------------------
-- 7. TRIGGER — trg_check_user_last_login (BEFORE UPDATE on users)
--    If NEW.last_login is being set to a value older than 3 years,
--    force status = 'inactive'.
-- ---------------------------------------------------------------
DROP TRIGGER IF EXISTS `trg_check_user_last_login`;

DELIMITER $$
CREATE TRIGGER `trg_check_user_last_login`
BEFORE UPDATE ON `users`
FOR EACH ROW
BEGIN
    IF NEW.last_login IS NOT NULL AND DATEDIFF(NOW(), NEW.last_login) > 1095 THEN
        SET NEW.status = 'inactive';
    END IF;
END$$
DELIMITER ;

-- ---------------------------------------------------------------
-- 8. TRIGGER — trg_prevent_duplicate_application (BEFORE INSERT on applications)
-- ---------------------------------------------------------------
DROP TRIGGER IF EXISTS `trg_prevent_duplicate_application`;

DELIMITER $$
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
DELIMITER ;

-- ---------------------------------------------------------------
-- 9. TRIGGER — trg_jobseeker_last_update (BEFORE UPDATE on jobseeker_profiles)
--    Auto-sets last_update = NOW(); deactivates user if profile
--    has not been updated for > 5 years.
-- ---------------------------------------------------------------
DROP TRIGGER IF EXISTS `trg_jobseeker_last_update`;

DELIMITER $$
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

-- ---------------------------------------------------------------
-- 10. EVENT — evt_deactivate_inactive_users (daily)
-- ---------------------------------------------------------------
DROP EVENT IF EXISTS `evt_deactivate_inactive_users`;

DELIMITER $$
CREATE EVENT `evt_deactivate_inactive_users`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    UPDATE `users`
    SET    `status` = 'inactive'
    WHERE  `last_login` < DATE_SUB(NOW(), INTERVAL 3 YEAR)
      AND  `status`     = 'active';
END$$
DELIMITER ;

-- ---------------------------------------------------------------
-- 11. EVENT — evt_deactivate_stale_jobseekers (daily)
-- ---------------------------------------------------------------
DROP EVENT IF EXISTS `evt_deactivate_stale_jobseekers`;

DELIMITER $$
CREATE EVENT `evt_deactivate_stale_jobseekers`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    UPDATE `users` u
    JOIN   `jobseeker_profiles` jsp ON jsp.user_id = u.id
    SET    u.`status` = 'inactive'
    WHERE  jsp.`last_update` < DATE_SUB(NOW(), INTERVAL 5 YEAR)
      AND  u.`status` = 'active';
END$$
DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;
