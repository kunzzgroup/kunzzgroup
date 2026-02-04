document.addEventListener("DOMContentLoaded", function () {
    const toggle = document.getElementById("toggle-password");
    const password = document.getElementById("password");
    const emailInput = document.querySelector('input[name="username"]');

    // Elements for register modal (that might be missing in HTML but present in original JS)
    const modal = document.getElementById("register-modal");
    const openBtn = document.getElementById("open-register-modal");
    const closeBtn = document.getElementById("close-modal");

    let visible = false;

    if (toggle && password) {
        toggle.addEventListener("click", function () {
            visible = !visible;
            password.type = visible ? "text" : "password";
        });
    }

    [emailInput, password].forEach(input => {
        if (input) {
            input.addEventListener("input", function () {
                // Remove Chinese characters
                this.value = this.value.replace(/[\u4e00-\u9fa5]/g, '');
            });
        }
    });

    if (openBtn && modal) {
        openBtn.addEventListener("click", function (e) {
            e.preventDefault();
            modal.classList.remove("hidden");
        });
    }

    if (closeBtn && modal) {
        closeBtn.addEventListener("click", function () {
            modal.classList.add("hidden");
        });
    }

    if (modal) {
        modal.addEventListener("click", function (e) {
            if (e.target === modal) {
                modal.classList.add("hidden");
            }
        });

        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                modal.classList.add("hidden");
            }
        });
    }
});
