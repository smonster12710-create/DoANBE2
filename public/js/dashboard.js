document.addEventListener("DOMContentLoaded", function () {

    const input = document.getElementById("postImages");
    const preview = document.getElementById("preview-images");

    if (!input || !preview) return;

    let selectedFiles = [];

    input.addEventListener("change", function (e) {

        const newFiles = Array.from(e.target.files);

        // thêm file mới vào mảng cũ
        selectedFiles = [...selectedFiles, ...newFiles];

        // render lại preview
        renderPreview();

        // cập nhật lại input files
        updateInputFiles();

    });

    function renderPreview() {

        preview.innerHTML = "";

        selectedFiles.forEach((file, index) => {

            const reader = new FileReader();

            reader.onload = function (event) {

                const wrapper = document.createElement("div");

                wrapper.style.position = "relative";

                const img = document.createElement("img");

                img.src = event.target.result;

                img.style.width = "100px";
                img.style.height = "100px";
                img.style.objectFit = "cover";
                img.style.borderRadius = "10px";

                // nút xoá
                const removeBtn = document.createElement("button");

                removeBtn.innerHTML = "×";

                removeBtn.style.position = "absolute";
                removeBtn.style.top = "0";
                removeBtn.style.right = "0";
                removeBtn.style.background = "red";
                removeBtn.style.color = "white";
                removeBtn.style.border = "none";
                removeBtn.style.borderRadius = "50%";
                removeBtn.style.width = "22px";
                removeBtn.style.height = "22px";
                removeBtn.style.cursor = "pointer";

                removeBtn.addEventListener("click", function () {

                    selectedFiles.splice(index, 1);

                    renderPreview();

                    updateInputFiles();
                });

                wrapper.appendChild(img);
                wrapper.appendChild(removeBtn);

                preview.appendChild(wrapper);
            };

            reader.readAsDataURL(file);
        });
    }

    function updateInputFiles() {

        const dataTransfer = new DataTransfer();

        selectedFiles.forEach(file => {
            dataTransfer.items.add(file);
        });

        input.files = dataTransfer.files;
    }

});
document.addEventListener("DOMContentLoaded", function () {
    const textarea = document.getElementById("postContent");
    const charCount = document.getElementById("charCount");
    const maxLength = textarea.getAttribute("maxlength");

    textarea.addEventListener("input", function () {
        const currentLength = textarea.value.length;
        charCount.textContent = currentLength;

        // Đổi màu chữ sang đỏ nếu sắp hết dung lượng (ví dụ còn 20 ký tự)
        if (maxLength - currentLength <= 20) {
            charCount.parentElement.classList.replace("text-muted", "text-danger");
        } else {
            charCount.parentElement.classList.replace("text-danger", "text-muted");
        }
    });
});
document.addEventListener("DOMContentLoaded", function () {
    const createForm = document.querySelector("#createPostModal form");
    const errorModal = new bootstrap.Modal(document.getElementById('errorPostModal'));
    const errorMessageText = document.getElementById('errorMessageText');

    if (createForm) {
        createForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            const submitBtn = createForm.querySelector("button[type='submit']");
            if (submitBtn) submitBtn.disabled = true;

            try {
                const response = await fetch(createForm.action, {
                    method: createForm.method,
                    body: new FormData(createForm),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    const result = await response.json();

                    if (result.errors && result.errors.content) {
                        errorMessageText.textContent = "Nội dung bài viết vượt quá giới hạn ký tự thực tế cho phép!";
                    } else {
                        errorMessageText.textContent = "Không thể đăng bài. Vui lòng kiểm tra lại dữ liệu!";
                    }

                    errorModal.show();
                    if (submitBtn) submitBtn.disabled = false;
                } else {
                    window.location.reload();
                }
            } catch (error) {
                errorMessageText.textContent = "Kết nối máy chủ thất bại!";
                errorModal.show();
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }
});