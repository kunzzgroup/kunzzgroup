<?php
session_start();
ob_start();

// 设置字符编码
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>企业蓝图 - KUNZZ HOLDINGS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #faf7f2;
            color: #000000;
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
        }

        /* 主内容区域样式 - sidebar.php 已经处理了 body 的 margin-left */
        /* 主内容容器 */
        .main-container {
            max-width: 1800px;
            margin: 0 auto;
            padding: clamp(16px, 1.25vw, 24px) 24px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* 标题区域 */
        .header {
            margin-bottom: clamp(16px, 1.67vw, 32px);
        }

        .header-title {
            font-size: clamp(20px, 2.6vw, 50px);
            font-weight: bold;
            color: #000000ff;
            margin-bottom: 0;
        }


        /* 内容区域 */
        .content-area {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* 卡片样式 */
        .card {
            background: rgba(255, 255, 255, 1);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .card-body {
            padding: clamp(16px, 1.25vw, 24px);
        }

        /* 幻灯片容器 */
        .slide-container {
            width: 100%;
            position: relative;
            background: rgba(255, 255, 255, 1);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            padding: clamp(24px, 2.08vw, 40px);
            min-height: 600px;
            position: relative;
            overflow: hidden;
        }



        /* 公司战略页面样式 */
        .strategic-slide {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            height: 100%;
            position: relative;
            z-index: 1;
            width: 100%;
        }

        .slide-header {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
            width: 100%;
        }

        .slide-header-line {
            width: 4px;
            height: 40px;
            background: #ff5c00;
            margin-right: 15px;
            flex-shrink: 0;
            border-radius: 2px;
        }

        .slide-title {
            font-size: clamp(18px, 1.56vw, 24px);
            font-weight: 700;
            color: #000000ff;
        }

        /* 公司信息区域 */
        .company-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .company-name {
            font-size: clamp(32px, 3.13vw, 48px);
            font-weight: 700;
            color: #000000ff;
            margin-bottom: 10px;
            letter-spacing: 2px;
            line-height: 1.2;
        }

        .company-subtitle {
            font-size: clamp(24px, 2.34vw, 36px);
            font-weight: 700;
            color: #000000ff;
            margin-bottom: 10px;
            letter-spacing: 1px;
            line-height: 1.2;
        }

        .company-subtitle-en {
            font-size: clamp(14px, 1.04vw, 18px);
            color: #6b7280;
            letter-spacing: 3px;
            margin-top: 10px;
            text-transform: uppercase;
            font-weight: 500;
        }

        /* Logo容器 */
        .logo-container {
            position: absolute;
            right: 60px;
            top: 60px;
            z-index: 2;
        }

        .logo {
            width: clamp(120px, 9.38vw, 150px);
            height: clamp(120px, 9.38vw, 150px);
            background: #ff5c00;
            border-radius: 50%;
            border: 3px solid #ffd700;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 4px 15px rgba(255, 92, 0, 0.3);
        }

        .logo-k {
            font-size: 60px;
            font-weight: 700;
            color: #fff;
            position: absolute;
            left: 30px;
            line-height: 1;
        }

        .logo-arrows {
            position: absolute;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .logo-arrow {
            width: 0;
            height: 0;
            border-left: 15px solid #fff;
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
        }


        /* 页面指示器 */
        .page-indicator {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #f3f4f6;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: clamp(12px, 0.84vw, 14px);
            color: #6b7280;
            font-weight: 600;
            z-index: 2;
            border: 1px solid #e5e7eb;
        }

        /* 自定义滚动条样式 */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Firefox 滚动条样式 */
        html {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }

        /* 响应式设计 */
        @media (max-width: 1024px) {
            .slide-container {
                padding: clamp(20px, 2.08vw, 40px);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }

            .main-content.sidebar-collapsed {
                margin-left: 0;
            }

            body.sidebar-collapsed .main-content {
                margin-left: 0;
            }

            .main-container {
                padding: 20px;
            }

            .slide-container {
                padding: clamp(16px, 1.25vw, 24px);
                min-height: 500px;
            }

            .logo-container {
                position: relative;
                right: auto;
                top: auto;
                margin: 20px 0;
                align-self: center;
            }

        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <!-- 主内容区域 -->
    <div class="main-content">
        <div class="main-container">
        <!-- 标题 -->
        <div class="header">
            <h1 class="header-title">企业蓝图</h1>
        </div>

        <!-- 内容区域 -->
        <div class="content-area">
            <!-- 幻灯片容器 -->
            <div class="slide-container">
                <!-- 公司战略页面 -->
                <div class="strategic-slide">
                    <div class="slide-header">
                        <div class="slide-header-line"></div>
                        <div class="slide-title">企业蓝图</div>
                    </div>
                    <div class="company-info">
                        <div class="company-name">KUNZZ HOLDINGS</div>
                        <div class="company-subtitle">SDN BHD 战略计划</div>
                        <div class="company-subtitle-en">CORPORATE STRATEGIC PLAN</div>
                    </div>
                    <div class="logo-container">
                        <div class="logo">
                            <div class="logo-k">K</div>
                            <div class="logo-arrows">
                                <div class="logo-arrow"></div>
                                <div class="logo-arrow"></div>
                            </div>
                        </div>
                    </div>
                    <div class="page-indicator">第1页</div>
                </div>
            </div>
        </div>
        </div>
    </div>

</body>
</html>
<?php
ob_end_flush();
?>

