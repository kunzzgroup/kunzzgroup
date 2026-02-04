<div class="reset-password-section">
    <form class="reset-password-form" onsubmit="event.preventDefault();">
        <button class="back-button" onclick="history.back()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M19 12H5M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
        <h2>Reset Password</h2>

        <input type="email" id="email" name="email" required
               placeholder="Please enter email"
               title="Please enter a valid email end with @gmail.com"> 
        <div class="password-container">
            <input type="password" id="newPassword" name="newPassword" required
                   placeholder="Please enter new password"
                   title="Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, 1 special character, and be at least 8 characters long">
            <img src="../images/images/眼睛.png" class="eye-icon" id="toggle-password" alt="ShowPassword">
        </div>

        <div class="password-container">
            <input type="password" id="confirmPassword" name="confirmPassword" required
               placeholder="Please confirm new password"
               title="Please re-enter again">
            <img src="../images/images/眼睛.png" class="eye-icon" id="toggle-confirm-password" alt="ShowPassword">
        </div>

        <p class="password-note">Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, 1 special character, and be at least 8 characters long</p>

        <div id="codeSection" class="code-input-container">
            <input type="text" id="verificationCode" name="verificationCode" maxlength="6" required
                   placeholder="Please enter TAC"
                   style="border: none;">
            <button type="button" id="sendCodeBtn" onclick="sendCode()" disabled>Get TAC</button>
        </div> 
        <button type="submit" id="resetPasswordBtn" onclick="verifyAndReset()" disabled>Reset Password</button>

        <p id="message"></p>
    </form>
</div>
