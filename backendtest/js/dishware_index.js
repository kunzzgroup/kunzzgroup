// Dishware Index JS

document.addEventListener('DOMContentLoaded', function () {
    // 为卡片添加点击效果
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('click', function () {
            const link = this.querySelector('.card-btn');
            if (link) {
                link.click();
            }
        });
    });

    // 添加键盘导航支持
    document.addEventListener('keydown', function (e) {
        if (e.key === '1') {
            const uploadLink = document.querySelector('a[href="../visual/dishware_upload"]'); // Updated path
            if (uploadLink) uploadLink.click();
        } else if (e.key === '2') {
            const stockLink = document.querySelector('a[href="dishware_stock"]');
            if (stockLink) stockLink.click();
        }
    });
});
