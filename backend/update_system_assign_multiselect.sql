-- 更新 system_assign 字段以支持多选（存储逗号分隔的值）
-- 将字段长度增加以容纳多个系统选择
ALTER TABLE `stock_data`
MODIFY COLUMN `system_assign` VARCHAR(255) DEFAULT NULL COMMENT '系统分配（多选，逗号分隔）';

