-- 创建考核表单表结构
-- 用于存储考核表单的模板和实例数据

-- 考核表单表：存储表单的基本信息
CREATE TABLE IF NOT EXISTS evaluation_forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_name VARCHAR(100) NOT NULL COMMENT '表单名称',
    department VARCHAR(50) NOT NULL COMMENT '部门 (service_line/sushi_bar/kitchen)',
    restaurant VARCHAR(10) DEFAULT 'J1' COMMENT '餐厅 (J1/J2/J3)',
    evaluator_name VARCHAR(100) NOT NULL COMMENT '评估人姓名',
    evaluation_date DATE NOT NULL COMMENT '评估日期',
    created_by INT COMMENT '创建人ID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    INDEX idx_department (department),
    INDEX idx_restaurant (restaurant),
    INDEX idx_evaluation_date (evaluation_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考核表单主表';

-- 考核表单详情表：存储每个员工的考核结果
CREATE TABLE IF NOT EXISTS evaluation_form_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_id INT NOT NULL COMMENT '表单ID',
    employee_id INT COMMENT '员工ID (关联schedule_employees)',
    employee_name VARCHAR(100) NOT NULL COMMENT '员工姓名',
    criteria_1 VARCHAR(255) COMMENT '考核指标1的评分',
    criteria_2 VARCHAR(255) COMMENT '考核指标2的评分',
    criteria_3 VARCHAR(255) COMMENT '考核指标3的评分',
    criteria_4 VARCHAR(255) COMMENT '考核指标4的评分',
    criteria_5 VARCHAR(255) COMMENT '考核指标5的评分',
    criteria_6 VARCHAR(255) COMMENT '考核指标6的评分（可选）',
    criteria_7 VARCHAR(255) COMMENT '考核指标7的评分（可选）',
    notes TEXT COMMENT '备注',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    FOREIGN KEY (form_id) REFERENCES evaluation_forms(id) ON DELETE CASCADE,
    INDEX idx_form_id (form_id),
    INDEX idx_employee_id (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考核表单详情表';

-- 考核指标配置表：存储不同部门的考核指标配置
CREATE TABLE IF NOT EXISTS evaluation_criteria_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department VARCHAR(50) NOT NULL COMMENT '部门',
    criteria_order INT NOT NULL COMMENT '指标顺序',
    criteria_name_zh VARCHAR(100) NOT NULL COMMENT '指标名称（中文）',
    criteria_name_en VARCHAR(100) NOT NULL COMMENT '指标名称（英文）',
    is_active TINYINT(1) DEFAULT 1 COMMENT '是否启用',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    UNIQUE KEY unique_dept_criteria (department, criteria_order),
    INDEX idx_department (department)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考核指标配置表';

-- 考核标准表：存储每个部门每个指标的 1-5 分说明（用于“考核标准”页面与导出PDF）
CREATE TABLE IF NOT EXISTS evaluation_criteria_standards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department VARCHAR(50) NOT NULL COMMENT '部门 (service_line/sushi_bar/kitchen)',
    criteria_order INT NOT NULL COMMENT '指标顺序(1-5)',
    score TINYINT NOT NULL COMMENT '分数(1-5)',
    description_text LONGTEXT NOT NULL COMMENT '考核标准说明文本',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    UNIQUE KEY uniq_dept_criteria_score (department, criteria_order, score),
    INDEX idx_department (department),
    INDEX idx_criteria (criteria_order),
    INDEX idx_score (score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考核标准说明表';

-- 插入默认的考核指标配置
-- Service Line (服务部门)
INSERT INTO evaluation_criteria_config (department, criteria_order, criteria_name_zh, criteria_name_en) VALUES
('service_line', 1, '工作态度', 'Work Attitude'),
('service_line', 2, '服务表现', 'Service Performance'),
('service_line', 3, '团队协作', 'Teamwork'),
('service_line', 4, '效率与准确度', 'Efficiency and Accuracy'),
('service_line', 5, '整洁与形象', 'Cleanliness and Image')
ON DUPLICATE KEY UPDATE criteria_name_zh = VALUES(criteria_name_zh), criteria_name_en = VALUES(criteria_name_en);

-- Sushi Bar (寿司吧)
INSERT INTO evaluation_criteria_config (department, criteria_order, criteria_name_zh, criteria_name_en) VALUES
('sushi_bar', 1, '手速与准确度', 'Speed and Accuracy'),
('sushi_bar', 2, '卫生与整洁', 'Hygiene and Tidiness'),
('sushi_bar', 3, '服务与态度', 'Service and Attitude'),
('sushi_bar', 4, '团队配合', 'Teamwork'),
('sushi_bar', 5, '职业形象', 'Professional Image')
ON DUPLICATE KEY UPDATE criteria_name_zh = VALUES(criteria_name_zh), criteria_name_en = VALUES(criteria_name_en);

-- Kitchen (厨房)
INSERT INTO evaluation_criteria_config (department, criteria_order, criteria_name_zh, criteria_name_en) VALUES
('kitchen', 1, '出餐速度与效率', 'Serving Speed and Efficiency'),
('kitchen', 2, '食材标准与品质', 'Ingredient Standards and Quality'),
('kitchen', 3, '卫生与整洁', 'Hygiene and Cleanliness'),
('kitchen', 4, '工作态度', 'Work Attitude'),
('kitchen', 5, '团队合作', 'Teamwork')
ON DUPLICATE KEY UPDATE criteria_name_zh = VALUES(criteria_name_zh), criteria_name_en = VALUES(criteria_name_en);

-- 考核标准表：存储每个部门每个指标的 1-5 分说明（用于"考核标准"页面与导出PDF）
CREATE TABLE IF NOT EXISTS evaluation_criteria_standards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department VARCHAR(50) NOT NULL COMMENT '部门 (service_line/sushi_bar/kitchen)',
    criteria_order INT NOT NULL COMMENT '指标顺序(1-5)',
    score TINYINT NOT NULL COMMENT '分数(1-5)',
    description_text LONGTEXT NOT NULL COMMENT '考核标准说明文本',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    UNIQUE KEY uniq_dept_criteria_score (department, criteria_order, score),
    INDEX idx_department (department),
    INDEX idx_criteria (criteria_order),
    INDEX idx_score (score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考核标准说明表';
