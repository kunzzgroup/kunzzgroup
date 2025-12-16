-- 为 stock_data 表添加规格列
-- 执行此SQL来添加规格字段

ALTER TABLE `stock_data` 
ADD COLUMN `specification` varchar(255) DEFAULT NULL COMMENT '规格' 
AFTER `product_name`;

-- 为规格列添加索引以提高查询性能
ALTER TABLE `stock_data` 
ADD KEY `idx_specification` (`specification`);

-- 更新现有记录的规格字段（可选，根据实际需求设置默认值）
-- UPDATE `stock_data` SET `specification` = '标准规格' WHERE `specification` IS NULL;
