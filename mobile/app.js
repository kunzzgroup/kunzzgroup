// ======= J3Mobile 登录相关功能 =======

// ======= WebP格式检测和fallback支持 =======
function supportsWebP() {
  return new Promise((resolve) => {
    const webP = new Image();
    webP.onload = webP.onerror = function () {
      resolve(webP.height === 2);
    };
    webP.src = "data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA";
  });
}

// 检测并应用背景图片（带WebP fallback）
async function setupBackgroundImage() {
  const loginSection = document.querySelector('.login-section');
  if (!loginSection) return;
  // 不预设背景色，优先由 CSS 控制；仅在失败时兜底
  try {
    const supports = await supportsWebP();
    const bgPath = supports
      ? '../../images/bg/phoneBG.webp'
      : '../../images/bg/phoneBG.jpg'; // 如果WebP不支持，使用JPG（需要你提供JPG版本）

    // 测试图片是否能加载
    const testImg = new Image();
    testImg.onload = function () {
      // 图片加载成功，应用背景图片
      loginSection.style.backgroundImage = `url('${bgPath}')`;
      loginSection.style.backgroundRepeat = 'no-repeat';
      loginSection.style.backgroundPosition = 'center center';
      loginSection.style.backgroundSize = 'cover';
    };
    testImg.onerror = function () {
      // 图片加载失败时，不设置内联颜色，保持由CSS接管
    };
    testImg.src = bgPath;
  } catch (error) {
    // 检测异常时，不设置内联颜色
  }
}

document.addEventListener("DOMContentLoaded", function () {
    // 设置背景图片（带WebP检测）
    setupBackgroundImage();
    // ======= 密码显示/隐藏切换 =======
    const toggle = document.getElementById("toggle-password");
    const password = document.getElementById("password");
    let visible = false;

    if (toggle && password) {
      toggle.addEventListener("click", function () {
        visible = !visible;
        password.type = visible ? "text" : "password";
        toggle.classList.toggle("active", visible);
      });
    }

    // ======= 输入框禁止输入中文字符 =======
    const emailInput = document.querySelector('input[name="username"]');

    if (emailInput && password) {
      [emailInput, password].forEach(input => {
        input.addEventListener("input", function () {
          this.value = this.value.replace(/[\u4e00-\u9fa5]/g, '');
        });
      });
    }

    // ======= 模态框管理 =======
    const modal = document.getElementById("register-modal");
    const openBtn = document.getElementById("open-register-modal");
    const closeBtn = document.getElementById("close-modal");

    // 打开模态框
    if (openBtn && modal) {
      openBtn.addEventListener("click", function (e) {
        e.preventDefault();
        modal.classList.remove("hidden");
      });
    }

    // 关闭模态框
    if (closeBtn && modal) {
      closeBtn.addEventListener("click", function () {
        modal.classList.add("hidden");
      });
    }

    // 点击遮罩层关闭模态框
    if (modal) {
      modal.addEventListener("click", function (e) {
        if (e.target === modal) {
          modal.classList.add("hidden");
        }
      });
    }

    // ESC 键关闭模态框
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && modal && !modal.classList.contains("hidden")) {
        modal.classList.add("hidden");
      }
    });

    // ======= 表单验证增强 =======
    const loginForm = document.querySelector('form[action="login.php"]');

    if (loginForm) {
      loginForm.addEventListener("submit", function (e) {
        const emailField = this.querySelector('input[name="username"]');
        const passwordField = this.querySelector('input[name="password"]');

        // 验证邮箱格式（必须是 Gmail）
        if (emailField && !emailField.value.match(/^.+@gmail\.com$/)) {
          e.preventDefault();
          alert('请输入有效的 Gmail 地址');
          emailField.focus();
          return false;
        }

        // 验证密码强度
        if (passwordField) {
          const password = passwordField.value;
          const hasUpper = /[A-Z]/.test(password);
          const hasLower = /[a-z]/.test(password);
          const hasNumber = /\d/.test(password);
          const hasSpecial = /[\W_]/.test(password);
          const hasChinese = /[\u4e00-\u9fa5]/.test(password);

          if (password.length < 8) {
            e.preventDefault();
            alert('密码长度至少需要 8 个字符');
            passwordField.focus();
            return false;
          }

          if (!hasUpper || !hasLower || !hasNumber || !hasSpecial) {
            e.preventDefault();
            alert('密码必须包含大写字母、小写字母、数字和特殊符号');
            passwordField.focus();
            return false;
          }

          if (hasChinese) {
            e.preventDefault();
            alert('密码不能包含中文字符');
            passwordField.focus();
            return false;
          }
        }
      });
    }
  });

