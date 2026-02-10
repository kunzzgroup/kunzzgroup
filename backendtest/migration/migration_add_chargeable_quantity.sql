-- 破损记录计费数量：仅「从最少再往下扣」的部分计费
-- 使用前请执行本迁移（如通过 phpMyAdmin 或 mysql 客户端）
ALTER TABLE dishware_break_records 
ADD COLUMN chargeable_quantity INT NULL DEFAULT NULL 
COMMENT '计费数量（扣到少于最少的部分）' 
AFTER break_quantity;
