// File: public/js/detail.js

document.addEventListener('DOMContentLoaded', function () {

    // Lắng nghe sự kiện submit form comment trên TOÀN BỘ TRANG
    document.body.addEventListener('submit', function (e) {
        if (e.target && e.target.classList.contains('ajax-form')) {
            e.preventDefault(); // Chặn load lại trang liền

            const form = e.target;
            const postId = form.getAttribute('data-id');
            const input = form.querySelector('.comment-input');
            const content = input.value.trim();
            const commentContainer = document.querySelector(`.danhmuc-comment-${postId}`);

            if (!content) return;

            // Gửi ngầm dữ liệu lên Controller
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(form)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Tự vẽ khung comment mới bằng dữ liệu thật từ Controller
                        const newCommentHtml = `
                        <div class="d-flex mb-3 justify-content-between align-items-start small">
                            <div class="d-flex">
                                <img src="${data.user_avatar}" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                                <div>
                                    <strong>${data.user_fullname}</strong>
                                    ${content}
                                    <div class="text-muted" style="font-size: 11px;">Vừa xong</div>
                                </div>
                            </div>
                            
                            <form action="${data.destroy_route}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa bình luận này?')" class="m-0">
                                <input type="hidden" name="_token" value="${form.querySelector('input[name="_token"]').value}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn-delete-comment p-0 border-0 bg-transparent text-danger" title="Xóa bình luận">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    `;

                        if (commentContainer) {
                            commentContainer.insertAdjacentHTML('beforeend', newCommentHtml);
                        }

                        input.value = '';

                        const scrollContainer = commentContainer ? commentContainer.closest('[style*="overflow-y: auto"]') : null;
                        if (scrollContainer) {
                            scrollContainer.scrollTop = scrollContainer.scrollHeight;
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Có lỗi xảy ra, thử lại nhé!');
                });
        }
    });

});