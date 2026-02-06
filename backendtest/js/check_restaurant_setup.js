document.addEventListener('DOMContentLoaded', function () {
    checkTableExists();
    checkTableStructure();
    getEmployees();
    getStats();
});

const API_URL = '../api/check_restaurant_setup_data.php';

function checkTableExists() {
    fetch(`${API_URL}?action=check_table_exists`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('table-exists-container');
            if (!data.success) {
                container.innerHTML = `<div class="alert-error"><p>${data.message}</p></div>`;
                return;
            }

            if (!data.exists) {
                container.innerHTML = `
                    <div class="alert-error">
                        <h3 style="color: #c00;">❌ schedule_employees 表不存在！</h3>
                        <p>请先创建员工排班表。执行以下SQL：</p>
                        <pre class="code-block">
CREATE TABLE schedule_employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    position VARCHAR(100) NOT NULL,
    work_area VARCHAR(50) NOT NULL,
    restaurant VARCHAR(10) DEFAULT 'J1',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
                        </pre>
                    </div>`;
            } else {
                container.innerHTML = `<p class="text-green">✅ schedule_employees 表存在</p>`;
            }
        })
        .catch(err => console.error(err));
}

function checkTableStructure() {
    fetch(`${API_URL}?action=check_table_structure`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('table-structure-container');
            if (!data.success) {
                container.innerHTML = `<div class="alert-error"><p>${data.message}</p></div>`;
                return;
            }

            let html = `
                <table>
                    <tr><th>字段</th><th>类型</th><th>默认值</th></tr>
            `;

            data.columns.forEach(col => {
                html += `
                    <tr>
                        <td>${col.Field}</td>
                        <td>${col.Type}</td>
                        <td>${col.Default || 'NULL'}</td>
                    </tr>
                `;
            });
            html += `</table>`;

            if (data.hasRestaurantColumn) {
                html += `<p class="text-green">✅ restaurant 字段已存在</p>`;
            } else {
                html += `
                    <div class="alert-warning">
                        <p style="color: #c60; font-weight: bold;">❌ restaurant 字段不存在！</p>
                        <p>请执行以下SQL添加字段：</p>
                        <pre class="code-block">
ALTER TABLE schedule_employees 
ADD COLUMN restaurant VARCHAR(10) DEFAULT 'J1' AFTER work_area;

CREATE INDEX idx_restaurant ON schedule_employees(restaurant);
                        </pre>
                    </div>
                `;
            }
            container.innerHTML = html;
        })
        .catch(err => console.error(err));
}

function getEmployees() {
    fetch(`${API_URL}?action=get_employees`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('employee-list-container');
            if (!data.success) {
                container.innerHTML = `<div class="alert-error"><p>${data.message}</p></div>`;
                return;
            }

            if (data.employees.length > 0) {
                let html = `<table><tr><th>ID</th><th>餐厅</th><th>部门</th><th>姓名</th><th>电话</th><th>职位</th><th>状态</th></tr>`;
                data.employees.forEach(emp => {
                    const status = emp.is_active == 1 ? '✅' : '❌';
                    const restaurant = emp.restaurant || '未设置';
                    html += `
                        <tr>
                            <td>${emp.id}</td>
                            <td><strong>${restaurant}</strong></td>
                            <td>${emp.work_area}</td>
                            <td>${emp.name}</td>
                            <td>${emp.phone}</td>
                            <td>${emp.position}</td>
                            <td>${status}</td>
                        </tr>
                    `;
                });
                html += `</table>`;
                container.innerHTML = html;
            } else {
                container.innerHTML = `<p style="color: #999;">暂无员工数据。请在排班管理页面添加员工。</p>`;
            }
        })
        .catch(err => console.error(err));
}

function getStats() {
    fetch(`${API_URL}?action=get_stats`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('stats-container');
            if (!data.success) {
                container.innerHTML = `<div class="alert-error"><p>${data.message}</p></div>`;
                return;
            }

            if (data.stats.length > 0) {
                let html = `<table><tr><th>餐厅</th><th>部门</th><th>员工数</th></tr>`;
                data.stats.forEach(stat => {
                    html += `
                        <tr>
                            <td><strong>${stat.restaurant}</strong></td>
                            <td>${stat.work_area}</td>
                            <td>${stat.count}</td>
                        </tr>
                    `;
                });
                html += `</table>`;
                container.innerHTML = html;
            } else {
                container.innerHTML = `<p style="color: #999;">暂无统计数据</p>`;
            }
        })
        .catch(err => console.error(err));
}
