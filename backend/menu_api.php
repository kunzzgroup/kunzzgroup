<?php
// ============================================================
//  TOKYO JAPANESE CUISINE — Menu API
//  Single-file REST API for menu management
//  Usage: menu_api.php?action=<action>
// ============================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
//  DATABASE CONFIG
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'u690174784_kunzz');
define('DB_USER', 'u690174784_kunzz');
define('DB_PASS', 'Kunzz1688');

// Image upload directory (relative to this file)
define('UPLOAD_BASE', __DIR__ . '/uploads/');
define('UPLOAD_URL_BASE', '/uploads/'); // Web-accessible path

// ============================================================
//  DB CONNECTION
// ============================================================
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            respond(false, 'Database connection failed: ' . $e->getMessage(), null, 500);
        }
    }
    return $pdo;
}

// ============================================================
//  HELPERS
// ============================================================
function respond(bool $success, string $message, $data = null, int $code = 200): void {
    http_response_code($code);
    $payload = ['success' => $success, 'message' => $message];
    if ($data !== null) $payload['data'] = $data;
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

function validMenuType(string $type): bool {
    return in_array($type, ['grand', 'sushi'], true);
}

function formatPrice(?string $price): ?string {
    return $price !== null ? 'RM ' . number_format((float)$price, 2) : null;
}

function buildImageUrl(?string $path): ?string {
    if (!$path) return null;
    // If already a full URL, return as-is
    if (str_starts_with($path, 'http')) return $path;
    return UPLOAD_URL_BASE . ltrim($path, '/');
}

function uploadImage(array $file, string $menu_type, string $item_code): string {
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed)) {
        respond(false, '图片格式不支持，请上传 JPG / PNG / WEBP', null, 422);
    }
    if ($file['size'] > $maxSize) {
        respond(false, '图片超过 5MB 限制', null, 422);
    }

    $ext     = pathinfo($file['name'], PATHINFO_EXTENSION);
    $subDir  = UPLOAD_BASE . $menu_type . '/';
    if (!is_dir($subDir)) mkdir($subDir, 0755, true);

    $filename = strtolower($item_code ?: uniqid()) . '_' . time() . '.' . $ext;
    $dest     = $subDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        respond(false, '图片保存失败，请检查目录权限', null, 500);
    }

    return $menu_type . '/' . $filename; // Stored path (relative to UPLOAD_BASE)
}

// ============================================================
//  ROUTER
// ============================================================
$action = $_REQUEST['action'] ?? '';

match ($action) {
    // ── Categories ──────────────────────────────────────────
    'get_categories'    => actionGetCategories(),
    'add_category'      => actionAddCategory(),
    'delete_category'   => actionDeleteCategory(),

    // ── Menu Items ──────────────────────────────────────────
    'get'               => actionGet(),
    'add'               => actionAdd(),
    'edit'              => actionEdit(),
    'delete'            => actionDelete(),

    // ── Toggle Status ────────────────────────────────────────
    'toggle_status'     => actionToggleStatus(),

    default             => respond(false, "Unknown action: '$action'. Valid: get_categories, add_category, delete_category, get, add, edit, delete, toggle_status", null, 400),
};

// ============================================================
//  ACTION: GET CATEGORIES
//  GET  menu_api.php?action=get_categories&type=grand
// ============================================================
function actionGetCategories(): void {
    $type = $_GET['type'] ?? '';
    if (!validMenuType($type)) respond(false, 'type 必须是 grand 或 sushi', null, 422);

    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "SELECT id, category_name, sort_order,
                (SELECT COUNT(*) FROM menus WHERE category_id = mc.id) AS item_count
         FROM menu_categories mc
         WHERE menu_type = ?
         ORDER BY sort_order ASC, id ASC"
    );
    $stmt->execute([$type]);
    $cats = $stmt->fetchAll();

    respond(true, 'OK', ['menu_type' => $type, 'categories' => $cats]);
}

// ============================================================
//  ACTION: ADD CATEGORY
//  POST menu_api.php?action=add_category
//  Body: type, category_name, [sort_order]
// ============================================================
function actionAddCategory(): void {
    $type  = trim($_POST['type'] ?? '');
    $name  = trim($_POST['category_name'] ?? '');
    $order = (int)($_POST['sort_order'] ?? 0);

    if (!validMenuType($type))  respond(false, 'type 必须是 grand 或 sushi', null, 422);
    if (empty($name))           respond(false, 'category_name 不能为空', null, 422);

    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "INSERT INTO menu_categories (menu_type, category_name, sort_order) VALUES (?, ?, ?)"
    );
    $stmt->execute([$type, $name, $order]);

    respond(true, '分类新增成功', ['id' => (int)$pdo->lastInsertId()]);
}

