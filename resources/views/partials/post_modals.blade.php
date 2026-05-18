{{-- VÒNG LẶP 2: ĐƯA TẤT CẢ MODAL VỀ CUỐI FILE (NGOÀI THẺ GRID) --}}
{{-- MODAL SỬA --}}
<div class="modal fade" id="editPostModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sửa bài viết</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('posts.update', $post->id) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body">
                    <textarea name="content" class="form-control" rows="5" required>{{ $post->content }}</textarea>
                    @if ($post->media->count())
                    <img src="{{ asset($post->media->first()->media_url) }}" class="mt-3 w-100 rounded">
                    @endif
                    <input type="file" name="image" class="form-control mt-3">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL XÓA --}}
<div class="modal fade" id="deletePostModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Xóa bài viết</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Bạn có chắc muốn xóa bài viết này không?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CHI TIẾT (INSTAGRAM STYLE) --}}
<div class="modal fade" id="instagramModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="border-radius: 4px; overflow: hidden; height: 85vh; border:none;">
            <div class="d-flex flex-row h-100">

                {{-- CỘT TRÁI: KHU VỰC HIỂN THỊ TRÌNH CHIẾU ẢNH (SLIDE NHIỀU ẢNH) --}}
                <div style="flex: 1.5; background: #000; display: flex; align-items: center; justify-content: center; position: relative; height: 100%; overflow: hidden;">
                    @if ($post->media->count())
                    @if ($post->media->count() > 1)
                    {{-- Sử dụng Carousel của Bootstrap để slide nhiều ảnh --}}
                    <div id="detailCarousel{{ $post->id }}" class="carousel slide h-100 w-100" data-bs-ride="false">

                        {{-- Các chấm tròn chỉ số slide nhỏ tinh tế (Instagram style) --}}
                        <div class="carousel-indicators" style="margin-bottom: 15px;">
                            @foreach ($post->media as $index => $item)
                            <button type="button"
                                data-bs-target="#detailCarousel{{ $post->id }}"
                                data-bs-slide-to="{{ $index }}"
                                class="{{ $index == 0 ? 'active' : '' }}"
                                aria-current="{{ $index == 0 ? 'true' : 'false' }}"
                                aria-label="Slide {{ $index + 1 }}"
                                style="width: 6px; height: 6px; border-radius: 50%; margin: 0 3px; background-color: #fff; border: none; opacity: 0.6;">
                            </button>
                            @endforeach
                        </div>

                        {{-- Danh sách các ảnh chạy slide --}}
                        <div class="carousel-inner h-100">
                            @foreach ($post->media as $index => $item)
                            <div class="carousel-item h-100 {{ $index == 0 ? 'active' : '' }}">
                                <div class="d-flex justify-content-center align-items-center h-100">
                                    <img src="{{ asset($item->media_url) }}"
                                        style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Nút bấm chuyển ảnh Trước/Sau --}}
                        <button class="carousel-control-prev" type="button" data-bs-target="#detailCarousel{{ $post->id }}" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true" style="width: 2rem; height: 2rem;"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#detailCarousel{{ $post->id }}" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true" style="width: 2rem; height: 2rem;"></span>
                            <span class="visually-hidden">Next</span>
                        </button>

                    </div>
                    @else
                    {{-- Nếu chỉ có duy nhất 1 ảnh --}}
                    <img src="{{ asset($post->media->first()->media_url) }}"
                        style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    @endif
                    @else
                    <div class="text-white">Không có ảnh</div>
                    @endif
                </div>

                {{-- CỘT PHẢI: THÔNG TIN USER VÀ BÌNH LUẬN --}}
                <div style="flex: 1; background: #fff; display: flex; flex-direction: column; width: 400px;">
                    <div class="p-3 d-flex align-items-center border-bottom">
                        <img src="{{ $post->user->avatar_url
                            ? asset($post->user->avatar_url)
                            : 'https://i.pravatar.cc/40?u=' . $post->user_id }}" class="rounded-circle me-2" width="32" height="32"
                            style="object-fit: cover;">
                        <strong>{{ $post->user->fullname ?? 'Người dùng' }}</strong>
                    </div>

                    <div class="flex-grow-1 p-3" style="overflow-y: auto;">
                        <div class="d-flex mb-3">
                            <div style="font-size: 14px;">
                                <strong>{{ $post->user->fullname ?? 'Người dùng' }}</strong>
                                {!! $post->formatted_content !!}
                            </div>
                        </div>
                        <hr>

                        <div class="danhmuc-comment-{{ $post->id }}">
                            @foreach ($post->comments as $comment)
                            <div class="d-flex mb-3 justify-content-between align-items-start small">
                                <div class="d-flex">
                                    <img src="{{ $comment->user->avatar ? asset('storage/' . $comment->user->avatar) : 'https://i.pravatar.cc/40?u=' . $comment->user_id }}"
                                        class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                                    <div>
                                        <strong>{{ $comment->user->fullname ?? 'Người dùng' }}</strong>
                                        {{ $comment->content }}
                                        <div class="text-muted" style="font-size: 11px;">
                                            {{ $comment->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>

                                @if (auth()->id() == $comment->user_id || auth()->id() == $post->user_id)
                                <button type="button"
                                    class="btn-trigger-delete-comment p-0 border-0 bg-transparent text-danger"
                                    data-url="{{ route('comments.destroy', $comment->id) }}"
                                    title="Xóa bình luận">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-top p-3">
                        <form action="{{ route('comments.store', $post->id) }}" method="POST" class="d-flex align-items-center ajax-form" data-id="{{ $post->id }}">
                            @csrf
                            <input type="text" name="content" class="form-control border-0 shadow-none p-0 comment-input" placeholder="Thêm bình luận..." style="font-size: 14px;" required autocomplete="off">
                            <button type="submit" class="btn text-primary fw-bold shadow-none">Đăng</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- MODAL XEM ẢNH FULLSCREEN --}}
