-- 创建碗碟库存管理系统数据库表
-- 执行前请备份数据库

-- 1. 碗碟信息表 (存储照片、尺寸、价格、分类等基本信息)
CREATE TABLE IF NOT EXISTS `dishware_info` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_name` varchar(255) NOT NULL COMMENT '碗碟名称',
    `code_number` varchar(100) DEFAULT NULL COMMENT '产品编号',
    `category` varchar(10) NOT NULL COMMENT '分类 (AG,CU,DN,DR,IP,MA,ME,MU,OM,OT,SA,SU,SAR,SER,SET,TA,TE,WAN,YA)',
    `size` varchar(100) DEFAULT NULL COMMENT '尺寸规格',
    `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '单价',
    `photo_path` varchar(500) DEFAULT NULL COMMENT '照片路径',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `idx_category` (`category`),
    KEY `idx_code_number` (`code_number`),
    KEY `idx_product_name` (`product_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='碗碟信息表';

-- 2. 碗碟库存记录表 (记录各地点数量)
CREATE TABLE IF NOT EXISTS `dishware_stock` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `dishware_id` int(11) NOT NULL COMMENT '碗碟ID',
    `wenhua_quantity` int(11) NOT NULL DEFAULT 0 COMMENT '文化楼数量',
    `central_quantity` int(11) NOT NULL DEFAULT 0 COMMENT '中央数量',
    `j1_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'J1数量',
    `j2_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'J2数量',
    `j3_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'J3数量',
    `total_quantity` int(11) NOT NULL DEFAULT 0 COMMENT '总数量',
    `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_dishware_id` (`dishware_id`),
    KEY `idx_total_quantity` (`total_quantity`),
    CONSTRAINT `fk_dishware_stock_dishware_id` FOREIGN KEY (`dishware_id`) REFERENCES `dishware_info` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='碗碟库存记录表';

-- 3. 创建触发器自动计算总数量
DELIMITER $$

CREATE TRIGGER `update_dishware_total_quantity` 
BEFORE UPDATE ON `dishware_stock`
FOR EACH ROW
BEGIN
    SET NEW.total_quantity = NEW.wenhua_quantity + NEW.central_quantity + NEW.j1_quantity + NEW.j2_quantity + NEW.j3_quantity;
END$$

CREATE TRIGGER `insert_dishware_total_quantity` 
BEFORE INSERT ON `dishware_stock`
FOR EACH ROW
BEGIN
    SET NEW.total_quantity = NEW.wenhua_quantity + NEW.central_quantity + NEW.j1_quantity + NEW.j2_quantity + NEW.j3_quantity;
END$$

DELIMITER ;

-- 4. 插入一些示例数据 (可选)
INSERT INTO `dishware_info` (`product_name`, `code_number`, `category`, `size`, `unit_price`) VALUES
('白瓷碗', 'WA001', 'WAN', '直径15cm', 8.50),
('陶瓷盘', 'PL001', 'DR', '直径20cm', 12.00),
('不锈钢勺', 'SP001', 'AG', '长度18cm', 3.50),
('玻璃杯', 'CU001', 'CU', '容量300ml', 6.00);

-- 为示例数据创建对应的库存记录
INSERT INTO `dishware_stock` (`dishware_id`, `wenhua_quantity`, `central_quantity`, `j1_quantity`, `j2_quantity`, `j3_quantity`) VALUES
(1, 50, 30, 20, 25, 15),
(2, 40, 25, 15, 20, 10),
(3, 100, 60, 40, 50, 30),
(4, 80, 45, 30, 35, 25);
