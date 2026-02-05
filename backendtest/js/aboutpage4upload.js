// 切换年份 Tab
function switchYear(year) {
    document.querySelectorAll('.year-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.timeline-content').forEach(content => {
        content.classList.remove('active');
    });

    document.querySelector(`.year-tab[data-year="${year}"]`).classList.add('active');
    document.getElementById(`timeline-${year}`).classList.add('active');
}

// 文件选择功能
function initFileInputs() {
    document.querySelectorAll('.file-input').forEach(input => {
        input.addEventListener('dragover', (e) => {
            e.preventDefault();
            input.style.borderColor = '#e54a00';
            input.style.background = '#fff5f0';
        });

        input.addEventListener('dragleave', (e) => {
            e.preventDefault();
            input.style.borderColor = '#FF5C00';
            input.style.background = '#fff9f5';
        });

        input.addEventListener('drop', (e) => {
            e.preventDefault();
            const files = e.dataTransfer.files;
            const fileInput = input.querySelector('input[type="file"]');
            fileInput.files = files;

            input.style.borderColor = '#FF5C00';
            input.style.background = '#fff9f5';

            if (files.length > 0) {
                const textDiv = input.querySelector('.file-input-text');
                textDiv.innerHTML = `已选择: ${files[0].name}`;
            }
        });

        // 点击触发
        input.onclick = function () {
            input.querySelector('input[type="file"]').click();
        };

        // 阻止冒泡
        input.querySelector('input[type="file"]').onclick = function (e) {
            e.stopPropagation();
        };
    });

    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function () {
            const textDiv = this.parentElement.querySelector('.file-input-text');
            if (this.files.length > 0) {
                textDiv.innerHTML = `已选择: ${this.files[0].name}`;
            }
        });
    });
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', () => {
    initFileInputs();

    // 如果有 Hash，切换到对应年份
    if (window.location.hash) {
        const hashYear = window.location.hash.substring(1);
        const tab = document.querySelector(`.year-tab[data-year="${hashYear}"]`);
        if (tab) switchYear(hashYear);
    }
});
