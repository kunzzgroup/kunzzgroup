# 类型字段更新指南

## 📋 项目背景

将货品类型（Type）字段集成到整个库存系统中，包括历史数据和未来新增数据。

---

## ✅ 已完成的修改

### 1. **后端API修改**（已完成）

#### `backend/j1stockeditpageapi.php`
- ✅ `code_by_product` API：返回 `category` 字段
- ✅ `product_by_code` API：返回 `category` 字段

#### `backend/j2stockeditpageapi.php`
- ✅ `code_by_product` API：返回 `category` 字段
- ✅ `product_by_code` API：返回 `category` 字段

#### `backend/j3stockeditpageapi.php`
- ✅ `code_by_product` API：返回 `category` 字段
- ✅ `product_by_code` API：返回 `category` 字段

#### `backend/stockeditapi.php`
- ✅ `saveToJ1Table()`：保存到 `j1stockinout_data` 时自动获取并保存类型
- ✅ `saveToJ2Table()`：保存到 `j2stockinout_data` 时自动获取并保存类型
- ✅ `saveToJ3Table()`：保存到 `j3stockinout_data` 时自动获取并保存类型
- ✅ `saveToJ1EditTable()`：保存到 `j1stockedit_data` 时自动获取并保存类型
- ✅ `saveToJ2EditTable()`：保存到 `j2stockedit_data` 时自动获取并保存类型
- ✅ `saveToJ3EditTable()`：保存到 `j3stockedit_data` 时自动获取并保存类型

#### `backend/j1stocklistapi.php`、`backend/j2stocklistapi.php`、`backend/j3stocklistapi.php`
- ✅ 按类型统计总库存（Kitchen、Sushi Bar、Drinks、Sake）

### 2. **前端JavaScript修改**（已完成）

#### `backend/stockeditall.php`
- ✅ `getCodeByProduct()`：获取并返回 `category`
- ✅ `getProductByCode()`：获取并返回 `category`
- ✅ `handleProductChange()`：自动填充类型字段
- ✅ `handleCodeNumberChange()`：自动填充类型字段
- ✅ `selectComboboxOption()`：自动填充类型字段
- ✅ `clearOutboundFields()`：清空类型字段
- ✅ 新增行和批量添加表单：类型下拉框选项已修正（Kitchen、Sushi Bar、Drinks、Sake）

### 3. **数据库**
- ✅ `stock_data` 表：所有货品已通过 `stockproductname.php` 更新了 `category` 字段

---

## 🔧 现在需要执行的步骤

### **第一步：备份数据库** ⚠️ **非常重要！**

```sql
-- 在执行任何更新前，请先备份整个数据库
-- 使用 phpMyAdmin 或命令行：
mysqldump -u u857194726_kunzzgroup -p u857194726_kunzzgroup > backup_$(date +%Y%m%d_%H%M%S).sql
```

或者在 **phpMyAdmin** 中：
1. 选择数据库 `u857194726_kunzzgroup`
2. 点击"导出"
3. 选择"快速"导出方法
4. 点击"执行"保存备份文件

---

### **第二步：执行SQL更新脚本**

#### 方法A：使用 phpMyAdmin（推荐）

1. 登录到你的 **phpMyAdmin**
2. 选择数据库 `u857194726_kunzzgroup`
3. 点击顶部的 **"SQL"** 标签
4. 打开文件 `update_historical_type_data.sql`
5. 复制整个SQL脚本内容
6. 粘贴到 phpMyAdmin 的 SQL 查询框中
7. 点击 **"执行"** 按钮
8. 等待执行完成（可能需要几秒到几分钟，取决于数据量）

#### 方法B：使用命令行

```bash
mysql -u u857194726_kunzzgroup -p u857194726_kunzzgroup < update_historical_type_data.sql
```

---

### **第三步：验证数据更新**

执行以下SQL查询来检查更新是否成功：

```sql
-- 检查 J1 Edit 表的类型分布
SELECT type, COUNT(*) as count 
FROM j1stockedit_data 
GROUP BY type 
ORDER BY count DESC;

-- 检查 J2 Edit 表的类型分布
SELECT type, COUNT(*) as count 
FROM j2stockedit_data 
GROUP BY type 
ORDER BY count DESC;

-- 检查 J3 Edit 表的类型分布
SELECT type, COUNT(*) as count 
FROM j3stockedit_data 
GROUP BY type 
ORDER BY count DESC;

-- 检查 J1 InOut 表的类型分布
SELECT type, COUNT(*) as count 
FROM j1stockinout_data 
GROUP BY type 
ORDER BY count DESC;

-- 检查 J2 InOut 表的类型分布
SELECT type, COUNT(*) as count 
FROM j2stockinout_data 
GROUP BY type 
ORDER BY count DESC;

-- 检查 J3 InOut 表的类型分布
SELECT type, COUNT(*) as count 
FROM j3stockinout_data 
GROUP BY type 
ORDER BY count DESC;

-- 检查是否有记录没有更新类型（应该很少或为0）
SELECT COUNT(*) as records_without_type
FROM j1stockedit_data 
WHERE (type IS NULL OR type = '') 
AND product_name IS NOT NULL 
AND product_name != '';

SELECT COUNT(*) as records_without_type
FROM j2stockedit_data 
WHERE (type IS NULL OR type = '') 
AND product_name IS NOT NULL 
AND product_name != '';

SELECT COUNT(*) as records_without_type
FROM j3stockedit_data 
WHERE (type IS NULL OR type = '') 
AND product_name IS NOT NULL 
AND product_name != '';
```

