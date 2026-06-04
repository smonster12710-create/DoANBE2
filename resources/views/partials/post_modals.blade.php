{{-- VÒNG LẶP 2: ĐƯA TẤT CẢ MODAL VỀ CUỐI FILE (NGOÀI THẺ GRID) --}}
{{-- MODAL SỬA --}}
<div class="modal fade" id="editPostModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sửa bài viết</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('posts.update', $post->id) }}" method="POST" enctype="multipart/form-data"
                id="editPostForm{{ $post->id }}">
                @csrf @method('PUT')
                <input type="hidden" name="last_updated_at" value="{{ $post->updated_at }}">

                <div class="modal-body">
                    <div class="position-relative">
                        <textarea name="content" id="editContent{{ $post->id }}" class="form-control" rows="5"
                            maxlength="500" required>{{ $post->content }}</textarea>
                        <div class="text-end text-muted small mt-1">
                            <span id="charCount{{ $post->id }}">0</span>/500 ký tự
                        </div>
                    </div>

                    <div class="row g-2 mt-2" id="imagePreviewContainer{{ $post->id }}">
                        @if ($post->media->count())
                            @foreach ($post->media as $media)
                                <div class="col-4 position-relative old-image-item">
                                    <img src="{{ asset($media->media_url) }}" class="w-100 rounded object-fit-cover"
                                        style="height: 120px;">
                                    <span class="badge bg-secondary position-absolute top-0 start-0 m-1">Ảnh cũ</span>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-bold small">Thay đổi danh sách ảnh (Có thể chọn nhiều lần):</label>
                        <input type="file" name="images[]" id="editImagesInput{{ $post->id }}" class="form-control"
                            accept="image/*" multiple>
                        <div class="form-text text-danger small">* Lưu ý: Khi bạn bắt đầu chọn ảnh mới, toàn bộ ảnh cũ
                            sẽ bị thay thế.</div>
                    </div>
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
                    <input type="hidden" name="last_updated_at" value="{{ $post->updated_at }}">
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL YÊU CẦU TẢI LẠI TRANG --}}
<div class="modal fade" id="reloadPageModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center py-4">
            <div class="modal-body">
                <h5 class="text-success mb-3">Thao tác đang được xử lý!</h5>
                <p>Hệ thống đang cập nhật dữ liệu. Vui lòng tải lại trang để xem thay đổi và thực hiện thao tác mới.</p>
                <button type="button" class="btn btn-primary mt-2" onclick="window.location.reload();">
                    Tải lại trang ngay
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CHI TIẾT (INSTAGRAM STYLE) --}}
<div class="modal fade" id="instagramModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
    @php
        $displayMedia = $post->media ?? collect();
        $hasMedia = $displayMedia->count() > 0;
    @endphp

    <div class="modal-dialog modal-dialog-centered {{ $hasMedia ? 'modal-lg' : 'modal-md' }}">
        <div class="modal-content" style="border-radius: 4px; overflow: hidden; height: 85vh; border:none;">
            <div class="d-flex flex-row h-100">

                {{-- CỘT TRÁI: HIỂN THỊ TRÌNH CHIẾU ẢNH LỚN --}}
                @if ($hasMedia)
                    <div
                        style="flex: 1; background: #000; display: flex; align-items: center; justify-content: center; position: relative; height: 100%; overflow: hidden;">
                        @if ($displayMedia->count() > 1)
                            <div id="detailCarousel{{ $post->id }}" class="carousel slide h-100 w-100" data-bs-ride="false">
                                <div class="carousel-indicators" style="margin-bottom: 15px;">
                                    @foreach ($displayMedia as $index => $item)
                                        <button type="button" data-bs-target="#detailCarousel{{ $post->id }}"
                                            data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}"
                                            aria-current="{{ $index == 0 ? 'true' : 'false' }}"
                                            style="width: 6px; height: 6px; border-radius: 50%; margin: 0 3px; background-color: #fff; border: none; opacity: 0.6;">
                                        </button>
                                    @endforeach
                                </div>

                                <div class="carousel-inner h-100">
                                    @foreach ($displayMedia as $index => $item)
                                        <div class="carousel-item h-100 {{ $index == 0 ? 'active' : '' }}">
                                            <div class="d-flex justify-content-center align-items-center h-100">
                                                <img src="{{ asset($item->media_url) }}" data-bs-toggle="modal"
                                                    data-bs-target="#imagePreviewModal{{ $post->id }}"
                                                    style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <button class="carousel-control-prev" type="button"
                                    data-bs-target="#detailCarousel{{ $post->id }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"
                                        style="width: 2rem; height: 2rem;"></span>
                                </button>
                                <button class="carousel-control-next" type="button"
                                    data-bs-target="#detailCarousel{{ $post->id }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"
                                        style="width: 2rem; height: 2rem;"></span>
                                </button>
                            </div>
                        @else
                            <img src="{{ asset($displayMedia->first()->media_url) }}" data-bs-toggle="modal"
                                data-bs-target="#imagePreviewModal{{ $post->id }}"
                                style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;">
                        @endif
                    </div>
                @endif

                {{-- CỘT PHẢI: THÔNG TIN USER, NỘI DUNG VÀ BÌNH LUẬN --}}
                <div {!! $hasMedia
    ? 'style="background: #fff; display: flex; flex-direction: column; width: 340px; flex-shrink: 0;"'
    : 'style="background: #fff; display: flex; flex-direction: column; width: 100%; flex-grow: 1;"' !!}>

                    {{-- 1. THÔNG TIN USER ĐĂNG BÀI (HEADER) --}}
                    <div class="p-3 d-flex align-items-center border-bottom">
                        @if ($post->is_anonymous)
                            <img class="avatar" src="{{ asset('img/user/user.jpg') }}" alt="Ẩn danh">
                            <strong class="name d-block">Người dùng ẩn danh 🕵️</strong>
                        @else
                            <img src="{{ $post->user->avatar_url ? asset($post->user->avatar_url) : 'https://i.pravatar.cc/40?u=' . $post->user_id }}"
                                class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                            <strong>{{ $post->user->fullname ?? 'Người dùng' }}</strong>
                        @endif
                    </div>

                    {{-- KHOANG CHỨA NỘI DUNG VÀ CÁC BÌNH LUẬN (CÓ SCROLLY) --}}
                    <div class="flex-grow-1 p-3" style="overflow-y: auto;">
                        <div class="w-100">
                            <div class="post-main-content mb-3"
                                style="font-size: 14px; line-height: 1.5; color: #1c1e21;">
                                {!! Str::replace('[#LOCK_COMMENT#]', '', $post->formatted_content ?? e($post->content)) !!}
                            </div>

                            @if ($post->sharedPost)
                                <div class="card mt-2 p-3 w-100 d-block shared-post-card"
                                    style="background-color: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6; cursor: pointer; min-width: 100%;"
                                    r: 1px solid #dee2e6; cursor: pointer; min-width: 100%;"
                                    data-current-modal="#instagramModal{{ $post->id }}"
                                    data-target-modal="#instagramModal{{ $post->sharedPost->id }}">

                                    <div class="d-flex align-items-center mb-2">
                                        @if ($post->sharedPost->is_anonymous)
                                            <img src="{{ asset('img/user/user.jpg') }}" class="rounded-circle me-2" width="24"
                                                height="24" style="object-fit: cover;" alt="Ẩn danh">
                                            <strong class="small text-dark" style="font-size: 13px;">Người dùng ẩn
                                                danh 🕵️</strong>
                                        @else
                                            <img src="{{ $post->sharedPost->user->avatar_url ? asset($post->sharedPost->user->avatar_url) : 'https://i.pravatar.cc/40?u=' . $post->sharedPost->user_id }}"
                                                class="rounded-circle me-2" width="24" height="24" style="object-fit: cover;">
                                            <strong class="small"
                                                style="font-size: 13px;">{{ $post->sharedPost->user->fullname ?? 'Người dùng gốc' }}</strong>
                                        @endif
                                    </div>

                                    @if ($post->sharedPost->content)
                                        <div class="small text-secondary mb-2"
                                            style="font-size: 13px; line-height: 1.4; word-break: break-word;">
                                            {{ $post->sharedPost->content }}
                                        </div>
                                    @endif

                                    @if ($post->sharedPost->media && $post->sharedPost->media->count() > 0)
                                        @if ($post->sharedPost->media->count() == 1)
                                            <div class="row mt-2 g-0">
                                                <div class="col-12">
                                                    <div
                                                        style="padding-top: 60%; position: relative; overflow: hidden; border-radius: 6px;">
                                                        <img src="{{ asset($post->sharedPost->media->first()->media_url) }}"
                                                            class="position-absolute top-0 start-0 w-100 h-100"
                                                            style="object-fit: cover;">
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="row g-1 mt-2">
                                                @foreach ($post->sharedPost->media->take(4) as $innerIndex => $media)
                                                    <div class="col-3 position-relative">
                                                        <div
                                                            style="padding-top: 100%; position: relative; overflow: hidden; border-radius: 4px;">
                                                            <img src="{{ asset($media->media_url) }}"
                                                                class="position-absolute top-0 start-0 w-100 h-100"
                                                                style="object-fit: cover;">

                                                            @if ($loop->index == 3 && $post->sharedPost->media->count() > 4)
                                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold small"
                                                                    style="background: rgba(0,0,0,0.5);">
                                                                    +{{ $post->sharedPost->media->count() - 4 }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endif
                        </div>
                        <hr>

                        {{-- DANH SÁCH BÌNH LUẬN --}}
                        <div class="danhmuc-comment-{{ $post->id }}">
                            @foreach ($post->comments as $comment)
                                <div class="d-flex mb-3 justify-content-between align-items-start small">
                                    <div class="d-flex" style="flex: 1; max-width: 85%;">
                                        @if($comment->is_anonymous)
                                            <img src="{{ asset('img/user/user.jpg') }}" class="rounded-circle me-2" width="32"
                                                height="32" style="object-fit: cover;">
                                        @else
                                            <img src="{{ $comment->user->avatar ? asset('storage/' . $comment->user->avatar) : 'https://i.pravatar.cc/40?u=' . $comment->user_id }}"
                                                class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                                        @endif
                                        <div style="flex: 1;">
                                            @if($comment->is_anonymous)
                                                <strong>Ẩn danh</strong>
                                            @else
                                                <strong>{{ $comment->user->fullname ?? 'Người dùng' }}</strong>
                                            @endif

                                            {{-- BỌC NỘI DUNG VĂN BẢN ĐỂ SỬA TRỰC TIẾP --}}
                                            <div id="comment-text-{{ $comment->id }}"
                                                style="word-break: break-word; margin-top: 2px;">
                                                {{ $comment->content }}
                                            </div>

                                            <div class="text-muted" style="font-size: 11px; margin-top: 2px;">
                                                {{ $comment->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- DROPDOWN MENU BA CHẤM MỚI --}}
                                    @if (auth()->id() == $comment->user_id || auth()->id() == $post->user_id)
                                        <div class="dropdown">
                                            <button class="btn btn-link text-muted p-0 border-0 m-0 lh-1 shadow-none"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                style="text-decoration: none; font-size: 13px;">
                                                •••
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                                                style="min-width: 100px; padding: 4px 0; border-radius: 6px; border: 1px solid #e4e6eb;">
                                                @if (auth()->id() == $comment->user_id)
                                                    <li>
                                                        <button type="button"
                                                            class="dropdown-item d-flex align-items-center py-1 px-3"
                                                            onclick="triggerEditComment({{ $comment->id }}, '{{ addslashes($comment->content) }}', '{{ $comment->updated_at }}')"
                                                            style="font-size: 13px; gap: 6px;">
                                                            Sửa
                                                        </button>
                                                    </li>
                                                @endif
                                                <li>
                                                    <button type="button"
                                                        class="btn-trigger-delete-comment dropdown-item d-flex align-items-center text-danger py-1 px-3"
                                                        data-url="{{ route('comments.destroy', $comment->id) }}"
                                                        style="font-size: 13px; gap: 6px;">
                                                        Xóa
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div> {{-- ĐÓNG KHOANG CHỨA BÌNH LUẬN CHUẨN --}}

                    {{-- KHU VỰC FORM SUBMIT BÌNH LUẬN (BỊ CỐ ĐỊNH Ở ĐÁY) --}}
                    <div class="border-top p-3 bg-white">
                        @if (Str::contains($post->content, '[#LOCK_COMMENT#]'))
                            <div class="text-muted text-center small py-2 w-100">
                                🔒 Tính năng bình luận đã bị đóng cho bài viết này.
                            </div>
                        @else
                            <form action="{{ route('comments.store', $post->id) }}" method="POST"
                                class="d-flex align-items-center ajax-form" data-id="{{ $post->id }}">
                                @csrf
                                <input type="text" name="content"
                                    class="form-control border-0 shadow-none p-0 comment-input"
                                    placeholder="Thêm bình luận..." style="font-size: 14px;" required autocomplete="off">
                                <button type="submit" class="btn text-primary fw-bold shadow-none p-0 ms-2 ">Đăng</button>
                            </form>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL XEM ẢNH FULLSCREEN --}}
