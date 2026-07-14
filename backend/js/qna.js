
function isQnaReactV2Page() {
    return /\/qna-v2(?:\/|$|\?)/.test(window.location.pathname || '');
}

function getQnaBackendBase() {
    if (window.__KUNZZ_BACKEND_BASE__) {
        return String(window.__KUNZZ_BACKEND_BASE__).replace(/\/$/, '');
    }

    const path = window.location.pathname || '';
    const match = path.match(/^(.*?\/backend)(?:\/|$)/);
    if (match) {
        return match[1];
    }

    return '/backend';
}

function getSiteRootBase() {
    const backendBase = getQnaBackendBase();
    if (backendBase.endsWith('/backend')) {
        return backendBase.slice(0, -'/backend'.length);
    }
    return backendBase;
}

function getFormPdfUrl(filename) {
    const root = getSiteRootBase();
    return `${root}/form/${filename}`.replace(/([^:]\/)\/+/g, '$1');
}

function getFontUrl(filename) {
    const root = getSiteRootBase();
    return `${root}/fonts/${filename}`.replace(/([^:]\/)\/+/g, '$1');
}

function getQnaApiUrl() {
    return `${getQnaBackendBase()}/qnaapi.php`;
}

function getQnaUser() {
    return window.__QNA_USER__ || { username: 'User', position: 'User' };
}

let userResponse = null;
let isSubmitted = false;

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

async function loadUserResponse() {
    try {
        const res = await fetch(getQnaApiUrl(), {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
        });
        const data = await res.json();

        if (data.success && data.data) {
            userResponse = data.data;
            isSubmitted = true;
            switchToViewMode();
            populateViewMode(data.data);
        } else {
            isSubmitted = false;
        }
    } catch (error) {
        console.error('加载问卷失败:', error);
        if (typeof showAlert === 'function') {
            showAlert('加载问卷失败，请刷新页面重试', 'error');
        }
    }
}

function switchToViewMode() {
    document.body.classList.add('mode-view');
    const form = document.getElementById('qnaForm');
    if (form) form.style.display = 'none';
    updateButtonVisibility();
}

function populateViewMode(data) {
    for (let i = 1; i <= 10; i++) {
        const answer = data[`question${i}`] || '';
        const viewElement = document.getElementById(`view-question${i}`);
        if (viewElement) {
            if (answer.trim()) {
                viewElement.textContent = answer;
                viewElement.classList.remove('empty-answer');
            } else {
                viewElement.innerHTML = '<span class="empty-answer">未填写</span>';
            }
        }
    }
}

async function handleQnaFormSubmit(event) {
    event.preventDefault();

    if (isSubmitted) {
        if (typeof showAlert === 'function') {
            showAlert('您已经提交过问卷，每个用户只能提交一次', 'error');
        }
        return;
    }

    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
        submitBtn.innerHTML = '<div class="loading"></div> 提交中...';
        submitBtn.disabled = true;
    }

    try {
        const formData = new FormData(event.target);
        const data = {};
        for (let i = 1; i <= 10; i++) {
            data[`question${i}`] = formData.get(`question${i}`) || '';
        }

        const res = await fetch(getQnaApiUrl(), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(data),
        });

        const result = await res.json();

        if (result.success) {
            if (typeof showAlert === 'function') {
                showAlert('问卷提交成功！', 'success');
            }
            isSubmitted = true;
            await loadUserResponse();
            updateButtonVisibility();
        } else {
            if (typeof showAlert === 'function') {
                showAlert(result.message || '提交失败，请重试', 'error');
            }
            if (submitBtn) {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }
    } catch (error) {
        console.error('提交失败:', error);
        if (typeof showAlert === 'function') {
            showAlert('网络错误，请稍后重试', 'error');
        }
        if (submitBtn) {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }
}

function resetForm() {
    if (confirm('确定要清空所有回答吗？')) {
        const form = document.getElementById('qnaForm');
        if (form) form.reset();
    }
}

function loadScriptOnce(src, id) {
    const existing = document.getElementById(id);
    if (existing?.src === src) {
        return Promise.resolve();
    }
    if (existing) {
        existing.remove();
    }

    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.id = id;
        script.src = src;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error(`Failed to load ${src}`));
        document.body.appendChild(script);
    });
}

async function ensurePdfLibraries() {
    const backendBase = getQnaBackendBase();

    if (typeof window.PDFLib === 'undefined') {
        await loadScriptOnce(
            'https://cdn.jsdelivr.net/npm/pdf-lib/dist/pdf-lib.min.js',
            'qna-pdf-lib',
        );
    }

    if (typeof window.fontkit === 'undefined') {
        try {
            await loadScriptOnce(`${backendBase}/get_fontkit.php`, 'qna-fontkit');
        } catch (error) {
            console.warn('fontkit 加载失败，将尝试继续生成 PDF:', error);
        }
    }
}

async function printTemplate() {
    try {
        const response = await fetch(getFormPdfUrl('surveyform.pdf'));

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
        if (typeof showAlert === 'function') {
            showAlert('下载PDF模板失败：' + error.message, 'error');
        }
    }
}

