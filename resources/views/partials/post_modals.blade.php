{{-- VÒNG LẶP 2: ĐƯA TẤT CẢ MODAL VỀ CUỐI FILE (NGOÀI THẺ GRID) --}}
{{-- MODAL SỬA --}}
<div class="modal fade" id="editPostModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sửa bài viết</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('posts.update', $post->id) }}" method="POST" enctype="multipart/form-data" id="editPostForm{{ $post->id }}">
                @csrf @method('PUT')
                <input type="hidden" name="last_updated_at" value="{{ $post->updated_at }}">

                <div class="modal-body">
                    <div class="position-relative">
                        <textarea name="content" id="editContent{{ $post->id }}" class="form-control" rows="5" maxlength="500" required>{{ $post->content }}</textarea>
                        <div class="text-end text-muted small mt-1">
                            <span id="charCount{{ $post->id }}">0</span>/500 ký tự
                        </div>
                    </div>

                    <div class="row g-2 mt-2" id="imagePreviewContainer{{ $post->id }}">
                        @if ($post->media->count())
                        @foreach($post->media as $media)
                        <div class="col-4 position-relative old-image-item">
                            <img src="{{ asset($media->media_url) }}" class="w-100 rounded object-fit-cover" style="height: 120px;">
                            <span class="badge bg-secondary position-absolute top-0 start-0 m-1">Ảnh cũ</span>
                        </div>
                        @endforeach
                        @endif
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-bold small">Thay đổi danh sách ảnh (Có thể chọn nhiều lần):</label>
                        <input type="file" name="images[]" id="editImagesInput{{ $post->id }}" class="form-control" accept="image/*" multiple>
                        <div class="form-text text-danger small">* Lưu ý: Khi bạn bắt đầu chọn ảnh mới, toàn bộ ảnh cũ sẽ bị thay thế.</div>
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
<div class="modal fade" id="reloadPageModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
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
                <div style="flex: 1; background: #000; display: flex; align-items: center; justify-content: center; position: relative; height: 100%; overflow: hidden;">
                    @if ($displayMedia->count() > 1)
                    <div id="detailCarousel{{ $post->id }}" class="carousel slide h-100 w-100" data-bs-ride="false">
                        <div class="carousel-indicators" style="margin-bottom: 15px;">
                            @foreach ($displayMedia as $index => $item)
                            <button type="button"
                                data-bs-target="#detailCarousel{{ $post->id }}"
                                data-bs-slide-to="{{ $index }}"
                                class="{{ $index == 0 ? 'active' : '' }}"
                                aria-current="{{ $index == 0 ? 'true' : 'false' }}"
                                style="width: 6px; height: 6px; border-radius: 50%; margin: 0 3px; background-color: #fff; border: none; opacity: 0.6;">
                            </button>
                            @endforeach
                        </div>

                        <div class="carousel-inner h-100">
                            @foreach ($displayMedia as $index => $item)
                            <div class="carousel-item h-100 {{ $index == 0 ? 'active' : '' }}">
                                <div class="d-flex justify-content-center align-items-center h-100">
                                    <img src="{{ asset($item->media_url) }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#imagePreviewModal{{ $post->id }}"
                                        style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;">
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <button class="carousel-control-prev" type="button" data-bs-target="#detailCarousel{{ $post->id }}" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true" style="width: 2rem; height: 2rem;"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#detailCarousel{{ $post->id }}" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true" style="width: 2rem; height: 2rem;"></span>
                        </button>
                    </div>
                    @else
                    <img src="{{ asset($displayMedia->first()->media_url) }}"
                        data-bs-toggle="modal"
                        data-bs-target="#imagePreviewModal{{ $post->id }}"
                        style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;">
                    @endif
                </div>
                @endif

                {{-- CỘT PHẢI: THÔNG TIN USER, NỘI DUNG VÀ BÌNH LUẬN --}}
                <div {!! $hasMedia ? 'style="background: #fff; display: flex; flex-direction: column; width: 340px; flex-shrink: 0;"' : 'style="background: #fff; display: flex; flex-direction: column; width: 100%; flex-grow: 1;"' !!}>

                    {{-- 1. THÔNG TIN USER ĐĂNG BÀI (HEADER) --}}
                    <div class="p-3 d-flex align-items-center border-bottom">
                        <img src="{{ $post->user->avatar_url ? asset($post->user->avatar_url) : 'https://i.pravatar.cc/40?u=' . $post->user_id }}" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                        <strong>{{ $post->user->fullname ?? 'Người dùng' }}</strong>
                    </div>

                    {{-- KHOANG CHỨA NỘI DUNG VÀ CÁC BÌNH LUẬN --}}
                    <div class="flex-grow-1 p-3" style="overflow-y: auto;">
                        <div class="mb-3">
                            <div class="post-main-content mb-3" style="font-size: 14px; line-height: 1.5; color: #1c1e21;">
                                {!! Str::replace('[#LOCK_COMMENT#]', '', $post->formatted_content ?? e($post->content)) !!}
                            </div>

                            @if($post->sharedPost)
                            <div class="card mt-2 p-3"
                                style="background-color: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6; cursor: pointer;"
                                data-bs-toggle="modal"
                                data-bs-target="#instagramModal{{ $post->sharedPost->id }}">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="{{ $post->sharedPost->user->avatar_url ? asset($post->sharedPost->user->avatar_url) : 'https://i.pravatar.cc/40?u=' . $post->sharedPost->user_id }}" class="rounded-circle me-2" width="24" height="24" style="object-fit: cover;">
                                    <strong class="small" style="font-size: 13px;">{{ $post->sharedPost->user->fullname ?? 'Người dùng gốc' }}</strong>
                                </div>

                                @if($post->sharedPost->content)
                                <div class="small text-secondary mb-2" style="font-size: 13px; line-height: 1.4;">
                                    {{ $post->sharedPost->content }}
                                </div>
                                @endif

                                @if($post->sharedPost->media && $post->sharedPost->media->count() > 0)
                                @if($post->sharedPost->media->count() == 1)
                                <div class="row mt-2">
                                    <div class="col-10">
                                        <div style="padding-top: 100%; position: relative; overflow: hidden; border-radius: 6px;">
                                            <img src="{{ asset($post->sharedPost->media->first()->media_url) }}"
                                                class="position-absolute top-0 start-0 w-100 h-100"
                                                style="object-fit: cover;">
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="row g-1 mt-2">
                                    @foreach($post->sharedPost->media->take(4) as $innerIndex => $media)
                                    <div class="col-3 position-relative">
                                        <div style="padding-top: 100%; position: relative; overflow: hidden; border-radius: 4px;">
                                            <img src="{{ asset($media->media_url) }}" class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover;">

                                            @if($loop->index == 3 && $post->sharedPost->media->count() > 4)
                                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold small" style="background: rgba(0,0,0,0.5);">
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

                    {{-- NƠI ĐƯỢC CHUYỂN ĐẾN: KHU VỰC NÚT BẤM HÀNH ĐỘNG (LIKE, COMMENT, SAVE, PIN, SHARE) --}}
                    <div class="card-actions d-flex justify-content-between align-items-center border-top pt-3 px-3 prevent-post-modal no-post-modal">
                        <div class="d-flex align-items-center gap-3">
                            {{-- LIKE --}}
                            <form action="{{ route('post.like', $post->id) }}" method="POST" class="m-0 like-form">
                                @csrf
                                @php
                                $userId = auth()->id();
                                $checkLike = $userId ? $post->likes->contains('user_id', $userId) : false;
                                @endphp
                                <button type="submit"
                                    class="btn-action border-0 bg-transparent p-0 d-flex align-items-center btn-like-ajax btn-like-{{ $post->id }} {{ $checkLike ? 'text-danger' : '' }}"
                                    style="gap: 5px;"
                                    data-id="{{ $post->id }}">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="like-icon"
                                        fill="{{ $checkLike ? 'currentColor' : 'none' }}"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                    </svg>
                                    <span class="fw-bold like-count-text like-count-{{ $post->id }}" style="font-size: 14px;">{{ $post->likes->count() }}</span>
                                </button>
                            </form>

                            {{-- COMMENT --}}
                            <button class="btn-action border-0 bg-transparent p-0 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#instagramModal{{ $post->id }}" style="gap: 5px;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48l-1.115 2.91a.45.45 0 0 0 .584.584l2.91-1.115A8.97 8.97 0 0 0 12 20.25Z" />
                                </svg>
                                <span class="fw-bold comment-count-{{ $post->id }}" style="font-size: 14px;">{{ $post->comments->count() }}</span>
                            </button>

                            {{-- SAVE --}}
                            @php
                            $isSaved = auth()->user() ? auth()->user()->savedPosts->contains($post->id) : false;
                            @endphp
                            <form action="{{ route('posts.save', $post->id) }}" method="POST" class="no-post-modal m-0 ajax-save-form">
                                @csrf
                                <button type="submit" class="btn-action save-btn {{ $isSaved ? 'saved text-warning' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="{{ $isSaved ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M6 3h12a1 1 0 0 1 1 1v17l-7-5-7 5V4a1 1 0 0 1 1-1z" />
                                    </svg>
                                </button>
                            </form>

                            {{-- PIN --}}
                            @if(auth()->id() == $post->user_id && request()->routeIs('profile.show'))
                            <form action="{{ route('post.pin', $post->id) }}" method="POST" class="no-post-modal m-0 ajax-pin-form">
                                @csrf
                                <button type="submit" class="btn-action pin-btn {{ $post->is_pinned ? 'is-pinned text-primary' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 640 640">
                                        <path d="M288.6 76.8C344.8 20.6 436 20.6 492.2 76.8C548.4 133 548.4 224.2 492.2 280.4L328.2 444.4C293.8 478.8 238.1 478.8 203.7 444.4C169.3 410 169.3 354.3 203.7 319.9L356.5 167.3C369 154.8 389.3 154.8 401.8 167.3C414.3 179.8 414.3 200.1 401.8 212.6L249 365.3C239.6 374.7 239.6 389.9 249 399.2C258.4 408.5 273.6 408.6 282.9 399.2L446.9 235.2C478.1 204 478.1 153.3 446.9 122.1C415.7 90.9 365 90.9 333.8 122.1L169.8 286.1C116.7 339.2 116.7 425.3 169.8 478.4C222.9 531.5 309 531.5 362.1 478.4L492.3 348.3C504.8 335.8 525.1 335.8 537.6 348.3C550.1 360.8 550.1 381.1 537.6 393.6L407.4 523.6C329.3 601.7 202.7 601.7 124.6 523.6C46.5 445.5 46.5 318.9 124.6 240.8L288.6 76.8z" />
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>

                        {{-- SHARE --}}
                        <button type="button" class="btn-action border-0 bg-transparent p-0 d-flex align-items-center text-primary" data-bs-toggle="modal" data-bs-target="#shareModal-{{ $post->id }}" style="gap: 5px;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 640 640" width="24" height="24" opacity="0.7">
                                <path d="M371.8 82.4C359.8 87.4 352 99 352 112L352 192L240 192C142.8 192 64 270.8 64 368C64 481.3 145.5 531.9 164.2 542.1C166.7 543.5 169.5 544 172.3 544C183.2 544 192 535.1 192 524.3C192 516.8 187.7 509.9 182.2 504.8C172.8 496 160 478.4 160 448.1C160 395.1 203 352.1 256 352.1L352 352.1L352 432.1C352 445 359.8 456.7 371.8 461.7C383.8 466.7 397.5 463.9 406.7 454.8L566.7 294.8C579.2 282.3 579.2 262 566.7 249.5L406.7 89.5C397.5 80.3 383.8 77.6 371.8 82.6z" />
                            </svg>
                        </button>

                        {{-- MODAL SHARE ĐƯỢC GIỮ LẠI ĐÚNG VỊ TRÍ TRONG DOM PUSH --}}
                        @push('modals')
                        <div class="modal fade" id="shareModal-{{ $post->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Chia sẻ bài viết</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('post.share', $post->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <textarea name="content" class="form-control mb-3" rows="3" placeholder="Hãy viết gì đó về bài viết này..."></textarea>
                                            <div class="p-3 bg-light border rounded">
                                                <div class="d-flex align-items-center mb-2">
                                                    <strong class="text-dark">{{ $post->fullname ?? $post->user->fullname ?? $post->user->username }}</strong>
                                                </div>
                                                <p class="text-muted mb-0 style-content-preview">
                                                    {{ Str::limit($post->content, 120) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary shadow-none" data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-primary shadow-none">Chia sẻ ngay</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endpush
                    </div>

                    {{-- LIKERS ROW ĐƯỢC ĐƯA VÀO DƯỚI NÚT HÀNH ĐỘNG --}}
                    @if ($post->likes->count() > 0)
                    <div class="likers-row pb-2 px-3">
                        <a href="{{ route('post.likers', $post->id) }}" class="likers-link text-decoration-none small fw-bold text-muted">
                            Xem tất cả người đã thích
                        </a>
                    </div>
                    @endif

                    {{-- KHU VỰC FORM SUBMIT BÌNH LUẬN --}}
                    <div class="border-top p-3">
                        @if(Str::contains($post->content, '[#LOCK_COMMENT#]'))
                        <div class="text-muted text-center small py-2 w-100">
                            🔒 Tính năng bình luận đã bị đóng cho bài viết này.
                        </div>
                        @else
                        <form action="{{ route('comments.store', $post->id) }}" method="POST" class="d-flex align-items-center ajax-form" data-id="{{ $post->id }}">
                            @csrf
                            <input type="text" name="content" class="form-control border-0 shadow-none p-0 comment-input" placeholder="Thêm bình luận..." style="font-size: 14px;" required autocomplete="off">
                            <button type="submit" class="btn text-primary fw-bold shadow-none">Đăng</button>
                        </form>
                        @endif
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
    aria-hidden="true"
    style="z-index: 1060;"> {{-- SỬA: Đảm bảo đè lên trên modal chi tiết --}}

    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-black border-0">

            {{-- nút đóng --}}
            <button type="button"
                class="btn-close btn-close-white position-absolute top-0 end-0 m-4 z-3"
                data-bs-dismiss="modal">
            </button>

            @if ($post->media && $post->media->count())

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
                                alt="preview"
                                style="max-width: 100%; max-height: 100%; object-fit: contain;">
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
<div class="modal fade" id="deleteCommentModal" tabindex="-1" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center" style="border-radius: 12px; border: none; box-shadow: 0 4px 24px rgba(0,0,0,0.15);">
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
    document.addEventListener('DOMContentLoaded', function() {
        // Khi click vào box bài viết gốc, tắt modal hiện tại đi trước để tránh lỗi đen màn hình
        $(document).on('click', '[data-bs-target^="#instagramModal"]', function() {
            var currentModal = $(this).closest('.modal');
            if (currentModal.length) {
                var modalInstance = bootstrap.Modal.getInstance(currentModal[0]);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
        });
    });
    document.addEventListener("DOMContentLoaded", function() {
        const actionForms = document.querySelectorAll("form[action*='posts']");
        const reloadModalEl = document.getElementById('reloadPageModal');
        const reloadModal = new bootstrap.Modal(reloadModalEl);

        actionForms.forEach(form => {
            if (!form.querySelector("input[name='last_updated_at']")) {
                return;
            }
            form.addEventListener("submit", async function(e) {
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
                            const modalInstance = bootstrap.Modal.getInstance(currentModalEl);
                            if (modalInstance) {
                                currentModalEl.addEventListener('hidden.bs.modal', function() {
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
    document.addEventListener("DOMContentLoaded", function() {
        const postId = "{{ $post->id }}";
        const form = document.getElementById(`editPostForm${postId}`);
        const textarea = document.getElementById(`editContent${postId}`);
        const charCount = document.getElementById(`charCount${postId}`);
        const imageInput = document.getElementById(`editImagesInput${postId}`);
        const previewContainer = document.getElementById(`imagePreviewContainer${postId}`);

        let accumulatedFiles = [];
        let isFirstTimeSelecting = true;

        // 1. Đếm ký tự
        function updateCharCount() {
            charCount.textContent = textarea.value.length;
        }
        textarea.addEventListener("input", updateCharCount);
        updateCharCount();

        // 2. Gom ảnh tích lũy
        imageInput.addEventListener("change", function() {
            if (!this.files || this.files.length === 0) return;

            if (isFirstTimeSelecting) {
                previewContainer.innerHTML = "";
                isFirstTimeSelecting = false;
            }

            Array.from(this.files).forEach(file => {
                if (!file.type.startsWith('image/')) return;

                const isDuplicate = accumulatedFiles.some(f => f.name === file.name && f.size === file.size);
                if (!isDuplicate) {
                    accumulatedFiles.push(file);
                    renderPreviewCard(file);
                }
            });

            // BƯỚC THAY ĐỔI: Đồng bộ mảng tích lũy NGAY LẬP TỨC vào chính input gốc
            syncFilesToInput();
        });

        function renderPreviewCard(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement("div");
                col.className = "col-4 position-relative";
                col.innerHTML = `
                <img src="${e.target.result}" class="w-100 rounded object-fit-cover" style="height: 120px;">
                <span class="badge bg-success position-absolute top-0 start-0 m-1">Mới</span>
                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 p-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 20px; height: 20px; font-size: 10px;">✕</button>
            `;

                col.querySelector('button').addEventListener('click', function() {
                    accumulatedFiles = accumulatedFiles.filter(f => f !== file);
                    col.remove();
                    syncFilesToInput(); // Cập nhật lại input gốc khi xóa ảnh preview
                });

                previewContainer.appendChild(col);
            }
            reader.readAsDataURL(file);
        }

        // Hàm đồng bộ mảng tạm thời vào thẳng input gốc của Form để trình duyệt gửi đi thành công
        function syncFilesToInput() {
            const dataTransfer = new DataTransfer();
            accumulatedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            imageInput.files = dataTransfer.files;
        }
    });
</script>