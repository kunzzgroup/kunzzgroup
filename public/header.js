// Header相关的JavaScript功能
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    const loginBtn = document.getElementById('loginBtn');

    // 登录下拉菜单元素
    const loginDropdownMenu = document.getElementById('loginDropdownMenu');

    // 语言切换下拉菜单元素
    const languageBtn = document.getElementById('languageBtn');
    const languageDropdownMenu = document.getElementById('languageDropdownMenu');

    function moveLoginBtn() {
        if (window.innerWidth <= 768) {
            if (!navMenu.contains(loginBtn)) {
                navMenu.appendChild(loginBtn);
            }
        } else {
            // 如果宽度大于768，确保loginBtn在right-section中
            const rightSection = document.querySelector('.header-right-section');
            if (rightSection && !rightSection.contains(loginBtn)) {
                rightSection.insertBefore(loginBtn, rightSection.firstChild);
            }
        }
    }

    // 点击汉堡切换菜单
    if (hamburger) {
        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }

    // ========== 登录下拉菜单功能 ==========
    let loginHoverTimeout;

    // 鼠标进入登录按钮区域时显示下拉菜单
    if (loginBtn) {
        loginBtn.addEventListener('mouseenter', function() {
            // 清除可能存在的隐藏延时
            clearTimeout(loginHoverTimeout);
            
            // 显示菜单
            loginDropdownMenu.classList.add('show');
            loginBtn.classList.add('active');
        });

        // 鼠标离开登录按钮区域时延迟隐藏下拉菜单
        loginBtn.addEventListener('mouseleave', function() {
            // 设置延时隐藏，给用户时间移动到下拉菜单
            loginHoverTimeout = setTimeout(() => {
                loginDropdownMenu.classList.remove('show');
                loginBtn.classList.remove('active');
            }, 100); // 100ms延迟
        });
    }

    // 鼠标进入登录下拉菜单时保持显示
    if (loginDropdownMenu) {
        loginDropdownMenu.addEventListener('mouseenter', function() {
            // 清除隐藏延时
            clearTimeout(loginHoverTimeout);
            
            // 确保菜单保持显示
            loginDropdownMenu.classList.add('show');
            loginBtn.classList.add('active');
        });

        // 鼠标离开登录下拉菜单时隐藏
        loginDropdownMenu.addEventListener('mouseleave', function() {
            loginDropdownMenu.classList.remove('show');
            loginBtn.classList.remove('active');
        });

        // 点击登录下拉菜单项时的处理
        const loginDropdownItems = document.querySelectorAll('.login-dropdown-item');
        loginDropdownItems.forEach(item => {
            item.addEventListener('click', function(e) {
                console.log('选择了登录：', this.textContent);
                
                // 关闭下拉菜单
                loginDropdownMenu.classList.remove('show');
                loginBtn.classList.remove('active');
            });
        });
    }

    // ========== 语言切换下拉菜单功能 ==========
    let languageHoverTimeout;

    // 鼠标进入语言按钮区域时显示下拉菜单
    if (languageBtn) {
        languageBtn.addEventListener('mouseenter', function() {
            // 清除可能存在的隐藏延时
            clearTimeout(languageHoverTimeout);
            
            // 显示菜单
            languageDropdownMenu.classList.add('show');
            languageBtn.classList.add('active');
        });

        // 鼠标离开语言按钮区域时延迟隐藏下拉菜单
        languageBtn.addEventListener('mouseleave', function() {
            // 设置延时隐藏，给用户时间移动到下拉菜单
            languageHoverTimeout = setTimeout(() => {
                languageDropdownMenu.classList.remove('show');
                languageBtn.classList.remove('active');
            }, 200); // 200ms延迟
        });
    }

    // 鼠标进入语言下拉菜单时保持显示
    if (languageDropdownMenu) {
        languageDropdownMenu.addEventListener('mouseenter', function() {
            // 清除隐藏延时
            clearTimeout(languageHoverTimeout);
            
            // 确保菜单保持显示
            languageDropdownMenu.classList.add('show');
            languageBtn.classList.add('active');
        });

        // 鼠标离开语言下拉菜单时隐藏
        languageDropdownMenu.addEventListener('mouseleave', function() {
            languageDropdownMenu.classList.remove('show');
            languageBtn.classList.remove('active');
        });

        // 点击语言下拉菜单项时的处理
        const languageDropdownItems = document.querySelectorAll('.language-dropdown-item');
        languageDropdownItems.forEach(item => {
            item.addEventListener('click', function() {
                console.log('选择了语言：', this.textContent);

                // 关闭下拉菜单（这仍然可以保留）
                languageDropdownMenu.classList.remove('show');
                languageBtn.classList.remove('active');
                
                // 更新语言按钮显示
                const selectedLang = this.getAttribute('data-lang');
                if (selectedLang === 'en') {
                    languageBtn.textContent = 'English';
                } else if (selectedLang === 'my') {
                    languageBtn.textContent = 'Malay';
                } else {
                    languageBtn.textContent = '中文';
                }
                
                // 关闭下拉菜单
                languageDropdownMenu.classList.remove('show');
                languageBtn.classList.remove('active');
                
                // 这里可以添加实际的语言切换逻辑
                console.log('切换到语言：', selectedLang);
            });
        });
    }

    // ESC键关闭所有下拉菜单
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (loginDropdownMenu) {
                loginDropdownMenu.classList.remove('show');
                loginBtn.classList.remove('active');
            }
            if (languageDropdownMenu) {
                languageDropdownMenu.classList.remove('show');
                languageBtn.classList.remove('active');
            }
        }
    });

    // 点击页面其他地方关闭下拉菜单
    document.addEventListener('click', function(e) {
        // 如果点击的不是登录相关元素，关闭登录下拉菜单
        if (loginBtn && loginDropdownMenu && !loginBtn.contains(e.target) && !loginDropdownMenu.contains(e.target)) {
            loginDropdownMenu.classList.remove('show');
            loginBtn.classList.remove('active');
        }
        
        // 如果点击的不是语言相关元素，关闭语言下拉菜单
        if (languageBtn && languageDropdownMenu && !languageBtn.contains(e.target) && !languageDropdownMenu.contains(e.target)) {
            languageDropdownMenu.classList.remove('show');
            languageBtn.classList.remove('active');
        }
    });

    // 页面加载时处理
    moveLoginBtn();

    // 窗口大小改变时也处理，防止resize后login位置错乱
    window.addEventListener('resize', moveLoginBtn);

    // 导航栏旗下品牌下拉菜单控制
    const navBrandsDropdown = document.querySelector('.header-nav-dropdown');
    const navBrandsDropdownMenu = document.getElementById('brandsNavDropdownMenu');

    if (navBrandsDropdown && navBrandsDropdownMenu) {
        navBrandsDropdown.addEventListener('mouseenter', function() {
            navBrandsDropdownMenu.classList.add('show');
        });

        navBrandsDropdown.addEventListener('mouseleave', function() {
            navBrandsDropdownMenu.classList.remove('show');
        });
    }
});
