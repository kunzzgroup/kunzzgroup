<?php
// ============================================================
//  TOKYO JAPANESE CUISINE — Menu API
// ============================================================
/**
 * 🛠️ 修改建议 (Maintenance Guide):
 * 1. 修改样式: 找到下方的 renderItemHTML() 和 renderCategoryHTML()。
 * 2. 增加字段: 在 renderItemHTML() 中增加 <div> 或 <input>，并在 actionAdd/Edit 中添加该字段。
 */

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
define('UPLOAD_URL_BASE', 'uploads/'); // Web-accessible relative path

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
//  ── HTML RENDERERS (UI 结构锁在这里) ──
// ============================================================

function renderCategoryHTML(array $c, bool $isActive): string {
    $id = (int)$c['id'];
    $name = htmlspecialchars($c['category_name'] ?? '');
    $count = (int)($c['item_count'] ?? 0);
    $activeCls = $isActive ? 'active' : '';
    return "
        <div class=\"cat-item $activeCls\" data-id=\"$id\" draggable=\"true\" onclick=\"selectCat($id,'$name')\">
          <div class=\"cat-item-left\"><span class=\"cat-drag-handle\">⠿</span><span class=\"cat-name-box\">$name</span></div>
          <div class=\"cat-item-right\">
            <span class=\"cat-badge\">$count</span>
            <div class=\"cat-actions\">
              <button class=\"btn-cat-act\" onclick=\"event.stopPropagation();renameCat($id,'$name')\">✎</button>
              <button class=\"btn-cat-act\" onclick=\"event.stopPropagation();confirmDelCat($id,'$name')\">✕</button>
            </div>
          </div>
        </div>";
}

function renderItemHTML(array $i): string {
    $id = (int)$i['id']; $code = htmlspecialchars($i['item_code'] ?? 'N/A');
    $nameEn = htmlspecialchars($i['item_name'] ?? ''); $nameCn = htmlspecialchars($i['item_name_cn'] ?? '');
    $desc = htmlspecialchars($i['item_desc'] ?? ''); $price = htmlspecialchars($i['price'] ?? '0.00');
    $isPub = $i['status'] === 'published';
    $imgUrl = buildImageUrl($i['image_path']);
    $imgUrlEnc = addslashes($imgUrl);
    $itemThumbBox = "
    <div class=\"item-thumb-box-wrap\">
        <div class=\"item-thumb-box " . ($imgUrl ? "clickable" : "") . "\" " . ($imgUrl ? "onclick=\"openPhoto('$imgUrlEnc')\"" : "") . ">
            " . ($imgUrl ? "<img src=\"$imgUrl\">" : "<span class=\"item-thumb-none\">📸</span>") . "
        </div>
    </div>";
    $editSvg = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
    $delSvg = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>';

    return "
    <div class=\"item-row\" data-id=\"$id\" draggable=\"true\">
        $itemThumbBox
        <div class=\"item-details\">
            <span class=\"item-code-tag\">$code</span>
            <input class=\"inline-input item-name-en\" value=\"$nameEn\" onblur=\"updateInline($id,'item_name',this.value)\">
            <div class=\"item-name-cn static-field\">$nameCn</div>
            <div class=\"item-desc-row static-field\">$desc</div>
        </div>
        <div class=\"item-price\">
            <div style=\"font-size:10px;opacity:0.5\">PRICE</div>
            <div class=\"item-price-val static-field\">$price</div>
        </div>
        <div class=\"item-status\"><div class=\"status-toggle ".($isPub?'published':'draft')."\" onclick=\"toggleStatus($id)\"><span class=\"dot-".($isPub?'green':'gray')."\"></span>".($isPub?'已发布':'草稿')."</div></div>
        <div class=\"item-actions\"><button class=\"btn-act\" onclick=\"openEditPanel($id)\">$editSvg</button><button class=\"btn-act btn-del\" onclick=\"confirmDelItem($id,'$nameEn')\">$delSvg</button></div>
    </div>";
}

// ============================================================
//  ROUTER (只在有 action 参数时运行，防止 include 出错)
// ============================================================
$action = $_REQUEST['action'] ?? '';

