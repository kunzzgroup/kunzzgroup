-- Add category column to stock_data table
-- Date: 2025-10-22

ALTER TABLE `stock_data` 
ADD COLUMN `category` varchar(50) DEFAULT NULL COMMENT '货物类型' AFTER `specification`;

-- Add index for better query performance
ALTER TABLE `stock_data`
ADD KEY `idx_category` (`category`);

-- Update comment
ALTER TABLE `stock_data` 
COMMENT = '精简版库存管理表（包含货物类型）';

