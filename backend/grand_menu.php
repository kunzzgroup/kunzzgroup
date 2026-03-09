<?php
require_once 'config.php';

$menu_type = 'grand';
$msg = '';
$msg_type = '';

// ── 1. 新增分类 ──────────────────────────────────────────────
if (isset($_POST['add_category'])) {
    $cat_name   = $conn->real_escape_string(trim($_POST['category_name']));
    $sort_order = (int)$_POST['sort_order'];
    if ($cat_name !== '') {
        $conn->query("INSERT INTO menu_categories (menu_type, category_name, sort_order)
                      VALUES ('$menu_type','$cat_name',$sort_order)");
        $msg = '✅ 分类新增成功！';
        $msg_type = 'success';
    }
}

// ── 2. 删除分类 ──────────────────────────────────────────────
if (isset($_GET['del_cat'])) {
    $cat_id = (int)$_GET['del_cat'];
    $conn->query("DELETE FROM menu_categories WHERE id=$cat_id AND menu_type='$menu_type'");
    header('Location: grand_menu.php');
    exit;
}

// ── 3. 上传菜单项目 ──────────────────────────────────────────
if (isset($_POST['add_menu'])) {
    $cat_id      = (int)$_POST['category_id'];
    $item_code   = $conn->real_escape_string(trim($_POST['item_code']));
    $item_name   = $conn->real_escape_string(trim($_POST['item_name']));
    $item_name_cn= $conn->real_escape_string(trim($_POST['item_name_cn']));
    $item_desc   = $conn->real_escape_string(trim($_POST['item_desc']));
    $price       = (float)$_POST['price'];
    $status      = $_POST['status'] === 'draft' ? 'draft' : 'published';
    $image_path  = '';

    // 图片上传
    if (!empty($_FILES['menu_image']['name'])) {
        $upload_dir = 'uploads/grand/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $ext       = strtolower(pathinfo($_FILES['menu_image']['name'], PATHINFO_EXTENSION));
        $allowed   = ['jpg','jpeg','png','webp'];
        if (in_array($ext, $allowed) && $_FILES['menu_image']['size'] <= 5 * 1024 * 1024) {
            $filename   = time() . '_' . uniqid() . '.' . $ext;
            $target     = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['menu_image']['tmp_name'], $target)) {
                $image_path = $target;
            }
        } else {
            $msg = '⚠️ 图片格式不支持或超过 5MB';
            $msg_type = 'error';
        }
    }

    if ($msg === '') {
        $conn->query("INSERT INTO menus
            (menu_type, category_id, item_code, item_name, item_name_cn, item_desc, price, image_path, status)
            VALUES ('$menu_type',$cat_id,'$item_code','$item_name','$item_name_cn','$item_desc',$price,'$image_path','$status')");
        $msg = '✅ 菜单项目上传成功！';
        $msg_type = 'success';
    }
}

// ── 4. 删除菜单项目 ──────────────────────────────────────────
if (isset($_GET['del_menu'])) {
    $menu_id = (int)$_GET['del_menu'];
    // 删除图片文件
    $row = $conn->query("SELECT image_path FROM menus WHERE id=$menu_id")->fetch_assoc();
    if ($row && $row['image_path'] && file_exists($row['image_path'])) {
        unlink($row['image_path']);
    }
    $conn->query("DELETE FROM menus WHERE id=$menu_id");
    header('Location: grand_menu.php' . (isset($_GET['cat']) ? '?cat='.(int)$_GET['cat'] : ''));
    exit;
}

// ── 读取分类 ─────────────────────────────────────────────────
$categories = $conn->query("SELECT * FROM menu_categories WHERE menu_type='$menu_type' ORDER BY sort_order ASC, id ASC");
$cats = [];
while ($row = $categories->fetch_assoc()) $cats[] = $row;

// ── 当前选中分类 ──────────────────────────────────────────────
$active_cat_id   = isset($_GET['cat']) ? (int)$_GET['cat'] : ($cats[0]['id'] ?? 0);
$active_cat_name = 'Zensai';
foreach ($cats as $c) {
    if ($c['id'] == $active_cat_id) { $active_cat_name = $c['category_name']; break; }
}

// ── 读取菜单项目 ──────────────────────────────────────────────
$menus = [];
if ($active_cat_id) {
    $res = $conn->query("SELECT * FROM menus WHERE category_id=$active_cat_id AND menu_type='$menu_type' ORDER BY sort_order ASC, id ASC");
    while ($row = $res->fetch_assoc()) $menus[] = $row;
}