async function generatePDF() {
    if (!userResponse) {
        if (typeof showAlert === 'function') {
            showAlert('没有可打印的问卷数据', 'error');
        }
        return;
    }

    const { username: currentUsername, position: currentPosition } = getQnaUser();

    try {
        await ensurePdfLibraries();

        if (typeof showAlert === 'function') {
            showAlert('正在生成PDF，请稍候...', 'success');
        }

        const templateResponse = await fetch(getFormPdfUrl('survey.pdf'));
        if (!templateResponse.ok) {
            throw new Error('无法加载PDF模板，请确保 form/survey.pdf 文件存在');
        }

        const templateBytes = await templateResponse.arrayBuffer();
        const { PDFDocument, rgb } = PDFLib;

        const pdfDoc = await PDFDocument.load(templateBytes);

        let fontkitInstance = null;
        if (typeof window.fontkit !== 'undefined') {
            if (window.fontkit.default) {
                fontkitInstance = window.fontkit.default;
            } else if (window.fontkit.fontkit) {
                fontkitInstance = window.fontkit.fontkit;
            } else {
                fontkitInstance = window.fontkit;
            }
        } else {
            const possibleNames = ['FontKit', 'pdfLibFontkit', 'PDFLibFontkit'];
            for (const name of possibleNames) {
                if (typeof window[name] !== 'undefined') {
                    fontkitInstance = window[name];
                    break;
                }
            }
        }

        if (fontkitInstance) {
            if (typeof pdfDoc.registerFontkit === 'function') {
                pdfDoc.registerFontkit(fontkitInstance);
            } else {
                throw new Error('pdfDoc.registerFontkit 不是函数。请检查 pdf-lib 版本是否正确。');
            }
        } else {
            throw new Error('fontkit 未加载。请确保 fonts/fontkit.umd.min.js 文件存在并已正确加载。');
        }

        const page = pdfDoc.getPage(0);
        const { width, height } = page.getSize();

        const regularFontResponse = await fetch(getFontUrl('NotoSansSC-Regular.ttf'));
        const boldFontResponse = await fetch(getFontUrl('NotoSansSC-Bold.ttf'));

        if (!regularFontResponse.ok || !boldFontResponse.ok) {
            throw new Error('无法加载中文字体文件，请确保 fonts 文件夹中有 NotoSansSC-Regular.ttf 和 NotoSansSC-Bold.ttf');
        }

        const regularFontBytes = await regularFontResponse.arrayBuffer();
        const boldFontBytes = await boldFontResponse.arrayBuffer();

        const font = await pdfDoc.embedFont(regularFontBytes);
        const boldFont = await pdfDoc.embedFont(boldFontBytes);

        const fontSize = 12;
        const headerFontSize = 12;
        const textColor = rgb(0, 0, 0);
        const lineHeight = 14;
        const leftMargin = 25;
        const rightMargin = 100;
        const maxWidth = width - leftMargin - rightMargin;

        const userInfoText = `${currentUsername} (${currentPosition})`;
        const textWidth = boldFont.widthOfTextAtSize(userInfoText, headerFontSize);
        const centerX = (width - textWidth) / 2;

        page.drawText(userInfoText, {
            x: centerX,
            y: height - 40,
            size: headerFontSize,
            font: boldFont,
            color: textColor,
        });

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
            { num: 10, text: userResponse.question10 || '' },
        ];

        const currentPage = page;

        for (let i = 0; i < questions.length; i++) {
            const q = questions[i];
            if (!q.text || !q.text.trim()) continue;

            const startY = answerPositions[q.num];
            if (typeof startY !== 'number') continue;

            const lines = wrapText(q.text, maxWidth, fontSize, font);
            const yBase = startY;

            for (let lineIndex = 0; lineIndex < lines.length; lineIndex++) {
                const line = lines[lineIndex];
                const yPos = yBase - (lineIndex * lineHeight);
                currentPage.drawText(line, {
                    x: leftMargin,
                    y: yPos,
                    size: fontSize,
                    font,
                    color: textColor,
                });
            }
        }

        const pdfBytes = await pdfDoc.save();
        const blob = new Blob([pdfBytes], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        const now = new Date();
        const dateStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
        link.download = `surveyform_${dateStr}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        if (typeof showAlert === 'function') {
            showAlert('PDF生成成功！', 'success');
        }
    } catch (error) {
        console.error('生成PDF失败:', error);
        if (typeof showAlert === 'function') {
            showAlert('生成PDF失败：' + error.message, 'error');
        }
    }
}

function wrapText(text, maxWidth, fontSize, font) {
    if (!text) return [];

    const charsPerLine = 60;
    const lines = [];
    let currentLine = '';

    for (let i = 0; i < text.length; i++) {
        const char = text[i];
        currentLine += char;

        if (char === '\n') {
            lines.push(currentLine.trim());
            currentLine = '';
        } else if (currentLine.length >= charsPerLine) {
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

function bindPrintButton() {
    const button = document.querySelector('[data-qna-root] .btn-print-template');
    if (!button || button.dataset.qnaPrintBound === '1') {
        return;
    }

    button.dataset.qnaPrintBound = '1';
    button.addEventListener('click', (event) => {
        event.preventDefault();
        printTemplate();
    });
}

function bindQnaForm() {
    const form = document.getElementById('qnaForm');
    if (!form || form.dataset.qnaBound === '1') {
        return;
    }

    form.dataset.qnaBound = '1';
    form.addEventListener('submit', handleQnaFormSubmit);
}

async function bootQna() {
    userResponse = null;
    isSubmitted = false;
    document.body.classList.remove('mode-view');

    const form = document.getElementById('qnaForm');
    if (form) {
        form.style.display = '';
    }

    bindQnaForm();
    bindPrintButton();
    await loadUserResponse();
    updateButtonVisibility();
}

window.bootQna = bootQna;
window.reinitQna = bootQna;
window.printTemplate = printTemplate;
window.generatePDF = generatePDF;
window.resetForm = resetForm;

if (!isQnaReactV2Page()) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootQna);
    } else {
        bootQna();
    }
}