if ($action) {
    match ($action) {
        'get_categories'      => actionGetCategories(),
        'add_category'        => actionAddCategory(),
        'delete_category'     => actionDeleteCategory(),
        'get'                 => actionGet(),
        'add'                 => actionAdd(),
        'edit'                => actionEdit(),
        'delete'              => actionDelete(),
        'toggle_status'       => actionToggleStatus(),
        'reorder_items'       => actionReorderItems(),
        'reorder_cats'        => actionReorderCats(),
        'edit_category'       => actionEditCategory(),
        'get_item'            => actionGetItem(),
        'get_categories_html' => actionGetCategoriesHTML(),
        'get_items_html'      => actionGetItemsHTML(),
        default               => respond(false, "Unknown action: $action", null, 400),
    };
}

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

    // 1. 获取该分类下所有项目的图片路径
    $stmt = $pdo->prepare("SELECT image_path FROM menus WHERE category_id = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll();

    // 2. 删除服务器上的图片文件
    foreach ($items as $item) {
        if (!empty($item['image_path'])) {
            $file = UPLOAD_BASE . $item['image_path'];
            if (file_exists($file)) @unlink($file);
        }
    }

    // 3. 执行删除操作 (级联删除分类及项目)
    $stmt = $pdo->prepare("DELETE FROM menu_categories WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) respond(false, '分类不存在', null, 404);
    respond(true, '分类及其关联项目的图片已全部删除');
}

// ============================================================
//  ACTION: EDIT CATEGORY
//  POST menu_api.php?action=edit_category
//  Body: id, category_name, sort_order
// ============================================================
function actionEditCategory(): void {
    $id    = (int)($_POST['id'] ?? 0);
    $name  = trim($_POST['category_name'] ?? '');
    $order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : null;

    if ($id <= 0) respond(false, 'id 无效', null, 422);

    $pdo = getDB();
    $sql = "UPDATE menu_categories SET category_name = ?";
    $params = [$name];

    if ($order !== null) {
        $sql .= ", sort_order = ?";
        $params[] = $order;
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    respond(true, '分类更新成功');
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
    if (empty($item_name))      respond(false, '英文名称不能为空', null, 422);
    if (empty(trim($_POST['item_code'] ?? ''))) respond(false, '编码不能为空', null, 422);

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
    // Handle manual image deletion
    else if (!empty($_POST['delete_image'])) {
        if ($existing['image_path']) {
            $oldFile = UPLOAD_BASE . $existing['image_path'];
            if (file_exists($oldFile)) @unlink($oldFile);
        }
        $fields[] = "image_path = ?";
        $params[] = null;
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

    // 1. 查询图片路径
    $stmt = $pdo->prepare("SELECT image_path FROM menus WHERE id = ?");
    $stmt->execute([$id]);
    $row  = $stmt->fetch();
    if (!$row) respond(false, '菜单项目不存在', null, 404);

    // 2. unlink 删除服务器图片
    if ($row['image_path']) {
        $file = UPLOAD_BASE . $row['image_path'];
        if (file_exists($file)) @unlink($file);
    }

    // 3. DELETE database (执行数据库删除)
    $pdo->prepare("DELETE FROM menus WHERE id = ?")->execute([$id]);

    respond(true, '菜单项目及图片已删除');
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

// ============================================================
//  ACTION: REORDER ITEMS
// ============================================================
function actionReorderItems(): void {
    $idsRaw = $_POST['ids'] ?? '';
    if (empty($idsRaw)) respond(false, 'ids 不能为空', null, 422);
    $idsArr = explode(',', $idsRaw);
    $pdo    = getDB();
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE menus SET sort_order = ? WHERE id = ?");
        foreach ($idsArr as $index => $id) {
            $stmt->execute([(int)$index + 1, (int)$id]);
        }
        $pdo->commit();
        respond(true, '项目顺序已更新');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        respond(false, '更新失败: ' . $e->getMessage(), null, 500);
    }
}

// ============================================================
//  ACTION: REORDER CATEGORIES
// ============================================================
function actionReorderCats(): void {
    $idsRaw = $_POST['ids'] ?? '';
    if (empty($idsRaw)) respond(false, 'ids 不能为空', null, 422);
    $idsArr = explode(',', $idsRaw);
    $pdo    = getDB();
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE menu_categories SET sort_order = ? WHERE id = ?");
        foreach ($idsArr as $index => $id) {
            $stmt->execute([(int)$index + 1, (int)$id]);
        }
        $pdo->commit();
        respond(true, '分类顺序已更新');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        respond(false, '更新失败: ' . $e->getMessage(), null, 500);
    }
}

// ============================================================
//  ACTION: GET SINGLE ITEM
// ============================================================
function actionGetItem(): void {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) respond(false, 'id 无效', null, 422);
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT m.*, mc.category_name FROM menus m LEFT JOIN menu_categories mc ON mc.id = m.category_id WHERE m.id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) respond(false, '未找到该项目', null, 404);
    $item['image_url'] = buildImageUrl($item['image_path']);
    respond(true, 'OK', $item);
}

// ── ACTIONS: HTML FRAGMENTS (JS 调用) ─────────────────────

function actionGetCategoriesHTML(): void {
    $type = $_GET['type'] ?? 'grand';
    $activeId = (int)($_GET['active_id'] ?? 0);
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, category_name, (SELECT COUNT(*) FROM menus WHERE category_id = mc.id) AS item_count FROM menu_categories mc WHERE menu_type = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$type]);
    $cats = $stmt->fetchAll();
    if (empty($cats)) { 
        echo "
        <div class='empty-state mini'>
            <div class='empty-icon'>📁</div>
            <div class='empty-text'>暂无分类</div>
        </div>"; 
        exit; 
    }
    foreach ($cats as $c) echo renderCategoryHTML($c, $c['id'] == $activeId);
    exit;
}

function actionGetItemsHTML(): void {
    $catId = (int)($_GET['category_id'] ?? 0);
    $search = trim($_GET['search'] ?? '');
    if ($catId <= 0) { echo "<div class='empty-state'>请选择分类</div>"; exit; }
    $pdo = getDB();
    $sql = "SELECT * FROM menus WHERE category_id = ?"; $params = [$catId];
    if ($search !== '') {
        $sql .= " AND (item_name LIKE ? OR item_name_cn LIKE ? OR item_code LIKE ?)";
        $like = "%$search%"; $params[] = $like; $params[] = $like; $params[] = $like;
    }
    $sql .= " ORDER BY sort_order ASC, id ASC";
    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    $items = $stmt->fetchAll();
    if (empty($items)) { 
        echo "
        <div class='empty-state'>
            <div class='empty-icon'>🍱</div>
            <div class='empty-text'>该分类下暂无项目</div>
            <div class='empty-hint'>点击右侧“新增”按钮开始添加</div>
        </div>"; 
        exit; 
    }
    foreach ($items as $i) echo renderItemHTML($i);
    exit;
}
