-- 迁移脚本：将固定餐厅店面改为动态餐厅店面系统
-- 执行日期：2026-01-22
-- 注意：此脚本创建新的 dishware_restaurant_locations 表，不会影响现有的 restaurants 表

-- 1. 创建新的餐厅店面表（专门用于碗碟库存管理）
CREATE TABLE IF NOT EXISTS `dishware_restaurant_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '餐厅店面名称',
  `code` varchar(50) NOT NULL COMMENT '餐厅店面代码（用于标识，如wenhua, central, j1等）',
  `display_order` int(11) NOT NULL DEFAULT 0 COMMENT '显示顺序',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_code` (`code`),
  KEY `idx_display_order` (`display_order`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='碗碟库存餐厅店面表';

-- 2. 插入现有的餐厅店面数据
INSERT INTO `dishware_restaurant_locations` (`name`, `code`, `display_order`, `is_active`) VALUES
('文化楼', 'wenhua', 1, 1),
('中央', 'central', 2, 1),
('J1', 'j1', 3, 1),
('J2', 'j2', 4, 1),
('J3', 'j3', 5, 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 3. 创建碗碟库存关联表（替代原来的固定列结构）
-- 如果表已存在，先删除旧的外键约束
SET @dbname = DATABASE();
SET @constraint_name = 'fk_dishware_stock_by_restaurant_restaurant';
SET @table_name = 'dishware_stock_by_restaurant';

-- 检查并删除旧的外键约束（如果存在）
SET @sql = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (constraint_name = @constraint_name)
      AND (table_schema = @dbname)
      AND (table_name = @table_name)
  ) > 0,
  CONCAT('ALTER TABLE `', @table_name, '` DROP FOREIGN KEY `', @constraint_name, '`'),
  'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 创建表（如果不存在）
CREATE TABLE IF NOT EXISTS `dishware_stock_by_restaurant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dishware_id` int(11) NOT NULL COMMENT '碗碟ID',
  `restaurant_id` int(11) NOT NULL COMMENT '餐厅店面ID',
  `quantity` int(11) NOT NULL DEFAULT 0 COMMENT '库存数量',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_dishware_restaurant` (`dishware_id`, `restaurant_id`),
  KEY `idx_dishware_id` (`dishware_id`),
  KEY `idx_restaurant_id` (`restaurant_id`),
  CONSTRAINT `fk_dishware_stock_by_restaurant_dishware` FOREIGN KEY (`dishware_id`) REFERENCES `dishware_info` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dishware_stock_by_restaurant_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `dishware_restaurant_locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='碗碟库存按餐厅店面关联表';

-- 如果表已存在但外键约束不存在，先清理无效数据，然后添加外键约束
SET @constraint_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE constraint_name = @constraint_name
  AND table_schema = @dbname
  AND table_name = @table_name);

-- 如果表已存在，先删除无效的 restaurant_id 数据（不在 dishware_restaurant_locations 表中的）
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
  WHERE table_schema = @dbname
  AND table_name = @table_name);

