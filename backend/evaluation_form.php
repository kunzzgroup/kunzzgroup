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
            border: 2px solid #000;
        }

        .evaluation-table thead {
            background: #ff5c00;
            color: white;
        }

        .evaluation-table th {
            padding: 18px 12px;
            text-align: center;
            font-weight: 600;
            border: 2px solid #000;
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
            border: 2px solid #000;
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
            border: 2px solid #000;
        }

        #pdf-content .evaluation-table th {
            border: 2px solid #000;
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
            border: 2px solid #000;
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

        /* Kitchen和Service评分标准表样式 */
        #pdf-content #kitchen-rubrics,
        #pdf-content #service-rubrics {
            margin-top: 40px;
        }

        #pdf-content #kitchen-rubrics table,
        #pdf-content #service-rubrics table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            page-break-inside: avoid;
            border: 2px solid #000;
        }

        #pdf-content #kitchen-rubrics table th,
        #pdf-content #service-rubrics table th {
            background: #ff5c00;
            color: white;
            padding: 15px;
            border: 2px solid #000;
            font-weight: 600;
            text-align: center;
        }

        #pdf-content #kitchen-rubrics table td,
        #pdf-content #service-rubrics table td {
            padding: 15px;
            border: 2px solid #000;
            vertical-align: top;
            line-height: 1.6;
        }

        #pdf-content #kitchen-rubrics table td:first-child {
            width: 80px;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
        }

        /* 确保分页正确 */
        #pdf-content .evaluation-table {
            page-break-after: always !important;
        }

        #pdf-content #kitchen-rubrics {
            page-break-before: always !important;
        }

        #pdf-content #kitchen-rubrics .rubric-page[data-page="2"] {
            page-break-before: always !important;
        }

        #pdf-content #kitchen-rubrics .rubric-page[data-page="3"] {
            page-break-before: always !important;
        }

        #pdf-content #kitchen-rubrics .rubric-page[data-page="4"] {
            page-break-before: always !important;
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

        // Service部门评分标准数据
        const serviceRubrics = {
            '工作态度': [
                { score: 1, desc: '在工作态度上有很多不足，如招呼（いらっしゃいませ、谢谢ありがとうございました）明显不够，团队配合度也差，主动性不足。经常跟不上节奏或对招呼、安排有抗拒心理。' },
                { score: 2, desc: '招呼与团队配合偶尔会不一致，有时会漏掉吆喝或说话声音太小，需要主管提醒。对公司的安排配合度也不够，主动性也偏低。' },
                { score: 3, desc: '工作态度基本达标。能够完成招呼动作，配合公司安排，但偶尔在节奏上会不一致或需要提醒。有一些主动性，但还有改进空间。' },
                { score: 4, desc: '整体招呼表现良好。大多时候能与团队保持节奏，配合公司安排。有良好的主动性，愿意在需要时帮忙。' },
                { score: 5, desc: '整体工作态度非常稳定。能够与团队同步进行招呼，让氛围更有活力。高度配合公司安排，主动协助同事，展现出很强的主动性。' }
            ],
            '形象整洁': [
                { score: 1, desc: '形象整洁度有明显不足，如制服不整齐、头发凌乱、仪容不专业、精神状态低落等，都影响整体服务形象。' },
                { score: 2, desc: '仪容的稳定度不够，有时出现头发不整齐、制服不够干净或不够挺，指甲、配饰上也偶尔未符合要求。精神状态也偏弱，站姿不够挺直。' },
                { score: 3, desc: '整体形象符合基本要求，但在细节上需要更多注意，例如头发偶尔会散落、制服略显皱折，或站姿不够精神。' },
                { score: 4, desc: '仪容整体保持得很好，制服整洁、头发固定得体，指甲和配饰也符合要求。偶尔会出现一些小细节需要注意（如领口或袖口），但不影响整体专业度。' },
                { score: 5, desc: '整体形象非常整洁且专业。制服始终干净平整，仪容保持良好，头发、指甲和个人气味都符合日式餐厅标准。站姿精神、态度端正，让顾客一看到你就感到舒服和安心。' }
            ],
            '效率与准确度': [
                { score: 1, desc: '效率与准确度明显不足。点餐经常出现错误或遗漏，POS操作慢且容易出错，对顾客需求的感知能力弱，优先级混乱，多任务处理能力差，容易遗漏特殊要求，结账也容易出现错误。动作不连贯、混乱，错误率高。' },
                { score: 2, desc: '点餐准确度不稳定，容易出现遗漏或错误。POS操作在高峰期容易出错或偏慢，眼观能力弱，优先级不清晰，多任务处理不够，偶尔会漏掉特殊要求或结账错误。动作不够顺畅，走重复路线或停顿过久。' },
                { score: 3, desc: '点餐基本正确，但偶尔会出现遗漏或混乱，需要重新确认。出餐顺序有时跟不上厨房，POS操作速度尚可但高峰期容易有小错误，眼观能力需要加强，多任务处理达到标准但高峰期容易慌张，特殊要求有时会被遗漏，结账速度偶尔偏慢。动作偶尔会有重复路径或绕路。' },
                { score: 4, desc: '点餐准确度大多时候都很好，偶尔需要重新确认。出餐顺序掌握良好，与厨房和前台节奏一致，POS操作在高峰期也能应对，错误少。眼观能力好，能注意到大部分顾客需求并合理安排优先级。多任务处理大多顺畅，只是高峰期节奏可能会稍微加快。特殊要求记录和结账都保持准确。整体动作自然，流畅度还有提升空间。' },
                { score: 5, desc: '点餐清晰准确，会主动确认，几乎零错误。出餐顺序掌握非常稳定，知道什么时候出什么。POS操作非常熟练，高峰期也能快速无误。眼观能力强，能发现需要帮助的桌子并按照紧急程度安排优先级。多任务处理能力出色，忙时动作依然流畅不慌张。能准确记录顾客需求，结账快速无误。整体动作连贯，路线清晰，错误率极低。' }
            ],
            '团队协作': [
                { score: 1, desc: '团队协作有明显不足。分工不够稳定，缺乏主动观察能力，沟通经常断开，对现场节奏的掌握差（忙时混乱、闲时被动），补位意识弱需要提醒，沟通不清晰导致误解或延误。' },
                { score: 2, desc: '团队协作主动性不够。分工部分完成，协作经常断开，需要提醒才能跟上节奏，主动观察能力弱，经常漏掉顾客需求或现场情况，现场节奏不稳定（忙时容易慌张），补位习惯不够（只专注自己的工作），沟通有时不清晰导致信息传递慢或重复。' },
                { score: 3, desc: '团队协作表现基本达标。能够执行分工与协作，但偶尔需要同事提醒才能进入状态。有一些主动观察能力但不够稳定，有时会漏掉顾客求助信号。现场节奏需要进一步加强，容易被忙碌打断。补位能力有时能做到但忙时容易忘记。' },
                { score: 4, desc: '团队内配合良好。能够按照分工完成自己的角色，并顺利交接给同事。主动观察能力好，大多时候能注意到顾客需求。现场节奏相对稳定，能够根据需要调整。有补位习惯，但偶尔会因为忙碌而忽略细节。' },
                { score: 5, desc: '团队协作表现非常出色。分工非常清晰，即使忙碌时也能与同事顺畅协作。观察能力强，能主动发现顾客或现场需求。现场节奏掌握非常好，忙时能加速，不忙时会主动整理环境。补位习惯非常好，看到同事忙碌会自动接手，不需要主管指示。现场沟通简洁、快速、准确，让整个服务流程更顺畅。' }
            ],
            '服务态度': [
                { score: 1, desc: '服务态度在很多方面都有不足，如笑容明显不够，对顾客的态度和语气冷淡，容易在互动中显得不耐烦。身体语言也明显不足，如站姿不稳定、动作匆忙、节奏混乱，容易影响顾客感知。' },
                { score: 2, desc: '服务态度偶尔达标但不够稳定。笑容往往不够，对顾客的语气有时平淡或不耐烦。身体语言也需要加强，如站姿松散、动作过快，上菜或收桌有时显得匆忙。' },
                { score: 3, desc: '服务表现基本达标，能够保持基本笑容和礼貌语气。整体态度友善，但有时因为忙碌，笑容减少，语气稍微急促。身体语言方面，有时站姿不够挺直，动作过快或显得匆忙。' },
                { score: 4, desc: '服务态度表现良好，大多时候有笑容，对顾客的语气和态度保持礼貌友善。身体语言也相对稳定，站姿自然，动作不慌不忙。' },
                { score: 5, desc: '服务态度非常出色。笑容自然友善，让顾客感到舒服。语气温和有礼，对顾客始终保持耐心。身体语言非常专业，包括站姿挺直、节奏稳定、上菜和收桌动作轻柔不匆忙，与顾客互动时保持适当距离，让人感到被尊重。' }
            ]
        };

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

        // 生成Service评分标准表HTML（分页）
        function generateServiceRubrics() {
            let html = '<div id="service-rubrics" style="display: none;">';
            
            const rubricPages = [
                // 第二页：工作态度和形象整洁
                ['工作态度', '形象整洁'],
                // 第三页：效率与准确度和团队协作
                ['效率与准确度', '团队协作'],
                // 第四页：服务态度
                ['服务态度']
            ];
            
            rubricPages.forEach((pageTitles, pageIndex) => {
                pageTitles.forEach((title, titleIndex) => {
                    const isFirstOnPage = titleIndex === 0;
                    const isFirstPage = pageIndex === 0;
                    
                    // 为每个页面添加分页标记
                    const pageBreak = isFirstOnPage && !isFirstPage ? 'page-break-before: always;' : '';
                    
                    html += `
                        <div class="rubric-page" data-page="${pageIndex + 2}" style="margin-top: ${isFirstOnPage && !isFirstPage ? '0' : '30px'}; ${pageBreak}">
                            <table style="width: 100%; border-collapse: collapse; margin-bottom: ${titleIndex < pageTitles.length - 1 ? '30px' : '20px'}; font-size: 15px; border: 2px solid #000;">
                                <thead>
                                    <tr>
                                        <th style="width: 80px; padding: 15px; text-align: center; background: #ff5c00; color: white; border: 2px solid #000; font-weight: 600;">分数</th>
                                        <th style="padding: 15px; text-align: center; background: #ff5c00; color: white; border: 2px solid #000; font-weight: 600; font-size: 18px;">${title}</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    serviceRubrics[title].forEach(item => {
                        html += `
                            <tr>
                                <td style="width: 80px; padding: 15px; text-align: center; border: 2px solid #000; font-size: 18px; font-weight: 600; vertical-align: top;">${item.score}</td>
                                <td style="padding: 15px; border: 2px solid #000; line-height: 1.6; vertical-align: top;">${item.desc}</td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                });
            });
            
            html += '</div>';
            return html;
        }

        // 生成Kitchen评分标准表HTML（分四页）
        function generateKitchenRubrics() {
            let html = '<div id="kitchen-rubrics" style="display: none;">';
            
            const rubricPages = [
                // 第二页：出餐速度和食材标准
                ['出餐速度与效率', '食材标准与品质'],
                // 第三页：卫生和工作态度
                ['卫生与整洁', '工作态度'],
                // 第四页：团队合作
                ['团队合作']
            ];
            
            rubricPages.forEach((pageTitles, pageIndex) => {
                pageTitles.forEach((title, titleIndex) => {
                    const isFirstOnPage = titleIndex === 0;
                    const isFirstPage = pageIndex === 0;
                    
                    // 为每个页面添加分页标记
                    const pageBreak = isFirstOnPage && !isFirstPage ? 'page-break-before: always;' : '';
                    
                    html += `
                        <div class="rubric-page" data-page="${pageIndex + 2}" style="margin-top: ${isFirstOnPage && !isFirstPage ? '0' : '30px'}; ${pageBreak}">
                            <table style="width: 100%; border-collapse: collapse; margin-bottom: ${titleIndex < pageTitles.length - 1 ? '30px' : '20px'}; font-size: 15px; border: 2px solid #000;">
                                <thead>
                                    <tr>
                                        <th style="width: 80px; padding: 15px; text-align: center; background: #ff5c00; color: white; border: 2px solid #000; font-weight: 600;">分数</th>
                                        <th style="padding: 15px; text-align: center; background: #ff5c00; color: white; border: 2px solid #000; font-weight: 600; font-size: 18px;">${title}</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    kitchenRubrics[title].forEach(item => {
                        html += `
                            <tr>
                                <td style="width: 80px; padding: 15px; text-align: center; border: 2px solid #000; font-size: 18px; font-weight: 600; vertical-align: top;">${item.score}</td>
                                <td style="padding: 15px; border: 2px solid #000; line-height: 1.6; vertical-align: top;">${item.desc}</td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                });
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
            
            // 如果是kitchen或service部门，添加评分标准表到PDF内容
            if (department === 'kitchen') {
                const rubricsHtml = generateKitchenRubrics().replace('style="display: none;"', '');
                html += rubricsHtml;
            } else if (department === 'service_line') {
                const rubricsHtml = generateServiceRubrics().replace('style="display: none;"', '');
                html += rubricsHtml;
            }
            
            html += `</div>`;
            
            // 确保第一页（表单）和后面的评分标准表分页
            if (department === 'kitchen') {
                const style = document.createElement('style');
                style.textContent = `
                    #pdf-content .evaluation-table {
                        page-break-after: always;
                    }
                    #pdf-content #kitchen-rubrics > div:first-child {
                        page-break-before: always;
                    }
                `;
                document.head.appendChild(style);
            }

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

            const department = document.getElementById('department').value;
            
            // 如果是kitchen或service部门，需要分页生成
            if (department === 'kitchen') {
                await downloadKitchenPDF(pdfContent);
                return;
            } else if (department === 'service_line') {
                await downloadServicePDF(pdfContent);
                return;
            }

            // 非kitchen部门使用原来的生成方式
            await downloadRegularPDF(pdfContent);
        }

        // 生成普通PDF（非kitchen部门）
        async function downloadRegularPDF(pdfContent) {
            // 显示加载提示
            showMessage('正在生成PDF，请稍候...', 'success');

            // 临时显示PDF内容
            const originalDisplay = pdfContent.style.display;
            pdfContent.style.display = 'block';
            
            // 确保内容已渲染
            await new Promise(resolve => setTimeout(resolve, 500));

            try {
                const { jsPDF } = window.jspdf;
                
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
                
                const pdf = new jsPDF('l', 'mm', 'a4');
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                
                const marginX = 8;
                const marginY = 8;
                const availableWidth = pdfWidth - marginX * 2;
                const availableHeight = pdfHeight - marginY * 2;
                
                const ratioWidth = availableWidth / canvas.width;
                const ratioHeight = availableHeight / canvas.height;
                const ratio = Math.min(ratioWidth, ratioHeight);
                
                const imgScaledWidth = canvas.width * ratio;
                const imgScaledHeight = canvas.height * ratio;
                
                const xOffset = (pdfWidth - imgScaledWidth) / 2;
                const yOffset = marginY;

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

                const evaluationDate = document.getElementById('evaluation_date').value;
                const department = document.getElementById('department').value;
                const deptName = deptNames[department] || department;
                const fileName = `考核表单_${deptName}_${evaluationDate}.pdf`;

                pdf.save(fileName);
                showMessage('PDF下载成功', 'success');
            } catch (error) {
                console.error('生成PDF失败:', error);
                showMessage('生成PDF失败: ' + error.message, 'error');
            } finally {
                pdfContent.style.display = originalDisplay;
            }
        }

        // 生成Kitchen部门PDF（分四页）
        async function downloadKitchenPDF(pdfContent) {
            // 确保评分标准表已添加
            let existingRubrics = pdfContent.querySelector('#kitchen-rubrics');
            if (!existingRubrics) {
                const rubricsHtml = generateKitchenRubrics().replace('style="display: none;"', '');
                pdfContent.innerHTML += rubricsHtml;
                existingRubrics = pdfContent.querySelector('#kitchen-rubrics');
            }
            if (existingRubrics) {
                existingRubrics.style.display = 'block';
            }

            // 显示加载提示
            showMessage('正在生成PDF，请稍候...', 'success');

            const originalDisplay = pdfContent.style.display;
            pdfContent.style.display = 'block';
            
            await new Promise(resolve => setTimeout(resolve, 500));

            try {
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('l', 'mm', 'a4');
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const marginX = 8;
                const marginY = 8;
                const availableWidth = pdfWidth - marginX * 2;
                const availableHeight = pdfHeight - marginY * 2;

                // 第一页：员工考核表
                const formHeader = pdfContent.querySelector('.form-header');
                const deptBanner = pdfContent.querySelector('[style*="background: #ff5c00"]');
                const formTable = pdfContent.querySelector('.evaluation-table');
                
                const formContainer = document.createElement('div');
                formContainer.style.width = '1300px';
                formContainer.style.padding = '40px 50px';
                formContainer.style.background = 'white';
                
                if (formHeader) formContainer.appendChild(formHeader.cloneNode(true));
                if (deptBanner) formContainer.appendChild(deptBanner.cloneNode(true));
                if (formTable) formContainer.appendChild(formTable.cloneNode(true));
                
                formContainer.style.position = 'absolute';
                formContainer.style.left = '-9999px';
                document.body.appendChild(formContainer);

                const formCanvas = await html2canvas(formContainer, {
                    scale: 2.5,
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#ffffff',
                    width: formContainer.scrollWidth,
                    height: formContainer.scrollHeight
                });

                const formRatio = Math.min(availableWidth / formCanvas.width, availableHeight / formCanvas.height);
                const formScaledWidth = formCanvas.width * formRatio;
                const formScaledHeight = formCanvas.height * formRatio;
                const formXOffset = (pdfWidth - formScaledWidth) / 2;
                const formYOffset = marginY;

                pdf.addImage(formCanvas.toDataURL('image/png', 1.0), 'PNG', formXOffset, formYOffset, formScaledWidth, formScaledHeight);
                document.body.removeChild(formContainer);

                // 第二页：出餐速度和食材标准
                pdf.addPage();
                const page2Divs = pdfContent.querySelectorAll('#kitchen-rubrics .rubric-page[data-page="2"]');
                await addRubricPage(pdf, page2Divs, pdfWidth, pdfHeight, marginX, marginY, availableWidth, availableHeight);

                // 第三页：卫生和工作态度
                pdf.addPage();
                const page3Divs = pdfContent.querySelectorAll('#kitchen-rubrics .rubric-page[data-page="3"]');
                await addRubricPage(pdf, page3Divs, pdfWidth, pdfHeight, marginX, marginY, availableWidth, availableHeight);

                // 第四页：团队合作
                pdf.addPage();
                const page4Divs = pdfContent.querySelectorAll('#kitchen-rubrics .rubric-page[data-page="4"]');
                await addRubricPage(pdf, page4Divs, pdfWidth, pdfHeight, marginX, marginY, availableWidth, availableHeight);

                const evaluationDate = document.getElementById('evaluation_date').value;
                const fileName = `考核表单_KITCHEN_${evaluationDate}.pdf`;

                pdf.save(fileName);
                showMessage('PDF下载成功', 'success');
            } catch (error) {
                console.error('生成PDF失败:', error);
                showMessage('生成PDF失败: ' + error.message, 'error');
            } finally {
                pdfContent.style.display = originalDisplay;
            }
        }

        // 生成Service部门PDF（分页）
        async function downloadServicePDF(pdfContent) {
            // 确保评分标准表已添加
            let existingRubrics = pdfContent.querySelector('#service-rubrics');
            if (!existingRubrics) {
                const rubricsHtml = generateServiceRubrics().replace('style="display: none;"', '');
                pdfContent.innerHTML += rubricsHtml;
                existingRubrics = pdfContent.querySelector('#service-rubrics');
            }
            if (existingRubrics) {
                existingRubrics.style.display = 'block';
            }

            // 显示加载提示
            showMessage('正在生成PDF，请稍候...', 'success');

            const originalDisplay = pdfContent.style.display;
            pdfContent.style.display = 'block';
            
            await new Promise(resolve => setTimeout(resolve, 500));

            try {
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('l', 'mm', 'a4');
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const marginX = 8;
                const marginY = 8;
                const availableWidth = pdfWidth - marginX * 2;
                const availableHeight = pdfHeight - marginY * 2;

                // 第一页：员工考核表
                const formHeader = pdfContent.querySelector('.form-header');
                const deptBanner = pdfContent.querySelector('[style*="background: #ff5c00"]');
                const formTable = pdfContent.querySelector('.evaluation-table');
                
                const formContainer = document.createElement('div');
                formContainer.style.width = '1300px';
                formContainer.style.padding = '40px 50px';
                formContainer.style.background = 'white';
                
                if (formHeader) formContainer.appendChild(formHeader.cloneNode(true));
                if (deptBanner) formContainer.appendChild(deptBanner.cloneNode(true));
                if (formTable) formContainer.appendChild(formTable.cloneNode(true));
                
                formContainer.style.position = 'absolute';
                formContainer.style.left = '-9999px';
                document.body.appendChild(formContainer);

                const formCanvas = await html2canvas(formContainer, {
                    scale: 2.5,
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#ffffff',
                    width: formContainer.scrollWidth,
                    height: formContainer.scrollHeight
                });

                const formRatio = Math.min(availableWidth / formCanvas.width, availableHeight / formCanvas.height);
                const formScaledWidth = formCanvas.width * formRatio;
                const formScaledHeight = formCanvas.height * formRatio;
                const formXOffset = (pdfWidth - formScaledWidth) / 2;
                const formYOffset = marginY;

                pdf.addImage(formCanvas.toDataURL('image/png', 1.0), 'PNG', formXOffset, formYOffset, formScaledWidth, formScaledHeight);
                document.body.removeChild(formContainer);

                // 第二页：工作态度和形象整洁
                pdf.addPage();
                const page2Divs = pdfContent.querySelectorAll('#service-rubrics .rubric-page[data-page="2"]');
                await addRubricPage(pdf, page2Divs, pdfWidth, pdfHeight, marginX, marginY, availableWidth, availableHeight);

                // 第三页：效率与准确度和团队协作
                pdf.addPage();
                const page3Divs = pdfContent.querySelectorAll('#service-rubrics .rubric-page[data-page="3"]');
                await addRubricPage(pdf, page3Divs, pdfWidth, pdfHeight, marginX, marginY, availableWidth, availableHeight);

                // 第四页：服务态度
                pdf.addPage();
                const page4Divs = pdfContent.querySelectorAll('#service-rubrics .rubric-page[data-page="4"]');
                await addRubricPage(pdf, page4Divs, pdfWidth, pdfHeight, marginX, marginY, availableWidth, availableHeight);

                const evaluationDate = document.getElementById('evaluation_date').value;
                const fileName = `考核表单_SERVICE LINE_${evaluationDate}.pdf`;

                pdf.save(fileName);
                showMessage('PDF下载成功', 'success');
            } catch (error) {
                console.error('生成PDF失败:', error);
                showMessage('生成PDF失败: ' + error.message, 'error');
            } finally {
                pdfContent.style.display = originalDisplay;
            }
        }

        // 添加评分标准页面到PDF
        async function addRubricPage(pdf, divs, pdfWidth, pdfHeight, marginX, marginY, availableWidth, availableHeight) {
            if (divs.length === 0) return;

            const container = document.createElement('div');
            container.style.width = '1300px';
            container.style.padding = '40px 50px';
            container.style.background = 'white';
            
            Array.from(divs).forEach(div => {
                container.appendChild(div.cloneNode(true));
            });

            container.style.position = 'absolute';
            container.style.left = '-9999px';
            document.body.appendChild(container);

            const canvas = await html2canvas(container, {
                scale: 2.5,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff',
                width: container.scrollWidth,
                height: container.scrollHeight
            });

            const ratio = Math.min(availableWidth / canvas.width, availableHeight / canvas.height);
            const scaledWidth = canvas.width * ratio;
            const scaledHeight = canvas.height * ratio;
            const xOffset = (pdfWidth - scaledWidth) / 2;
            const yOffset = marginY;

            pdf.addImage(canvas.toDataURL('image/png', 1.0), 'PNG', xOffset, yOffset, scaledWidth, scaledHeight);
            document.body.removeChild(container);
        }
    </script>
</body>
</html>
