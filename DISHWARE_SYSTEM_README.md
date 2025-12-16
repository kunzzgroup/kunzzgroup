# 碗碟库存管理系统

## 系统概述

这是一个专为餐厅设计的碗碟库存管理系统，支持多地点库存管理、照片上传、分类管理等功能。

## 功能特性

### 1. 碗碟信息管理
- ✅ 上传碗碟照片（支持拖拽上传）
- ✅ 设置碗碟名称、编号、尺寸、价格
- ✅ 19种分类选择（AG、CU、DN、DR、IP、MA、ME、MU、OM、OT、SA、SU、SAR、SER、SET、TA、TE、WAN、YA）
- ✅ 支持单个上传和批量CSV导入

### 2. 库存管理
- ✅ 多地点库存跟踪（文化楼、中央、J1、J2、J3）
- ✅ 实时计算总数量和总价值
- ✅ 在线编辑库存数量
- ✅ 智能搜索和筛选功能

### 3. 数据管理
- ✅ CSV格式数据导出
- ✅ 批量数据导入
- ✅ 实时数据同步

## 文件结构

```
├── dishware_index.php          # 系统首页导航
├── dishware_upload.php         # 碗碟信息上传页面
├── dishware_stock.php          # 库存管理页面
├── dishware_api.php            # API接口
├── create_dishware_tables.sql  # 数据库表结构
└── DISHWARE_SYSTEM_README.md   # 系统说明文档
```

## 安装步骤

### 1. 数据库设置
首先执行SQL文件创建数据库表：

```sql
-- 执行 create_dishware_tables.sql 文件
-- 这将创建以下表：
-- - dishware_info: 碗碟信息表
-- - dishware_stock: 库存记录表
```

### 2. 文件上传
将所有PHP文件上传到您的Web服务器目录。

### 3. 权限设置
确保以下目录有写入权限：
- `uploads/dishware/` - 用于存储上传的照片

### 4. 数据库配置
在 `dishware_api.php` 中确认数据库连接配置：

```php
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';
```

## 使用指南

### 1. 访问系统
打开浏览器访问：`http://your-domain/dishware_index.php`

### 2. 上传碗碟信息
1. 点击"碗碟信息上传"
2. 填写碗碟基本信息（名称、编号、分类、尺寸、价格）
3. 上传照片（可选）
4. 点击"保存碗碟信息"

### 3. 批量上传
1. 下载CSV模板
2. 按模板格式填写数据
3. 上传CSV文件
4. 点击"处理批量上传"

### 4. 管理库存
1. 点击"库存管理"
2. 查看所有碗碟信息
3. 在表格中直接编辑各地点数量
4. 点击"保存所有更改"

### 5. 搜索和筛选
- 使用搜索框按产品名称、编号搜索
- 使用分类下拉菜单筛选
- 点击"重置"清除所有筛选条件

## 分类说明

| 代码 | 名称 | 说明 |
|------|------|------|
| AG   | 餐具 | 餐具类 |
| CU   | 杯子 | 杯子类 |
| DN   | 碟子 | 碟子类 |
| DR   | 盘子 | 盘子类 |
| IP   | 盘子 | 盘子类 |
| MA   | 餐具 | 餐具类 |
| ME   | 餐具 | 餐具类 |
| MU   | 餐具 | 餐具类 |
| OM   | 其他 | 其他类 |
| OT   | 其他 | 其他类 |
| SA   | 餐具 | 餐具类 |
| SU   | 餐具 | 餐具类 |
| SAR  | 餐具 | 餐具类 |
| SER  | 餐具 | 餐具类 |
| SET  | 套装 | 套装类 |
| TA   | 餐具 | 餐具类 |
| TE   | 餐具 | 餐具类 |
| WAN  | 碗   | 碗类 |
| YA   | 餐具 | 餐具类 |

## API接口

### 获取碗碟列表
```
GET dishware_api.php?action=list
```

### 获取库存列表
```
GET dishware_api.php?action=stock
```

### 添加碗碟
```
POST dishware_api.php
Content-Type: application/json
{
    "action": "add",
    "product_name": "白瓷碗",
    "code_number": "WA001",
    "category": "WAN",
    "size": "直径15cm",
    "unit_price": 8.50,
    "description": "标准白瓷碗"
}
```

### 更新库存
```
PUT dishware_api.php
Content-Type: application/json
{
    "action": "update_stock",
    "dishware_id": 1,
    "wenhua_quantity": 50,
    "central_quantity": 30,
    "j1_quantity": 20,
    "j2_quantity": 25,
    "j3_quantity": 15
}
```

## 技术特性

- **响应式设计**: 完美适配桌面端和移动端
- **实时更新**: 库存数量变化实时反映
- **数据验证**: 前端和后端双重数据验证
- **错误处理**: 完善的错误提示和处理机制
- **性能优化**: 数据库索引优化，查询性能提升

## 浏览器支持

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## 注意事项

1. 确保服务器支持PHP 7.4+
2. 需要MySQL 5.7+或MariaDB 10.2+
3. 建议定期备份数据库
4. 上传的照片文件大小限制为5MB
5. 支持的图片格式：JPG、PNG、GIF

## 故障排除

### 常见问题

1. **照片上传失败**
   - 检查uploads/dishware/目录权限
   - 确认文件大小不超过5MB
   - 检查文件格式是否支持

2. **数据库连接失败**
   - 检查数据库配置信息
   - 确认数据库服务是否运行
   - 检查用户权限

3. **页面显示异常**
   - 检查PHP错误日志
   - 确认所有文件已正确上传
   - 检查浏览器控制台错误信息

## 更新日志

### v1.0.0 (2024-01-XX)
- 初始版本发布
- 支持碗碟信息上传和管理
- 支持多地点库存管理
- 支持CSV批量导入导出
- 响应式界面设计

## 联系支持

如有问题或建议，请联系系统管理员。