SET @sql = (SELECT IF(
  @table_exists > 0,
  CONCAT('DELETE dsbr FROM `', @table_name, '` dsbr LEFT JOIN `dishware_restaurant_locations` drl ON dsbr.`restaurant_id` = drl.`id` WHERE drl.`id` IS NULL'),
  'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 添加外键约束（如果不存在）
SET @sql = (SELECT IF(
  @constraint_exists = 0,
  CONCAT('ALTER TABLE `', @table_name, '` ADD CONSTRAINT `', @constraint_name, '` FOREIGN KEY (`restaurant_id`) REFERENCES `dishware_restaurant_locations` (`id`) ON DELETE CASCADE'),
  'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. 创建套装库存关联表（替代原来的固定列结构）
-- 如果表已存在，先删除旧的外键约束
SET @constraint_name = 'fk_dishware_set_stock_by_restaurant_restaurant';
SET @table_name = 'dishware_set_stock_by_restaurant';

-- 检查并删除旧的外键约束（如果存在）
SET @sql = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (constraint_name = @constraint_name)
      AND (table_schema = @dbname)
      AND (table_name = @table_name)
  ) > 0,
  CONCAT('ALTER TABLE `', @table_name, '` DROP FOREIGN KEY `', @constraint_name, '`'),
  'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 创建表（如果不存在）
CREATE TABLE IF NOT EXISTS `dishware_set_stock_by_restaurant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `set_id` int(11) NOT NULL COMMENT '套装ID',
  `restaurant_id` int(11) NOT NULL COMMENT '餐厅店面ID',
  `quantity` int(11) NOT NULL DEFAULT 0 COMMENT '库存数量',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_set_restaurant` (`set_id`, `restaurant_id`),
  KEY `idx_set_id` (`set_id`),
  KEY `idx_restaurant_id` (`restaurant_id`),
  CONSTRAINT `fk_dishware_set_stock_by_restaurant_set` FOREIGN KEY (`set_id`) REFERENCES `dishware_sets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dishware_set_stock_by_restaurant_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `dishware_restaurant_locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='套装库存按餐厅店面关联表';

-- 如果表已存在但外键约束不存在，先清理无效数据，然后添加外键约束
SET @constraint_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE constraint_name = @constraint_name
  AND table_schema = @dbname
  AND table_name = @table_name);

-- 如果表已存在，先删除无效的 restaurant_id 数据（不在 dishware_restaurant_locations 表中的）
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
  WHERE table_schema = @dbname
  AND table_name = @table_name);

SET @sql = (SELECT IF(
  @table_exists > 0,
  CONCAT('DELETE dsbr FROM `', @table_name, '` dsbr LEFT JOIN `dishware_restaurant_locations` drl ON dsbr.`restaurant_id` = drl.`id` WHERE drl.`id` IS NULL'),
  'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 添加外键约束（如果不存在）
SET @sql = (SELECT IF(
  @constraint_exists = 0,
  CONCAT('ALTER TABLE `', @table_name, '` ADD CONSTRAINT `', @constraint_name, '` FOREIGN KEY (`restaurant_id`) REFERENCES `dishware_restaurant_locations` (`id`) ON DELETE CASCADE'),
  'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. 迁移现有数据到新表结构
-- 迁移碗碟库存数据
INSERT INTO `dishware_stock_by_restaurant` (`dishware_id`, `restaurant_id`, `quantity`)
SELECT 
    ds.dishware_id,
    r.id as restaurant_id,
    CASE r.code
        WHEN 'wenhua' THEN ds.wenhua_quantity
        WHEN 'central' THEN ds.central_quantity
        WHEN 'j1' THEN ds.j1_quantity
        WHEN 'j2' THEN ds.j2_quantity
        WHEN 'j3' THEN ds.j3_quantity
        ELSE 0
    END as quantity
FROM dishware_stock ds
CROSS JOIN dishware_restaurant_locations r
WHERE r.code IN ('wenhua', 'central', 'j1', 'j2', 'j3')
ON DUPLICATE KEY UPDATE quantity = VALUES(quantity);

-- 迁移套装库存数据
INSERT INTO `dishware_set_stock_by_restaurant` (`set_id`, `restaurant_id`, `quantity`)
SELECT 
    dss.set_id,
    r.id as restaurant_id,
    CASE r.code
        WHEN 'wenhua' THEN dss.wenhua_quantity
        WHEN 'central' THEN dss.central_quantity
        WHEN 'j1' THEN dss.j1_quantity
        WHEN 'j2' THEN dss.j2_quantity
        WHEN 'j3' THEN dss.j3_quantity
        ELSE 0
    END as quantity
FROM dishware_set_stock dss
CROSS JOIN dishware_restaurant_locations r
WHERE r.code IN ('wenhua', 'central', 'j1', 'j2', 'j3')
ON DUPLICATE KEY UPDATE quantity = VALUES(quantity);

-- 注意：原有的 dishware_stock 和 dishware_set_stock 表暂时保留
-- 等确认新系统运行正常后，可以删除旧表的固定列（wenhua_quantity, central_quantity等）
-- 或者保留作为备份
