<section class="login-section">
    <div class="login-form">
      <button class="back-button" onclick="history.back()">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M19 12H5M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      </button>
      <h2>请登入您的账号</h2>
      <form action="login.php" method="POST">
        <label for="username" class="input-label">账号</label>
        <input type="email" name="username" placeholder="请输入 Gmail 地址" pattern=".+@gmail\.com" required>
        <label for="password" class="input-label">密码</label>
        <div class="password-container">
        <input type="password" id="password" name="password" placeholder="密码" 
          pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])(?!.*[\u4e00-\u9fa5]).{8,}$" 
          title="密码至少包含一个大写字母、一个小写字母、一个数字和一个特殊符号，且不少于8个字符，且不能包含中文字符" required>
        <img src="../images/images/眼睛.png" id="toggle-password" alt="显示密码" class="eye-icon">
        </div>

        <div class="form-options">
        <label class="remember-me">
        <input type="checkbox" name="remember" value="1">
        记住我
      </label>
      <div class="forgot-password">
          <a href="reset_password.html">忘记密码？</a>
        </div>
      </div>
        <button type="submit">登入</button>
      </form>
      <hr class="form-divider">
      <div class="form-links">
    </div>
  </div>
</section>
