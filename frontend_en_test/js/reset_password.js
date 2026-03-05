document.addEventListener("DOMContentLoaded", function () {
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("newPassword");
    const togglePassword = document.getElementById("toggle-password");
    const sendCodeBtn = document.getElementById("sendCodeBtn");
    const confirmPasswordInput = document.getElementById("confirmPassword");
    const toggleConfirmPassword = document.getElementById("toggle-confirm-password");

    if (toggleConfirmPassword) {
        toggleConfirmPassword.addEventListener("click", function () {
            confirmPasswordInput.type = confirmPasswordInput.type === "password" ? "text" : "password";
        });
    }

    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener("input", validateInputs);
    }

    if (togglePassword) {
        togglePassword.addEventListener("click", function () {
            passwordInput.type = passwordInput.type === "password" ? "text" : "password";
        });
    }

    function validateInputs() {
        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();
        const confirmPassword = confirmPasswordInput.value.trim();

        // Remove Chinese characters
        emailInput.value = email.replace(/[\u4e00-\u9fa5]/g, '');
        passwordInput.value = password.replace(/[\u4e00-\u9fa5]/g, '');
        confirmPasswordInput.value = confirmPassword.replace(/[\u4e00-\u9fa5]/g, '');

        // Validate format
        const isValidEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        const isValidPassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(password);
        const passwordsMatch = password === confirmPassword && confirmPassword !== "";

        if (sendCodeBtn) {
            sendCodeBtn.disabled = !(isValidEmail && isValidPassword && passwordsMatch);
        }

        const resetBtn = document.getElementById("resetPasswordBtn");
        if (resetBtn) {
            const codeSection = document.getElementById("codeSection");
            // Logic: Reset button enabled only if code section is visible (meaning code sent) AND validation passes? 
            // Original code: resetBtn.disabled = !isCodeSectionVisible;
            // But usually we also wait for user to enter code. 
            // Original logic seems to just enable it once section is visible.
            const isCodeSectionVisible = !codeSection.classList.contains("hidden");
            resetBtn.disabled = !isCodeSectionVisible;
        }
    }

    if (emailInput) emailInput.addEventListener("input", validateInputs);
    if (passwordInput) passwordInput.addEventListener("input", validateInputs);
});

// Make these functions global as they are called by onclick attributes
window.sendCode = function () {
    const email = document.getElementById("email").value;
    // password not needed for sending code, usually? Original code grabs it.

    fetch("../send_verification.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showMessage("Tac sent to email, please check", "green");
                document.getElementById("codeSection").classList.remove("hidden");
                document.getElementById("resetPasswordBtn").disabled = false;
            } else {
                showMessage(data.message);
            }
        })
        .catch(err => showMessage("Error sending code: " + err));
};

window.verifyAndReset = function () {
    const email = document.getElementById("email").value;
    const code = document.getElementById("verificationCode").value;
    const password = document.getElementById("newPassword").value;

    // First verify code
    fetch("verify_code.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, code })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Then reset password
                fetch("reset_password.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ email, new_password: password })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showMessage("Password updated successfully, redirecting...", "green");
                            document.getElementById("codeSection").classList.add("hidden");
                            setTimeout(() => {
                                window.location.href = "login.php"; // Redirect to login controller
                            }, 2000);
                        } else {
                            showMessage(data.message);
                        }
                    });
            } else {
                showMessage(data.message);
            }
        })
        .catch(err => showMessage("Error verification/reset: " + err));
};

function showMessage(msg, color = "red") {
    const p = document.getElementById("message");
    if (p) {
        p.textContent = msg;
        p.style.color = color;
    }
}
