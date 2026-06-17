-- 更新历史数据的类型字段
-- 此脚本将根据 stock_data 表中的 category 更新所有历史进出库记录的 type 字段
-- 执行前请备份数据库！

-- 1. 更新 j1stockedit_data 表的类型字段（通过 product_name）
UPDATE j1stockedit_data j1
LEFT JOIN stock_data s ON j1.product_name = s.product_name
SET j1.type = s.category
WHERE j1.product_name IS NOT NULL 
AND j1.product_name != ''
AND s.category IS NOT NULL;

-- 2. 更新 j1stockedit_data 表的类型字段（通过 code_number，针对没有通过 product_name 匹配的记录）
UPDATE j1stockedit_data j1
LEFT JOIN stock_data s ON j1.code_number = s.product_code
SET j1.type = s.category
WHERE (j1.type IS NULL OR j1.type = '')
AND j1.code_number IS NOT NULL 
AND j1.code_number != ''
AND s.category IS NOT NULL;

-- 3. 更新 j2stockedit_data 表的类型字段（通过 product_name）
UPDATE j2stockedit_data j2
LEFT JOIN stock_data s ON j2.product_name = s.product_name
SET j2.type = s.category
WHERE j2.product_name IS NOT NULL 
AND j2.product_name != ''
AND s.category IS NOT NULL;

-- 4. 更新 j2stockedit_data 表的类型字段（通过 code_number，针对没有通过 product_name 匹配的记录）
UPDATE j2stockedit_data j2
LEFT JOIN stock_data s ON j2.code_number = s.product_code
SET j2.type = s.category
WHERE (j2.type IS NULL OR j2.type = '')
AND j2.code_number IS NOT NULL 
AND j2.code_number != ''
AND s.category IS NOT NULL;

-- 5. 更新 j3stockedit_data 表的类型字段（通过 product_name）
UPDATE j3stockedit_data j3
LEFT JOIN stock_data s ON j3.product_name = s.product_name
SET j3.type = s.category
WHERE j3.product_name IS NOT NULL 
AND j3.product_name != ''
AND s.category IS NOT NULL;

-- 6. 更新 j3stockedit_data 表的类型字段（通过 code_number，针对没有通过 product_name 匹配的记录）
UPDATE j3stockedit_data j3
LEFT JOIN stock_data s ON j3.code_number = s.product_code
SET j3.type = s.category
WHERE (j3.type IS NULL OR j3.type = '')
AND j3.code_number IS NOT NULL 
AND j3.code_number != ''
AND s.category IS NOT NULL;

-- 7. 更新 j1stockinout_data 表的类型字段（通过 product_name）
UPDATE j1stockinout_data j1
LEFT JOIN stock_data s ON j1.product_name = s.product_name
SET j1.type = s.category
WHERE j1.product_name IS NOT NULL 
AND j1.product_name != ''
AND s.category IS NOT NULL
AND j1.type != 'AUTO_INBOUND'; -- 保留 AUTO_INBOUND 标记

-- 8. 更新 j1stockinout_data 表的类型字段（通过 code_number，针对没有通过 product_name 匹配的记录）
UPDATE j1stockinout_data j1
LEFT JOIN stock_data s ON j1.code_number = s.product_code
SET j1.type = s.category
WHERE (j1.type IS NULL OR j1.type = '')
AND j1.code_number IS NOT NULL 
AND j1.code_number != ''
AND s.category IS NOT NULL;

-- 9. 更新 j2stockinout_data 表的类型字段（通过 product_name）
UPDATE j2stockinout_data j2
LEFT JOIN stock_data s ON j2.product_name = s.product_name
SET j2.type = s.category
WHERE j2.product_name IS NOT NULL 
AND j2.product_name != ''
AND s.category IS NOT NULL
AND j2.type != 'AUTO_INBOUND'; -- 保留 AUTO_INBOUND 标记

-- 10. 更新 j2stockinout_data 表的类型字段（通过 code_number，针对没有通过 product_name 匹配的记录）
UPDATE j2stockinout_data j2
LEFT JOIN stock_data s ON j2.code_number = s.product_code
SET j2.type = s.category
WHERE (j2.type IS NULL OR j2.type = '')
AND j2.code_number IS NOT NULL 
AND j2.code_number != ''
AND s.category IS NOT NULL;

-- 11. 更新 j3stockinout_data 表的类型字段（通过 product_name）
UPDATE j3stockinout_data j3
LEFT JOIN stock_data s ON j3.product_name = s.product_name
SET j3.type = s.category
WHERE j3.product_name IS NOT NULL 
AND j3.product_name != ''
AND s.category IS NOT NULL
AND j3.type != 'AUTO_INBOUND'; -- 保留 AUTO_INBOUND 标记

-- 12. 更新 j3stockinout_data 表的类型字段（通过 code_number，针对没有通过 product_name 匹配的记录）
UPDATE j3stockinout_data j3
LEFT JOIN stock_data s ON j3.code_number = s.product_code
SET j3.type = s.category
WHERE (j3.type IS NULL OR j3.type = '')
AND j3.code_number IS NOT NULL 
AND j3.code_number != ''
AND s.category IS NOT NULL;

-- 完成！
-- 执行后，请检查数据是否正确更新
SELECT '更新完成！' as status;

-- 可以运行以下查询来检查更新结果：
-- SELECT type, COUNT(*) FROM j1stockedit_data GROUP BY type;
-- SELECT type, COUNT(*) FROM j2stockedit_data GROUP BY type;
-- SELECT type, COUNT(*) FROM j3stockedit_data GROUP BY type;
-- SELECT type, COUNT(*) FROM j1stockinout_data GROUP BY type;
-- SELECT type, COUNT(*) FROM j2stockinout_data GROUP BY type;
-- SELECT type, COUNT(*) FROM j3stockinout_data GROUP BY type;

