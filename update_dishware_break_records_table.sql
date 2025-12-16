-- 更新碗碟破损记录表，添加日期字段
-- 添加破损日期字段

-- 添加单价字段（如果不存在）
ALTER TABLE dishware_break_records 
ADD COLUMN IF NOT EXISTS unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 
COMMENT '单价' 
AFTER break_quantity;

-- 添加总价字段（如果不存在）
ALTER TABLE dishware_break_records 
ADD COLUMN IF NOT EXISTS total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 
COMMENT '总价' 
AFTER unit_price;

-- 添加破损日期字段（如果不存在）
ALTER TABLE dishware_break_records 
ADD COLUMN IF NOT EXISTS break_date DATE NOT NULL DEFAULT (CURDATE())
COMMENT '破损日期' 
AFTER total_price;

-- 更新现有记录的总价（基于单价和破损数量计算）
UPDATE dishware_break_records dbr
JOIN dishware_info di ON dbr.dishware_id = di.id
SET dbr.unit_price = di.unit_price,
    dbr.total_price = di.unit_price * dbr.break_quantity
WHERE dbr.unit_price = 0 OR dbr.total_price = 0;

-- 更新现有记录的破损日期（如果没有设置，使用创建日期）
UPDATE dishware_break_records 
SET break_date = DATE(created_at) 
WHERE break_date = '0000-00-00' OR break_date IS NULL;

-- 添加日期索引
ALTER TABLE dishware_break_records 
ADD INDEX IF NOT EXISTS idx_break_date (break_date);

-- 添加备注说明
ALTER TABLE dishware_break_records COMMENT = '碗碟破损记录表';
