-- 创建碗碟破损记录表
CREATE TABLE IF NOT EXISTS dishware_break_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dishware_id INT NOT NULL,
    shop_type ENUM('j1', 'j2', 'j3') NOT NULL,
    break_quantity INT NOT NULL DEFAULT 0,
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    break_date DATE NOT NULL,
    recorded_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dishware_id) REFERENCES dishware_info(id) ON DELETE CASCADE,
    INDEX idx_dishware_id (dishware_id),
    INDEX idx_shop_type (shop_type),
    INDEX idx_break_date (break_date),
    INDEX idx_created_at (created_at)
);

-- 添加备注说明
ALTER TABLE dishware_break_records COMMENT = '碗碟破损记录表';
