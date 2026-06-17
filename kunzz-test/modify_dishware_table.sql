-- 修改 dishware_info 表结构，让尺寸和单价字段变为可选
-- 执行此SQL来修改数据库表结构

-- 修改 unit_price 字段，允许为 NULL
ALTER TABLE `dishware_info` 
MODIFY `unit_price` decimal(10,2) DEFAULT NULL COMMENT '单价';

-- 修改 size 字段，确保允许为 NULL（通常已经是，但为了确保一致性）
ALTER TABLE `dishware_info` 
MODIFY `size` varchar(100) DEFAULT NULL COMMENT '尺寸规格';
