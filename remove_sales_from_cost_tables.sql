-- 移除 cost 表中的 sales 字段
-- 执行前请备份数据库！

-- 根据诊断结果，表中有以下计算列：
-- c_total (不依赖 sales，保留)
-- gross_total (通常依赖 sales，需要删除)
-- cost_percent (通常依赖 sales，需要删除)

-- 步骤 1: 先删除依赖 sales 字段的计算列
-- 注意：MySQL 某些版本不支持 IF EXISTS，如果报错请手动检查列是否存在

-- j1cost 表
ALTER TABLE j1cost DROP COLUMN gross_total;
ALTER TABLE j1cost DROP COLUMN cost_percent;

-- j2cost 表
ALTER TABLE j2cost DROP COLUMN gross_total;
ALTER TABLE j2cost DROP COLUMN cost_percent;

-- j3cost 表
ALTER TABLE j3cost DROP COLUMN gross_total;
ALTER TABLE j3cost DROP COLUMN cost_percent;

-- 步骤 2: 删除 sales 字段
ALTER TABLE j1cost DROP COLUMN sales;
ALTER TABLE j2cost DROP COLUMN sales;
ALTER TABLE j3cost DROP COLUMN sales;

-- 步骤 3（可选）: 如果需要，可以重新创建不依赖 sales 的计算列
-- 注意：c_total 已经存在且不依赖 sales，所以不需要重新创建
-- 如果需要 gross_total 和 cost_percent，需要在应用层计算（从 KPI 表获取销售额）

