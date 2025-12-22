-- 修复 KPI 表触发器，移除对 cost 表 sales 字段的引用
-- 执行前请备份数据库！

-- 步骤 1: 检查并删除可能存在的触发器
-- 注意：根据实际触发器名称调整

-- 删除 j1data 表的触发器（如果存在）
DROP TRIGGER IF EXISTS j1data_after_insert_cost;
DROP TRIGGER IF EXISTS j1data_after_update_cost;
DROP TRIGGER IF EXISTS sync_j1data_to_j1cost;

-- 删除 j2data 表的触发器（如果存在）
DROP TRIGGER IF EXISTS j2data_after_insert_cost;
DROP TRIGGER IF EXISTS j2data_after_update_cost;
DROP TRIGGER IF EXISTS sync_j2data_to_j2cost;

-- 删除 j3data 表的触发器（如果存在）
DROP TRIGGER IF EXISTS j3data_after_insert_cost;
DROP TRIGGER IF EXISTS j3data_after_update_cost;
DROP TRIGGER IF EXISTS sync_j3data_to_j3cost;

-- 步骤 2: 如果需要自动同步，重新创建不包含 sales 字段的触发器
-- 注意：这些触发器只创建 cost 记录，不包含 sales 字段（销售额从 KPI 表实时获取）

-- j1data 触发器示例（如果需要）
/*
DELIMITER $$
CREATE TRIGGER j1data_after_insert_cost
AFTER INSERT ON j1data
FOR EACH ROW
BEGIN
    INSERT INTO j1cost (date, day_name, c_beverage, c_kitchen)
    VALUES (NEW.date, DAYNAME(NEW.date), 0, 0)
    ON DUPLICATE KEY UPDATE day_name = DAYNAME(NEW.date);
END$$
DELIMITER ;
*/

-- 注意：如果不需要自动创建 cost 记录，可以跳过步骤 2
-- cost 记录应该由用户在 costedit.php 中手动创建

