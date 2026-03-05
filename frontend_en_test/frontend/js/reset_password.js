document.addEventListener("DOMContentLoaded", function () {
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("newPassword");
    const togglePassword = document.getElementById("toggle-password");
    const sendCodeBtn = document.getElementById("sendCodeBtn");
    const confirmPasswordInput = document.getElementById("confirmPassword");
    const toggleConfirmPassword = document.getElementById("toggle-confirm-password");

    // Toggle confirm password visibility
    if (toggleConfirmPassword) {
        toggleConfirmPassword.addEventListener("click", function () {
            confirmPasswordInput.type = confirmPasswordInput.type === "password" ? "text" : "password";
        });
    }

    // Toggle new password visibility
    if (togglePassword) {
        togglePassword.addEventListener("click", function () {
            passwordInput.type = passwordInput.type === "password" ? "text" : "password";
        });
    }

    // Input validation
    function validateInputs() {
        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();
        const confirmPassword = confirmPasswordInput.value.trim();

        // Remove Chinese characters
        if (emailInput.value !== email.replace(/[\u4e00-\u9fa5]/g, '')) {
            emailInput.value = email.replace(/[\u4e00-\u9fa5]/g, '');
        }
        if (passwordInput.value !== password.replace(/[\u4e00-\u9fa5]/g, '')) {
            passwordInput.value = password.replace(/[\u4e00-\u9fa5]/g, '');
        }
        if (confirmPasswordInput.value !== confirmPassword.replace(/[\u4e00-\u9fa5]/g, '')) {
            confirmPasswordInput.value = confirmPassword.replace(/[\u4e00-\u9fa5]/g, '');
        }

        // Check formats
        const isValidEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        const isValidPassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(password);
        const passwordsMatch = password === confirmPassword && confirmPassword !== "";

        if (sendCodeBtn) {
            sendCodeBtn.disabled = !(isValidEmail && isValidPassword && passwordsMatch);
        }

        // Disable reset button if code section is hidden
        const resetBtn = document.getElementById("resetPasswordBtn");
        if (resetBtn) {
            const codeSection = document.getElementById("codeSection");
            const isCodeSectionVisible = !codeSection.classList.contains("hidden");
            resetBtn.disabled = !isCodeSectionVisible;
        }
    }

    if (emailInput) emailInput.addEventListener("input", validateInputs);
    if (passwordInput) passwordInput.addEventListener("input", validateInputs);
    if (confirmPasswordInput) confirmPasswordInput.addEventListener("input", validateInputs);
});

function sendCode() {
    const email = document.getElementById("email").value;

    // Using relative path assuming we are in frontend/ or page is loaded from frontend/
    fetch("../send_verification.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showMessage("验证码已发送到邮箱，请查收", "green");
                document.getElementById("codeSection").classList.remove("hidden");
                document.getElementById("resetPasswordBtn").disabled = false;
            } else {
                showMessage(data.message);
            }
        })
        .catch(err => showMessage("发送失败，请稍后重试"));
}

function verifyAndReset() {
    const email = document.getElementById("email").value;
    const code = document.getElementById("verificationCode").value;
    const password = document.getElementById("newPassword").value;

    fetch("../verify_code.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, code })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Updated to post to current controller (reset_password.php)
                // It expects JSON body and returns JSON
                fetch("reset_password.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ email, new_password: password })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showMessage("密码已成功更新，正在跳转登录页面...", "green");
                            document.getElementById("codeSection").classList.add("hidden");
                            setTimeout(() => {
                                window.location.href = "login.php"; // Updated to login.php
                            }, 2000);
                        } else {
                            showMessage(data.message);
                        }
                    })
                    .catch(err => showMessage("重置请求失败"));
            } else {
                showMessage(data.message);
            }
        })
        .catch(err => showMessage("验证请求失败"));
}

function showMessage(msg, color = "red") {
    const p = document.getElementById("message");
    if (p) {
        p.textContent = msg;
        p.style.color = color;
    }
}
