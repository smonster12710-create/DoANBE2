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
                        }
                    }
                })
                .catch(error => console.error('Lỗi hệ thống:', error));
        });
    });

});