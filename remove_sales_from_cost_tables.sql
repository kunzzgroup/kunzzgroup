-- 移除 cost 表中的 sales 字段
-- 执行前请备份数据库！

-- 步骤 1: 先删除依赖 sales 字段的计算列
-- 注意：根据实际表结构，可能需要调整列名

-- j1cost 表
ALTER TABLE j1cost DROP COLUMN IF EXISTS gross_total;
ALTER TABLE j1cost DROP COLUMN IF EXISTS cost_percent;
-- 注意：c_total 可能不依赖 sales，如果依赖也需要删除
-- ALTER TABLE j1cost DROP COLUMN IF EXISTS c_total;

-- j2cost 表
ALTER TABLE j2cost DROP COLUMN IF EXISTS gross_total;
ALTER TABLE j2cost DROP COLUMN IF EXISTS cost_percent;
-- ALTER TABLE j2cost DROP COLUMN IF EXISTS c_total;

-- j3cost 表
ALTER TABLE j3cost DROP COLUMN IF EXISTS gross_total;
ALTER TABLE j3cost DROP COLUMN IF EXISTS cost_percent;
-- ALTER TABLE j3cost DROP COLUMN IF EXISTS c_total;

-- 步骤 2: 删除 sales 字段
ALTER TABLE j1cost DROP COLUMN IF EXISTS sales;
ALTER TABLE j2cost DROP COLUMN IF EXISTS sales;
ALTER TABLE j3cost DROP COLUMN IF EXISTS sales;

-- 步骤 3（可选）: 如果需要，可以重新创建不依赖 sales 的计算列
-- 例如：只计算总成本（不依赖 sales）
-- ALTER TABLE j1cost ADD COLUMN c_total DECIMAL(10,2) GENERATED ALWAYS AS (c_beverage + c_kitchen) STORED;
-- ALTER TABLE j2cost ADD COLUMN c_total DECIMAL(10,2) GENERATED ALWAYS AS (c_beverage + c_kitchen) STORED;
-- ALTER TABLE j3cost ADD COLUMN c_total DECIMAL(10,2) GENERATED ALWAYS AS (c_beverage + c_kitchen) STORED;

