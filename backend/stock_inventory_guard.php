<?php
/**
 * 库存删除防护：删除入库后若该品名+单价会倒扣，则拒绝。
 *
 * 可用库存口径与总库存一致：显示单价 ROUND(price, 2)，而不是数据库 raw 精度。
 */

function stock_sql_product_name_eq(): string
{
    return "REPLACE(product_name, '&amp;', '&') = REPLACE(?, '&amp;', '&')";
}

function stock_sql_price_display_eq(): string
{
    return 'ROUND(price, 2) = ROUND(?, 2)';
}

function stock_sql_price_display_expr(): string
{
    return 'ROUND(price, 2)';
}

function stock_display_price_key($price): string
{
    return number_format(round(floatval($price), 2), 2, '.', '');
}

/**
 * @param PDO $pdo
 * @param string $editTable j1stockedit_data / j2stockedit_data / j3stockedit_data / stockinout_data
 * @param array $record 含 product_name, price, in_quantity, out_quantity
 * @return array{ok:bool,message?:string,projected?:float}
 */
function assertDeleteInWouldNotGoNegative(PDO $pdo, string $editTable, array $record): array
{
    $inQty = floatval($record['in_quantity'] ?? 0);
    if ($inQty <= 0) {
        return ['ok' => true]; // 出库删除不走此校验
    }

    $product = $record['product_name'] ?? '';
    $price = $record['price'] ?? 0;
    if ($product === '') {
        return ['ok' => true];
    }

    $sql = "SELECT 
                (COALESCE(SUM(CASE WHEN in_quantity > 0 THEN in_quantity ELSE 0 END), 0) -
                 COALESCE(SUM(CASE WHEN out_quantity > 0 THEN out_quantity ELSE 0 END), 0)) AS stock
            FROM {$editTable}
            WHERE deleted_at IS NULL
              AND " . stock_sql_product_name_eq() . "
              AND " . stock_sql_price_display_eq();
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product, $price]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $current = floatval($row['stock'] ?? 0);
    $projected = $current - $inQty;

    if ($projected < -0.0005) {
        return [
            'ok' => false,
            'projected' => $projected,
            'message' => "删除会导致倒扣（{$product} @ {$price}：当前 {$current}，删除入库 {$inQty} 后约为 {$projected}）。请先处理出货或去回收站恢复相关记录。",
        ];
    }

    return ['ok' => true, 'projected' => $projected];
}
