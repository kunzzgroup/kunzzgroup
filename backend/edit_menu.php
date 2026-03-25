<?php
require_once __DIR__ . '/permission_guard.php';
require_once __DIR__ . '/heic_convert.php';
requirePermission('visual');

require_once 'config.php';

$id  = (int)($_GET['id'] ?? 0);
$msg = '';

// 读取当前数据
$menu = $conn->query("SELECT * FROM menus WHERE id=$id")->fetch_assoc();
if (!$menu) { header('Location: grand_menu.php'); exit; }

$menu_type = $menu['menu_type'];
$back_url  = $menu_type === 'grand' ? 'grand_menu.php' : 'sushi_menu.php';

// 读取所有分类
$cats = [];
$res  = $conn->query("SELECT * FROM menu_categories WHERE menu_type='$menu_type' ORDER BY sort_order ASC");
while ($r = $res->fetch_assoc()) $cats[] = $r;

// 处理更新
if (isset($_POST['update_menu'])) {
    $cat_id       = (int)$_POST['category_id'];
    $item_code    = $conn->real_escape_string(trim($_POST['item_code']));
    $item_name    = $conn->real_escape_string(trim($_POST['item_name']));
    $item_name_cn = $conn->real_escape_string(trim($_POST['item_name_cn']));
    $item_desc    = $conn->real_escape_string(trim($_POST['item_desc']));
    $price        = (float)$_POST['price'];
    $status       = $_POST['status'] === 'draft' ? 'draft' : 'published';
    $image_path   = $menu['image_path'];

    // 新图片上传
    if (!empty($_FILES['menu_image']['name'])) {
        $upload_dir = 'uploads/' . $menu_type . '/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $ext     = strtolower(pathinfo($_FILES['menu_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','heic','heif'];
        if (in_array($ext, $allowed) && $_FILES['menu_image']['size'] <= 5 * 1024 * 1024) {
            $filename = time() . '_' . uniqid() . '.' . $ext;
            $target   = $upload_dir . $filename;
            if (move_uploaded_file(<?php
require_once __DIR__ . '/permission_guard.php';
require_once __DIR__ . '/heic_convert.php';
requirePermission('visual');

require_once 'config.php';

$id  = (int)($_GET['id'] ?? 0);
$msg = '';

// 读取当前数据
$menu = $conn->query("SELECT * FROM menus WHERE id=$id")->fetch_assoc();
if (!$menu) { header('Location: grand_menu.php'); exit; }

$menu_type = $menu['menu_type'];
$back_url  = $menu_type === 'grand' ? 'grand_menu.php' : 'sushi_menu.php';

// 读取所有分类
$cats = [];
$res  = $conn->query("SELECT * FROM menu_categories WHERE menu_type='$menu_type' ORDER BY sort_order ASC");
while ($r = $res->fetch_assoc()) $cats[] = $r;

// 处理更新
if (isset($_POST['update_menu'])) {
    $cat_id       = (int)$_POST['category_id'];
    $item_code    = $conn->real_escape_string(trim($_POST['item_code']));
    $item_name    = $conn->real_escape_string(trim($_POST['item_name']));
    $item_name_cn = $conn->real_escape_string(trim($_POST['item_name_cn']));
    $item_desc    = $conn->real_escape_string(trim($_POST['item_desc']));
    $price        = (float)$_POST['price'];
    $status       = $_POST['status'] === 'draft' ? 'draft' : 'published';
    $image_path   = $menu['image_path'];

    // 新图片上传
    if (!empty($_FILES['menu_image']['name'])) {
        $upload_dir = 'uploads/' . $menu_type . '/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $ext     = strtolower(pathinfo($_FILES['menu_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','heic','heif'];
        if (in_array($ext, $allowed) && $_FILES['menu_image']['size'] <= 5 * 1024 * 1024) {
            $filename = time() . '_' . uniqid() . '.' . $ext;
            $target   = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['menu_image']['tmp_name'], $target)) {
                // 删除旧图片
                if ($image_path && file_exists($image_path)) unlink($image_path);
                $image_path = $target;
            }
        }
    }

    $conn->query("UPDATE menus SET
        category_id='$cat_id',
        item_code='$item_code',
        item_name='$item_name',
        item_name_cn='$item_name_cn',
        item_desc='$item_desc',
        price=$price,
        image_path='$image_path',
        status='$status'
        WHERE id=$id");

    $msg = '✅ 更新成功！';
    $menu = $conn->query("SELECT * FROM menus WHERE id=$id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>编辑菜单 – Tokyo Japanese Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@300;400;500;700&family=Cinzel:wght@400;600&display=swap" rel="stylesheet">
<?php include 'style.php'; ?>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
  <div class="topbar">
    <div class="topbar-left" style="display:flex;align-items:center;gap:10px;">
      <h2>✏️ 编辑菜单项目</h2>
      <div class="breadcrumb">菜单管理 › <?= $menu_type==='grand'?'Grand Menu':'Sushi Menu' ?> › <span>编辑</span></div>
    </div>
    <div class="topbar-right">
      <a href="<?= $back_url ?>" class="btn-secondary" style="font-size:13px;">← 返回列表</a>
      <div class="avatar">A</div>
    </div>
  </div>

  <div class="page" style="max-width:800px;">

    <?php if ($msg): ?>
      <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h3>✏️ 编辑：<?= htmlspecialchars($menu['item_name']) ?></h3>
        <div class="subtitle">ID: #<?= $menu['id'] ?> · <?= $menu_type==='grand'?'Grand Menu':'Sushi Menu' ?></div>
      </div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data">

          <div class="form-row-2" style="margin-bottom:14px;">
            <div class="fg">
              <label>分类</label>
              <select name="category_id">
                <?php foreach ($cats as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= $c['id']==$menu['category_id']?'selected':'' ?>>
                    <?= htmlspecialchars($c['category_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label>菜单编号</label>
              <input type="text" name="item_code" value="<?= htmlspecialchars($menu['item_code']) ?>" placeholder="H01">
            </div>
          </div>

          <div class="form-row-2" style="margin-bottom:14px;">
            <div class="fg">
              <label>英文名称 *</label>
              <input type="text" name="item_name" value="<?= htmlspecialchars($menu['item_name']) ?>" required>
            </div>
            <div class="fg">
              <label>中文名称</label>
              <input type="text" name="item_name_cn" value="<?= htmlspecialchars($menu['item_name_cn']) ?>">
            </div>
          </div>

          <div class="form-row-2" style="margin-bottom:14px;">
            <div class="fg">
              <label>描述 / 食材</label>
              <input type="text" name="item_desc" value="<?= htmlspecialchars($menu['item_desc']) ?>">
            </div>
            <div class="fg">
              <label>价格 (RM)</label>
              <input type="text" name="price" value="<?= $menu['price'] ?>">
            </div>
          </div>

          <div class="form-row-2" style="margin-bottom:14px;">
            <div class="fg">
              <label>状态</label>
              <select name="status">
                <option value="published" <?= $menu['status']==='published'?'selected':'' ?>>✅ 已发布</option>
                <option value="draft"     <?= $menu['status']==='draft'?'selected':'' ?>>📝 草稿</option>
              </select>
            </div>
          </div>

          <!-- Current image -->
          <?php if ($menu['image_path'] && file_exists($menu['image_path'])): ?>
            <div style="margin-bottom:14px;">
              <p style="font-size:12px;color:var(--muted);margin-bottom:8px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">当前图片</p>
              <img src="<?= htmlspecialchars($menu['image_path']) ?>"
                   style="max-height:140px;border-radius:8px;border:2px solid var(--gold);">
            </div>
          <?php endif; ?>

          <div class="fg" style="margin-bottom:16px;">
            <label>更换图片（留空保留原图）</label>
            <div class="upload-zone" id="drop-zone">
              <input type="file" name="menu_image" id="file-input" accept="image/*">
              <span class="uz-icon">🖼️</span>
              <p>拖拽新图片，或 <strong>点击上传</strong></p>
              <p class="hint">支持 JPG · PNG · WEBP · HEIC（自动转换）· 最大 5MB</p>
            </div>
            <div id="preview-wrap">
              <img id="preview-img" src="" alt="">
              <p id="preview-name"></p>
            </div>
          </div>

          <div class="btn-row">
            <button type="submit" name="update_menu" class="btn-primary">💾 保存更新</button>
            <a href="<?= $back_url ?>" class="btn-secondary">取消</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.has-sub > a').forEach(a => {
  a.addEventListener('click', e => { e.preventDefault(); a.parentElement.classList.toggle('open'); });
});
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
</script>
</body>
</html>
FILES['menu_image']['tmp_name'], $target)) {
                // HEIC/HEIF 自动转换为 JPG
                $converted = convertHeicToJpg($target, $ext);
                if ($converted['converted']) {
                    $target = $converted['path'];
                }
                // 删除旧图片
                if ($image_path && file_exists($image_path)) unlink($image_path);
                $image_path = $target;
            }
        }
    }

    $conn->query("UPDATE menus SET
        category_id='$cat_id',
        item_code='$item_code',
        item_name='$item_name',
        item_name_cn='$item_name_cn',
        item_desc='$item_desc',
        price=$price,
        image_path='$image_path',
        status='$status'
        WHERE id=$id");

    $msg = '✅ 更新成功！';
    $menu = $conn->query("SELECT * FROM menus WHERE id=$id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>编辑菜单 – Tokyo Japanese Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@300;400;500;700&family=Cinzel:wght@400;600&display=swap" rel="stylesheet">
<?php include 'style.php'; ?>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
  <div class="topbar">
    <div class="topbar-left" style="display:flex;align-items:center;gap:10px;">
      <h2>✏️ 编辑菜单项目</h2>
      <div class="breadcrumb">菜单管理 › <?= $menu_type==='grand'?'Grand Menu':'Sushi Menu' ?> › <span>编辑</span></div>
    </div>
    <div class="topbar-right">
      <a href="<?= $back_url ?>" class="btn-secondary" style="font-size:13px;">← 返回列表</a>
      <div class="avatar">A</div>
    </div>
  </div>

  <div class="page" style="max-width:800px;">

    <?php if ($msg): ?>
      <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h3>✏️ 编辑：<?= htmlspecialchars($menu['item_name']) ?></h3>
        <div class="subtitle">ID: #<?= $menu['id'] ?> · <?= $menu_type==='grand'?'Grand Menu':'Sushi Menu' ?></div>
      </div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data">

          <div class="form-row-2" style="margin-bottom:14px;">
            <div class="fg">
              <label>分类</label>
              <select name="category_id">
                <?php foreach ($cats as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= $c['id']==$menu['category_id']?'selected':'' ?>>
                    <?= htmlspecialchars($c['category_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label>菜单编号</label>
              <input type="text" name="item_code" value="<?= htmlspecialchars($menu['item_code']) ?>" placeholder="H01">
            </div>
          </div>

          <div class="form-row-2" style="margin-bottom:14px;">
            <div class="fg">
              <label>英文名称 *</label>
              <input type="text" name="item_name" value="<?= htmlspecialchars($menu['item_name']) ?>" required>
            </div>
            <div class="fg">
              <label>中文名称</label>
              <input type="text" name="item_name_cn" value="<?= htmlspecialchars($menu['item_name_cn']) ?>">
            </div>
          </div>

          <div class="form-row-2" style="margin-bottom:14px;">
            <div class="fg">
              <label>描述 / 食材</label>
              <input type="text" name="item_desc" value="<?= htmlspecialchars($menu['item_desc']) ?>">
            </div>
            <div class="fg">
              <label>价格 (RM)</label>
              <input type="text" name="price" value="<?= $menu['price'] ?>">
            </div>
          </div>

          <div class="form-row-2" style="margin-bottom:14px;">
            <div class="fg">
              <label>状态</label>
              <select name="status">
                <option value="published" <?= $menu['status']==='published'?'selected':'' ?>>✅ 已发布</option>
                <option value="draft"     <?= $menu['status']==='draft'?'selected':'' ?>>📝 草稿</option>
              </select>
            </div>
          </div>

          <!-- Current image -->
          <?php if ($menu['image_path'] && file_exists($menu['image_path'])): ?>
            <div style="margin-bottom:14px;">
              <p style="font-size:12px;color:var(--muted);margin-bottom:8px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">当前图片</p>
              <img src="<?= htmlspecialchars($menu['image_path']) ?>"
                   style="max-height:140px;border-radius:8px;border:2px solid var(--gold);">
            </div>
          <?php endif; ?>

          <div class="fg" style="margin-bottom:16px;">
            <label>更换图片（留空保留原图）</label>
            <div class="upload-zone" id="drop-zone">
              <input type="file" name="menu_image" id="file-input" accept="image/*">
              <span class="uz-icon">🖼️</span>
              <p>拖拽新图片，或 <strong>点击上传</strong></p>
              <p class="hint">支持 JPG · PNG · WEBP · HEIC（自动转换）· 最大 5MB</p>
            </div>
            <div id="preview-wrap">
              <img id="preview-img" src="" alt="">
              <p id="preview-name"></p>
            </div>
          </div>

          <div class="btn-row">
            <button type="submit" name="update_menu" class="btn-primary">💾 保存更新</button>
            <a href="<?= $back_url ?>" class="btn-secondary">取消</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.has-sub > a').forEach(a => {
  a.addEventListener('click', e => { e.preventDefault(); a.parentElement.classList.toggle('open'); });
});
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
</script>
</body>
</html>
