<?php
require_once 'session_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>问卷回答 - KUNZZ HOLDINGS</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
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

        .qna-content-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 2px solid #000000ff;
            min-height: 0;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .qna-content-wrapper {
            overflow-y: auto;
            overflow-x: hidden;
            flex: 1;
            min-height: 0;
        }

        .qna-content-wrapper::-webkit-scrollbar {
            width: 8px;
        }

        .qna-content-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .qna-content-wrapper::-webkit-scrollbar-thumb {
            background: #ff5c00;
            border-radius: 4px;
        }

        .qna-content-wrapper::-webkit-scrollbar-thumb:hover {
            background: #ff7700;
        }

        .form-section {
            margin-bottom: 0;
            border: 1px solid #000000ff;
            background-color: #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }

        .form-section-header {
            padding: clamp(6px, 0.52vw, 10px) clamp(10px, 0.73vw, 14px);
            border-bottom: 1px solid #ffddaa;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 700;
            text-align: left;
            color: white;
            background: #f99e00;
        }

        .form-section-content {
            padding: clamp(6px, 0.52vw, 10px) clamp(8px, 0.63vw, 12px);
        }

        .question-item {
            margin-bottom: 0;
        }

        .question-number {
            font-size: clamp(10px, 0.83vw, 16px);
            font-weight: bold;
            color: #000000ff;
            margin-bottom: clamp(4px, 0.42vw, 8px);
        }

        .question-text {
            font-size: clamp(10px, 0.83vw, 16px);
            color: #000000ff;
            margin-bottom: clamp(4px, 0.42vw, 8px);
            line-height: 1.6;
        }

        .question-example {
            font-size: clamp(8px, 0.74vw, 14px);
            color: #999999;
            margin-bottom: clamp(6px, 0.63vw, 12px);
            font-style: italic;
        }

        .question-input {
            width: 100%;
            padding: 0px clamp(4px, 0.625vw, 12px);
            border: 2px solid #ff5c00;
            border-radius: 8px;
            font-size: clamp(10px, 0.83vw, 16px);
            font-family: inherit;
            resize: none;
            height: clamp(20px, 1.56vw, 30px);
            transition: all 0.3s ease;
        }

        .question-input:focus {
            outline: none;
            border-color: #ff5c00;
            box-shadow: 0 0 10px rgba(255, 115, 0, 0.8);
        }

        .question-input:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
            border-color: #d5d5d5;
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: clamp(12px, 1.25vw, 24px);
            margin-top: clamp(20px, 2.08vw, 40px);
            flex-shrink: 0;
        }

        .btn {
            width: clamp(60px, 6.25vw, 120px);
            background: #ff5c00;
            border: none;
            border-radius: 20px;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            color: white;
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 500;
            cursor: pointer;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            transition: all 0.3s ease;
        }

        .btn:hover:not(:disabled) {
            background: #ff7700;
            transform: translateY(-1px);
        }

        .btn:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            transform: none;
        }

        .btn-reset {
            background: #6b7280;
        }

        .btn-reset:hover {
            background: #4b5563;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
            display: none;
        }

        .message.success {
            background: #C8E6C9;
            color: #2E7D32;
            border: 2px solid #4CAF50;
        }

        .message.error {
            background: #FFCDD2;
            color: #C62828;
            border: 2px solid #F44336;
        }

        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .view-mode {
            display: none;
        }

        .edit-mode {
            display: block;
        }

        .mode-view .view-mode {
            display: block;
        }

        .mode-view .edit-mode {
            display: none;
        }

        .view-answer {
            width: 100%;
            padding: 0px clamp(4px, 0.625vw, 12px);
            border: 2px solid #d5d5d5;
            border-radius: 8px;
            font-size: clamp(10px, 0.83vw, 16px);
            font-family: inherit;
            height: clamp(20px, 1.56vw, 30px);
            line-height: clamp(20px, 1.56vw, 30px);
            color: #333333;
            white-space: pre-wrap;
            word-wrap: break-word;
            background-color: white;
            box-sizing: border-box;
        }

        .empty-answer {
            color: #999999;
            font-style: italic;
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 40px;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="container">
        <div class="header">
            <h1>问卷回答</h1>
        </div>
        
        <div id="messageArea"></div>
        
        <div class="qna-content-container">
            <div class="qna-content-wrapper">
                <!-- 编辑模式 -->
                <form id="qnaForm" class="edit-mode">
                <div class="form-section">
                    <div class="form-section-header">问题 1</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">如果不考虑现实限制,你希望自己在3-5年后成为什么样的人?</div>
                            <textarea class="question-input" name="question1" id="question1" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 2</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">你目前最重要的个人目标或梦想是什么?</div>
                            <div class="question-example">(例如:事业发展,专业技能,经济目标,生活稳定,家庭等)</div>
                            <textarea class="question-input" name="question2" id="question2" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 3</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">如果公司为你提供机会,你是否愿意承担更高的责任与压力?你认为这些责任具体体现在哪些方面?</div>
                            <div class="question-example">(例如:结果要求,学习投入,团队管理,时间管理,抗压能力等)</div>
                            <textarea class="question-input" name="question3" id="question3" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 4</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">在实现的目标过程中,你目前遇到最大的困难或挑战是什么?</div>
                            <div class="question-example">(可以是工作上的,也可以是个人层面的)</div>
                            <textarea class="question-input" name="question4" id="question4" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 5</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">如果公司可以提供支持,你最希望公司在哪些方面给予帮助?</div>
                            <textarea class="question-input" name="question5" id="question5" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 6</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">在目前的公司中,有没有你特别希望尝试或发展的方向?为什么?</div>
                            <div class="question-example">(例如:管理,专业深度,跨部门,新项目等)</div>
                            <textarea class="question-input" name="question6" id="question6" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 7</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">你认为哪些能力或经验,是你未来1-2年最需要重点提升的?</div>
                            <textarea class="question-input" name="question7" id="question7" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 8</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">如果未来1年内，公司只能为你提供一项最有价值的支持，你希望是什么？</div>
                            <div class="question-example">(请写下你认为最重要的一项)</div>
                            <textarea class="question-input" name="question8" id="question8"></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 9</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">当你想到“理想的工作状态”时，请写下你最重视的3个关键词。</div>
                            <div class="question-example">(例如：成长，稳定，被尊重，有挑战，自由，有意义等)</div>
                            <textarea class="question-input" name="question9" id="question9"></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 10</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">你希望公司在“员工发展”这件事上，扮演什么角色？</div>
                            <div class="question-example">(例如：平台，导师，伙伴，资源提供者，稳定后盾等)</div>
                            <textarea class="question-input" name="question10" id="question10"></textarea>
                        </div>
                    </div>
                </div>

                </form>

                <!-- 查看模式 -->
                <div class="view-mode">
                <div class="form-section">
                    <div class="form-section-header">问题 1</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">如果不考虑现实限制,你希望自己在3-5年后成为什么样的人?</div>
                            <div class="view-answer" id="view-question1"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 2</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">你目前最重要的个人目标或梦想是什么?</div>
                            <div class="view-answer" id="view-question2"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 3</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">如果公司为你提供机会,你是否愿意承担更高的责任与压力?你认为这些责任具体体现在哪些方面?</div>
                            <div class="view-answer" id="view-question3"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 4</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">在实现的目标过程中,你目前遇到最大的困难或挑战是什么?</div>
                            <div class="view-answer" id="view-question4"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 5</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">如果公司可以提供支持,你最希望公司在哪些方面给予帮助?</div>
                            <div class="view-answer" id="view-question5"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 6</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">在目前的公司中,有没有你特别希望尝试或发展的方向?为什么?</div>
                            <div class="view-answer" id="view-question6"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 7</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">你认为哪些能力或经验,是你未来1-2年最需要重点提升的?</div>
                            <div class="view-answer" id="view-question7"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 8</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">如果未来1年内，公司只能为你提供一项最有价值的支持，你希望是什么？</div>
                            <div class="view-answer" id="view-question8"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 9</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">当你想到"理想的工作状态"时，请写下你最重视的3个关键词。</div>
                            <div class="view-answer" id="view-question9"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 10</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">你希望公司在"员工发展"这件事上，扮演什么角色？</div>
                            <div class="view-answer" id="view-question10"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                </div>
            </div>
        </div>
        
        <div class="button-group" id="buttonGroup">
            <button type="button" class="btn btn-reset" onclick="resetForm()" id="resetBtn" style="display: none;">重新回答</button>
            <button type="submit" class="btn" id="submitBtn" form="qnaForm" style="display: none;">提交问卷</button>
            <button type="button" class="btn" onclick="generatePDF()" id="printBtn" style="display: none;">打印问卷</button>
        </div>
    </div>

    <script>
        // 从PHP获取用户名和职位
        const currentUsername = '<?php echo isset($username) ? addslashes($username) : "User"; ?>';
        const currentPosition = '<?php echo isset($position) && !empty($position) ? addslashes($position) : "User"; ?>';
        
        let userResponse = null;
        let isSubmitted = false;

        // 页面加载时检查是否已提交
        document.addEventListener('DOMContentLoaded', async function() {
            await loadUserResponse();
            // 根据模式显示/隐藏按钮
            updateButtonVisibility();
        });
        
        // 更新按钮显示状态
        function updateButtonVisibility() {
            const resetBtn = document.getElementById('resetBtn');
            const submitBtn = document.getElementById('submitBtn');
            const printBtn = document.getElementById('printBtn');
            
            if (isSubmitted) {
                if (resetBtn) resetBtn.style.display = 'none';
                if (submitBtn) submitBtn.style.display = 'none';
                if (printBtn) printBtn.style.display = 'block';
            } else {
                if (resetBtn) resetBtn.style.display = 'block';
                if (submitBtn) submitBtn.style.display = 'block';
                if (printBtn) printBtn.style.display = 'none';
            }
        }

        // 加载用户的问卷回答
        async function loadUserResponse() {
            try {
                const res = await fetch('qnaapi.php', {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                
                if (data.success && data.data) {
                    userResponse = data.data;
                    isSubmitted = true;
                    switchToViewMode();
                    populateViewMode(data.data);
                } else {
                    // 未提交，保持编辑模式
                    isSubmitted = false;
                }
            } catch (error) {
                console.error('加载问卷失败:', error);
                showAlert('加载问卷失败，请刷新页面重试', 'error');
            }
        }

        // 切换到查看模式
        function switchToViewMode() {
            document.body.classList.add('mode-view');
            document.getElementById('qnaForm').style.display = 'none';
            updateButtonVisibility();
        }

        // 填充查看模式的数据
        function populateViewMode(data) {
            for (let i = 1; i <= 10; i++) {
                const answer = data[`question${i}`] || '';
                const viewElement = document.getElementById(`view-question${i}`);
                if (viewElement) {
                    if (answer.trim()) {
                        viewElement.innerHTML = answer;
                        viewElement.classList.remove('empty-answer');
                    } else {
                        viewElement.innerHTML = '<span class="empty-answer">未填写</span>';
                    }
                }
            }
        }

        // 表单提交
        document.getElementById('qnaForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (isSubmitted) {
                showAlert('您已经提交过问卷，每个用户只能提交一次', 'error');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<div class="loading"></div> 提交中...';
            submitBtn.disabled = true;

            try {
                const formData = new FormData(this);
                const data = {};
                for (let i = 1; i <= 10; i++) {
                    data[`question${i}`] = formData.get(`question${i}`) || '';
                }

                const res = await fetch('qnaapi.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await res.json();

                if (result.success) {
                    showAlert('问卷提交成功！', 'success');
                    isSubmitted = true;
                    await loadUserResponse();
                    updateButtonVisibility();
                } else {
                    showAlert(result.message || '提交失败，请重试', 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('提交失败:', error);
                showAlert('网络错误，请稍后重试', 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });

        // 重置表单
        function resetForm() {
            if (confirm('确定要清空所有回答吗？')) {
                document.getElementById('qnaForm').reset();
            }
        }

        // 显示提示信息
        function showAlert(message, type) {
            const messageArea = document.getElementById('messageArea');
            messageArea.className = `message ${type}`;
            messageArea.textContent = message;
            messageArea.style.display = 'block';
            
            setTimeout(() => {
                messageArea.style.display = 'none';
            }, 5000);
        }

        // 生成PDF
        async function generatePDF() {
            if (!userResponse) {
                showAlert('没有可打印的问卷数据', 'error');
                return;
            }

            try {
                showAlert('正在生成PDF，请稍候...', 'success');
                
                // 检查PDF模板是否存在
                const templatePath = '../form/survey.pdf';
                
                // 使用fetch加载PDF模板
                const templateResponse = await fetch(templatePath);
                if (!templateResponse.ok) {
                    throw new Error('无法加载PDF模板，请确保form/survey.pdf文件存在');
                }

                const templateBytes = await templateResponse.arrayBuffer();
                const { PDFDocument, rgb, StandardFonts } = PDFLib;
                const pdfDoc = await PDFDocument.load(templateBytes);

                // 获取第一页
                const page = pdfDoc.getPage(0);
                const { width, height } = page.getSize();
                console.log(`PDF尺寸: 宽度=${width.toFixed(2)}pt, 高度=${height.toFixed(2)}pt`);

                // 加载支持中文的字体（使用在线字体文件）
                // 使用 Noto Sans SC (思源黑体) 字体，支持简体中文
                let chineseFont, chineseBoldFont;
                
                // 尝试多个字体源，按优先级顺序
                const fontSources = [
                    // 方案1: 使用 jsDelivr CDN (Google Fonts - OTF格式)
                    {
                        regular: 'https://cdn.jsdelivr.net/gh/google/fonts@main/ofl/notosanssc/NotoSansSC-Regular.otf',
                        bold: 'https://cdn.jsdelivr.net/gh/google/fonts@main/ofl/notosanssc/NotoSansSC-Bold.otf'
                    },
                    // 方案2: 使用 raw.githubusercontent.com (备用源)
                    {
                        regular: 'https://raw.githubusercontent.com/google/fonts/main/ofl/notosanssc/NotoSansSC-Regular.otf',
                        bold: 'https://raw.githubusercontent.com/google/fonts/main/ofl/notosanssc/NotoSansSC-Bold.otf'
                    },
                    // 方案3: 本地字体文件（如果用户有）
                    {
                        regular: '../fonts/NotoSansSC-Regular.otf',
                        bold: '../fonts/NotoSansSC-Bold.otf'
                    },
                    // 方案4: 本地字体文件 TTF 格式
                    {
                        regular: '../fonts/NotoSansSC-Regular.ttf',
                        bold: '../fonts/NotoSansSC-Bold.ttf'
                    }
                ];
                
                // 尝试加载字体
                for (const source of fontSources) {
                    if (!chineseFont) {
                        try {
                            const fontResponse = await fetch(source.regular);
                            if (fontResponse.ok) {
                                const fontBytes = await fontResponse.arrayBuffer();
                                chineseFont = await pdfDoc.embedFont(fontBytes);
                                console.log('成功加载中文字体（常规）');
                            }
                        } catch (e) {
                            // 继续尝试下一个源
                        }
                    }
                    
                    if (!chineseBoldFont) {
                        try {
                            const boldFontResponse = await fetch(source.bold);
                            if (boldFontResponse.ok) {
                                const boldFontBytes = await boldFontResponse.arrayBuffer();
                                chineseBoldFont = await pdfDoc.embedFont(boldFontBytes);
                                console.log('成功加载中文字体（粗体）');
                            }
                        } catch (e) {
                            // 继续尝试下一个源
                        }
                    }
                    
                    // 如果两个字体都加载成功，停止尝试
                    if (chineseFont && chineseBoldFont) {
                        break;
                    }
                }
                
                // 检查是否成功加载了中文字体
                // 如果字体加载失败，chineseFont 和 chineseBoldFont 仍为 undefined
                let useCanvasFallback = false;
                if (!chineseFont || !chineseBoldFont) {
                    console.warn('无法加载中文字体，将使用 Canvas 渲染中文文本');
                    useCanvasFallback = true;
                    // 仍然需要标准字体用于英文和数字
                    if (!chineseFont) {
                        chineseFont = await pdfDoc.embedFont(StandardFonts.Helvetica);
                    }
                    if (!chineseBoldFont) {
                        chineseBoldFont = await pdfDoc.embedFont(StandardFonts.HelveticaBold);
                    }
                } else {
                    console.log('成功加载中文字体，将直接以文字形式打印中文');
                }
                
                // 使用字体（如果加载成功就用中文字体，否则用标准字体）
                const font = chineseFont;
                const boldFont = chineseBoldFont;
                
                // 辅助函数：检查文本是否包含中文字符
                function containsChinese(text) {
                    return /[\u4e00-\u9fa5]/.test(text);
                }
                
                // Canvas 渲染备选方案（仅在字体加载失败时使用）
                async function renderTextAsImage(text, fontSize, isBold = false) {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    
                    const fontFamily = 'Arial, "Microsoft YaHei", "SimHei", "PingFang SC", sans-serif';
                    ctx.font = `${isBold ? 'bold ' : ''}${fontSize}px ${fontFamily}`;
                    
                    const metrics = ctx.measureText(text);
                    const textWidth = metrics.width;
                    const textHeight = fontSize * 1.2;
                    
                    const padding = 10;
                    canvas.width = Math.ceil(textWidth) + padding * 2;
                    canvas.height = Math.ceil(textHeight) + padding * 2;
                    
                    ctx.font = `${isBold ? 'bold ' : ''}${fontSize}px ${fontFamily}`;
                    ctx.fillStyle = '#000000';
                    ctx.textBaseline = 'top';
                    ctx.textAlign = 'left';
                    
                    ctx.fillText(text, padding, padding);
                    
                    return {
                        dataUrl: canvas.toDataURL('image/png'),
                        width: textWidth,
                        height: textHeight
                    };
                }
                
                // 智能文本绘制函数（自动选择文字或图片方式）
                async function drawTextSmart(page, text, x, y, fontSize, isBold = false) {
                    // 如果字体加载成功且文本不包含中文，直接使用文字
                    if (!useCanvasFallback || !containsChinese(text)) {
                        const fontToUse = isBold ? boldFont : font;
                        page.drawText(text, {
                            x: x,
                            y: y,
                            size: fontSize,
                            font: fontToUse,
                            color: textColor,
                        });
                    } else {
                        // 字体加载失败且包含中文，使用 Canvas 渲染
                        const { dataUrl, width: textWidth, height: textHeight } = await renderTextAsImage(text, fontSize, isBold);
                        
                        const base64Data = dataUrl.split(',')[1];
                        const binaryString = atob(base64Data);
                        const bytes = new Uint8Array(binaryString.length);
                        for (let i = 0; i < binaryString.length; i++) {
                            bytes[i] = binaryString.charCodeAt(i);
                        }
                        
                        const image = await pdfDoc.embedPng(bytes);
                        page.drawImage(image, {
                            x: x,
                            y: y - textHeight,
                            width: textWidth,
                            height: textHeight,
                        });
                    }
                }

                // 设置字体大小和颜色
                const fontSize = 12;
                const headerFontSize = 12;
                const textColor = rgb(0, 0, 0);
                const lineHeight = 14;
                const leftMargin = 100;
                const rightMargin = 100;
                const maxWidth = width - leftMargin - rightMargin;
                const topMargin = 100;
                
                // 在页面顶部中间绘制用户名和职位
                const userInfoText = `${currentUsername} (${currentPosition})`;
                // 计算文本宽度
                let textWidth, centerX;
                if (useCanvasFallback && containsChinese(userInfoText)) {
                    // 使用 Canvas 测量
                    const tempCanvas = document.createElement('canvas');
                    const tempCtx = tempCanvas.getContext('2d');
                    tempCtx.font = `bold ${headerFontSize}px Arial, "Microsoft YaHei", "SimHei", sans-serif`;
                    textWidth = tempCtx.measureText(userInfoText).width;
                } else {
                    textWidth = boldFont.widthOfTextAtSize(userInfoText, headerFontSize);
                }
                centerX = (width - textWidth) / 2;
                
                await drawTextSmart(page, userInfoText, centerX, height - 40, headerFontSize, true);
                
                // 每个问题的“绝对起始Y坐标”配置（完全类似 invoice：固定坐标）
                // 这里建议先用一组大概的初始值，之后你只改这些数字就能调位置
                // 例：1: height - 200 表示第1题起点Y为 height-200。
                const answerPositions = {
                    1: height - 95,
                    2: height - 170,
                    3: height - 265,
                    4: height - 340,
                    5: height - 450,
                    6: height - 496,
                    7: height - 573,
                    8: height - 650,
                    9: height - 727,
                    10: height - 805,
                };

                // 问题列表
                const questions = [
                    { num: 1, text: userResponse.question1 || '' },
                    { num: 2, text: userResponse.question2 || '' },
                    { num: 3, text: userResponse.question3 || '' },
                    { num: 4, text: userResponse.question4 || '' },
                    { num: 5, text: userResponse.question5 || '' },
                    { num: 6, text: userResponse.question6 || '' },
                    { num: 7, text: userResponse.question7 || '' },
                    { num: 8, text: userResponse.question8 || '' },
                    { num: 9, text: userResponse.question9 || '' },
                    { num: 10, text: userResponse.question10 || '' }
                ];

                // 在PDF上填写答案（完全按绝对坐标绘制，每题互不影响）
                let currentPage = page; // 当前使用的页面
                
                for (let i = 0; i < questions.length; i++) {
                    const q = questions[i];
                    if (!q.text || !q.text.trim()) continue;

                    // 该题的起始Y坐标（必须在 answerPositions 里配置）
                    const startY = answerPositions[q.num];
                    if (typeof startY !== 'number') {
                        // 如果没有配置位置，就跳过这题，避免画到奇怪的位置
                        console.warn(`answerPositions 中未配置第 ${q.num} 题的位置，已跳过该题`);
                        continue;
                    }

                    // 处理长文本换行
                    const lines = wrapText(q.text, maxWidth, fontSize, font);

                    let yBase = startY;
                    // 固定位置模式：不进行自动换页检查，直接使用用户指定的位置
                    // （如果用户设置的位置会导致内容超出页面，由用户自己负责调整位置）

                    // 绘制答案文本（每行，不显示问题编号）
                    // 固定位置模式：直接按用户指定的位置绘制，不进行换页检查
                    for (let lineIndex = 0; lineIndex < lines.length; lineIndex++) {
                        const line = lines[lineIndex];
                        const yPos = yBase - (lineIndex * lineHeight);
                        await drawTextSmart(currentPage, line, leftMargin, yPos, fontSize, false);
                    }
                }

                // 保存PDF
                const pdfBytes = await pdfDoc.save();
                const blob = new Blob([pdfBytes], { type: 'application/pdf' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                const dateStr = new Date().toISOString().split('T')[0];
                link.download = `问卷回答_${dateStr}.pdf`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);

                showAlert('PDF生成成功！', 'success');
            } catch (error) {
                console.error('生成PDF失败:', error);
                showAlert('生成PDF失败：' + error.message, 'error');
            }
        }

        // 文本换行辅助函数（改进版，按字符处理中文）
        function wrapText(text, maxWidth, fontSize, font) {
            if (!text) return [];
            
            // 简化处理：按字符分割，每行约60个字符（可根据实际调整）
            const charsPerLine = 60;
            const lines = [];
            let currentLine = '';
            
            // 按字符处理，支持中文
            for (let i = 0; i < text.length; i++) {
                const char = text[i];
                currentLine += char;
                
                // 如果遇到换行符，直接换行
                if (char === '\n') {
                    lines.push(currentLine.trim());
                    currentLine = '';
                } 
                // 如果当前行长度达到限制，换行
                else if (currentLine.length >= charsPerLine) {
                    // 尝试在空格或标点处换行
                    let breakPoint = currentLine.length;
                    for (let j = currentLine.length - 1; j >= currentLine.length - 20 && j >= 0; j--) {
                        const c = currentLine[j];
                        if (c === ' ' || c === '，' || c === '。' || c === '、' || c === '；') {
                            breakPoint = j + 1;
                            break;
                        }
                    }
                    
                    lines.push(currentLine.substring(0, breakPoint).trim());
                    currentLine = currentLine.substring(breakPoint);
                }
            }
            
            if (currentLine.trim()) {
                lines.push(currentLine.trim());
            }
            
            return lines.length > 0 ? lines : [''];
        }
    </script>
</body>
</html>

