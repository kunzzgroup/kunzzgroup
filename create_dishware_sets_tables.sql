-- 创建碗碟套装表
CREATE TABLE IF NOT EXISTS `dishware_sets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `set_name` varchar(255) NOT NULL COMMENT '套装名称',
  `set_code` varchar(50) NOT NULL COMMENT '套装编号',
  `set_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '套装总价',
  `description` text COMMENT '套装描述',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_set_code` (`set_code`),
  KEY `idx_set_name` (`set_name`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='碗碟套装表';

-- 创建碗碟套装关系表
CREATE TABLE IF NOT EXISTS `dishware_set_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `set_id` int(11) NOT NULL COMMENT '套装ID',
  `dishware_id` int(11) NOT NULL COMMENT '碗碟ID',
  `quantity_in_set` int(11) NOT NULL DEFAULT 1 COMMENT '套装中的数量',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序顺序',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_set_dishware` (`set_id`, `dishware_id`),
  KEY `idx_set_id` (`set_id`),
  KEY `idx_dishware_id` (`dishware_id`),
  KEY `idx_sort_order` (`sort_order`),
  CONSTRAINT `fk_dishware_set_items_set` FOREIGN KEY (`set_id`) REFERENCES `dishware_sets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dishware_set_items_dishware` FOREIGN KEY (`dishware_id`) REFERENCES `dishware_info` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='碗碟套装关系表';

-- 创建套装库存表
CREATE TABLE IF NOT EXISTS `dishware_set_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `set_id` int(11) NOT NULL COMMENT '套装ID',
  `wenhua_quantity` int(11) NOT NULL DEFAULT 0 COMMENT '文化店库存',
  `central_quantity` int(11) NOT NULL DEFAULT 0 COMMENT '中央店库存',
  `j1_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'J1店库存',
  `j2_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'J2店库存',
  `j3_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'J3店库存',
  `total_quantity` int(11) GENERATED ALWAYS AS (wenhua_quantity + central_quantity + j1_quantity + j2_quantity + j3_quantity) STORED COMMENT '总库存',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_set_stock` (`set_id`),
  KEY `idx_wenhua_quantity` (`wenhua_quantity`),
  KEY `idx_central_quantity` (`central_quantity`),
  KEY `idx_j1_quantity` (`j1_quantity`),
  KEY `idx_j2_quantity` (`j2_quantity`),
  KEY `idx_j3_quantity` (`j3_quantity`),
  CONSTRAINT `fk_dishware_set_stock_set` FOREIGN KEY (`set_id`) REFERENCES `dishware_sets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='套装库存表';

-- 创建套装破损记录表
CREATE TABLE IF NOT EXISTS `dishware_set_break_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `set_id` int(11) NOT NULL COMMENT '套装ID',
  `shop_type` enum('wenhua','central','j1','j2','j3') NOT NULL COMMENT '店铺类型',
  `break_quantity` int(11) NOT NULL DEFAULT 1 COMMENT '破损数量',
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '单价',
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '总价',
  `break_date` date NOT NULL COMMENT '破损日期',
  `recorded_by` varchar(100) DEFAULT 'system' COMMENT '记录人',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_set_id` (`set_id`),
  KEY `idx_shop_type` (`shop_type`),
  KEY `idx_break_date` (`break_date`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_dishware_set_break_records_set` FOREIGN KEY (`set_id`) REFERENCES `dishware_sets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='套装破损记录表';