<div class="modal fade image-preview-modal" id="imagePreviewModal{{ $post->id }}" tabindex="-1" aria-hidden="true"
    style="z-index: 1060;">

    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-black border-0">

            {{-- nút đóng --}}
            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-4 z-3"
                data-bs-dismiss="modal">
            </button>

            @if ($post->media && $post->media->count())

                <div id="carousel{{ $post->id }}" class="carousel slide h-100" data-bs-ride="false">

                    {{-- images --}}
                    <div class="carousel-inner h-100">
                        @foreach ($post->media as $index => $item)
                            <div class="carousel-item h-100 {{ $index == 0 ? 'active' : '' }}">
                                {{-- SỬA: Thêm class panzoom-container và style để ẩn phần thừa khi zoom --}}
                                <div class="d-flex justify-content-center align-items-center h-100 panzoom-container"
                                    style="overflow: hidden; position: relative;">
                                    {{-- SỬA: Thêm class panzoom-element vào thẻ img --}}
                                    <img src="{{ asset($item->media_url) }}" class="preview-image panzoom-element" alt="preview"
                                        style="max-width: 100%; max-height: 100%; object-fit: contain; cursor: grab; transition: transform 0.1s ease-out;">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- arrows --}}
                    @if ($post->media->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel{{ $post->id }}"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>

                        <button class="carousel-control-next" type="button" data-bs-target="#carousel{{ $post->id }}"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    @endif

                </div>
            @endif

        </div>
    </div>
</div>
<div class="modal fade" id="deleteCommentModal" tabindex="-1" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center"
            style="border-radius: 12px; border: none; box-shadow: 0 4px 24px rgba(0,0,0,0.15);">
            <div class="modal-body p-4">
                <h5 class="fw-bold mb-2">Xóa bình luận?</h5>
                <p class="text-muted small mb-0">Bạn có chắc chắn muốn xóa bình luận này không? Hành động này không thể
                    hoàn tác.</p>
            </div>
            <div class="d-flex border-top">
                <button type="button"
                    class="btn btn-link text-muted text-decoration-none w-50 m-0 p-3 border-end shadow-none"
                    data-bs-dismiss="modal">Hủy</button>
                <button type="button" id="btnConfirmDeleteComment"
                    class="btn btn-link text-danger fw-bold text-decoration-none w-50 m-0 p-3 shadow-none">Xóa</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@panzoom/panzoom@4.5.1/dist/panzoom.min.js"></script>
<script>
    // ==========================================================
    // 1. HÀM THÔNG BÁO ĐỒNG BỘ (SỬ DỤNG GIAO DIỆN HÌNH CUỐI)
    // ==========================================================
    function showCommentToast(message, type = 'danger') {
        // Xóa toast cũ nếu có
        const existingToast = document.querySelector('.custom-toast-notification');
        if (existingToast) existingToast.remove();

        const toast = document.createElement('div');
        toast.className =
            `custom-toast-notification alert alert-${type === 'success' ? 'success' : 'danger'} shadow-sm position-fixed`;
        // CSS này khớp với thông báo "Bình luận không được vượt quá 1000 ký tự" ở hình cuối
        toast.style.cssText =
            'bottom: 20px; right: 20px; z-index: 9999; min-width: 300px; display: flex; align-items: center; justify-content: space-between;';

        toast.innerHTML = `
        <span>${message}</span>
        <button type="button" class="btn-close" style="margin-left: 15px;" onclick="this.parentElement.remove()"></button>
    `;

        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }

    // ==========================================================
    // 2. XỬ LÝ ĐĂNG BÌNH LUẬN (TỐI ƯU & AN TOÀN)
    // ==========================================================
    if (typeof window.commentEventAttached === 'undefined') {
        window.commentEventAttached = true;

        document.body.addEventListener('submit', function (e) {
            if (e.target && e.target.classList.contains('ajax-form')) {
                e.preventDefault();

                const form = e.target;
                const postId = form.getAttribute('data-id');
                const input = form.querySelector('.comment-input');
                const commentContainer = document.querySelector(`.danhmuc-comment-${postId}`);
                const content = input.value.trim();

                if (!content) return;

                // Kiểm tra giới hạn 1000 ký tự ngay tại trình duyệt trước khi gửi
                if (content.length > 1000) {
                    showCommentToast('Bình luận không được vượt quá 1000 ký tự.', 'danger');
                    return;
                }

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
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const escapedContent = content.replace(/'/g, "\\'").replace(/"/g, '&quot;');

                            const newCommentHtml = `
                        <div class="d-flex mb-3 justify-content-between align-items-start small" data-post-id="${postId}">
                            <div class="d-flex" style="flex-grow: 1; max-width: calc(100% - 30px);">
                                <img src="${data.user_avatar}" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover; width: 32px; height: 32px; flex-shrink: 0;">
                                <div style="flex-grow: 1; min-width: 0; word-break: break-word;">
                                    <strong>${data.user_fullname}</strong>
                                    <span class="ms-1" id="comment-text-${data.comment_id}">${content}</span>
                                    <div class="text-muted" style="font-size: 11px; margin-top: 2px;">Vừa xong</div>
                                </div>
                            </div>
                            <div class="dropdown" style="flex-shrink: 0;">
                                <button class="btn btn-link text-muted p-0 border-0 bg-transparent shadow-none" type="button" data-bs-toggle="dropdown">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/></svg>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 12px; min-width: 80px;">
                                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="triggerEditComment(${data.comment_id}, '${escapedContent}')">Sửa</a></li>
                                    <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="document.querySelector('.btn-trigger-delete-comment[data-url=\'${data.destroy_route}\']').click()">Xóa</a></li>
                                </ul>
                            </div>
                        </div>`;

                            if (commentContainer) commentContainer.insertAdjacentHTML('beforeend',
                                newCommentHtml);

                            const countSpan = document.querySelector(`.comment-count-${postId}`);
                            if (countSpan) countSpan.textContent = parseInt(countSpan.textContent || 0) + 1;

                            input.value = '';
                            showCommentToast('Đăng bình luận thành công!', 'success');
                        } else {
                            showCommentToast(data.message || 'Lỗi: Không thể đăng bình luận.', 'danger');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showCommentToast('Lỗi mạng, vui lòng thử lại.', 'danger');
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

        // Hàm helper để tạo Toast
        const showToast = (message, type = 'success') => {
            const toastHtml = `
            <div class="toast align-items-center text-white bg-${type} border-0 position-fixed bottom-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`;
            document.body.insertAdjacentHTML('beforeend', toastHtml);
            const toastEl = document.body.lastElementChild;
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
            setTimeout(() => toastEl.remove(), 4000);
        };

        document.body.addEventListener('click', function (e) {
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

        document.getElementById('btnConfirmDeleteComment').addEventListener('click', function () {
            if (!deleteUrl || !commentElementToDelete || !currentTriggerBtn) return;

            const confirmBtn = this;
            confirmBtn.disabled = true;

            const parentModal = currentTriggerBtn.closest('.modal-content');
            const csrfTokenInput = parentModal ? parentModal.querySelector('input[name="_token"]') : document
                .querySelector('input[name="_token"]');
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
                    if (!res.ok) throw new Error('System Error');
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');

                        const container = commentElementToDelete.closest('[class*="danhmuc-comment-"]');
                        if (container) {
                            const postId = container.className.match(/danhmuc-comment-(\d+)/)?.[1];
                            const countSpan = document.querySelector(`.comment-count-${postId}`);
                            if (countSpan) {
                                let currentCount = parseInt(countSpan.textContent) || 0;
                                countSpan.textContent = Math.max(0, currentCount - 1);
                            }
                        }

                        commentElementToDelete.style.transition = 'all 0.3s ease';
                        commentElementToDelete.style.opacity = '0';
                        setTimeout(() => commentElementToDelete.remove(), 300);
                    } else {
                        showToast(data.message || 'Bình luận đã bị xóa từ trước!', 'danger');
                        setTimeout(() => window.location.reload(), 2000);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showToast('Đã có lỗi xảy ra, trang sẽ tải lại!', 'danger');
                    setTimeout(() => window.location.reload(), 2000);
                })
                .finally(() => {
                    confirmBtn.disabled = false;
                    if (deleteModalInstance) deleteModalInstance.hide();
                });
        });
    }

    // ==========================================
    // 3. XỬ LÝ SỬA BÌNH LUẬN INLINE (ĐÃ CÓ TOAST ĐỘC LẬP)
    // ==========================================

    // Hàm tự tạo Toast không phụ thuộc vào bên ngoài
    const showInlineToast = (message, type = 'success') => {
        const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'danger' ? 'danger' : 'success'} border-0 position-fixed bottom-0 end-0 m-3" 
             role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', toastHtml);
        const toastEl = document.body.lastElementChild;
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
        setTimeout(() => toastEl.remove(), 4000);
    };

    if (typeof window.triggerEditComment !== 'function') {
        window.triggerEditComment = function (commentId, oldContent, updatedAt) {
            const container = document.getElementById(`comment-text-${commentId}`);
            if (!container || document.getElementById(`edit-form-${commentId}`)) return;

            container.innerHTML = `
            <form id="edit-form-${commentId}" action="/comments/${commentId}" method="POST" style="margin-top: 5px; width: 100%;">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="last_updated_at" value="${updatedAt}">
                <div class="input-group input-group-sm">
                    <input type="text" name="content" class="form-control" value="${oldContent.replace(/"/g, '&quot;')}" required style="border-radius: 8px 0 0 8px; font-size: 12px;">
                    <button class="btn btn-primary btn-sm" type="submit" style="border-radius: 0 8px 8px 0; font-size: 11px; font-weight: bold;">Lưu</button>
                </div>
                <div style="margin-top: 2px;">
                    <button type="button" class="btn btn-link btn-sm text-muted p-0" onclick="cancelEditComment(${commentId}, '${oldContent.replace(/'/g, "\\'")}', '${updatedAt}')" style="font-size: 11px; text-decoration: none;">Hủy sửa</button>
                </div>
            </form>
        `;
        };

        window.cancelEditComment = function (commentId, oldContent, updatedAt) {
            const container = document.getElementById(`comment-text-${commentId}`);
            if (container) {
                container.innerHTML = oldContent;
            }
        };

        document.body.addEventListener('submit', function (e) {
            if (e.target && e.target.id && e.target.id.startsWith('edit-form-')) {
                e.preventDefault();

                const form = e.target;
                const commentId = form.id.replace('edit-form-', '');
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                    },
                    body: new FormData(form)
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showInlineToast('Sửa bình luận thành công!', 'success');
                            const container = document.getElementById(`comment-text-${commentId}`);
                            if (container) container.innerHTML = data.content;

                            const editBtn = document.querySelector(
                                `[onclick*="triggerEditComment(${commentId},"]`);
                            if (editBtn) {
                                editBtn.setAttribute('onclick',
                                    `triggerEditComment(${commentId}, '${data.content.replace(/'/g, "\\'")}', '${data.updated_at}')`
                                );
                            }
                        } else {
                            // Hiển thị thông báo lỗi từ server nếu có
                            showInlineToast(data.message || 'Có lỗi xảy ra', 'danger');
                            // Nếu cần reload trang thì để reload, không thì bỏ dòng dưới
                            if (data.reload) setTimeout(() => window.location.reload(), 2000);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showInlineToast('Đã có lỗi xảy ra!', 'danger');
                    })
                    .finally(() => {
                        if (submitBtn) submitBtn.disabled = false;
                    });
            }
        });
    }

    // ==========================================
    // 4. CÁC EVENT KHÁC (DOM CONTENT LOADED / UPDATE POST)
    // ==========================================
    document.addEventListener('click', function (e) {
        const sharedCard = e.target.closest('.shared-post-card');
        if (!sharedCard) return;

        e.preventDefault();
        e.stopPropagation();

        const currentModalEl = sharedCard.closest('.modal');
        const targetModalSelector = sharedCard.dataset.targetModal;
        const targetModalEl = document.querySelector(targetModalSelector);

        if (!targetModalEl) return;

        if (currentModalEl) {
            const currentModal = bootstrap.Modal.getOrCreateInstance(currentModalEl);

            currentModalEl.addEventListener('hidden.bs.modal', function openTargetModal() {
                const targetModal = bootstrap.Modal.getOrCreateInstance(targetModalEl);
                targetModal.show();

                currentModalEl.removeEventListener('hidden.bs.modal', openTargetModal);
            });

            currentModal.hide();
        } else {
            const targetModal = bootstrap.Modal.getOrCreateInstance(targetModalEl);
            targetModal.show();
        }
    });

    document.addEventListener('hidden.bs.modal', function () {
        setTimeout(function () {
            const stillOpenModal = document.querySelector('.modal.show');

            if (!stillOpenModal) {
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');

                document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
                    backdrop.remove();
                });
            }
        }, 200);
    });
    document.addEventListener("DOMContentLoaded", function () {
        const actionForms = document.querySelectorAll("form[action*='posts']");
        const reloadModalEl = document.getElementById('reloadPageModal');
        const reloadModal = new bootstrap.Modal(reloadModalEl);

        actionForms.forEach(form => {
            if (!form.querySelector("input[name='last_updated_at']")) {
                return;
            }
            form.addEventListener("submit", async function (e) {
                e.preventDefault();

                const submitBtns = form.querySelectorAll("button[type='submit']");
                submitBtns.forEach(btn => btn.disabled = true);

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
                        const currentModalEl = form.closest('.modal');
                        if (currentModalEl) {
                            const modalInstance = bootstrap.Modal.getInstance(
                                currentModalEl);
                            if (modalInstance) {
                                currentModalEl.addEventListener('hidden.bs.modal',
                                    function () {
                                        reloadModal.show();
                                    }, {
                                    once: true
                                });
                                modalInstance.hide();
                            }
                        } else {
                            reloadModal.show();
                        }
                    } else {
                        window.location.reload();
                    }
                } catch (error) {
                    window.location.reload();
                }
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const postId = "{{ $post->id }}";
        const form = document.getElementById(`editPostForm${postId}`);
        const textarea = document.getElementById(`editContent${postId}`);
        const charCount = document.getElementById(`charCount${postId}`);
        const imageInput = document.getElementById(`editImagesInput${postId}`);
        const previewContainer = document.getElementById(`imagePreviewContainer${postId}`);

        if (!form) return;

        let accumulatedFiles = [];
        let isFirstTimeSelecting = true;

        function updateCharCount() {
            if (textarea && charCount) {
                charCount.textContent = textarea.value.length;
            }
        }
        if (textarea) textarea.addEventListener("input", updateCharCount);
        updateCharCount();

        if (imageInput && previewContainer) {
            imageInput.addEventListener("change", function () {
                if (!this.files || this.files.length === 0) return;

                if (isFirstTimeSelecting) {
                    previewContainer.innerHTML = "";
                    isFirstTimeSelecting = false;
                }

                Array.from(this.files).forEach(file => {
                    if (!file.type.startsWith('image/')) return;

                    const isDuplicate = accumulatedFiles.some(f => f.name === file.name && f
                        .size === file.size);
                    if (!isDuplicate) {
                        accumulatedFiles.push(file);
                        renderPreviewCard(file);
                    }
                });

                syncFilesToInput();
            });
        }

        function renderPreviewCard(file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const col = document.createElement("div");
                col.className = "col-4 position-relative";
                col.innerHTML = `
                    <img src="${e.target.result}" class="w-100 rounded object-fit-cover" style="height: 120px;">
                    <span class="badge bg-success position-absolute top-0 start-0 m-1">Mới</span>
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 p-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 20px; height: 20px; font-size: 10px;">✕</button>
                `;

                col.querySelector('button').addEventListener('click', function () {
                    accumulatedFiles = accumulatedFiles.filter(f => f !== file);
                    col.remove();
                    syncFilesToInput();
                });

                previewContainer.appendChild(col);
            }
            reader.readAsDataURL(file);
        }

        function syncFilesToInput() {
            if (!imageInput) return;
            const dataTransfer = new DataTransfer();
            accumulatedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            imageInput.files = dataTransfer.files;
        }
    });
    document.addEventListener('DOMContentLoaded', function () {
        // Tìm tất cả các modal preview ảnh
        const modals = document.querySelectorAll('.image-preview-modal');

        modals.forEach(modal => {
            let panzoomInstances = new Map(); // Lưu trữ các bộ zoom của từng ảnh

            // 1. Khởi tạo Panzoom khi modal được mở
            modal.addEventListener('shown.bs.modal', function () {
                const activeItem = modal.querySelector('.carousel-item.active');
                initPanzoomForActiveItem(activeItem);
            });

            // 2. Khởi tạo Panzoom cho ảnh mới khi chuyển slide và reset ảnh cũ
            const carousel = modal.querySelector('.carousel');
            if (carousel) {
                carousel.addEventListener('slide.bs.carousel', function (e) {
                    // Reset ảnh hiện tại trước khi chuyển qua ảnh mới
                    const currentActiveItem = modal.querySelector('.carousel-item.active');
                    resetPanzoom(currentActiveItem);
                });

                carousel.addEventListener('slid.bs.carousel', function (e) {
                    // Khởi tạo zoom cho ảnh mới sau khi đã chuyển slide xong
                    initPanzoomForActiveItem(e.relatedTarget);
                });
            }

            // 3. Reset toàn bộ ảnh về ban đầu khi đóng modal
            modal.addEventListener('hidden.bs.modal', function () {
                const items = modal.querySelectorAll('.carousel-item');
                items.forEach(item => resetPanzoom(item));
            });

            // Hàm khởi tạo Panzoom cho slide đang hiển thị
            function initPanzoomForActiveItem(item) {
                if (!item) return;
                const img = item.querySelector('.panzoom-element');

                // Nếu ảnh này chưa từng được cài đặt Panzoom
                if (img && !panzoomInstances.has(img)) {
                    const panzoom = Panzoom(img, {
                        maxScale: 4, // Độ phóng to tối đa (4x)
                        minScale: 1, // Kích thước nhỏ nhất (1x)
                        canvas: true // Giúp di chuyển mượt mà hơn
                    });

                    // Cho phép dùng cuộn chuột để zoom
                    img.parentElement.addEventListener('wheel', function (e) {
                        e.preventDefault();
                        panzoom.zoomWithWheel(e);
                    });

                    // Đổi icon chuột khi nắm kéo
                    img.addEventListener('panzoomstart', () => img.style.cursor = 'grabbing');
                    img.addEventListener('panzoomend', () => img.style.cursor = 'grab');

                    panzoomInstances.set(img, panzoom);
                }
            }

            // Hàm reset ảnh về trạng thái gốc
            function resetPanzoom(item) {
                if (!item) return;
                const img = item.querySelector('.panzoom-element');
                if (img && panzoomInstances.has(img)) {
                    const panzoom = panzoomInstances.get(img);
                    panzoom.reset(); // Đưa ảnh về kích thước và vị trí ban đầu
                }
            }
        });
    });
</script>