<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>碗碟库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f1dfbc 0%, #e8d5a3 100%);
            color: #111827;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 24px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 48px;
        }
        
        .header h1 {
            font-size: 64px;
            font-weight: bold;
            color: #583e04;
            margin-bottom: 16px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .header p {
            font-size: 20px;
            color: #6b7280;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* 卡片网格 */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
            margin-bottom: 48px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            border: 2px solid #583e04;
            box-shadow: 0 8px 24px rgba(88, 62, 4, 0.15);
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #583e04, #8b6914);
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 40px rgba(88, 62, 4, 0.25);
        }

        .card-icon {
            font-size: 64px;
            color: #583e04;
            margin-bottom: 24px;
            display: block;
        }

        .card h3 {
            font-size: 24px;
            font-weight: 600;
            color: #583e04;
            margin-bottom: 16px;
        }

        .card p {
            font-size: 16px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .card-btn {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: #583e04;
            color: white;
            padding: 16px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            width: 100%;
            justify-content: center;
        }

        .card-btn:hover {
            background: #462d03;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(88, 62, 4, 0.3);
        }

        .card-btn i {
            font-size: 20px;
        }

        /* 功能特性 */
        .features {
            background: white;
            border-radius: 16px;
            padding: 32px;
            border: 2px solid #583e04;
            box-shadow: 0 8px 24px rgba(88, 62, 4, 0.15);
        }

        .features h2 {
            font-size: 28px;
            font-weight: 600;
            color: #583e04;
            margin-bottom: 24px;
            text-align: center;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 16px;
            background: #f8f5eb;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .feature-icon {
            font-size: 24px;
            color: #583e04;
            margin-top: 4px;
        }

        .feature-content h4 {
            font-size: 16px;
            font-weight: 600;
            color: #583e04;
            margin-bottom: 8px;
        }

        .feature-content p {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.5;
        }

        /* 返回按钮 */
        .back-button {
            position: fixed;
            top: 24px;
            left: 24px;
            background-color: #583e04;
            color: white;
            font-weight: 500;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
            z-index: 1000;
        }
        
        .back-button:hover {
            background-color: #462d03;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(88, 62, 4, 0.2);
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            .container {
                padding: 24px 16px;
            }
            
            .header h1 {
                font-size: 48px;
            }
            
            .header p {
                font-size: 18px;
            }
            
            .card-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            
            .card {
                padding: 24px;
            }
            
            .card-icon {
                font-size: 48px;
            }
            
            .card h3 {
                font-size: 20px;
            }
            
            .features {
                padding: 24px;
            }
            
            .features h2 {
                font-size: 24px;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .back-button {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 24px;
                display: inline-flex;
            }
        }
    </style>
</head>
<body>
    <a href="/" class="back-button">
        <i class="fas fa-arrow-left"></i>
        返回首页
    </a>
    
    <div class="container">
        <div class="header">
            <h1>碗碟库存管理系统</h1>
            <p>高效管理餐厅碗碟库存，支持多地点库存跟踪，实时更新库存状态</p>
        </div>
        
        <div class="card-grid">
            <div class="card">
                <i class="fas fa-upload card-icon"></i>
                <h3>碗碟信息上传</h3>
                <p>上传碗碟照片、设置尺寸价格、选择分类，支持单个上传和批量CSV导入</p>
                <a href="dishware_upload.php" class="card-btn">
                    <i class="fas fa-plus"></i>
                    开始上传
                </a>
            </div>
            
            <div class="card">
                <i class="fas fa-warehouse card-icon"></i>
                <h3>库存管理</h3>
                <p>查看所有碗碟信息，管理各地点库存数量，实时计算总库存价值</p>
                <a href="dishware_stock.php" class="card-btn">
                    <i class="fas fa-chart-bar"></i>
                    管理库存
                </a>
            </div>
        </div>
        
        <div class="features">
            <h2>系统功能特性</h2>
            <div class="features-grid">
                <div class="feature-item">
                    <i class="fas fa-camera feature-icon"></i>
                    <div class="feature-content">
                        <h4>照片管理</h4>
                        <p>支持上传碗碟照片，拖拽上传，自动预览功能</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-tags feature-icon"></i>
                    <div class="feature-content">
                        <h4>分类管理</h4>
                        <p>19种分类：AG、CU、DN、DR、IP、MA、ME、MU、OM、OT、SA、SU、SAR、SER、SET、TA、TE、WAN、YA</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-map-marker-alt feature-icon"></i>
                    <div class="feature-content">
                        <h4>多地点库存</h4>
                        <p>支持文化楼、中央、J1、J2、J3五个地点的库存管理</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-calculator feature-icon"></i>
                    <div class="feature-content">
                        <h4>自动计算</h4>
                        <p>自动计算总数量和总价值，实时更新库存状态</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-file-csv feature-icon"></i>
                    <div class="feature-content">
                        <h4>批量导入</h4>
                        <p>支持CSV文件批量导入碗碟信息，提高工作效率</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-search feature-icon"></i>
                    <div class="feature-content">
                        <h4>智能搜索</h4>
                        <p>支持按产品名称、编号、分类进行快速搜索和筛选</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-download feature-icon"></i>
                    <div class="feature-content">
                        <h4>数据导出</h4>
                        <p>支持导出CSV格式的库存报表，便于数据分析和备份</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-mobile-alt feature-icon"></i>
                    <div class="feature-content">
                        <h4>响应式设计</h4>
                        <p>完美适配桌面端和移动端，随时随地管理库存</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 添加一些交互效果
        document.addEventListener('DOMContentLoaded', function() {
            // 为卡片添加点击效果
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.addEventListener('click', function() {
                    const link = this.querySelector('.card-btn');
                    if (link) {
                        link.click();
                    }
                });
            });
            
            // 添加键盘导航支持
            document.addEventListener('keydown', function(e) {
                if (e.key === '1') {
                    document.querySelector('a[href="dishware_upload.php"]').click();
                } else if (e.key === '2') {
                    document.querySelector('a[href="dishware_stock.php"]').click();
                }
            });
        });
    </script>
</body>
</html>
