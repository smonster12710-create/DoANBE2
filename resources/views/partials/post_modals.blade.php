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
                            <form action="{{ route('comments.destroy', $comment->id) }}" method="POST"
                                onsubmit="return confirm('Bạn có chắc muốn xóa bình luận này?')" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete-comment p-0 border-0 bg-transparent text-danger"
                                    title="Xóa bình luận">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <div class="border-top p-3">
                        <form action="{{ route('comments.store', $post->id) }}" method="POST"
                            class="d-flex align-items-center">
                            @csrf
                            <input type="text" name="content" class="form-control border-0 shadow-none p-0"
                                placeholder="Thêm bình luận..." style="font-size: 14px;">
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