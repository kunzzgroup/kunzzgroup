<?php
// 包含会话验证
require_once 'session_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <title>考核表单管理系统</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #faf7f2;
            min-height: 100vh;
        }

        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: clamp(16px, 1.25vw, 24px) 24px;
            height: 100vh;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            margin-bottom: clamp(10px, 1.56vw, 30px);
            position: relative;
        }

        .header h1 {
            color: #000000ff;
            font-size: clamp(20px, 2.6vw, 50px);
            margin-bottom: 10px;
            text-align: left;
        }

        .header h1::after {
            content: "";
            display: block;
            height: 3px;
            width: 100%;
            margin-top: 16px;
            background: linear-gradient(90deg, rgba(255,92,0,0) 0%, rgba(0, 0, 0, 1) 25%, rgba(0, 0, 0, 1) 75%, rgba(255,92,0,0) 100%);
        }

        .back-button {
            background-color: #6b7280;
            color: white;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
            position: absolute;
            top: 135px;
            right: 0;
        }

        .back-button:hover {
            background-color: #4b5563;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(107, 114, 128, 0.2);
        }

        .content-wrapper {
            flex: 1;
            display: flex;
            gap: 20px;
            overflow: hidden;
        }

        .sidebar {
            width: 280px;
            flex-shrink: 0;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow-y: auto;
            max-height: calc(100vh - 200px);
        }

        .main-content {
            flex: 1;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow-y: auto;
            max-height: calc(100vh - 200px);
        }

        .form-section {
            margin-bottom: 25px;
        }

        .form-section label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-section input,
        .form-section select {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .form-section input:focus,
        .form-section select:focus {
            outline: none;
            border-color: #ff5c00;
        }

        .btn-primary {
            background-color: #ff5c00;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: #e65100;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 92, 0, 0.3);
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        .evaluation-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            table-layout: fixed;
            font-size: 15px;
        }

        .evaluation-table thead {
            background: #ff5c00;
            color: white;
        }

        .evaluation-table th {
            padding: 18px 12px;
            text-align: center;
            font-weight: 600;
            border-right: 1px solid rgba(255,255,255,0.2);
            word-wrap: break-word;
            font-size: 15px;
        }

        .evaluation-table th:first-child {
            min-width: 200px;
            width: 200px;
            text-align: left;
            padding-left: 18px;
        }

        .evaluation-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
            text-align: center;
            word-wrap: break-word;
            font-size: 14px;
        }

        .evaluation-table td:first-child {
            text-align: left;
            padding-left: 18px;
            font-weight: 500;
        }

        .evaluation-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .employee-name {
            font-weight: 500;
            color: #333;
            font-size: 15px;
        }

        .score-input {
            width: 100%;
            padding: 12px 10px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            text-align: center;
            font-size: 15px;
            min-height: 40px;
            box-sizing: border-box;
        }

        .score-input:focus {
            outline: none;
            border-color: #ff5c00;
        }

        .evaluation-table th small {
            display: block;
            font-size: 12px;
            margin-top: 5px;
            font-weight: normal;
            opacity: 0.9;
        }

        .form-header {
            background: #ff5c00;
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
            margin: -30px -30px 30px -30px;
        }

        .form-header h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .form-info {
            display: flex;
            gap: 30px;
            font-size: 14px;
            opacity: 0.95;
        }

        .form-list {
            margin-top: 20px;
        }

        .form-item {
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .form-item:hover {
            border-color: #ff5c00;
            background-color: #fff5f0;
        }

        .form-item.active {
            border-color: #ff5c00;
            background-color: #fff5f0;
        }

        .form-item-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .form-item-meta {
            font-size: 12px;
            color: #6b7280;
        }

        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .message.success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        .message.error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }

        .add-employee-btn {
            margin-top: 15px;
            padding: 8px 16px;
            background-color: #10b981;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .add-employee-btn:hover {
            background-color: #059669;
        }

        .save-form-btn {
            transition: all 0.2s;
        }

        .save-form-btn:hover {
            background-color: #2563eb !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        }

        .print-btn:hover {
            background-color: #059669 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
        }

        .delete-btn {
            padding: 6px 12px;
            background-color: #ef4444;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-left: 10px;
        }

        .delete-btn:hover {
            background-color: #dc2626;
        }

        .print-btn {
            margin-top: 20px;
            padding: 15px 30px;
            background-color: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }

        .print-btn:hover {
            background-color: #059669;
        }

        /* PDF内容区域样式 */
        #pdf-content {
            display: none;
            background: white;
            padding: 40px 50px;
            width: 1300px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        #pdf-content .form-header {
            margin: 0 0 25px 0;
            border-radius: 0;
            padding: 25px;
        }

        #pdf-content .form-header h2 {
            font-size: 44px;
            margin-bottom: 18px;
            font-weight: 700;
        }

        #pdf-content .form-info {
            font-size: 18px;
            gap: 50px;
            font-weight: 500;
        }

        #pdf-content .evaluation-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
        }

        #pdf-content .evaluation-table th {
            border: 1px solid #000;
            padding: 18px 12px;
            text-align: center;
            font-weight: 600;
            background: #ff5c00;
            color: white;
            min-width: 140px;
            font-size: 16px;
        }

        #pdf-content .evaluation-table th:first-child {
            min-width: 220px;
            text-align: left;
            padding-left: 18px;
        }

        #pdf-content .evaluation-table td {
            border: 1px solid #000;
            padding: 18px 12px;
            text-align: center;
            min-height: 50px;
            min-width: 140px;
            font-size: 15px;
        }

        #pdf-content .evaluation-table td.employee-name {
            text-align: left;
            padding-left: 18px;
            font-weight: 500;
            min-width: 220px;
            font-size: 16px;
        }

        #pdf-content .evaluation-table thead {
            background: #ff5c00;
            color: white;
        }

        #pdf-content .evaluation-table tbody tr {
            height: 60px;
        }

        #pdf-content .evaluation-table small {
            display: block;
            font-size: 12px;
            margin-top: 5px;
            font-weight: normal;
            opacity: 0.9;
        }

        /* Kitchen评分标准表样式 */
        #pdf-content #kitchen-rubrics {
            margin-top: 40px;
        }

        #pdf-content #kitchen-rubrics table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        #pdf-content #kitchen-rubrics table th {
            background: #ff5c00;
            color: white;
            padding: 15px;
            border: 1px solid #000;
            font-weight: 600;
            text-align: center;
        }

        #pdf-content #kitchen-rubrics table td {
            padding: 15px;
            border: 1px solid #000;
            vertical-align: top;
            line-height: 1.6;
        }

        #pdf-content #kitchen-rubrics table td:first-child {
            width: 80px;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
        }

        /* 打印样式 */
        @media print {
            body {
                background: white;
            }

            .sidebar,
            .back-button,
            .save-form-btn,
            .print-btn,
            .header h1::after,
            #message {
                display: none !important;
            }

            .container {
                max-width: 100%;
                padding: 0;
                height: auto;
            }

            .header {
                margin-bottom: 20px;
            }

            .header h1 {
                font-size: 28px;
                margin-bottom: 10px;
            }

            .main-content {
                box-shadow: none;
                padding: 20px;
                overflow: visible;
                max-height: none;
            }

            .content-wrapper {
                display: block;
            }

            .evaluation-table {
                page-break-inside: auto;
            }

            .evaluation-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .evaluation-table thead {
                display: table-header-group;
            }

            .evaluation-table tfoot {
                display: table-footer-group;
            }

            .score-input {
                border: 1px solid #000;
                background: transparent;
                padding: 8px;
                min-height: 30px;
            }

            .form-header {
                margin: 0;
                border-radius: 0;
            }

            .form-header h2 {
                font-size: 32px;
            }

            /* 确保表格完整显示 */
            table {
                width: 100%;
                border-collapse: collapse;
            }

            th, td {
                border: 1px solid #333;
                padding: 10px;
            }

            /* 避免分页打断表格行 */
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
    <?php include 'sidebar.php'; ?>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>考核表单管理</h1>
        </div>

        <div id="message" class="message"></div>

        <div class="content-wrapper">
            <div class="sidebar">
                <div class="form-section">
                    <label for="restaurant">餐厅</label>
                    <select id="restaurant">
                        <option value="J1">J1分店</option>
                        <option value="J2">J2分店</option>
                        <option value="J3">J3分店</option>
                    </select>
                </div>

                <div class="form-section">
                    <label for="department">部门</label>
                    <select id="department">
                        <option value="">请选择部门</option>
                        <option value="service_line">服务部门 (SERVICE LINE)</option>
                        <option value="sushi_bar">寿司吧 (SUSHI BAR)</option>
                        <option value="kitchen">厨房 (KITCHEN)</option>
                    </select>
                </div>

                <div class="form-section">
                    <label for="evaluator_name">评估人姓名</label>
                    <input type="text" id="evaluator_name" placeholder="请输入评估人姓名">
                </div>

                <div class="form-section">
                    <label for="evaluation_date">评估日期</label>
                    <input type="date" id="evaluation_date" value="<?php echo date('Y-m-d'); ?>">
                </div>

                <button class="btn-primary" onclick="createNewForm()">
                    <i class="fas fa-plus"></i> 创建新表单
                </button>

                <div id="formButtons" style="margin-top: 100px; display: none; flex-direction: column; gap: 10px;">
                    <!-- 按钮将在这里动态显示 -->
                </div>
            </div>

            <div class="main-content" id="mainContent">
                <div style="text-align: center; padding: 60px 20px; color: #6b7280;">
                    <i class="fas fa-clipboard-list" style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;"></i>
                    <p>请选择或创建一个考核表单</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentFormId = null;
        let currentDepartment = '';
        let employees = [];
        let criteria = [];

        // Kitchen部门评分标准数据
        const kitchenRubrics = {
            '出餐速度与效率': [
                { score: 1, desc: '出餐速度与工作效率不足。经常出现延误、节奏混乱、跟不上前台节奏。动作较慢、流程不够顺畅，对火候和食材处理的控制不稳定，导致整个厨房节奏被拖慢。' },
                { score: 2, desc: '出餐速度不够稳定，容易在忙时造成延误或同一桌点单不同步，需要前台催单或提醒才能跟上。效率方面也有改进空间，动作有时会偏慢或不够流畅，导致整体流程变慢。' },
                { score: 3, desc: '出餐速度基本达标，能够完成订单，但节奏不够稳定，容易受到高峰期影响。偶尔会有轻微延误或需要前台提醒。工作效率不算慢，但动作和流程有时会有多余或重复的步骤。' },
                { score: 4, desc: '整体出餐速度和工作效率非常稳定，能够按时出餐，并可以配合前台节奏。高峰期可能会有轻微延误，但不影响整体流程。动作熟练，出错少，只是在忙时节奏可能会稍慢或需要额外确认。' },
                { score: 5, desc: '出餐速度非常稳定，总能在规定时间内完成订单，并能同步出餐同一桌的菜，几乎不需要前台催单。动作快而不乱，调味、火候和分量都准确一致。高峰期节奏也能保持清晰，不会有单被延误或堆积，整个厨房的进展非常顺畅。' }
            ],
            '食材标准与品质': [
                { score: 1, desc: '时常出现味道差异大、分量不一致、火候不准或外观不整齐的问题，甚至造成客诉风险。食材判断与食安意识也需加强。' },
                { score: 2, desc: '味道偶尔不稳定、火候不够精准，分量或摆盘也会出现不一致的问题。部分餐点在高峰期容易品质下降。' },
                { score: 3, desc: '食品品质达到基本要求，但在味道、分量或火候方面偶尔会出现不一致的情况。摆盘基本整齐，但有时会因赶单而略显仓促。' },
                { score: 4, desc: '食物品质整体表现良好，大多数餐点都符合味道与摆盘标准。火候控制稳定，分量也准确。偶尔在繁忙时段会有些微差异，但不影响整体品质。' },
                { score: 5, desc: '每一道料理在味道、火候、分量和外观上都能达到稳定标准。你对食材的新鲜度判断精准，不会让任何不合格食材进入餐点。即使在高峰期，你依然能保持良好的品质控制，不会为了速度牺牲标准。' }
            ],
            '卫生与整洁': [
                { score: 1, desc: '工作区常出现油渍、脏污、碎料堆积，工具未及时清洗，食材管理也偶有不符合标准的情况。这样的状况可能造成安全隐患或影响食品品质。' },
                { score: 2, desc: '工作区偶尔会出现油渍、水渍或碎料未及时处理，工具清洁也不够及时。食材处理时偶尔会出现混乱或分类不明确。' },
                { score: 3, desc: '能够基本保持工作区整洁，但有时会在忙碌时忘记清理台面、工具或地面。食材处理大多干净，但偶尔有摆放不整齐的情况。' },
                { score: 4, desc: '工作区保持干净，工具也能及时清洗与归位。多数时候都能维持整洁，只是在高峰期偶尔会忽略一些小细节（如桌面小碎料或酱汁痕迹）。' },
                { score: 5, desc: '工作区始终保持干净，不论多忙都能做到随做随清，桌面、工具和地面都很整齐。食材处理细心、生熟分开，绝对不会使用不合格食材。油炸、炒锅、冷台等区域都被你维护得很专业，让人看见就放心。' }
            ],
            '工作态度': [
                { score: 1, desc: '时常出现拖延、敷衍、需要不断提醒才能完成工作。情绪不稳定，高峰期容易发脾气或影响团队气氛。主动性不足、责任感弱，会造成团队负担。' },
                { score: 2, desc: '偶尔会出现心不在焉、动作拖延或需要重复提醒的情况。忙碌时容易带有情绪，影响团队合作。主动性不足，常常只完成基本工作，没有额外关注工位或团队整体状况。' },
                { score: 3, desc: '能完成被安排的任务，但需要提醒才会开始行动。偶尔会因为忙碌而出现焦躁或节奏不稳的情况。主动性方面也不够一致，有时会忽略工位整洁或团队需求。' },
                { score: 4, desc: '能专注完成自己的任务，也愿意配合团队。你有责任心，工作习惯稳定，情绪控制也不错。只是偶尔在忙碌时会略显紧绷，主动性也还有空间提升。' },
                { score: 5, desc: '无论忙或闲，你都能保持稳定的专注力和专业性，有责任感、可靠、不会拖延工作。你会主动协助同事、整理工位、补位，不需要主管提醒。情绪管理也非常好，高峰期依然能保持冷静。你愿意学习新技能，也愿意改进自己的不足。' }
            ],
            '团队合作': [
                { score: 1, desc: '容易与同事节奏脱节。缺乏补位意识，高峰期不够配合前台或寿司吧，沟通也不够及时。有时情绪会影响团队氛围。' },
                { score: 2, desc: '与同事的节奏容易对不上，忙的时候不太会主动协助其他岗位。你常专注自己的工作，忽略团队整体节奏，有时也需重复提醒才会行动。' },
                { score: 3, desc: '基本能与同事沟通并配合出餐。但偶尔只专注自己的部分，不太会主动支援。高峰期容易有点跟不上节奏，需要别人提醒。' },
                { score: 4, desc: '能够配合同事完成工作，也会在需要时帮忙，但主动性稍微不足。高峰期的情绪控制稳定，沟通清晰。偶尔在忙碌时会有点专注于自己的工作，但整体协作度仍然可靠。' },
                { score: 5, desc: '无论是厨房内部、前台还是寿司巴，你都能保持顺畅的沟通与节奏配合。忙碌时会主动补位、支援同事，不需要主管安排。你总是能快速回应、协助并维持稳定情绪，让整个厨房节奏更顺畅。' }
            ]
        };

        // 生成Kitchen评分标准表HTML
        function generateKitchenRubrics() {
            let html = '<div id="kitchen-rubrics" style="display: none;">';
            
            Object.keys(kitchenRubrics).forEach((title, index) => {
                html += `
                    <div style="margin-top: ${index > 0 ? '50px' : '30px'}; page-break-before: ${index > 0 ? 'always' : 'auto'};">
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 15px;">
                            <thead>
                                <tr>
                                    <th style="width: 80px; padding: 15px; text-align: center; background: #ff5c00; color: white; border: 1px solid #000; font-weight: 600;">分数</th>
                                    <th style="padding: 15px; text-align: center; background: #ff5c00; color: white; border: 1px solid #000; font-weight: 600; font-size: 18px;">${title}</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                kitchenRubrics[title].forEach(item => {
                    html += `
                        <tr>
                            <td style="width: 80px; padding: 15px; text-align: center; border: 1px solid #000; font-size: 18px; font-weight: 600; vertical-align: top;">${item.score}</td>
                            <td style="padding: 15px; border: 1px solid #000; line-height: 1.6; vertical-align: top;">${item.desc}</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            });
            
            html += '</div>';
            return html;
        }

        // 部门变化时加载员工和指标
        document.getElementById('department').addEventListener('change', function() {
            const dept = this.value;
            if (dept) {
                currentDepartment = dept;
                loadEmployees(dept);
                loadCriteria(dept);
            }
        });

        // 加载员工列表
        async function loadEmployees(department) {
            try {
                const restaurant = document.getElementById('restaurant').value;
                const response = await fetch(`../backend/schedule_api.php?action=get_employees&work_area=${department}&restaurant=${restaurant}`);
                const result = await response.json();
                if (result.success) {
                    employees = result.data.filter(emp => emp.is_active === 1 || emp.is_active === '1');
                }
            } catch (error) {
                console.error('加载员工失败:', error);
                showMessage('加载员工列表失败', 'error');
            }
        }

        // 加载考核指标
        async function loadCriteria(department) {
            try {
                const response = await fetch(`evaluation_form_api.php?action=get_criteria&department=${department}`);
                const result = await response.json();
                if (result.success) {
                    criteria = result.data;
                }
            } catch (error) {
                console.error('加载指标失败:', error);
            }
        }

        // 创建新表单
        function createNewForm() {
            const department = document.getElementById('department').value;
            const restaurant = document.getElementById('restaurant').value;
            const evaluatorName = document.getElementById('evaluator_name').value.trim();
            const evaluationDate = document.getElementById('evaluation_date').value;

            if (!department) {
                showMessage('请选择部门', 'error');
                return;
            }

            if (!evaluatorName) {
                showMessage('请输入评估人姓名', 'error');
                return;
            }

            if (!evaluationDate) {
                showMessage('请选择评估日期', 'error');
                return;
            }

            if (employees.length === 0) {
                showMessage('该部门暂无员工，请先添加员工', 'error');
                return;
            }

            currentFormId = null;
            renderForm();
            // 渲染表单后更新按钮
            setTimeout(() => {
                updateSidebarButtons();
            }, 200);
        }

        // 渲染表单
        function renderForm() {
            const department = document.getElementById('department').value;
            const restaurant = document.getElementById('restaurant').value;
            const evaluatorName = document.getElementById('evaluator_name').value;
            const evaluationDate = document.getElementById('evaluation_date').value;

            const deptNames = {
                'service_line': 'SERVICE LINE',
                'sushi_bar': 'SUSHI BAR',
                'kitchen': 'KITCHEN'
            };

            let html = `
                <div class="form-header">
                    <h2>TOKYO IZAKAYA</h2>
                    <div class="form-info">
                        <div><strong>Name:</strong> ${evaluatorName}</div>
                        <div><strong>Date:</strong> ${evaluationDate}</div>
                    </div>
                </div>
                <div style="background: #ff5c00; color: white; padding: 20px; margin: -30px -30px 25px -30px; text-align: center; font-weight: 600; font-size: 20px; letter-spacing: 1px;">
                    ${deptNames[department] || department.toUpperCase()}
                </div>
                <table class="evaluation-table">
                    <thead>
                        <tr>
                            <th>Name</th>
            `;

            // 添加考核指标列
            criteria.forEach(c => {
                html += `<th>${c.criteria_name_zh}<br><small>${c.criteria_name_en}</small></th>`;
            });

            html += `</tr></thead><tbody>`;

            // 添加员工行
            employees.forEach((emp, index) => {
                html += `<tr>
                    <td class="employee-name">${emp.name}</td>`;
                
                criteria.forEach((c, cIndex) => {
                    html += `<td>
                        <input type="text" 
                               class="score-input" 
                               data-employee-id="${emp.id || index}"
                               data-employee-name="${emp.name}"
                               data-criteria-index="${cIndex + 1}"
                               maxlength="20">
                    </td>`;
                });

                html += `</tr>`;
            });

            html += `</tbody></table>`;

            html += `
                <div id="pdf-content">
                    <div class="form-header">
                        <h2>TOKYO IZAKAYA</h2>
                        <div class="form-info">
                            <div><strong>Name:</strong> ${evaluatorName}</div>
                            <div><strong>Date:</strong> ${evaluationDate}</div>
                        </div>
                    </div>
                    <div style="background: #ff5c00; color: white; padding: 22px; text-align: center; font-weight: 600; font-size: 24px; margin-bottom: 30px; letter-spacing: 1.5px;">
                        ${deptNames[department] || department.toUpperCase()}
                    </div>
                    <table class="evaluation-table">
                        <thead>
                            <tr>
                                <th>Name</th>
            `;

            // 添加考核指标列（PDF版本）
            criteria.forEach(c => {
                html += `<th>${c.criteria_name_zh}<br><small style="font-size: 12px; margin-top: 5px; display: block; font-weight: normal; opacity: 0.9;">${c.criteria_name_en}</small></th>`;
            });

            html += `</tr></thead><tbody>`;

            // 添加员工行（PDF版本）
            employees.forEach((emp, index) => {
                html += `<tr>
                    <td class="employee-name">${emp.name}</td>`;
                
                criteria.forEach((c, cIndex) => {
                    html += `<td></td>`;
                });

                html += `</tr>`;
            });

            html += `</tbody></table>`;
            
            // 如果是kitchen部门，添加评分标准表到PDF内容
            if (department === 'kitchen') {
                const rubricsHtml = generateKitchenRubrics().replace('style="display: none;"', '');
                html += rubricsHtml;
            }
            
            html += `</div>`;

            document.getElementById('mainContent').innerHTML = html;
            
            // 更新侧边栏的按钮
            updateSidebarButtons();
            
            // 延迟填充PDF内容区域的数据
            setTimeout(() => {
                updatePDFContent();
            }, 200);
        }

        // 保存表单
        async function saveForm() {
            const department = document.getElementById('department').value;
            const restaurant = document.getElementById('restaurant').value;
            const evaluatorName = document.getElementById('evaluator_name').value.trim();
            const evaluationDate = document.getElementById('evaluation_date').value;

            if (!evaluatorName || !evaluationDate) {
                showMessage('请填写评估人姓名和评估日期', 'error');
                return;
            }

            // 收集表单数据
            const formData = {
                action: currentFormId ? 'update_form' : 'create_form',
                form_id: currentFormId,
                form_name: `${deptNames[department]} - ${evaluationDate}`,
                department: department,
                restaurant: restaurant,
                evaluator_name: evaluatorName,
                evaluation_date: evaluationDate,
                details: []
            };

            // 收集每个员工的评分
            const scoreInputs = document.querySelectorAll('.score-input');
            const employeeScores = {};

            scoreInputs.forEach(input => {
                const employeeId = input.getAttribute('data-employee-id');
                const employeeName = input.getAttribute('data-employee-name');
                const criteriaIndex = input.getAttribute('data-criteria-index');
                const score = input.value.trim();

                if (!employeeScores[employeeId]) {
                    employeeScores[employeeId] = {
                        employee_id: employeeId,
                        employee_name: employeeName,
                        scores: {}
                    };
                }
                employeeScores[employeeId].scores[`criteria_${criteriaIndex}`] = score;
            });

            // 转换为详情数组
            Object.values(employeeScores).forEach(emp => {
                const detail = {
                    employee_id: emp.employee_id,
                    employee_name: emp.employee_name,
                    ...emp.scores
                };
                formData.details.push(detail);
            });

            try {
                const response = await fetch('evaluation_form_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();
                if (result.success) {
                    showMessage('表单保存成功', 'success');
                    currentFormId = result.data.form_id || result.data.id;
                    updateSidebarButtons();
                } else {
                    showMessage(result.message || '保存失败', 'error');
                }
            } catch (error) {
                console.error('保存失败:', error);
                showMessage('保存失败: ' + error.message, 'error');
            }
        }

        // 更新侧边栏按钮
        function updateSidebarButtons() {
            const buttonArea = document.getElementById('formButtons');
            if (!buttonArea) return;

            // 显示按钮区域
            buttonArea.style.display = 'flex';
            buttonArea.style.flexDirection = 'column';
            buttonArea.style.gap = '10px';

            buttonArea.innerHTML = `
                <button class="save-form-btn" onclick="saveForm()" style="width: 100%; padding: 12px; background-color: #3b82f6; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-save"></i> 保存表单
                </button>
                <button class="print-btn" onclick="downloadPDF()" style="width: 100%; padding: 12px; background-color: #10b981; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-file-pdf"></i> 下载PDF
                </button>
            `;
        }

        // 显示消息
        function showMessage(message, type) {
            const messageEl = document.getElementById('message');
            messageEl.textContent = message;
            messageEl.className = `message ${type}`;
            messageEl.style.display = 'block';
            setTimeout(() => {
                messageEl.style.display = 'none';
            }, 3000);
        }

        const deptNames = {
            'service_line': 'SERVICE LINE',
            'sushi_bar': 'SUSHI BAR',
            'kitchen': 'KITCHEN'
        };

        // 更新PDF内容区域的数据
        function updatePDFContent() {
            const pdfContent = document.getElementById('pdf-content');
            if (!pdfContent) return;

            // 同步输入框的值到PDF内容区域
            const scoreInputs = document.querySelectorAll('.score-input');
            scoreInputs.forEach(input => {
                const employeeName = input.getAttribute('data-employee-name');
                const criteriaIndex = parseInt(input.getAttribute('data-criteria-index'));
                const value = input.value.trim();
                
                // 查找对应的PDF表格行
                const pdfRows = pdfContent.querySelectorAll('.evaluation-table tbody tr');
                pdfRows.forEach(row => {
                    const nameCell = row.querySelector('td.employee-name');
                    if (nameCell && nameCell.textContent.trim() === employeeName) {
                        const cells = row.querySelectorAll('td');
                        // criteriaIndex是从1开始的，cells[0]是姓名，cells[1]是第一个指标
                        const cellIndex = criteriaIndex; // criteriaIndex已经是1,2,3...对应cells[1],cells[2],cells[3]...
                        if (cells[cellIndex]) {
                            cells[cellIndex].textContent = value;
                        }
                    }
                });
            });
        }

        // 监听输入框变化，实时更新PDF内容
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('score-input')) {
                updatePDFContent();
            }
        });

        // 下载PDF
        async function downloadPDF() {
            const pdfContent = document.getElementById('pdf-content');
            if (!pdfContent) {
                showMessage('找不到表单内容', 'error');
                return;
            }

            // 更新PDF内容
            updatePDFContent();

            // 如果是kitchen部门，确保评分标准表已添加
            const department = document.getElementById('department').value;
            if (department === 'kitchen') {
                let existingRubrics = pdfContent.querySelector('#kitchen-rubrics');
                if (!existingRubrics) {
                    const rubricsHtml = generateKitchenRubrics().replace('style="display: none;"', '');
                    pdfContent.innerHTML += rubricsHtml;
                    existingRubrics = pdfContent.querySelector('#kitchen-rubrics');
                }
                if (existingRubrics) {
                    existingRubrics.style.display = 'block';
                }
            }

            // 显示加载提示
            showMessage('正在生成PDF，请稍候...', 'success');

            // 临时显示PDF内容
            const originalDisplay = pdfContent.style.display;
            pdfContent.style.display = 'block';
            
            // 确保内容已渲染
            await new Promise(resolve => setTimeout(resolve, 500));

            try {
                const { jsPDF } = window.jspdf;
                
                // 使用html2canvas将内容转换为图片，使用更高的scale以获得更清晰的图片
                const canvas = await html2canvas(pdfContent, {
                    scale: 2.5,
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#ffffff',
                    width: pdfContent.scrollWidth,
                    height: pdfContent.scrollHeight,
                    windowWidth: pdfContent.scrollWidth,
                    windowHeight: pdfContent.scrollHeight
                });

                const imgData = canvas.toDataURL('image/png', 1.0);
                
                // 创建PDF（A4尺寸，横向以容纳表格）
                const pdf = new jsPDF('l', 'mm', 'a4'); // 'l' = landscape (297mm x 210mm)
                const pdfWidth = pdf.internal.pageSize.getWidth(); // 297mm
                const pdfHeight = pdf.internal.pageSize.getHeight(); // 210mm
                
                // 计算图片尺寸以适应PDF页面
                const imgWidth = canvas.width;
                const imgHeight = canvas.height;
                
                // A4横向尺寸: 297mm x 210mm
                // 使用更小的边距以最大化内容显示
                const marginX = 8; // 左右边距8mm
                const marginY = 8; // 上下边距8mm
                const availableWidth = pdfWidth - marginX * 2; // 281mm
                const availableHeight = pdfHeight - marginY * 2; // 194mm
                
                // 计算缩放比例，确保内容完全显示并尽可能大
                const ratioWidth = availableWidth / imgWidth;
                const ratioHeight = availableHeight / imgHeight;
                const ratio = Math.min(ratioWidth, ratioHeight);
                
                const imgScaledWidth = imgWidth * ratio;
                const imgScaledHeight = imgHeight * ratio;
                
                // 居中显示
                const xOffset = (pdfWidth - imgScaledWidth) / 2;
                const yOffset = marginY;

                // 添加图片
                pdf.addImage(imgData, 'PNG', xOffset, yOffset, imgScaledWidth, imgScaledHeight);
                
                // 如果内容超过一页，添加新页
                let heightLeft = imgScaledHeight;
                let position = yOffset;
                
                if (heightLeft > pdfHeight) {
                    while (heightLeft > 0) {
                        position = position - pdfHeight;
                        if (position < -imgScaledHeight) break;
                        
                        pdf.addPage();
                        pdf.addImage(imgData, 'PNG', xOffset, position, imgScaledWidth, imgScaledHeight);
                        heightLeft -= pdfHeight;
                    }
                }

                // 生成文件名
                const department = document.getElementById('department').value;
                const evaluationDate = document.getElementById('evaluation_date').value;
                const deptName = deptNames[department] || department;
                const fileName = `考核表单_${deptName}_${evaluationDate}.pdf`;

                // 下载PDF
                pdf.save(fileName);
                
                showMessage('PDF下载成功', 'success');
            } catch (error) {
                console.error('生成PDF失败:', error);
                showMessage('生成PDF失败: ' + error.message, 'error');
            } finally {
                // 恢复原始显示状态
                pdfContent.style.display = originalDisplay;
            }
        }
    </script>
</body>
</html>
