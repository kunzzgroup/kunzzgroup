
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$host = 'localhost';
$dbname = 'u857194726_kunzzgroup';
$dbuser = 'u857194726_kunzzgroup';
$dbpass = 'Kholdings1688@';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $bank_name = trim($_POST['bank_name']);
    $bank_account = trim($_POST['bank_account']);
    $home_address = trim($_POST['home_address']);
    $current_address = trim($_POST['current_address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $postcode = trim($_POST['postcode']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $gender = trim($_POST['gender']);

    if ($username === '') {
        $error = '用户名不能为空';
    } else {
        $stmt = $conn->prepare("UPDATE users SET username=?, phone_number=?, bank_name=?, bank_account=?, home_address=?, current_address=?, city=?, state=?, postcode=?, date_of_birth=?, gender=? WHERE id=?");
        $stmt->bind_param("sssssssssssi", $username, $phone, $bank_name, $bank_account, $home_address, $current_address, $city, $state, $postcode, $date_of_birth, $gender, $user_id);
        if ($stmt->execute()) {
            $success = "更新成功！";
            $_SESSION['username'] = $username;
        } else {
            $error = "更新失败，请稍后再试。";
        }
        $stmt->close();
    }
}

$stmt = $conn->prepare("SELECT username, email, phone_number, bank_name, bank_account, home_address, current_address, city, state, postcode, position, date_of_birth, gender FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>编辑个人资料 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="edit-section">
    <div class="edit-title">编辑个人资料</div>

    <?php if ($error): ?>
      <p style="color: red; text-align: center;"><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
      <p style="color: green; text-align: center;"><?= htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form method="POST" action="edit_profile.php">
      <div class="edit-section-title">联系资料</div>
      <div class="edit-grid">
        <div class="edit-group">
          <label class="edit-label">用户名:</label>
          <input class="edit-input" type="text" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
        </div>
        <div class="edit-group">
          <label class="edit-label">邮箱（不可修改）:</label>
          <input class="edit-input" type="email" value="<?= htmlspecialchars($user['email']); ?>" readonly>
        </div>
        <div class="edit-group">
          <label class="edit-label">联络号码:</label>
          <input class="edit-input" type="text" name="phone" value="<?= htmlspecialchars($user['phone_number']); ?>">
        </div>
        <div class="edit-group">
          <label class="edit-label">银行名称:</label>
          <input class="edit-input" type="text" name="bank_name" value="<?= htmlspecialchars($user['bank_name']); ?>">
        </div>
        <div class="edit-group">
          <label class="edit-label">银行账号:</label>
          <input class="edit-input" type="text" name="bank_account" value="<?= htmlspecialchars($user['bank_account']); ?>">
        </div>
        <div class="edit-group">
          <label class="edit-label">家庭地址:</label>
          <input class="edit-input" type="text" name="home_address" value="<?= htmlspecialchars($user['home_address']); ?>">
        </div>
        <div class="edit-group">
          <label class="edit-label">现居地址:</label>
          <input class="edit-input" type="text" name="current_address" value="<?= htmlspecialchars($user['current_address']); ?>">
        </div>
        <div class="edit-group">
          <label class="edit-label">城市:</label>
          <input class="edit-input" type="text" name="city" value="<?= htmlspecialchars($user['city']); ?>">
        </div>
        <div class="edit-group">
          <label class="edit-label">州属:</label>
          <input class="edit-input" type="text" name="state" value="<?= htmlspecialchars($user['state']); ?>">
        </div>
        <div class="edit-group">
          <label class="edit-label">邮编:</label>
          <input class="edit-input" type="text" name="postcode" value="<?= htmlspecialchars($user['postcode']); ?>">
        </div>
      </div>

      <div class="edit-section-title">个人资料</div>
      <div class="edit-grid">
        <div class="edit-group">
          <label class="edit-label">职位（不可修改）:</label>
          <input class="edit-input" type="text" value="<?= htmlspecialchars($user['position']); ?>" readonly>
        </div>
        <div class="edit-group">
          <label class="edit-label">出生日期:</label>
          <input class="edit-input" type="date" name="date_of_birth" value="<?= htmlspecialchars($user['date_of_birth']); ?>">
        </div>
        <div class="edit-group">
          <label class="edit-label">性别:</label>
          <select class="edit-input" name="gender">
            <option value="">请选择</option>
            <option value="男" <?= $user['gender'] === '男' ? 'selected' : '' ?>>男</option>
            <option value="女" <?= $user['gender'] === '女' ? 'selected' : '' ?>>女</option>
            <option value="其他" <?= $user['gender'] === '其他' ? 'selected' : '' ?>>其他</option>
          </select>
        </div>
      </div>

      <div class="edit-actions">
        <button class="edit-button" type="submit">保存修改</button>
        <a class="edit-link" href="dashboard.php">返回</a>
      </div>
    </form>
  </div>
</body>
</html>
