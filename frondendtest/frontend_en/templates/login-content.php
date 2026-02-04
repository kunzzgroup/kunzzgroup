<section class="login-section">
    <div class="login-form">
      <button class="back-button" onclick="history.back()">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M19 12H5M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      </button>
      <h2>Please Log In</h2>
      <form action="" method="POST">
        <label for="username" class="input-label">Account</label>
        <input type="email" name="username" placeholder="Please enter your Gmail address" pattern=".+@gmail\.com" required>
        <label for="password" class="input-label">Password</label>
        <div class="password-container">
        <input type="password" id="password" name="password" placeholder="Password" 
          pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])(?!.*[\u4e00-\u9fa5]).{8,}$" 
          title="Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, 1 special character, and be at least 8 characters long" required>
        <img src="../images/images/眼睛.png" id="toggle-password" alt="显示密码" class="eye-icon">
        </div>

        <div class="form-options">
        <label class="remember-me">
        <input type="checkbox" name="remember" value="1">
        Remember Me
      </label>
      <div class="forgot-password">
          <a href="reset_password.html">Forgot password?</a>
        </div>
      </div>
        <button type="submit">Login</button>
      </form>
      <hr class="form-divider">
      <div class="form-links">
    </div>
  </section>
  </div>
  </section>
