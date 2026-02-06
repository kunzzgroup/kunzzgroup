<?php
declare(strict_types=1);

// 设置内存和执行时间，以防数据量大
ini_set('memory_limit', '512M');
set_time_limit(120);

// 允许跨域（与现有接口保持一致）
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

$system = strtolower(trim($_GET['system'] ?? ''));
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

$tableMap = [
    'j1' => 'j1stockinout_data',
    'j2' => 'j2stockinout_data',
    'j3' => 'j3stockinout_data'
];

if (!isset($tableMap[$system])) {
    http_response_code(400);
    echo 'Invalid system parameter.';
    exit;
}

if (!$startDate || !$endDate) {
    http_response_code(400);
    echo 'Missing start_date or end_date.';
    exit;
}

// 验证日期格式 YYYY-MM-DD
foreach (['startDate' => $startDate, 'endDate' => $endDate] as $label => $value) {
    $date = DateTime::createFromFormat('Y-m-d', $value);
    if (!$date || $date->format('Y-m-d') !== $value) {
        http_response_code(400);
        echo "Invalid {$label} format. Expected YYYY-MM-DD.";
        exit;
    }
}

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $dbuser, $dbpass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Database connection failed.';
    exit;
}

$table = $tableMap[$system];

$sql = "
    SELECT 
        id,
        date,
        code_number,
        product_name,
        in_quantity,
        specification,
        price
    FROM {$table}
    WHERE date BETWEEN :start_date AND :end_date
      AND in_quantity > 0
    ORDER BY date ASC, time ASC, id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':start_date', $startDate);
$stmt->bindValue(':end_date', $endDate);
$stmt->execute();

$rows = $stmt->fetchAll();

$excelFileName = sprintf(
    '%s_stock_%s_to_%s.xls',
    strtoupper($system),
    str_replace('-', '', $startDate),
    str_replace('-', '', $endDate)
);

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $excelFileName . '"');
header('Cache-Control: max-age=0');

echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns='http://www.w3.org/TR/REC-html40'>";
echo "<head><meta charset='utf-8'><style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; font-family: Arial, sans-serif; font-size: 12px; }
        th { background-color: #99d9ea; font-weight: bold; }
        td.text-left { text-align: left; }
        td.currency { mso-number-format:'\\0022RM\\0022\\ #,##0.00'; }
        td.decimal { mso-number-format:'0.000'; }
        td.integer { mso-number-format:'0'; }
    </style></head><body>";

echo "<table>";
echo "<tr>
        <th>NO:</th>
        <th>Date</th>
        <th>Code</th>
        <th>Product</th>
        <th>In Quantity</th>
        <th>Specification</th>
        <th>Price</th>
        <th>Total Price</th>
    </tr>";

$counter = 1;
$grandTotalIn = 0.0;
$grandTotalValue = 0.0;

foreach ($rows as $row) {
    $inQuantity = (float)($row['in_quantity'] ?? 0);
    $price = (float)($row['price'] ?? 0);
    $totalPrice = $inQuantity * $price;

    $grandTotalIn += $inQuantity;
    $grandTotalValue += $totalPrice;

    echo "<tr>";
    echo "<td class='integer'>" . $counter . "</td>";
    echo "<td>" . htmlspecialchars($row['date'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td class='text-left'>" . htmlspecialchars($row['code_number'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td class='text-left'>" . htmlspecialchars($row['product_name'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td class='decimal'>" . number_format($inQuantity, 3, '.', '') . "</td>";
    echo "<td class='text-left'>" . htmlspecialchars($row['specification'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td class='currency'>" . number_format($price, 2, '.', '') . "</td>";
    echo "<td class='currency'>" . number_format($totalPrice, 2, '.', '') . "</td>";
    echo "</tr>";

    $counter++;
}

if ($counter === 1) {
    echo "<tr><td colspan='8'>No inbound records found for the selected range.</td></tr>";
}

echo "</table></body></html>";
exit;

