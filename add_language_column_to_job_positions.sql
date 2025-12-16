-- 为 job_positions 表添加 language 字段
ALTER TABLE `job_positions` ADD COLUMN `language` varchar(10) NOT NULL DEFAULT 'zh' COMMENT '语言版本 (zh/en)' AFTER `company_location`;

-- 为现有数据设置默认语言
UPDATE `job_positions` SET `language` = 'zh' WHERE `language` IS NULL OR `language` = '';

-- 添加索引以提高查询性能
ALTER TABLE `job_positions` ADD INDEX `idx_language` (`language`);
