
// 添加一些交互效果
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
            document.querySelector('a[href="dishware_upload.php"]').click();
        } else if (e.key === '2') {
            document.querySelector('a[href="dishware_stock.php"]').click();
        }
    });
});
