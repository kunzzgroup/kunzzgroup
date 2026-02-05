-- 迁移脚本：删除 code 列，保留 display_order 用于拖拽排序
-- 执行日期：2026-01-22

-- 注意：MySQL 不支持 IF EXISTS，如果列不存在会报错，可以忽略

-- 1. 删除 code 列的唯一索引（如果存在）
SET @dbname = DATABASE();
SET @tablename = 'dishware_restaurant_locations';
SET @indexname = 'unique_code';

SET @sql = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_schema = @dbname)
      AND (table_name = @tablename)
      AND (index_name = @indexname)
  ) > 0,
  CONCAT('ALTER TABLE `', @tablename, '` DROP INDEX `', @indexname, '`'),
  'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. 删除 code 列（如果存在）
SET @columnname = 'code';
SET @sql = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_schema = @dbname)
      AND (table_name = @tablename)
      AND (column_name = @columnname)
  ) > 0,
  CONCAT('ALTER TABLE `', @tablename, '` DROP COLUMN `', @columnname, '`'),
  'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 注意：display_order 列保留，用于存储拖拽排序后的顺序
