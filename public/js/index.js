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