<div class="modal fade image-preview-modal"
    id="imagePreviewModal{{ $post->id }}"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-black border-0">

            {{-- nút đóng --}}
            <button type="button"
                class="btn-close btn-close-white position-absolute top-0 end-0 m-4 z-3"
                data-bs-dismiss="modal">
            </button>

            @if ($post->media->count())

            <div id="carousel{{ $post->id }}"
                class="carousel slide h-100"
                data-bs-ride="false">

                {{-- images --}}
                <div class="carousel-inner h-100">

                    @foreach ($post->media as $index => $item)

                    <div class="carousel-item h-100 {{ $index == 0 ? 'active' : '' }}">

                        <div class="d-flex justify-content-center align-items-center h-100">

                            <img src="{{ asset($item->media_url) }}"
                                class="preview-image"
                                alt="preview">

                        </div>
                    </div>

                    @endforeach

                </div>

                {{-- arrows --}}
                @if ($post->media->count() > 1)

                <button class="carousel-control-prev"
                    type="button"
                    data-bs-target="#carousel{{ $post->id }}"
                    data-bs-slide="prev">

                    <span class="carousel-control-prev-icon"></span>
                </button>

                <button class="carousel-control-next"
                    type="button"
                    data-bs-target="#carousel{{ $post->id }}"
                    data-bs-slide="next">

                    <span class="carousel-control-next-icon"></span>
                </button>

                @endif

            </div>

            @endif

        </div>
    </div>
</div>
<div class="modal fade" id="deleteCommentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center" style="border-radius: 12px;">
            <div class="modal-body p-4">
                <h5 class="fw-bold mb-2">Xóa bình luận?</h5>
                <p class="text-muted small mb-0">Bạn có chắc chắn muốn xóa bình luận này không? Hành động này không thể hoàn tác.</p>
            </div>
            <div class="d-flex border-top">
                <button type="button" class="btn btn-link text-muted text-decoration-none w-50 m-0 p-3 border-end shadow-none" data-bs-dismiss="modal">Hủy</button>
                <button type="button" id="btnConfirmDeleteComment" class="btn btn-link text-danger fw-bold text-decoration-none w-50 m-0 p-3 shadow-none">Xóa</button>
            </div>
        </div>
    </div>
