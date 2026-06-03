function shareLinkJS(profileUrl) {
    // 1. Nếu trình duyệt có hỗ trợ Web Share API (Mobile hoặc Trình duyệt xịn)
    if (navigator.share) {
        navigator.share({
            title: 'Xem trang cá nhân',
            text: 'Tui thấy trang cá nhân này xịn nè, vô xem thử đi!',
            url: profileUrl
        })
            .then(() => console.log('Chia sẻ thành công!'))
            .catch((error) => console.log('User hủy chia sẻ', error));
    }
    // 2. Nếu xài PC hoặc trình duyệt không hỗ trợ -> Fallback về Copy Link
    else {
        navigator.clipboard.writeText(profileUrl).then(function () {
            // Tận dụng lại hàm Toast
            if (typeof showToastJS === 'function') {
                showToastJS('Đã sao chép liên kết!', 'success');
            } else {
                alert('Đã sao chép liên kết!');
            }
        }).catch(function (err) {
            if (typeof showToastJS === 'function') {
                showToastJS('Không thể sao chép liên kết!', 'error');
            }
            console.error('Lỗi chép Clipboard: ', err);
        });
    }
}
document.addEventListener("DOMContentLoaded", function () {
    // 1. XỬ LÝ PREVIEW VÀ XÓA ẢNH
    const input = document.getElementById("wallPostImages");
    const preview = document.getElementById("wall-preview-images");

    if (input && preview) {
        let selectedFiles = [];

        input.addEventListener("change", function (e) {
            const newFiles = Array.from(e.target.files);
            selectedFiles = [...selectedFiles, ...newFiles];
            renderPreview();
            updateInputFiles();
        });

        function renderPreview() {
            preview.innerHTML = "";
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function (event) {
                    const wrapper = document.createElement("div");
                    wrapper.classList.add("wall-preview-wrapper");

                    const img = document.createElement("img");
                    img.src = event.target.result;
                    img.classList.add("wall-preview-img");

                    const removeBtn = document.createElement("button");
                    removeBtn.innerHTML = "×";
                    removeBtn.classList.add("wall-preview-remove");

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
    }

    // 2. XỬ LÝ ĐẾM KÝ TỰ CỦA TEXTAREA
    const textarea = document.getElementById("wallPostContent");
    const charCount = document.getElementById("wallCharCount");

    if (textarea && charCount) {
        const maxLength = textarea.getAttribute("maxlength");
        textarea.addEventListener("input", function () {
            const currentLength = textarea.value.length;
            charCount.textContent = currentLength;

            if (maxLength - currentLength <= 20) {
                charCount.parentElement.classList.replace("text-muted", "text-danger");
            } else {
                charCount.parentElement.classList.replace("text-danger", "text-muted");
            }
        });
    }

    /// 3. XỬ LÝ SUBMIT FORM BẰNG AJAX (FETCH)
    const createForm = document.getElementById("createWallPostForm");
    const errorModalElem = document.getElementById('errorPostModal');
    const errorModal = errorModalElem ? new bootstrap.Modal(errorModalElem) : null;
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

                // Kiểm tra định dạng trả về có phải JSON không để tránh crash hàm .json()
                const contentType = response.headers.get("content-type");
                let result = {};
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    result = await response.json();
                }

                if (!response.ok) {
                    if (errorMessageText && errorModal) {
                        // Nếu server trả về thông điệp lỗi cụ thể thì hiển thị, không thì dùng câu mặc định
                        if (result.message) {
                            errorMessageText.textContent = result.message;
                        } else if (result.errors && result.errors.content) {
                            errorMessageText.textContent = "Nội dung bài viết vượt quá giới hạn ký tự thực tế cho phép!";
                        } else {
                            errorMessageText.textContent = "Không thể đăng bài. Vui lòng kiểm tra lại dữ liệu!";
                        }
                        errorModal.show();
                    } else {
                        alert(result.message || "Không thể đăng bài. Vui lòng kiểm tra lại!");
                    }
                    if (submitBtn) submitBtn.disabled = false;
                } else {
                    // Xử lý khi thành công điều hướng thông minh dựa vào chỉ định từ Controller
                    if (result.redirect_url) {
                        window.location.href = result.redirect_url;
                    } else {
                        window.location.reload();
                    }
                }
            } catch (error) {
                console.error("Lỗi AJAX Post:", error);
                if (errorMessageText && errorModal) {
                    errorMessageText.textContent = "Kết nối máy chủ thất bại hoặc có lỗi hệ thống xảy ra!";
                    errorModal.show();
                } else {
                    alert("Kết nối máy chủ thất bại!");
                }
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }
});