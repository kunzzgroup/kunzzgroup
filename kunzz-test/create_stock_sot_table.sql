-- 创建货品异常记录表
CREATE TABLE IF NOT EXISTS stock_sot (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL COMMENT '日期',
    product_code VARCHAR(100) DEFAULT NULL COMMENT '货品编号',
    product_name VARCHAR(255) NOT NULL COMMENT '货品名称',
    quantity DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '异常数量（只能是正数）',
    specification VARCHAR(100) DEFAULT NULL COMMENT '规格',
    price DECIMAL(10, 2) DEFAULT 0.00 COMMENT '单价',
    total_price DECIMAL(10, 2) DEFAULT 0.00 COMMENT '总价',
    category VARCHAR(100) DEFAULT NULL COMMENT '货品类型',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    INDEX idx_date (date),
    INDEX idx_product_code (product_code),
    INDEX idx_product_name (product_name),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='货品异常扣除记录表';

