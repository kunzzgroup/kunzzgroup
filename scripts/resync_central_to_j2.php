<?php
/**
 * 补同步：中央 stockinout_data（target=j2 出库）→ J2 j2stockinout_data + j2stockedit_data
 *
 * 用法（CLI）:
 *   php scripts/resync_central_to_j2.php              # 预览，不写库
 *   php scripts/resync_central_to_j2.php --execute    # 执行补同步
 *   php scripts/resync_central_to_j2.php --product="RAMEN EGG"
 */
require_once __DIR__ . '/../config.php';

$execute = in_array('--execute', $argv ?? [], true);
$productFilter = null;
foreach ($argv ?? [] as $arg) {
    if (str_starts_with($arg, '--product=')) {
        $productFilter = substr($arg, strlen('--product='));
    }
}

try {
    $pdo = get_pdo_connection();
} catch (Exception $e) {
    fwrite(STDERR, "数据库连接失败: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

$sql = "
    SELECT s.*
    FROM stockinout_data s
    WHERE s.deleted_at IS NULL
      AND s.out_quantity > 0
      AND s.target_system = 'j2'
      AND NOT EXISTS (
          SELECT 1 FROM j2stockinout_data j2
          WHERE j2.main_record_id = s.id AND j2.deleted_at IS NULL
      )
";
$params = [];
if ($productFilter !== null && $productFilter !== '') {
    $sql .= " AND s.product_name = ?";
    $params[] = $productFilter;
}
$sql .= " ORDER BY s.date ASC, s.time ASC, s.id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$missing = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo ($execute ? "[执行模式]" : "[预览模式]") . " 待补同步记录: " . count($missing) . PHP_EOL;

if (empty($missing)) {
    exit(0);
}

$catCache = [];
$getCategory = function (string $productName, ?string $codeNumber) use ($pdo, &$catCache): ?string {
    $key = $productName . '|' . ($codeNumber ?? '');
    if (array_key_exists($key, $catCache)) {
        return $catCache[$key];
    }
    if ($productName !== '') {
        $q = $pdo->prepare("SELECT category FROM stock_data WHERE product_name = ? LIMIT 1");
        $q->execute([$productName]);
        $cat = $q->fetchColumn();
        if ($cat !== false) {
            return $catCache[$key] = $cat;
        }
    }
    if ($codeNumber) {
        $q = $pdo->prepare("SELECT category FROM stock_data WHERE product_code = ? LIMIT 1");
        $q->execute([$codeNumber]);
        $cat = $q->fetchColumn();
        if ($cat !== false) {
            return $catCache[$key] = $cat;
        }
    }
    return $catCache[$key] = null;
};

$inoutSql = "INSERT INTO j2stockinout_data
    (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, total_value, type, receiver, remark, main_record_id, target_system)
    VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, 'from_main')";
$editSql = "INSERT INTO j2stockedit_data
    (date, time, code_number, product_name, in_quantity, out_quantity, specification, price, receiver, remark, target_system, type)
    VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, 'j2', ?)";

$inoutStmt = $pdo->prepare($inoutSql);
$editStmt = $pdo->prepare($editSql);

$synced = 0;
$errors = 0;

foreach ($missing as $row) {
    $outQty = floatval($row['out_quantity']);
    $price = floatval($row['price'] ?? 0);
    $category = $getCategory($row['product_name'] ?? '', $row['code_number'] ?? null);

    echo sprintf(
        "- id=%s %s %s %s qty=%s price=%s receiver=%s%s",
        $row['id'],
        $row['date'],
        $row['time'],
        $row['product_name'],
        $outQty,
        $price,
        $row['receiver'] ?? '',
        PHP_EOL
    );

    if (!$execute) {
        continue;
    }

    try {
        $pdo->beginTransaction();

        $inoutStmt->execute([
            $row['date'],
            $row['time'],
            $row['code_number'] ?? null,
            $row['product_name'],
            $outQty,
            $row['specification'] ?? null,
            $price,
            $outQty * $price,
            $category,
            $row['receiver'] ?? null,
            $row['remark'] ?? null,
            $row['id'],
        ]);

        $editStmt->execute([
            $row['date'],
            $row['time'],
            $row['code_number'] ?? null,
            $row['product_name'],
            $outQty,
            $row['specification'] ?? null,
            $price,
            $row['receiver'] ?? null,
            $row['remark'] ?? null,
            $category,
        ]);

        $pdo->commit();
        $synced++;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errors++;
        fwrite(STDERR, "  失败 id={$row['id']}: " . $e->getMessage() . PHP_EOL);
    }
}

if ($execute) {
    echo PHP_EOL . "完成: 成功 {$synced} 条, 失败 {$errors} 条" . PHP_EOL;
} else {
    echo PHP_EOL . "以上为预览。确认后执行: php scripts/resync_central_to_j2.php --execute" . PHP_EOL;
}
