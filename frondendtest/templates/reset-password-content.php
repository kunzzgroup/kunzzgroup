<div class="reset-password-section">
    <form class="reset-password-form" onsubmit="event.preventDefault();">
        <button class="back-button" onclick="history.back()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M19 12H5M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
        <h2>重设密码</h2>

        <input type="email" id="email" name="email" required
               placeholder="请输入 Gmail 地址"
               title="请输入以 @gmail.com 结尾的邮箱地址"> 
        <div class="password-container">
            <input type="password" id="newPassword" name="newPassword" required
                   placeholder="请输入新密码"
                   title="密码至少包含1个大写字母、小写字母、数字、特殊符号，且不少于8位，不能有中文">
            <img src="../images/images/眼睛.png" class="eye-icon" id="toggle-password" alt="显示密码">
        </div>

        <div class="password-container">
            <input type="password" id="confirmPassword" name="confirmPassword" required
               placeholder="请确认新密码"
               title="请再次输入密码">
            <img src="../images/images/眼睛.png" class="eye-icon" id="toggle-confirm-password" alt="显示密码">
        </div>

        <p class="password-note">密码至少包含1个大写字母、小写字母、数字、特殊符号，且不少于8位</p>

        <!-- Hidden by default, toggled via JS -->
        <div id="codeSection" class="code-input-container hidden">
            <input type="text" id="verificationCode" name="verificationCode" maxlength="6" required
                   placeholder="请输入验证码"
                   style="border: none;">
            <button type="button" id="sendCodeBtn" onclick="sendCode()" disabled>获取验证码</button>
        </div> 
        <button type="submit" id="resetPasswordBtn" onclick="verifyAndReset()" disabled>更换密码</button>

        <p id="message"></p>
    </form>
</div>
<!-- JS included in controller -->