// ── 各分类数量 ─────────────────────────────────────────────────
$counts = [];
$res2 = $conn->query("SELECT category_id, COUNT(*) as cnt FROM menus WHERE menu_type='$menu_type' GROUP BY category_id");
while ($r = $res2->fetch_assoc()) $counts[$r['category_id']] = $r['cnt'];
?>
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Grand Menu – Tokyo Japanese Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@300;400;500;700&family=Cinzel:wght@400;600&display=swap" rel="stylesheet">
<?php include 'style.php'; ?>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">

  <!-- TOPBAR -->
  <div class="topbar">
    <div class="topbar-left" style="display:flex;align-items:center;gap:10px;">
      <h2>🍽️ 菜单管理</h2>
      <div class="breadcrumb">视觉管理 › 菜单管理 › <span>Grand Menu</span></div>
    </div>
    <div class="topbar-right">
      <span style="font-size:12.5px;color:var(--muted)">管理员 Admin</span>
      <div class="avatar">A</div>
    </div>
  </div>

  <div class="page">

    <!-- MENU TYPE TABS -->
    <div class="menu-tabs">
      <a href="grand_menu.php" class="menu-tab active">
        <div class="tab-icon">🍽️</div>
        <div class="tab-label">
          <div class="t1">Grand Menu</div>
          <div class="t2">主菜单 · <?= array_sum($counts) ?> 项</div>
        </div>
      </a>
      <a href="sushi_menu.php" class="menu-tab">
        <div class="tab-icon">🍣</div>
        <div class="tab-label">
          <div class="t1">Sushi Menu</div>
          <div class="t2">寿司专区</div>
        </div>
      </a>
    </div>

    <!-- ALERT -->
    <?php if ($msg): ?>
      <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
    <?php endif; ?>

    <!-- CONTENT GRID -->
    <div class="content-grid">

      <!-- LEFT: CATEGORIES -->
      <div>
        <div class="cat-panel">
          <div class="cat-panel-header">
            <span>📂 分类管理</span>
            <a href="#" class="btn-add-cat" onclick="toggleCatForm();return false;">+</a>
          </div>

          <!-- Add Category Form (hidden by default) -->
          <div id="cat-form" style="display:none;padding:12px;border-bottom:1px solid var(--border);background:#fffbf2;">
            <form method="POST">
              <div class="fg" style="margin-bottom:8px;">
                <label>分类名称</label>
                <input type="text" name="category_name" placeholder="例：Zensai" required>
              </div>
              <div class="fg" style="margin-bottom:10px;">
                <label>排序</label>
                <input type="number" name="sort_order" value="0" min="0">
              </div>
              <button type="submit" name="add_category" class="btn-primary" style="width:100%;justify-content:center;font-size:12.5px;padding:8px;">✅ 新增分类</button>
            </form>
          </div>

          <div class="cat-list">
            <?php foreach ($cats as $c): ?>
              <div style="display:flex;align-items:center;gap:4px;">
                <a href="grand_menu.php?cat=<?= $c['id'] ?>" class="cat-item <?= $c['id']==$active_cat_id ? 'active' : '' ?>" style="flex:1;">
                  <?= htmlspecialchars($c['category_name']) ?>
                  <span class="cat-count"><?= $counts[$c['id']] ?? 0 ?></span>
                </a>
                <a href="grand_menu.php?del_cat=<?= $c['id'] ?>"
                   onclick="return confirm('删除分类「<?= htmlspecialchars($c['category_name']) ?>」？此分类下的菜单也会一并删除。')"
                   style="color:#c0392b;font-size:13px;padding:4px;opacity:0.5;text-decoration:none;"
                   title="删除分类">✕</a>
              </div>
            <?php endforeach; ?>
            <?php if (empty($cats)): ?>
              <p style="font-size:12px;color:var(--muted);padding:12px;text-align:center;">暂无分类<br>点 + 新增</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- RIGHT -->
      <div class="right-panel">

        <!-- UPLOAD FORM -->
        <div class="card">
          <div class="card-header">
            <h3>➕ 新增菜单项目</h3>
            <div class="subtitle">当前分类：<?= htmlspecialchars($active_cat_name) ?></div>
          </div>
          <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
              <input type="hidden" name="category_id" value="<?= $active_cat_id ?>">

              <div class="form-row">
                <div class="fg">
                  <label>菜单编号</label>
                  <input type="text" name="item_code" placeholder="H01">
                </div>
                <div class="fg">
                  <label>英文名称 *</label>
                  <input type="text" name="item_name" placeholder="ATSUYAKI TAMAGO" required>
                </div>
                <div class="fg">
                  <label>中文名称</label>
                  <input type="text" name="item_name_cn" placeholder="厚玉子烧">
                </div>
              </div>

              <div class="form-row-2">
                <div class="fg">
                  <label>描述 / 食材</label>
                  <input type="text" name="item_desc" placeholder="Japanese Omelette">
                </div>
                <div class="fg">
                  <label>价格 (RM)</label>
                  <input type="text" name="price" placeholder="13.90">
                </div>
              </div>

              <div class="form-row-2" style="margin-bottom:14px;">
                <div class="fg">
                  <label>状态</label>
                  <select name="status">
                    <option value="published">✅ 已发布</option>
                    <option value="draft">📝 草稿</option>
                  </select>
                </div>
              </div>

              <div class="upload-zone" id="drop-zone">
                <input type="file" name="menu_image" id="file-input" accept="image/*">
                <span class="uz-icon">🖼️</span>
                <p>拖拽图片到这里，或 <strong>点击上传</strong></p>
                <p class="hint">支持 JPG · PNG · WEBP · 最大 5MB</p>
              </div>

              <div id="preview-wrap">
                <img id="preview-img" src="" alt="">
                <p id="preview-name"></p>
              </div>

              <div class="btn-row">
                <button type="submit" name="add_menu" class="btn-primary">📤 上传并保存</button>
                <button type="reset" class="btn-secondary" onclick="document.getElementById('preview-wrap').style.display='none'">↩ 清除</button>
              </div>
            </form>
          </div>
        </div>

        <!-- TABLE -->
        <div class="card" style="margin-bottom:0;">
          <div class="table-header">
            <h3>📋 <?= htmlspecialchars($active_cat_name) ?> <span class="badge"><?= count($menus) ?> 项</span></h3>
            <input type="text" placeholder="🔍 搜索菜单名称..." id="search-input"
              style="padding:6px 12px;border:1.5px solid var(--border);border-radius:7px;font-size:12.5px;font-family:inherit;outline:none;width:180px;background:#fdfaf5;">
          </div>
          <table>
            <thead>
              <tr>
                <th>菜单项目</th>
                <th>价格</th>
                <th>图片</th>
                <th>状态</th>
                <th>上传时间</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody id="menu-tbody">
              <?php if (empty($menus)): ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--muted);font-size:13px;">暂无菜单项目，请使用上方表单新增 ☝️</td></tr>
              <?php else: ?>
                <?php foreach ($menus as $m): ?>
                <tr>
                  <td>
                    <div style="display:flex;align-items:center;gap:12px;">
                      <?php if ($m['image_path'] && file_exists($m['image_path'])): ?>
                        <img src="<?= htmlspecialchars($m['image_path']) ?>" class="item-thumb" alt="">
                      <?php else: ?>
                        <div class="item-thumb-placeholder">🍽️</div>
                      <?php endif; ?>
                      <div>
                        <?php if ($m['item_code']): ?>
                          <div class="item-code"><?= htmlspecialchars($m['item_code']) ?></div>
                        <?php endif; ?>
                        <div class="item-name"><?= htmlspecialchars($m['item_name']) ?></div>
                        <div class="item-name-cn">
                          <?= htmlspecialchars($m['item_name_cn']) ?>
                          <?php if ($m['item_desc']): ?> · <?= htmlspecialchars($m['item_desc']) ?><?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="price-cell"><?= $m['price'] ? 'RM '.number_format($m['price'],2) : '—' ?></td>
                  <td>
                    <?php if ($m['image_path']): ?>
                      <span style="color:var(--green);font-size:12px;">✅ 已上传</span>
                    <?php else: ?>
                      <span style="color:var(--muted);font-size:12px;">— 无图片</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="status-badge <?= $m['status']==='published' ? 's-pub' : 's-draft' ?>">
                      <span class="s-dot"></span>
                      <?= $m['status']==='published' ? '已发布' : '草稿' ?>
                    </span>
                  </td>
                  <td style="font-size:12px;color:var(--muted);"><?= date('Y-m-d', strtotime($m['created_at'])) ?></td>
                  <td>
                    <div class="act-btns">
                      <a href="edit_menu.php?id=<?= $m['id'] ?>" class="btn-sm btn-edit-sm">✏️ 编辑</a>
                      <a href="grand_menu.php?del_menu=<?= $m['id'] ?>&cat=<?= $active_cat_id ?>"
                         class="btn-sm btn-del-sm"
                         onclick="return confirm('确认删除「<?= htmlspecialchars($m['item_name']) ?>」？')">🗑️ 删除</a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div><!-- /right-panel -->
    </div><!-- /content-grid -->
  </div><!-- /page -->
