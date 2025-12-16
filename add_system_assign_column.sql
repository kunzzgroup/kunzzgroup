-- 添加 system_assign 字段到 stock_data 表
-- 用于存储产品分配到哪个系统（中央、J1、J2、J3）

ALTER TABLE `stock_data` 
ADD COLUMN `system_assign` VARCHAR(50) DEFAULT NULL COMMENT '系统分配' AFTER `approver`;

-- 添加索引以提高查询性能
ALTER TABLE `stock_data` 
ADD INDEX `idx_system_assign` (`system_assign`);

-- 可选：为现有数据设置默认值（如果有需要）
-- UPDATE `stock_data` SET `system_assign` = 'Central' WHERE `system_assign` IS NULL;

