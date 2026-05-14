<div class="modal fade" id="instagramModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="border-radius: 4px; overflow: hidden; height: 85vh; border:none;">
            <div class="d-flex flex-row h-100">
                {{-- Cục bên trái: Hiển thị ảnh --}}
                <div style="flex: 1.5; background: #000; display: flex; align-items: center; justify-content: center;">
                    @if ($post->media->count())
                        <img src="{{ asset($post->media->first()->media_url) }}"
                            style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    @else
                        <div class="text-white">Không có ảnh</div>
                    @endif
                </div>
                {{-- Cục bên phải: Thông tin, Bình luận, Form gõ --}}
                <div style="flex: 1; background: #fff; display: flex; flex-direction: column; width: 400px;">
                    {{-- Header của bài viết --}}
                    <div class="p-3 d-flex align-items-center border-bottom">
                        <img src="{{ $post->user->avatar_url ? asset($post->user->avatar_url) : 'https://i.pravatar.cc/40?u=' . $post->user_id }}"
                            class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                        <strong>{{ $post->user->fullname ?? 'Người dùng' }}</strong>
                    </div>
                    {{-- Khu vực danh sách bình luận (có thanh cuộn) --}}
                    <div class="flex-grow-1 p-3" style="overflow-y: auto;">
                        {{-- Nội dung (Caption) của bài viết --}}
                        <div class="d-flex mb-3">
                            <div style="font-size: 14px;">
                                <strong>{{ $post->user->fullname ?? 'Người dùng' }}</strong>
                                {!! $post->formatted_content ?? $post->content !!}
                            </div>
                        </div>
                        <hr>
                        {{-- VÒNG LẶP BÌNH LUẬN --}}
                        @foreach ($post->comments as $comment)
                            <div class="d-flex mb-3 justify-content-between align-items-start small">
                                <div class="d-flex">
                                    <img src="{{ $comment->user->avatar ? asset('storage/' . $comment->user->avatar) : 'https://i.pravatar.cc/40?u=' . $comment->user_id }}"
                                        class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">

                                    <div>
                                        {{-- Hàng 1: Fullname và @username --}}
                                        <strong>{{ $comment->user->fullname ?? 'Người dùng' }}</strong>
                                        <span class="text-muted ms-1"
                                            style="font-size: 12px;">{{ '@' . $comment->user->username }}</span>

                                        {{-- Hàng 2: NỘI DUNG (ĐÃ XÀI DẤU {!! !!} ĐỂ HIỆN LINK XANH) --}}
                                        <div class="mt-1 text-dark" style="font-size: 14px; word-break: break-word;">
                                            {!! $comment->formatted_content ?? e($comment->content) !!}
                                        </div>

                                        {{-- Hàng 3: Thời gian --}}
                                        <div class="text-muted mt-1" style="font-size: 11px;">
                                            {{ $comment->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Nút xóa với Icon thùng rác màu đỏ --}}
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
                    {{-- Form nhập bình luận mới --}}
                    <div class="border-top p-3">
                        <form action="{{ route('comments.store', $post->id) }}" method="POST"
                            class="d-flex align-items-center">
                            @csrf
                            <input type="text" name="content" class="form-control border-0 shadow-none p-0"
                                placeholder="Thêm bình luận... (gõ @ để nhắc đến ai đó)" style="font-size: 14px;"
                                autocomplete="off" required>
                            <button type="submit" class="btn text-primary fw-bold shadow-none">Đăng</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>