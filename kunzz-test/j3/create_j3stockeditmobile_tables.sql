-- 创建简化版的 j3stockedit_data 表（用于 j3stockeditmobile.php）
-- 不包含 receiver, specification, price, remark, target_system, type 字段

CREATE TABLE IF NOT EXISTS `j3stockeditmobile_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `code_number` varchar(100) DEFAULT NULL,
  `in_quantity` decimal(10,3) DEFAULT 0.000,
  `out_quantity` decimal(10,3) DEFAULT 0.000,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_date` (`date`),
  KEY `idx_product_name` (`product_name`),
  KEY `idx_code_number` (`code_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建 stocklist_total 表用于跟踪库存总数
CREATE TABLE IF NOT EXISTS `j3stocklist_total` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) NOT NULL,
  `code_number` varchar(100) DEFAULT NULL,
  `total_qty` decimal(10,3) DEFAULT 0.000,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_product` (`product_name`, `code_number`),
  KEY `idx_product_name` (`product_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

