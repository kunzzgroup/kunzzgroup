<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>媒体管理 - KUNZZ HOLDINGS</title>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <link rel="stylesheet" href="../css/media_manager.css">
</head>
<body class="has-sidebar">
    <?php $basePath = '../'; include '../pages/sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>媒体管理中心</h1>
        </div>
        
        <div class="content">
            <a href="dashboard" class="back-btn">← 返回仪表板</a>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- 页面分类管理 -->
            <div class="media-section">
                <h2>背景音乐管理</h2>
                <div class="page-grid">
                    <a href="bgmusicupload" class="page-card">
                        <div class="page-icon"></div>
                        <h3>管理网站所有页面的背景音乐</h3>
                        <span class="page-arrow">→</span>
                    </a>
                </div>
            </div>

            <div class="media-section">
                <h2>首页管理</h2>
                <div class="page-grid">
                    <a href="homepage1upload" class="page-card">
                        <div class="page-icon"></div>
                        <h3>首页第一页</h3>
                        <p>管理首页背景视频/图片</p>
                        <span class="page-arrow">→</span>
                    </a>
                </div>
            </div>
            
            <div class="media-section">
                <h2>关于我们管理</h2>
                <div class="page-grid">
                    <a href="aboutpage1upload" class="page-card">
                        <div class="page-icon"></div>
                        <h3>关于我们第一页</h3>
                        <p>管理封面背景图片</p>
                        <span class="page-arrow">→</span>
                    </a>
                    <a href="aboutpage4upload" class="page-card">
                        <div class="page-icon"></div>
                        <h3>关于我们第四页</h3>
                        <p>管理发展历史图片</p>
                        <span class="page-arrow">→</span>
                    </a>
                </div>
            </div>
            
            <div class="media-section">
                <h2>旗下品牌管理</h2>
                <div class="page-grid">
                    <a href="tokyopage1upload" class="page-card">
                        <div class="page-icon"></div>
                        <h3>Tokyo 首页背景</h3>
                        <p>管理品牌页面首页背景图片</p>
                        <span class="page-arrow">→</span>
                    </a>
                    <a href="tokyopage5upload" class="page-card">
                        <div class="page-icon"></div>
                        <h3>Tokyo 位置信息</h3>
                        <p>管理总店分店地址电话信息</p>
                        <span class="page-arrow">→</span>
                    </a>
                </div>
            </div>
            
            <div class="media-section">
                <h2>加入我们管理</h2>
                <div class="page-grid">
                    <a href="joinpage1upload" class="page-card">
                        <div class="page-icon"></div>
                        <h3>加入我们页面</h3>
                        <p>管理招聘页面图片</p>
                        <span class="page-arrow">→</span>
                    </a>
                    <a href="joinpage2upload" class="page-card">
                        <div class="page-icon"></div>
                        <h3>我们的足迹照片</h3>
                        <p>管理34张公司活动照片</p>
                        <span class="page-arrow">→</span>
                    </a>
                    <a href="joinpage3upload" class="page-card">
                        <div class="page-icon"></div>
                        <h3>招聘资料</h3>
                        <p>管理招聘职位</p>
                        <span class="page-arrow">→</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/media_manager.js"></script>
</body>
</html>
