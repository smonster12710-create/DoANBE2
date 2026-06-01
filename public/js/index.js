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
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.ajax-save-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Chặn load lại trang

            const url = this.action;
            const csrfToken = this.querySelector('input[name="_token"]').value;
            const button = this.querySelector('.save-btn');
            const svg = button.querySelector('svg');

            // Tìm block bao quanh bài viết (tìm lên class .post-clickable của bạn)
            const postCard = this.closest('.post-clickable');

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Dựa vào dữ liệu thật từ bộ xử lý Laravel trả về
                        if (data.is_saved) {
                            // Nếu đã lưu: Thêm màu và đổ màu kín SVG
                            button.classList.add('saved', 'text-warning');
                            svg.setAttribute('fill', 'currentColor');
                        } else {
                            // Nếu hủy lưu: Xóa màu và đưa SVG về dạng viền rỗng
                            button.classList.remove('saved', 'text-warning');
                            svg.setAttribute('fill', 'none');

                            // XỬ LÝ RIÊNG CHO TRANG "BÀI VIẾT ĐÃ LƯU":
                            // Nếu tìm thấy wrapper `.saved-page` (đã định nghĩa ở file view trước của bạn)
                            if (document.querySelector('.saved-page') && postCard) {
                                // Tạo hiệu ứng thu nhỏ và mờ dần trước khi xóa
                                postCard.style.transition = 'all 0.3s ease';
                                postCard.style.opacity = '0';
                                postCard.style.transform = 'scale(0.95)';

                                setTimeout(() => {
                                    postCard.remove(); // Xóa hẳn khỏi giao diện

                                    // Kiểm tra xem còn bài viết nào không, nếu hết thì hiện thông báo trống
                                    const remainingPosts = document.querySelectorAll('.post-clickable');
                                    if (remainingPosts.length === 0) {
                                        const grid = document.querySelector('.grid');
                                        if (grid) {
                                            grid.innerHTML = '<div class="saved-empty">Chưa có bài viết nào được lưu.</div>';
                                        }
                                    }
                                }, 300);
                            }
                        }
                    }
                })
                .catch(error => console.error('Lỗi hệ thống:', error));
        });
    });

});
Echo.channel('posts')
    .listen('PostUpdated', (e) => {
        let contentEl = document.getElementById('post-content-' + e.post.id);

        if (contentEl) {
            // Dùng innerHTML thay cho innerText
            contentEl.innerHTML = e.post.content;
        }
    });