</div><!-- /main -->

<script>
// Sidebar submenu
document.querySelectorAll('.has-sub > a').forEach(a => {
  a.addEventListener('click', e => { e.preventDefault(); a.parentElement.classList.toggle('open'); });
});

// Image preview
document.getElementById('file-input').addEventListener('change', function(){
  const f = this.files[0]; if(!f) return;
  const r = new FileReader();
  r.onload = e => {
    document.getElementById('preview-img').src = e.target.result;
    document.getElementById('preview-name').textContent = '✅ ' + f.name;
    document.getElementById('preview-wrap').style.display = 'block';
  };
  r.readAsDataURL(f);
});

// Drag & drop visual
const dz = document.getElementById('drop-zone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor='var(--gold)'; dz.style.background='#fffbf0'; });
dz.addEventListener('dragleave', () => { dz.style.borderColor='var(--border)'; dz.style.background='#fdfaf5'; });
dz.addEventListener('drop', e => { e.preventDefault(); dz.style.borderColor='var(--border)'; dz.style.background='#fdfaf5'; });

// Category form toggle
function toggleCatForm(){
  const f = document.getElementById('cat-form');
  f.style.display = f.style.display === 'none' ? 'block' : 'none';
}

// Search filter
document.getElementById('search-input').addEventListener('input', function(){
  const q = this.value.toLowerCase();
  document.querySelectorAll('#menu-tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
</body>
</html>