// ============================================================
//  ACTION: DELETE CATEGORY
//  POST menu_api.php?action=delete_category
//  Body: id
//  Note: CASCADE will delete all menus in this category
// ============================================================
function actionDeleteCategory(): void {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) respond(false, 'id 无效', null, 422);

    $pdo  = getDB();
    $stmt = $pdo->prepare("DELETE FROM menu_categories WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) respond(false, '分类不存在', null, 404);
    respond(true, '分类已删除（该分类下的菜单项也已一并删除）');
}

// ============================================================
//  ACTION: GET MENU ITEMS
//  GET  menu_api.php?action=get&type=grand
//       &category_id=1          (optional, filter by category)
//       &status=published|draft (optional)
//       &search=keyword         (optional)
// ============================================================
function actionGet(): void {
    $type        = $_GET['type'] ?? '';
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
    $status      = $_GET['status'] ?? null;
    $search      = trim($_GET['search'] ?? '');

    if (!validMenuType($type)) respond(false, 'type 必须是 grand 或 sushi', null, 422);

    $pdo    = getDB();
    $where  = ['m.menu_type = ?'];
    $params = [$type];

    if ($category_id) {
        $where[]  = 'm.category_id = ?';
        $params[] = $category_id;
    }
    if ($status && in_array($status, ['published', 'draft'])) {
        $where[]  = 'm.status = ?';
        $params[] = $status;
    }
    if ($search !== '') {
        $where[]  = '(m.item_name LIKE ? OR m.item_name_cn LIKE ? OR m.item_code LIKE ?)';
        $like     = "%$search%";
        $params   = array_merge($params, [$like, $like, $like]);
    }

    $sql  = "SELECT m.*, mc.category_name
             FROM menus m
             LEFT JOIN menu_categories mc ON mc.id = m.category_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY m.sort_order ASC, m.id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    // Format output
    foreach ($items as &$item) {
        $item['price_formatted'] = formatPrice($item['price']);
        $item['image_url']       = buildImageUrl($item['image_path']);
    }
    unset($item);

    respond(true, 'OK', [
        'menu_type' => $type,
        'count'     => count($items),
        'items'     => $items,
    ]);
}

// ============================================================
//  ACTION: ADD MENU ITEM
//  POST menu_api.php?action=add
//  Body (multipart/form-data):
//    type*, category_id*, item_name*,
//    item_code, item_name_cn, item_desc, price,
//    status, sort_order
//  File: image (optional)
// ============================================================
function actionAdd(): void {
    $type        = trim($_POST['type'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $item_name   = trim($_POST['item_name'] ?? '');

    if (!validMenuType($type))  respond(false, 'type 必须是 grand 或 sushi', null, 422);
    if ($category_id <= 0)      respond(false, 'category_id 无效', null, 422);
    if (empty($item_name))      respond(false, 'item_name 不能为空', null, 422);

    $item_code   = trim($_POST['item_code'] ?? '');
    $item_name_cn= trim($_POST['item_name_cn'] ?? '');
    $item_desc   = trim($_POST['item_desc'] ?? '');
    $price       = isset($_POST['price']) && $_POST['price'] !== '' ? (float)$_POST['price'] : null;
    $status      = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';
    $sort_order  = (int)($_POST['sort_order'] ?? 0);

    // Handle image upload
    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
        $image_path = uploadImage($_FILES['image'], $type, $item_code ?: $item_name);
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "INSERT INTO menus
            (menu_type, category_id, item_code, item_name, item_name_cn, item_desc, price, image_path, status, sort_order)
         VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $type, $category_id,
        $item_code ?: null,
        $item_name,
        $item_name_cn ?: null,
        $item_desc ?: null,
        $price,
        $image_path,
        $status,
        $sort_order,
    ]);

    $newId = (int)$pdo->lastInsertId();

    respond(true, '菜单项目新增成功', [
        'id'          => $newId,
        'image_url'   => buildImageUrl($image_path),
    ]);
}

