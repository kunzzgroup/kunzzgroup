/**
 * 社交侧边栏交互功能
 * 可在任何页面中通过 script 引入
 * 使用方式: <script src="public/social.js"></script>
 */

class SocialSidebar {
    constructor() {
        this.sidebar = null;
        this.isLoaded = false;
        this.init();
    }

    /**
     * 初始化社交侧边栏
     */
    init() {
        // 等待DOM加载完成
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupSidebar());
        } else {
            this.setupSidebar();
        }
    }

    /**
     * 设置社交侧边栏
     */
    setupSidebar() {
        this.sidebar = document.querySelector('.social-sidebar');
        
        if (!this.sidebar) {
            console.warn('Social sidebar not found');
            return;
        }

        // 添加事件监听器
        this.addEventListeners();
        
        // 延迟显示侧边栏，确保页面加载完成
        this.showSidebar();
    }

    /**
     * 添加事件监听器
     */
    addEventListeners() {
        // 为每个社交图标添加点击事件
        const socialIcons = this.sidebar.querySelectorAll('.social-icon');
        
        socialIcons.forEach(icon => {
            icon.addEventListener('click', (e) => this.handleIconClick(e));
            icon.addEventListener('mouseenter', (e) => this.handleIconHover(e, true));
            icon.addEventListener('mouseleave', (e) => this.handleIconHover(e, false));
        });

        // 添加键盘导航支持
        this.sidebar.addEventListener('keydown', (e) => this.handleKeydown(e));
    }

    /**
     * 处理图标点击事件
     */
    handleIconClick(event) {
        const icon = event.currentTarget;
        const platform = icon.classList.contains('facebook') ? 'Facebook' : 
                        icon.classList.contains('instagram') ? 'Instagram' : 
                        icon.classList.contains('whatsapp') ? 'WhatsApp' : 'Unknown';
        
        // 添加点击动画效果
        icon.style.transform = 'scale(0.95)';
        setTimeout(() => {
            icon.style.transform = '';
        }, 150);

        // 记录点击事件（可用于分析）
        this.trackSocialClick(platform, icon.href);
    }

    /**
     * 处理图标悬停事件
     */
    handleIconHover(event, isHovering) {
        const icon = event.currentTarget;
        
        if (isHovering) {
            // 悬停时添加额外效果
            icon.style.transition = 'all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        } else {
            // 离开时恢复
            icon.style.transition = '';
        }
    }

    /**
     * 处理键盘导航
     */
    handleKeydown(event) {
        const icons = Array.from(this.sidebar.querySelectorAll('.social-icon'));
        const currentIndex = icons.indexOf(document.activeElement);
        
        switch(event.key) {
            case 'ArrowUp':
                event.preventDefault();
                const prevIndex = currentIndex > 0 ? currentIndex - 1 : icons.length - 1;
                icons[prevIndex].focus();
                break;
            case 'ArrowDown':
                event.preventDefault();
                const nextIndex = currentIndex < icons.length - 1 ? currentIndex + 1 : 0;
                icons[nextIndex].focus();
                break;
            case 'Enter':
            case ' ':
                event.preventDefault();
                document.activeElement.click();
                break;
        }
    }

    /**
     * 显示社交侧边栏
     */
    showSidebar() {
        if (this.isLoaded) return;

        // 延迟显示，确保页面内容已加载
        setTimeout(() => {
            if (this.sidebar) {
                this.sidebar.classList.add('social-loaded');
                this.isLoaded = true;
                
                // 触发自定义事件
                this.dispatchEvent('socialLoaded');
            }
        }, 500);
    }

    /**
     * 隐藏社交侧边栏
     */
    hideSidebar() {
        if (this.sidebar) {
            this.sidebar.classList.remove('social-loaded');
            this.isLoaded = false;
            
            // 触发自定义事件
            this.dispatchEvent('socialHidden');
        }
    }

    /**
     * 切换社交侧边栏显示状态
     */
    toggleSidebar() {
        if (this.isLoaded) {
            this.hideSidebar();
        } else {
            this.showSidebar();
        }
    }

    /**
     * 跟踪社交点击事件
     */
    trackSocialClick(platform, url) {
        // 这里可以添加分析代码，比如 Google Analytics
        console.log(`Social click tracked: ${platform} - ${url}`);
        
        // 示例：发送到分析服务
        if (typeof gtag !== 'undefined') {
            gtag('event', 'social_click', {
                'platform': platform,
                'url': url
            });
        }
    }

    /**
     * 分发自定义事件
     */
    dispatchEvent(eventName) {
        const event = new CustomEvent(eventName, {
            detail: {
                sidebar: this.sidebar,
                isLoaded: this.isLoaded
            }
        });
        document.dispatchEvent(event);
    }

    /**
     * 获取社交侧边栏状态
     */
    getStatus() {
        return {
            isLoaded: this.isLoaded,
            isVisible: this.sidebar ? this.sidebar.classList.contains('social-loaded') : false,
            iconCount: this.sidebar ? this.sidebar.querySelectorAll('.social-icon').length : 0
        };
    }
}

// 自动初始化
const socialSidebar = new SocialSidebar();

// 导出到全局作用域（如果需要）
window.SocialSidebar = SocialSidebar;
window.socialSidebar = socialSidebar;

// 监听自定义事件
document.addEventListener('socialLoaded', (event) => {
    console.log('Social sidebar loaded successfully');
});

document.addEventListener('socialHidden', (event) => {
    console.log('Social sidebar hidden');
});

// 页面卸载时清理
window.addEventListener('beforeunload', () => {
    if (socialSidebar.sidebar) {
        socialSidebar.sidebar.classList.remove('social-loaded');
    }
});
