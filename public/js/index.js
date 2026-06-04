document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.post-clickable').forEach(card => {

        card.addEventListener('click', function (e) {

            // nếu click vào vùng bị loại trừ
            if (e.target.closest('.no-post-modal')) {
                return;
            }

            const postId = this.dataset.postId;

            const modalEl = document.getElementById(
                `instagramModal${postId}`
            );

            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }

        });

    });

});
const savingPostIds = {}; // Cờ chống spam click cho từng bài viết

document.addEventListener('submit', function (e) {
    const form = e.target.closest('.ajax-save-form');
    if (!form) return;

    e.preventDefault();
    e.stopPropagation();

    const postCard = form.closest('.post-clickable');
    const postId = postCard ? postCard.getAttribute('data-post-id') : null;

    if (postId && savingPostIds[postId]) return;
    if (postId) savingPostIds[postId] = true;

    const url = form.action;
    const csrfToken = form.querySelector('input[name="_token"]').value;
    const button = form.querySelector('.save-btn');
    const svg = button ? button.querySelector('svg') : null;

    if (button) button.disabled = true;

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
        .then(response => {
            if (!response.ok) throw new Error('Mạng không ổn định');
            return response.json();
        })
        .then(data => {
            if (data.success && button && svg) {
                if (data.is_saved) {
                    button.classList.add('saved', 'text-warning');
                    svg.setAttribute('fill', 'currentColor');
                } else {
                    button.classList.remove('saved', 'text-warning');
                    svg.setAttribute('fill', 'none');

                    // Hiệu ứng biến mất nếu đang ở trang Lưu trữ
                    if (document.querySelector('.saved-page') && postCard) {
                        postCard.style.transition = 'all 0.3s ease';
                        postCard.style.opacity = '0';
                        postCard.style.transform = 'scale(0.95)';

                        setTimeout(() => {
                            postCard.remove();
                            if (document.querySelectorAll('.post-clickable').length === 0) {
                                const grid = document.querySelector('.grid');
                                if (grid) grid.innerHTML = '<div class="saved-empty">Chưa có bài viết nào được lưu.</div>';
                            }
                        }, 300);
                    }
                }
            }
        })
        .catch(error => console.error('Lỗi hệ thống Ajax:', error))
        .finally(() => {
            if (postId) delete savingPostIds[postId];
            if (button) button.disabled = false;
        });
});
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Echo !== 'undefined') {
        Echo.channel('posts')
            .listen('PostUpdated', (e) => {
                let contentEl = document.getElementById('post-content-' + e.post.id);
                if (contentEl) {
                    contentEl.innerHTML = e.post.content;
                }
            });
    }
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".report-form").forEach(form => {
        if (form.dataset.executed) return;
        form.dataset.executed = "true";

        const input = form.querySelector(".report-file-input");
        const preview = form.querySelector(".report-preview");

        if (!input || !preview) return;

        let reportFiles = [];

        input.addEventListener("change", function (e) {
            const newFiles = Array.from(e.target.files);
            reportFiles = [...reportFiles, ...newFiles];
            renderReportPreview();
            updateReportInputFiles();
        });

        function renderReportPreview() {
            preview.innerHTML = "";
            reportFiles.forEach((file, index) => {
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
                        reportFiles.splice(index, 1);
                        renderReportPreview();
                        updateReportInputFiles();
                    });

                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);
                    preview.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });
        }

        function updateReportInputFiles() {
            const dataTransfer = new DataTransfer();
            reportFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            input.files = dataTransfer.files;
        }

        form.addEventListener("submit", async function (e) {
            e.preventDefault();
            const submitBtn = form.querySelector("button[type='submit']");
            if (submitBtn) submitBtn.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    const result = await response.json();
                    alert(result.message || "Không thể gửi báo cáo!");
                    if (submitBtn) submitBtn.disabled = false;
                } else {
                    window.location.reload();
                }
            } catch (error) {
                alert("Kết nối máy chủ thất bại!");
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    });
});