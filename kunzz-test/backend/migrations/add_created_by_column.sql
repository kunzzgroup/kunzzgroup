-- 添加 created_by 列到所有进出货数据表
-- 用于记录每条记录是由哪个用户创建的

-- 中央
ALTER TABLE stockinout_data ADD COLUMN created_by VARCHAR(100) DEFAULT NULL;

-- J1
ALTER TABLE j1stockinout_data ADD COLUMN created_by VARCHAR(100) DEFAULT NULL;

-- J2
ALTER TABLE j2stockinout_data ADD COLUMN created_by VARCHAR(100) DEFAULT NULL;

-- J1 编辑表
ALTER TABLE j1stockedit_data ADD COLUMN created_by VARCHAR(100) DEFAULT NULL;

-- J2 编辑表
ALTER TABLE j2stockedit_data ADD COLUMN created_by VARCHAR(100) DEFAULT NULL;

-- J3 编辑表
ALTER TABLE j3stockedit_data ADD COLUMN created_by VARCHAR(100) DEFAULT NULL;
