-- 更新数据库表以支持三位小数精度
-- 执行前请备份数据库

-- 更新 j1stockedit_data 表的 in_quantity 和 out_quantity 字段
ALTER TABLE `j1stockedit_data` 
MODIFY COLUMN `in_quantity` decimal(10,3) DEFAULT 0.000 COMMENT '入库数量',
MODIFY COLUMN `out_quantity` decimal(10,3) DEFAULT 0.000 COMMENT '出库数量';

-- 更新 j2stockedit_data 表的 in_quantity 和 out_quantity 字段  
ALTER TABLE `j2stockedit_data` 
MODIFY COLUMN `in_quantity` decimal(10,3) DEFAULT 0.000 COMMENT '入库数量',
MODIFY COLUMN `out_quantity` decimal(10,3) DEFAULT 0.000 COMMENT '出库数量';

-- 如果 stock_data 表也需要支持进出库数量，请取消下面的注释
-- ALTER TABLE `stock_data` 
-- ADD COLUMN `in_quantity` decimal(10,3) DEFAULT 0.000 COMMENT '入库数量',
-- ADD COLUMN `out_quantity` decimal(10,3) DEFAULT 0.000 COMMENT '出库数量';
