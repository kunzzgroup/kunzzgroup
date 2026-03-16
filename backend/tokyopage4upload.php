<?php
if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$uploadDir = '../images/images/';
$configFile = '../media_config.json';

// Create directory if not exists
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Load current configuration
$config = [];
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true) ?: [];
}

$message = "";
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $types = ['tokyo_featured_1', 'tokyo_featured_2'];
    $updated = false;

    foreach ($types as $type) {
        // Handle text updates
        if (isset($_POST[$type . '_title'])) {
            if (!isset($config[$type])) $config[$type] = [];
            $config[$type]['title'] = $_POST[$type . '_title'];
            $config[$type]['description'] = $_POST[$type . '_description'] ?? '';
            $config[$type]['button_text'] = $_POST[$type . '_button_text'] ?? 'View Menu';
            $config[$type]['updated'] = date('Y-m-d H:i:s');
            $updated = true;
        }

        // Handle file upload
        if (isset($_FILES[$type]) && $_FILES[$type]['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES[$type]['tmp_name'];
            $fileName = basename($_FILES[$type]['name']);
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedImages = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($fileExtension, $allowedImages)) {
                $newFileName = $type . '_' . time() . '.' . $fileExtension;
                $targetPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    // Update config
                    $config[$type]['file'] = $targetPath;
                    $config[$type]['type'] = 'image';
                    $config[$type]['updated'] = date('Y-m-d H:i:s');
                    $updated = true;
                } else {
                    $error .= "Failed to move uploaded file for $type. ";
                }
            } else {
                $error .= "Invalid file style for $type. Allowed: jpg, jpeg, png, webp. ";
            }
        }
    }

    if ($updated) {
        if (file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT))) {
            $message = "Configuration updated successfully!";
        } else {
            $error = "Failed to save configuration.";
        }
    }
}

// Prepare current values for display
function getConfigValue($config, $key, $subKey, $default = '') {
    return isset($config[$key][$subKey]) ? $config[$key][$subKey] : $default;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tokyo Page 4 Management</title>
    <link rel="stylesheet" href="css/tokyopage1upload.css">
    <style>
        .card-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        .upload-card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 1px solid #eee;
        }
        .current-preview {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"], 
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <h1>Tokyo Section 4 Management (Featured)</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="card-container">
                <!-- Card 1 -->
                <div class="upload-card">
                    <h2>Featured Card 1</h2>
                    <div class="current-preview">
                        <?php 
                        $file1 = getConfigValue($config, 'tokyo_featured_1', 'file');
                        if ($file1):
                        ?>
                            <img src="<?php echo $file1; ?>?t=<?php echo time(); ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <span>No image uploaded</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Image</label>
                        <input type="file" name="tokyo_featured_1" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="tokyo_featured_1_title" value="<?php echo htmlspecialchars(getConfigValue($config, 'tokyo_featured_1', 'title', 'Sushi & Sashimi Menu')); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="tokyo_featured_1_description" value="<?php echo htmlspecialchars(getConfigValue($config, 'tokyo_featured_1', 'description', 'Fresh catch from Tokyo Bay')); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Button Text</label>
                        <input type="text" name="tokyo_featured_1_button_text" value="<?php echo htmlspecialchars(getConfigValue($config, 'tokyo_featured_1', 'button_text', 'View Sushi & Sashimi Menu')); ?>">
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="upload-card">
                    <h2>Featured Card 2</h2>
                    <div class="current-preview">
                        <?php 
                        $file2 = getConfigValue($config, 'tokyo_featured_2', 'file');
                        if ($file2):
                        ?>
                            <img src="<?php echo $file2; ?>?t=<?php echo time(); ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <span>No image uploaded</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Image</label>
                        <input type="file" name="tokyo_featured_2" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="tokyo_featured_2_title" value="<?php echo htmlspecialchars(getConfigValue($config, 'tokyo_featured_2', 'title', 'Grand Menu')); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="tokyo_featured_2_description" value="<?php echo htmlspecialchars(getConfigValue($config, 'tokyo_featured_2', 'description', 'Authentic Hot Dishes & Sets')); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Button Text</label>
                        <input type="text" name="tokyo_featured_2_button_text" value="<?php echo htmlspecialchars(getConfigValue($config, 'tokyo_featured_2', 'button_text', 'View Grand Menu')); ?>">
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 20px; text-align: center;">
                <button type="submit" class="btn-submit" style="padding: 12px 40px; background: #c9a227; color: #fff; border: none; border-radius: 25px; cursor: pointer; font-weight: bold;">Save Changes</button>
            </div>
        </form>
    </div>
</body>
</html>