</div>
<script>
    // ==========================================
    // 1. XỬ LÝ ĐĂNG BÌNH LUẬN -> TĂNG SỐ LƯỢNG
    // ==========================================
    if (typeof window.commentEventAttached === 'undefined') {
        window.commentEventAttached = true;

        document.body.addEventListener('submit', function(e) {
            if (e.target && e.target.classList.contains('ajax-form')) {
                e.preventDefault();

                const form = e.target;
                const postId = form.getAttribute('data-id');
                const input = form.querySelector('.comment-input');
                const content = input.value.trim();
                const commentContainer = document.querySelector(`.danhmuc-comment-${postId}`);

                if (!content) return;

                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(form)
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Lỗi mạng hoặc lỗi Server');
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const newCommentHtml = `
                                <div class="d-flex mb-3 justify-content-between align-items-start small" data-post-id="${postId}">
                                    <div class="d-flex">
                                        <img src="${data.user_avatar}" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                                        <div>
                                            <strong>${data.user_fullname}</strong>
                                            <span class="ms-1">${content}</span>
                                            <div class="text-muted" style="font-size: 11px;">Vừa xong</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-trigger-delete-comment p-0 border-0 bg-transparent text-danger" data-url="${data.destroy_route}" title="Xóa bình luận">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            `;

                            if (commentContainer) {
                                commentContainer.insertAdjacentHTML('beforeend', newCommentHtml);
                            }

                            // --- TỰ ĐỘNG TĂNG SỐ LƯỢNG BÌNH LUẬN VỪA THÊM ---
                            const countSpan = document.querySelector(`.comment-count-${postId}`);
                            if (countSpan) {
                                let currentCount = parseInt(countSpan.textContent) || 0;
                                countSpan.textContent = currentCount + 1;
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
                    })
                    .finally(() => {
                        if (submitBtn) submitBtn.disabled = false;
                    });
            }
        });
    }

    // ==========================================
    // 2. XỬ LÝ XÓA BÌNH LUẬN -> GIẢM SỐ LƯỢNG
    // ==========================================
    if (typeof window.commentDeleteEventAttached === 'undefined') {
        window.commentDeleteEventAttached = true;

        let deleteUrl = '';
        let commentElementToDelete = null;
        let deleteModalInstance = null;
        let currentTriggerBtn = null;

        document.body.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.btn-trigger-delete-comment');

            if (deleteBtn) {
                e.preventDefault();
                currentTriggerBtn = deleteBtn;
                deleteUrl = deleteBtn.getAttribute('data-url');
                commentElementToDelete = deleteBtn.closest('.d-flex.mb-3');

                const modalEl = document.getElementById('deleteCommentModal');
                deleteModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                deleteModalInstance.show();
            }
        });

        document.getElementById('btnConfirmDeleteComment').addEventListener('click', function() {
            if (!deleteUrl || !commentElementToDelete || !currentTriggerBtn) return;

            const confirmBtn = this;
            confirmBtn.disabled = true;

            const parentModal = currentTriggerBtn.closest('.modal-content');
            const csrfTokenInput = parentModal ? parentModal.querySelector('input[name="_token"]') : document.querySelector('input[name="_token"]');
            const csrfToken = csrfTokenInput ? csrfTokenInput.value : '';

            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', csrfToken);

            fetch(deleteUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(res => {
                    if (!res.ok) throw new Error('Mã lỗi hệ thống: ' + res.status);
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        // --- TỰ ĐỘNG GIẢM SỐ LƯỢNG BÌNH LUẬN KHI XÓA THÀNH CÔNG ---
                        // Bằng cách tìm ID bài viết dựa vào class container danh mục bình luận cha
                        const container = commentElementToDelete.closest('[class*="danhmuc-comment-"]');
                        if (container) {
                            // Trích xuất lấy ID bài viết từ tên class (ví dụ: danhmuc-comment-5 -> lấy số 5)
                            const postId = container.className.match(/danhmuc-comment-(\d+)/)?.[1];
                            const countSpan = document.querySelector(`.comment-count-${postId}`);
                            if (countSpan) {
                                let currentCount = parseInt(countSpan.textContent) || 0;
                                countSpan.textContent = Math.max(0, currentCount - 1); // Không để số lượng âm
                            }
                        }

                        commentElementToDelete.style.transition = 'all 0.3s ease';
                        commentElementToDelete.style.opacity = '0';

                        setTimeout(() => {
                            commentElementToDelete.remove();
                        }, 300);
                    } else {
                        alert('Server từ chối xóa: ' + (data.message || 'Không rõ lý do'));
                    }
                })
                .catch(err => {
                    console.error('Chi tiết lỗi:', err);
                    alert('Không thể xóa bình luận.');
                })
                .finally(() => {
                    confirmBtn.disabled = false;
                    if (deleteModalInstance) {
                        deleteModalInstance.hide();
                    }
                });
        });
    }
</script>