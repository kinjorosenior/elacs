-- ============================================================
-- FIX CHECK-IN / CHECK-OUT  (v2)
-- Drops the UNIQUE constraint that prevents re-checking-in
-- a device after checkout, WITHOUT breaking foreign keys.
-- ============================================================

USE elacs;

-- ------------------------------------------------------------------
-- 1. Drop the foreign key that depends on the UNIQUE index.
--    The FK on (student_id) requires a leftmost index on student_id,
--    which is why MySQL refuses to drop the UNIQUE index directly.
-- ------------------------------------------------------------------
SET @fk_name := (
    SELECT CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME    = 'checkins'
      AND COLUMN_NAME   = 'student_id'
      AND REFERENCED_TABLE_NAME IS NOT NULL
    LIMIT 1
);

SET @drop_fk_sql := IF(
    @fk_name IS NOT NULL,
    CONCAT('ALTER TABLE checkins DROP FOREIGN KEY `', @fk_name, '`'),
    'SELECT 1'
);

PREPARE stmt FROM @drop_fk_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------------
-- 2. Drop the UNIQUE index on (student_id, serial_number, status)
--    which is what stopped a device from being checked in again
--    after it was checked out.
-- ------------------------------------------------------------------
SET @idx_name := (
    SELECT INDEX_NAME
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'checkins'
      AND NON_UNIQUE  = 0
      AND COLUMN_NAME IN ('student_id', 'serial_number', 'status')
    GROUP BY INDEX_NAME
    HAVING COUNT(DISTINCT COLUMN_NAME) >= 2
    LIMIT 1
);

SET @drop_idx_sql := IF(
    @idx_name IS NOT NULL,
    CONCAT('ALTER TABLE checkins DROP INDEX `', @idx_name, '`'),
    'SELECT 1'
);

PREPARE stmt FROM @drop_idx_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------------
-- 3. Add a plain (non-unique) index on student_id so the FK below
--    (and any lookup queries) remain fast.
-- ------------------------------------------------------------------
SET @idx_check := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'checkins'
      AND INDEX_NAME   = 'idx_checkins_student'
      AND COLUMN_NAME  = 'student_id'
);
SET @add_idx_sql := IF(
    @idx_check = 0,
    'ALTER TABLE checkins ADD INDEX idx_checkins_student (student_id)',
    'SELECT 1'
);
PREPARE stmt FROM @add_idx_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------------
-- 4. Re-create the foreign key on student_id (references students.student_id)
-- ------------------------------------------------------------------
SET @fk_now := (
    SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'checkins'
      AND COLUMN_NAME  = 'student_id'
      AND REFERENCED_TABLE_NAME IS NOT NULL
);
SET @add_fk_sql := IF(
    @fk_now = 0,
    'ALTER TABLE checkins ADD CONSTRAINT fk_checkins_student FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT 1'
);
PREPARE stmt FROM @add_fk_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------------
-- 5. Seed a default library + admin so check-ins don't fail with
--    foreign-key errors (library_id, admin_id).
-- ------------------------------------------------------------------
INSERT IGNORE INTO libraries (id, library_name, location)
VALUES (1, 'Kabarak University Library', 'Main Campus');

INSERT IGNORE INTO admins (id, library_id, full_name, email, password, role, status)
VALUES (1, 1, 'System Admin', 'admin@kabarak.ac.ke', 'admin123', 'admin', 'active');
