-- 迁移脚本：将固定餐厅店面改为动态餐厅店面系统
-- 执行日期：2026-01-22

-- 1. 为现有的 restaurants 表添加必要的列（如果不存在）
-- 注意：如果列已存在，这些语句会报错，但可以安全忽略错误继续执行

-- 添加 name 列（如果不存在）
SET @dbname = DATABASE();
SET @tablename = 'restaurants';
SET @columnname = 'name';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN `', @columnname, '` varchar(100) NULL COMMENT ''餐厅店面名称''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 添加 code 列（如果不存在）
SET @columnname = 'code';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN `', @columnname, '` varchar(50) NULL COMMENT ''餐厅店面代码''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 添加 display_order 列（如果不存在）
SET @columnname = 'display_order';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN `', @columnname, '` int(11) NOT NULL DEFAULT 0 COMMENT ''显示顺序''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 添加 is_active 列（如果不存在）
SET @columnname = 'is_active';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN `', @columnname, '` tinyint(1) NOT NULL DEFAULT 1 COMMENT ''是否启用''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 如果 name 列存在但为 NULL，尝试从 name_cn 或 name_en 填充
UPDATE `restaurants` SET `name` = COALESCE(`name_cn`, `name_en`, `name`) WHERE `name` IS NULL;

-- 2. 插入现有的餐厅店面数据（如果不存在）
-- 使用 INSERT IGNORE 避免重复插入
INSERT IGNORE INTO `restaurants` (`name`, `code`, `display_order`, `is_active`) VALUES
('文化楼', 'wenhua', 1, 1),
('中央', 'central', 2, 1),
('J1', 'j1', 3, 1),
('J2', 'j2', 4, 1),
('J3', 'j3', 5, 1);

-- 如果记录已存在，更新相关字段
UPDATE `restaurants` SET 
  `name` = CASE `code`
    WHEN 'wenhua' THEN '文化楼'
    WHEN 'central' THEN '中央'
    WHEN 'j1' THEN 'J1'
    WHEN 'j2' THEN 'J2'
    WHEN 'j3' THEN 'J3'
    ELSE `name`
  END,
  `display_order` = CASE `code`
    WHEN 'wenhua' THEN 1
    WHEN 'central' THEN 2
    WHEN 'j1' THEN 3
    WHEN 'j2' THEN 4
    WHEN 'j3' THEN 5
    ELSE `display_order`
  END,
  `is_active` = 1
WHERE `code` IN ('wenhua', 'central', 'j1', 'j2', 'j3');

-- 3. 创建碗碟库存关联表（替代原来的固定列结构）
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
  CONSTRAINT `fk_dishware_stock_by_restaurant_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='碗碟库存按餐厅店面关联表';

-- 4. 创建套装库存关联表（替代原来的固定列结构）
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
  CONSTRAINT `fk_dishware_set_stock_by_restaurant_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='套装库存按餐厅店面关联表';

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
CROSS JOIN restaurants r
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
CROSS JOIN restaurants r
WHERE r.code IN ('wenhua', 'central', 'j1', 'j2', 'j3')
ON DUPLICATE KEY UPDATE quantity = VALUES(quantity);

-- 注意：原有的 dishware_stock 和 dishware_set_stock 表暂时保留
-- 等确认新系统运行正常后，可以删除旧表的固定列（wenhua_quantity, central_quantity等）
-- 或者保留作为备份
