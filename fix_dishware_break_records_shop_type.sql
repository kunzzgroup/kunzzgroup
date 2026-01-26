-- 修复 dishware_break_records 表的 shop_type 字段
-- 将 ENUM 类型改为 VARCHAR，以支持任意餐厅名称

ALTER TABLE `dishware_break_records` 
MODIFY COLUMN `shop_type` VARCHAR(50) NOT NULL COMMENT '店铺类型';

-- 验证修改
-- SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = DATABASE() 
-- AND TABLE_NAME = 'dishware_break_records' 
-- AND COLUMN_NAME = 'shop_type';
