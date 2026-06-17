-- 更新班次表，移除 shift_name, color 和 is_active 字段
-- 如果你已经手动删除了这些字段，可以忽略此文件

USE u690174784_kunzz;

SET @dbname = 'u690174784_kunzz';
SET @tablename = 'schedule_shifts';

-- 删除 shift_name 字段
SET @columnname = 'shift_name';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @columnname) > 0,
  'ALTER TABLE schedule_shifts DROP COLUMN shift_name;',
  'SELECT 1;'
));
PREPARE alterStatement FROM @preparedStatement;
EXECUTE alterStatement;
DEALLOCATE PREPARE alterStatement;

-- 删除 color 字段
SET @columnname = 'color';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @columnname) > 0,
  'ALTER TABLE schedule_shifts DROP COLUMN color;',
  'SELECT 1;'
));
PREPARE alterStatement FROM @preparedStatement;
EXECUTE alterStatement;
DEALLOCATE PREPARE alterStatement;

-- 删除 is_active 字段
SET @columnname = 'is_active';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @columnname) > 0,
  'ALTER TABLE schedule_shifts DROP COLUMN is_active;',
  'SELECT 1;'
));
PREPARE alterStatement FROM @preparedStatement;
EXECUTE alterStatement;
DEALLOCATE PREPARE alterStatement;

SELECT 'schedule_shifts 表已更新完成，已移除 shift_name, color, is_active 字段' AS status;