**预期结果：**
- 应该看到 Kitchen、Sushi Bar、Drinks、Sake 四种类型
- 没有类型的记录数应该为 0 或非常少（只有那些在 stock_data 中不存在的货品）

---

### **第四步：清除浏览器缓存并测试前端**

1. **清除浏览器缓存**：
   - Chrome/Edge：按 `Ctrl + Shift + Delete`
   - 或使用无痕模式测试

2. **测试 J1 页面**：
   - 访问 J1 的进出货页面（`stockeditall.php?system=j1`）
   - 检查历史记录是否显示类型
   - 访问 J1 的总库存页面（`stocklistall.php?system=j1`）
   - 检查 Kitchen、Sushi Bar、Drinks、Sake 的总库存统计是否正确

3. **测试 J2 页面**：
   - 访问 J2 的进出货页面（`stockeditall.php?system=j2`）
   - 检查历史记录是否显示类型
   - 访问 J2 的总库存页面（`stocklistall.php?system=j2`）
   - 检查类型统计

4. **测试 J3 页面**：
   - 访问 J3 的进出货页面（`stockeditall.php?system=j3`）
   - 检查历史记录是否显示类型
   - 访问 J3 的总库存页面（`stocklistall.php?system=j3`）
   - 检查类型统计

5. **测试中央系统出货到 J1/J2/J3**：
   - 在中央系统添加新的出货记录
   - 选择出货到 J1/J2/J3
   - 保存后到对应的 J1/J2/J3 页面查看
   - 确认新记录显示了类型
   - 确认 stocklist 的类型统计正确增加

6. **测试 J1/J2/J3 直接添加新记录**：
   - 在 J1/J2/J3 页面直接添加新的进货/出货记录
   - 选择货品后，类型字段应该自动填充
   - 保存后检查类型是否正确保存

---

## 📊 更新脚本做了什么

`update_historical_type_data.sql` 脚本执行以下操作：

1. **更新 6 个表的历史数据**：
   - `j1stockedit_data`
   - `j2stockedit_data`
   - `j3stockedit_data`
   - `j1stockinout_data`
   - `j2stockinout_data`
   - `j3stockinout_data`

2. **匹配逻辑**：
   - 首先通过 `product_name` 匹配 `stock_data` 表获取 `category`
   - 如果没有匹配到，再通过 `code_number` 匹配

3. **保护机制**：
   - 只更新有货品名称或编号的记录
   - 不会覆盖已有的类型（除非为空）
   - 保留特殊标记（如 `AUTO_INBOUND`）

---

## 🎯 完成后的效果

### ✅ 历史数据
- 所有 J1/J2/J3 的历史进出货记录都会显示类型
- stocklist 的类型统计（Kitchen、Sushi Bar、Drinks、Sake）会包含所有历史数据

### ✅ 新增数据
- **中央系统出货到 J1/J2/J3**：类型会自动从 `stock_data` 获取并保存
- **J1/J2/J3 直接添加新记录**：
  - 选择货品名称时，类型自动填充
  - 选择货品编号时，类型自动填充
  - 可以手动修改类型
  - 保存时类型会一起保存

### ✅ 库存统计
- J1/J2/J3 的 stocklist 页面会按类型显示总库存金额
- 包括：Kitchen、Sushi Bar、Drinks、Sake 四个分类

---

## ⚠️ 注意事项

1. **执行前必须备份数据库**
2. **在非高峰时段执行**更新脚本（如果数据量大）
3. **执行完成后立即验证**数据是否正确
4. **如果发现问题**，可以从备份恢复数据库
5. **保留SQL脚本文件**以备将来参考

---

## 🔍 故障排除

### 问题1：执行SQL脚本时超时
**解决方案**：
- 将脚本分段执行（每次执行2-3个UPDATE语句）
- 增加 MySQL 的 `max_execution_time` 设置

### 问题2：部分记录没有更新类型
**原因**：
- 这些货品在 `stock_data` 表中不存在
- 或者 `stock_data` 中的货品名称/编号与记录不匹配

**解决方案**：
- 检查 `stock_data` 表是否包含这些货品
- 检查货品名称/编号是否完全匹配（注意空格、大小写）

### 问题3：前端页面不显示类型
**解决方案**：
- 清除浏览器缓存（Ctrl + Shift + Delete）
- 使用无痕模式测试
- 检查浏览器控制台是否有JavaScript错误

### 问题4：stocklist 类型统计不正确
**解决方案**：
- 检查数据库中的 `type` 字段值是否准确
- 确认类型名称完全匹配：`Kitchen`、`Sushi Bar`、`Drinks`、`Sake`（注意大小写和空格）
- 刷新页面或清除缓存

---

## 📞 支持

如有任何问题，请检查：
1. 浏览器控制台（F12）的错误信息
2. 服务器错误日志
3. 数据库查询结果

保留所有备份文件直到确认系统正常运行！

---

**创建日期**：2025-10-23  
**最后更新**：2025-10-23

