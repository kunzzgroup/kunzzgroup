<?php
// 渐进式加载系统 - 让网页先显示，媒体内容后续加载
// 这个系统会优先加载关键内容，延迟加载非关键媒体

function getProgressiveLoadingScript() {
    return '
<script>
// 渐进式加载系统
class ProgressiveLoader {
    constructor() {
        this.loadedImages = new Set();
        this.loadedVideos = new Set();
        this.loadedAudios = new Set();
        this.observerOptions = {
            root: null,
            rootMargin: "50px", // 提前50px开始加载
            threshold: 0.1
        };
        
        this.init();
    }
    
    init() {
        // 1. 立即显示页面结构
        this.showPageStructure();
        
        // 2. 延迟加载媒体内容
        this.setupLazyLoading();
        
        // 3. 优化用户体验
        this.setupLoadingStates();
    }
    
    showPageStructure() {
        // 立即显示页面框架，隐藏媒体内容
        document.body.style.opacity = "1";
        document.body.style.visibility = "visible";
        
        // 隐藏所有媒体内容，显示占位符
        this.hideMediaContent();
    }
    
    hideMediaContent() {
        // 隐藏图片，显示占位符
        const images = document.querySelectorAll("img");
        images.forEach(img => {
            if (!img.dataset.lazy) {
                img.style.opacity = "0";
                img.style.transition = "opacity 0.3s ease";
                
                // 添加占位符
                if (!img.parentElement.querySelector(".image-placeholder")) {
                    const placeholder = document.createElement("div");
                    placeholder.className = "image-placeholder";
                    placeholder.style.cssText = `
                        width: 100%;
                        height: 200px;
                        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                        background-size: 200% 100%;
                        animation: shimmer 1.5s infinite;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: #999;
                        font-size: 14px;
                    `;
                    placeholder.textContent = "加载中...";
                    img.parentElement.insertBefore(placeholder, img);
                }
            }
        });
        
        // 隐藏视频，显示占位符
        const videos = document.querySelectorAll("video");
        videos.forEach(video => {
            video.style.opacity = "0";
            video.style.transition = "opacity 0.3s ease";
        });
        
        // 隐藏音频，显示占位符
        const audios = document.querySelectorAll("audio");
        audios.forEach(audio => {
            audio.style.opacity = "0";
            audio.style.transition = "opacity 0.3s ease";
        });
    }
    
    setupLazyLoading() {
        // 图片懒加载
        this.setupImageLazyLoading();
        
        // 视频懒加载
        this.setupVideoLazyLoading();
        
        // 音频懒加载
        this.setupAudioLazyLoading();
        
        // 背景图片懒加载
        this.setupBackgroundLazyLoading();
    }
    
    setupImageLazyLoading() {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    this.loadImage(img);
                    imageObserver.unobserve(img);
                }
            });
        }, this.observerOptions);
        
        // 观察所有图片
        document.querySelectorAll("img").forEach(img => {
            if (!img.dataset.lazy) {
                imageObserver.observe(img);
            }
        });
    }
    
    loadImage(img) {
        return new Promise((resolve) => {
            // 创建新的图片对象进行预加载
            const imageLoader = new Image();
            
            imageLoader.onload = () => {
                // 图片加载完成，显示图片
                img.style.opacity = "1";
                
                // 移除占位符
                const placeholder = img.parentElement.querySelector(".image-placeholder");
                if (placeholder) {
                    placeholder.remove();
                }
                
                this.loadedImages.add(img.src);
                resolve();
            };
            
            imageLoader.onerror = () => {
                // 图片加载失败，显示错误占位符
                const placeholder = img.parentElement.querySelector(".image-placeholder");
                if (placeholder) {
                    placeholder.textContent = "加载失败";
                    placeholder.style.background = "#ffebee";
                    placeholder.style.color = "#c62828";
                }
                resolve();
            };
            
            // 开始加载图片
            imageLoader.src = img.src;
        });
    }
    
    setupVideoLazyLoading() {
        const videoObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const video = entry.target;
                    this.loadVideo(video);
                    videoObserver.unobserve(video);
                }
            });
        }, this.observerOptions);
        
        // 观察所有视频
        document.querySelectorAll("video").forEach(video => {
            videoObserver.observe(video);
        });
    }
    
    loadVideo(video) {
        return new Promise((resolve) => {
            video.addEventListener("loadeddata", () => {
                video.style.opacity = "1";
                this.loadedVideos.add(video.src);
                resolve();
            });
            
            video.addEventListener("error", () => {
                console.warn("视频加载失败:", video.src);
                resolve();
            });
            
            // 开始加载视频
            video.load();
        });
    }
    
    setupAudioLazyLoading() {
        const audioObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const audio = entry.target;
                    this.loadAudio(audio);
                    audioObserver.unobserve(audio);
                }
            });
        }, this.observerOptions);
        
        // 观察所有音频
        document.querySelectorAll("audio").forEach(audio => {
            audioObserver.observe(audio);
        });
    }
    
    loadAudio(audio) {
        return new Promise((resolve) => {
            audio.addEventListener("loadeddata", () => {
                audio.style.opacity = "1";
                this.loadedAudios.add(audio.src);
                resolve();
            });
            
            audio.addEventListener("error", () => {
                console.warn("音频加载失败:", audio.src);
                resolve();
            });
            
            // 开始加载音频
            audio.load();
        });
    }
    
    setupBackgroundLazyLoading() {
        // 懒加载背景图片
        const backgroundObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    const bgImage = element.style.backgroundImage;
                    if (bgImage && bgImage !== "none") {
                        this.loadBackgroundImage(element, bgImage);
                    }
                    backgroundObserver.unobserve(element);
                }
            });
        }, this.observerOptions);
        
        // 观察所有可能有背景图片的元素
        document.querySelectorAll("[style*=\"background-image\"]").forEach(element => {
            backgroundObserver.observe(element);
        });
    }
    
    loadBackgroundImage(element, bgImage) {
        const url = bgImage.match(/url\\([\'"]?([^\'"]*)[\'"]?\\)/);
        if (url && url[1]) {
            const img = new Image();
            img.onload = () => {
                element.style.backgroundImage = bgImage;
                element.style.opacity = "1";
            };
            img.src = url[1];
        }
    }
    
    setupLoadingStates() {
        // 添加加载状态指示器
        this.addLoadingIndicator();
        
        // 优化用户体验
        this.optimizeUserExperience();
    }
    
    addLoadingIndicator() {
        // 在页面顶部添加加载进度条
        const progressBar = document.createElement("div");
        progressBar.id = "loading-progress";
        progressBar.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
            z-index: 10000;
            transition: width 0.3s ease;
        `;
        document.body.appendChild(progressBar);
        
        // 模拟加载进度
        this.simulateLoadingProgress();
    }
    
    simulateLoadingProgress() {
        let progress = 0;
        const progressBar = document.getElementById("loading-progress");
        
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            
            progressBar.style.width = progress + "%";
            
            if (progress >= 90) {
                clearInterval(interval);
                // 延迟隐藏进度条
                setTimeout(() => {
                    progressBar.style.opacity = "0";
                    setTimeout(() => {
                        progressBar.remove();
                    }, 300);
                }, 500);
            }
        }, 100);
    }
    
    optimizeUserExperience() {
        // 添加CSS动画
        const style = document.createElement("style");
        style.textContent = `
            @keyframes shimmer {
                0% { background-position: -200% 0; }
                100% { background-position: 200% 0; }
            }
            
            .image-placeholder {
                animation: shimmer 1.5s infinite;
            }
            
            /* 平滑过渡 */
            img, video, audio {
                transition: opacity 0.3s ease;
            }
            
            /* 加载状态 */
            .loading {
                opacity: 0.6;
                pointer-events: none;
            }
        `;
        document.head.appendChild(style);
    }
    
    // 公共API
    getLoadingStatus() {
        return {
            images: this.loadedImages.size,
            videos: this.loadedVideos.size,
            audios: this.loadedAudios.size
        };
    }
    
    preloadCriticalMedia() {
        // 预加载关键媒体
        const criticalImages = document.querySelectorAll("img[data-critical]");
        criticalImages.forEach(img => {
            this.loadImage(img);
        });
    }
}

// 页面加载完成后初始化
document.addEventListener("DOMContentLoaded", () => {
    window.progressiveLoader = new ProgressiveLoader();
    
    // 预加载关键媒体
    setTimeout(() => {
        window.progressiveLoader.preloadCriticalMedia();
    }, 100);
});

// 页面完全加载后隐藏加载状态
window.addEventListener("load", () => {
    const progressBar = document.getElementById("loading-progress");
    if (progressBar) {
        progressBar.style.width = "100%";
        setTimeout(() => {
            progressBar.style.opacity = "0";
            setTimeout(() => {
                progressBar.remove();
            }, 300);
        }, 200);
    }
});
</script>
';
}

function getProgressiveLoadingCSS() {
    return '
<style>
/* 渐进式加载样式 */
body {
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease;
}

body.loaded {
    opacity: 1;
    visibility: visible;
}

/* 占位符样式 */
.image-placeholder {
    width: 100%;
    height: 200px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    font-size: 14px;
    border-radius: 4px;
    margin: 10px 0;
}

/* 加载动画 */
@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

/* 媒体元素初始状态 */
img, video, audio {
    opacity: 0;
    transition: opacity 0.3s ease;
}

img.loaded, video.loaded, audio.loaded {
    opacity: 1;
}

/* 加载进度条 */
#loading-progress {
    position: fixed;
    top: 0;
    left: 0;
    width: 0%;
    height: 3px;
    background: linear-gradient(90deg, #4CAF50, #8BC34A);
    z-index: 10000;
    transition: width 0.3s ease;
}

/* 响应式优化 */
@media (max-width: 768px) {
    .image-placeholder {
        height: 150px;
        font-size: 12px;
    }
}
</style>
';
}

// 输出渐进式加载系统
echo getProgressiveLoadingCSS();
echo getProgressiveLoadingScript();
?>
