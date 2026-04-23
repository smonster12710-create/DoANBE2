document.addEventListener("DOMContentLoaded", function () {
    const passwordInput = document.getElementById("password");
    const confirmInput = document.getElementById("password_confirmation");
    const confirmMessage = document.getElementById("confirm-message");

    const strengthBar = document.querySelector(".password-bar");
    const strengthText = document.querySelector(".text-warning");

    const ruleLength = document.getElementById("rule-length");
    const ruleUpper = document.getElementById("rule-uppercase");
    const ruleNumber = document.getElementById("rule-number");
    const ruleSpecial = document.getElementById("rule-special");

    // check strength
    passwordInput.addEventListener("input", function () {
        const value = passwordInput.value;

        let strength = 0;

        if (value.length >= 6) {
            ruleLength.classList.add("valid");
            ruleLength.classList.remove("invalid");
            strength++;
        } else {
            ruleLength.classList.add("invalid");
            ruleLength.classList.remove("valid");
        }

        if (/[A-Z]/.test(value)) {
            ruleUpper.classList.add("valid");
            ruleUpper.classList.remove("invalid");
            strength++;
        } else {
            ruleUpper.classList.add("invalid");
            ruleUpper.classList.remove("valid");
        }

        if (/[0-9]/.test(value)) {
            ruleNumber.classList.add("valid");
            ruleNumber.classList.remove("invalid");
            strength++;
        } else {
            ruleNumber.classList.add("invalid");
            ruleNumber.classList.remove("valid");
        }

        if (/[^A-Za-z0-9]/.test(value)) {
            ruleSpecial.classList.add("valid");
            ruleSpecial.classList.remove("invalid");
            strength++;
        } else {
            ruleSpecial.classList.add("invalid");
            ruleSpecial.classList.remove("valid");
        }

        let width = strength * 25;
        let color = ["red", "orange", "#3b82f6", "green"][strength - 1] || "red";
        let text = ["Yếu", "Trung bình", "Khá mạnh", "Mạnh"][strength - 1] || "Yếu";

        strengthBar.style.width = width + "%";
        strengthBar.style.background = color;
        strengthText.innerText = "Độ mạnh mật khẩu: " + text;
        strengthText.style.color = color;

        checkConfirmPassword(); // 👈 thêm dòng này để sync realtime
    });

    function checkConfirmPassword() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;

        if (confirm.length === 0) {
            confirmMessage.innerText = "";
            return;
        }

        if (password === confirm) {
            confirmMessage.innerText = "✓ Mật khẩu khớp";
            confirmMessage.classList.add("text-success");
            confirmMessage.classList.remove("text-error");
        } else {
            confirmMessage.innerText = "✗ Mật khẩu không khớp";
            confirmMessage.classList.add("text-error");
            confirmMessage.classList.remove("text-success");
        }
    }

    confirmInput.addEventListener("input", checkConfirmPassword);
});
const form = document.querySelector("form");

form.addEventListener("submit", function (e) {
    const password = passwordInput.value;
    const confirm = confirmInput.value;

    if (password !== confirm) {
        e.preventDefault(); // ❌ chặn submit

        confirmMessage.innerText = "✗ Mật khẩu không khớp";
        confirmMessage.classList.add("text-error");
        confirmMessage.classList.remove("text-success");
    }
});