// ============================================================
//  ACTION: EDIT MENU ITEM
//  POST menu_api.php?action=edit
//  Body (multipart/form-data):
//    id*  + any fields to update
//    image (optional, replaces existing)
// ============================================================
function actionEdit(): void {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) respond(false, 'id 无效', null, 422);

    $pdo  = getDB();

    // Fetch existing record
    $stmt = $pdo->prepare("SELECT * FROM menus WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) respond(false, '菜单项目不存在', null, 404);

    // Build update fields from POST (only update what's provided)
    $fields = [];
    $params = [];

    $updatable = [
        'category_id'  => 'int',
        'item_code'    => 'str',
        'item_name'    => 'str',
        'item_name_cn' => 'str',
        'item_desc'    => 'str',
        'price'        => 'float',
        'status'       => 'status',
        'sort_order'   => 'int',
    ];

    foreach ($updatable as $key => $type_hint) {
        if (!isset($_POST[$key])) continue;

        $val = $_POST[$key];
        switch ($type_hint) {
            case 'int':
                $fields[] = "$key = ?";
                $params[] = (int)$val;
                break;
            case 'float':
                $fields[] = "$key = ?";
                $params[] = $val !== '' ? (float)$val : null;
                break;
            case 'status':
                if (in_array($val, ['published', 'draft'])) {
                    $fields[] = "$key = ?";
                    $params[] = $val;
                }
                break;
            default:
                $fields[] = "$key = ?";
                $params[] = $val !== '' ? $val : null;
        }
    }

    // Handle new image upload
    if (!empty($_FILES['image']['name'])) {
        $item_code  = $_POST['item_code'] ?? $existing['item_code'] ?? '';
        $menu_type  = $existing['menu_type'];
        $image_path = uploadImage($_FILES['image'], $menu_type, $item_code);

        // Delete old image file if it exists
        if ($existing['image_path']) {
            $oldFile = UPLOAD_BASE . $existing['image_path'];
            if (file_exists($oldFile)) @unlink($oldFile);
        }

        $fields[] = "image_path = ?";
        $params[] = $image_path;
    }

    if (empty($fields)) respond(false, '没有提供任何需要更新的字段', null, 422);

    $params[] = $id;
    $sql      = "UPDATE menus SET " . implode(', ', $fields) . " WHERE id = ?";
    $pdo->prepare($sql)->execute($params);

    // Return updated record
    $stmt = $pdo->prepare("SELECT * FROM menus WHERE id = ?");
    $stmt->execute([$id]);
    $updated = $stmt->fetch();
    $updated['price_formatted'] = formatPrice($updated['price']);
    $updated['image_url']       = buildImageUrl($updated['image_path']);

    respond(true, '菜单项目已更新', $updated);
}

// ============================================================
//  ACTION: DELETE MENU ITEM
//  POST menu_api.php?action=delete
//  Body: id
// ============================================================
function actionDelete(): void {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) respond(false, 'id 无效', null, 422);

    $pdo  = getDB();

    // Get image path before deleting
    $stmt = $pdo->prepare("SELECT image_path FROM menus WHERE id = ?");
    $stmt->execute([$id]);
    $row  = $stmt->fetch();
    if (!$row) respond(false, '菜单项目不存在', null, 404);

    // Delete DB record
    $pdo->prepare("DELETE FROM menus WHERE id = ?")->execute([$id]);

    // Delete image file
    if ($row['image_path']) {
        $file = UPLOAD_BASE . $row['image_path'];
        if (file_exists($file)) @unlink($file);
    }

    respond(true, '菜单项目已删除');
}

// ============================================================
//  ACTION: TOGGLE STATUS (published ↔ draft)
//  POST menu_api.php?action=toggle_status
//  Body: id
// ============================================================
function actionToggleStatus(): void {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) respond(false, 'id 无效', null, 422);

    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT id, status FROM menus WHERE id = ?");
    $stmt->execute([$id]);
    $row  = $stmt->fetch();
    if (!$row) respond(false, '菜单项目不存在', null, 404);

    $newStatus = $row['status'] === 'published' ? 'draft' : 'published';
    $pdo->prepare("UPDATE menus SET status = ? WHERE id = ?")->execute([$newStatus, $id]);

    respond(true, "状态已切换为 $newStatus", ['id' => $id, 'status' => $newStatus]);
}
