
// 从PHP获取用户名和职位
const currentUsername = '<?php echo isset($username) ? addslashes($username) : "User"; ?>';
const currentPosition = '<?php echo isset($position) && !empty($position) ? addslashes($position) : "User"; ?>';

let userResponse = null;
let isSubmitted = false;

// 页面加载时检查是否已提交
document.addEventListener('DOMContentLoaded', async function () {
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
document.getElementById('qnaForm').addEventListener('submit', async function (e) {
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

// 打印问卷模板（直接下载）
async function printTemplate() {
    try {
        const templatePath = '../form/surveyform.pdf';
        const response = await fetch(templatePath);

        if (!response.ok) {
            throw new Error('无法加载PDF模板');
        }

        const blob = await response.blob();
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'surveyform.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    } catch (error) {
        console.error('下载PDF模板失败:', error);
        showAlert('下载PDF模板失败：' + error.message, 'error');
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

        // 加载 PDF 文档（必须先创建实例）
        const pdfDoc = await PDFDocument.load(templateBytes);

        // 查找 fontkit 实例
        let fontkitInstance = null;

        // @pdf-lib/fontkit 通常导出为 window.fontkit
        if (typeof window.fontkit !== 'undefined') {
            // 检查是否是 UMD 模块导出
            if (window.fontkit.default) {
                fontkitInstance = window.fontkit.default;
            } else if (window.fontkit.fontkit) {
                fontkitInstance = window.fontkit.fontkit;
            } else {
                fontkitInstance = window.fontkit;
            }
            console.log('找到 fontkit 实例');
        } else {
            // 尝试其他可能的变量名
            const possibleNames = ['FontKit', 'pdfLibFontkit', 'PDFLibFontkit'];
            for (const name of possibleNames) {
                if (typeof window[name] !== 'undefined') {
                    fontkitInstance = window[name];
                    console.log(`找到 fontkit，使用变量名: ${name}`);
                    break;
                }
            }
        }

        // 在实例上注册 fontkit（必须在 embedFont 之前）
        if (fontkitInstance) {
            if (typeof pdfDoc.registerFontkit === 'function') {
                pdfDoc.registerFontkit(fontkitInstance);
                console.log('fontkit 已成功注册到 PDFDocument 实例');
            } else {
                throw new Error('pdfDoc.registerFontkit 不是函数。请检查 pdf-lib 版本是否正确。');
            }
        } else {
            throw new Error('fontkit 未加载。请确保 fonts/fontkit.umd.min.js 文件存在并已正确加载。');
        }

        // 获取第一页
        const page = pdfDoc.getPage(0);
        const { width, height } = page.getSize();
        console.log(`PDF尺寸: 宽度=${width.toFixed(2)}pt, 高度=${height.toFixed(2)}pt`);

        // 加载本地中文字体文件
        const regularFontResponse = await fetch('../fonts/NotoSansSC-Regular.ttf');
        const boldFontResponse = await fetch('../fonts/NotoSansSC-Bold.ttf');

        if (!regularFontResponse.ok || !boldFontResponse.ok) {
            throw new Error('无法加载中文字体文件，请确保 fonts 文件夹中有 NotoSansSC-Regular.ttf 和 NotoSansSC-Bold.ttf');
        }

        const regularFontBytes = await regularFontResponse.arrayBuffer();
        const boldFontBytes = await boldFontResponse.arrayBuffer();

        // 尝试嵌入中文字体
        let font, boldFont;
        try {
            font = await pdfDoc.embedFont(regularFontBytes);
            boldFont = await pdfDoc.embedFont(boldFontBytes);
            console.log('成功加载并嵌入中文字体，将直接以文字形式打印中文');
        } catch (e) {
            console.error('嵌入字体失败:', e);
            if (e.message && e.message.includes('fontkit')) {
                throw new Error('嵌入字体需要 fontkit，但注册失败。请检查 fontkit 是否正确加载。错误: ' + e.message);
            } else {
                throw new Error('嵌入字体失败: ' + e.message);
            }
        }

        // 设置字体大小和颜色
        const fontSize = 12;
        const headerFontSize = 12;
        const textColor = rgb(0, 0, 0);
        const lineHeight = 14;
        const leftMargin = 25;  // 减小这个值可以让答案往左移（当前：80，原值：100）
        const rightMargin = 100;
        const maxWidth = width - leftMargin - rightMargin;
        const topMargin = 100;

        // 在页面顶部中间绘制用户名和职位
        const userInfoText = `${currentUsername} (${currentPosition})`;
        // 计算文本宽度并居中
        const textWidth = boldFont.widthOfTextAtSize(userInfoText, headerFontSize);
        const centerX = (width - textWidth) / 2;

        page.drawText(userInfoText, {
            x: centerX,
            y: height - 40,
            size: headerFontSize,
            font: boldFont,
            color: textColor,
        });

        // 每个问题的“绝对起始Y坐标”配置（完全类似 invoice：固定坐标）
        // 这里建议先用一组大概的初始值，之后你只改这些数字就能调位置
        // 例：1: height - 200 表示第1题起点Y为 height-200。
        const answerPositions = {
            1: height - 105,
            2: height - 181,
            3: height - 276,
            4: height - 353,
            5: height - 430,
            6: height - 507,
            7: height - 583,
            8: height - 661,
            9: height - 738,
            10: height - 815,
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
                currentPage.drawText(line, {
                    x: leftMargin,
                    y: yPos,
                    size: fontSize,
                    font: font,
                    color: textColor,
                });
            }
        }

        // 保存PDF
        const pdfBytes = await pdfDoc.save();
        const blob = new Blob([pdfBytes], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        const dateStr = new Date().toISOString().split('T')[0];
        link.download = `surveyform_${dateStr}.pdf`;
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