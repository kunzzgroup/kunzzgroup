-- 为套装表添加尺寸字段
ALTER TABLE dishware_sets ADD COLUMN set_size VARCHAR(255) AFTER set_code;
