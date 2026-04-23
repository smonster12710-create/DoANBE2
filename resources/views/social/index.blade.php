@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/social.css') }}">

<div class="grid">
    @foreach ($posts as $post)
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">

            <div style="display: flex; align-items: center;">
                <img class="avatar" src="{{ $post->user->avatar_url ?? 'https://i.pravatar.cc/40' }}" alt="avatar">
                <div class="info">
                    <span class="name">{{ $post->user->fullname ?? $post->user->username }}</span>
                    <span class="time">{{ $post->created_at->diffForHumans() }}</span>
                </div>
            </div>

            <div class="card-controls" style="display: flex; align-items: center;">
                <button type="button" data-bs-toggle="modal" data-bs-target="#editPostModal{{ $post->id }}"
                    style="background: none; border: none; color: #aaa; cursor: pointer; font-size: 18px; margin-right: 10px;">
                    <svg style="width: 18px; height: 18px;" fill="currentColor" viewBox="0 0 512 512">
                        <path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z" />
                    </svg>
                </button>

                <button type="button" data-bs-toggle="modal" data-bs-target="#deletePostModal{{ $post->id }}"
                    style="background: none; border: none; color: #ff4d4d; cursor: pointer; font-size: 18px;">
                    <svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 448 512">
                        <path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-64-32-64H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="post-clickable-area" data-bs-toggle="modal" data-bs-target="#postDetailModal{{ $post->id }}" style="cursor: pointer;">
            <div class="card-text px-3">
                {!! nl2br(e(Str::limit($post->content, 150))) !!}
            </div>

            @if($post->media && $post->media->count() > 0)
            <div class="mt-2">
                <img class="w-100" src="{{ $post->media->first()->media_url }}" alt="post image" style="max-height: 400px; object-fit: cover;">
            </div>
            @endif
        </div>

        <div class="card-actions">
            <span>
                <svg style="height: 20px; width: 20px;" fill="currentColor" viewBox="0 0 640 640">
                    <path d="M442.9 144C415.6 144 389.9 157.1 373.9 179.2L339.5 226.8C335 233 327.8 236.7 320.1 236.7C312.4 236.7 305.2 233 300.7 226.8L266.3 179.2C250.3 157.1 224.6 144 197.3 144C150.3 144 112.2 182.1 112.2 229.1C112.2 279 144.2 327.5 180.3 371.4C221.4 421.4 271.7 465.4 306.2 491.7C309.4 494.1 314.1 495.9 320.2 495.9C326.3 495.9 331 494.1 334.2 491.7C368.7 465.4 419 421.3 460.1 371.4C496.3 327.5 528.2 279 528.2 229.1C528.2 182.1 490.1 144 443.1 144z" />
                </svg>
                {{ number_format($post->like_count) }}
            </span>
            <span>
                <svg style="height: 20px; width: 20px;" fill="currentColor" viewBox="0 0 640 640">
                    <path d="M115.9 448.9C83.3 408.6 64 358.4 64 304C64 171.5 178.6 64 320 64C461.4 64 576 171.5 576 304C576 436.5 461.4 544 320 544C283.5 544 248.8 536.8 217.4 524L101 573.9C97.3 575.5 93.5 576 89.5 576C75.4 576 64 564.6 64 550.5C64 546.2 65.1 542 67.1 538.3L115.9 448.9z" />
                </svg>
                {{ number_format($post->comment_count) }}
            </span>
            <span class="share">
                <svg style="height: 20px; width: 20px;" fill="currentColor" viewBox="0 0 640 640">
                    <path d="M457.5 71C450.6 64.1 440.3 62.1 431.3 65.8C422.3 69.5 416.5 78.3 416.5 88L416.5 144L368.5 144C280.1 144 208.5 215.6 208.5 304C208.5 350.7 229.2 384.4 252.1 407.4C260.2 415.6 268.6 422.3 276.4 427.8C285.6 434.3 298.1 433.5 306.5 425.9C314.9 418.3 316.7 405.9 311 396.1C307.4 389.8 304.5 381.2 304.5 369.4C304.5 333.2 333.8 303.9 370 303.9L416.5 303.9L416.5 359.9C416.5 369.6 422.3 378.4 431.3 382.1C440.3 385.8 450.6 383.8 457.5 376.9L593.5 240.9C602.9 231.5 602.9 216.3 593.5 207L457.5 71z" />
                </svg>
            </span>
        </div>

        <div class="modal fade" id="editPostModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-dark">
                    <form action="{{ route('posts.update', $post->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Chỉnh sửa bài viết</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <textarea name="content" class="form-control" rows="5" required style="resize: none;">{{ $post->content }}</textarea>
                            <div class="mt-3">
                                <label>Đổi ảnh</label>
                                <input type="file" name="image" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deletePostModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content" style="color: black;">
                    <div class="modal-header">
                        <h5 class="modal-title">Xác nhận xóa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        Bạn có chắc chắn muốn xóa bài viết này không?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <form action="{{ route('posts.destroy', $post->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger">Xóa bài</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="postDetailModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered shadow-lg">
                <div class="modal-content border-0" style="height: 85vh; border-radius: 12px; overflow: hidden;">
                    <div class="row g-0 h-100">

                        <div class="col-lg-8 bg-dark d-flex align-items-center justify-content-center border-end">
                            @if($post->media && $post->media->count() > 0)
                            <img src="{{ $post->media->first()->media_url }}" class="img-fluid" style="max-height: 100%; object-fit: contain;">
                            @else
                            <div class="text-white p-5 fs-4 italic">
                                "{{ $post->content }}"
                            </div>
                            @endif
                        </div>

                        <div class="col-lg-4 d-flex flex-column bg-white">

                            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $post->user->avatar_url ?? 'https://i.pravatar.cc/40' }}" class="rounded-circle me-2 border" width="38" height="38">
                                    <div>
                                        <span class="fw-bold d-block">{{ $post->user->fullname ?? $post->user->username }}</span>
                                        <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="flex-grow-1 p-3 overflow-auto" style="background-color: #f8f9fa;">
                                <div class="d-flex mb-4">
                                    <img src="{{ $post->user->avatar_url ?? 'https://i.pravatar.cc/40' }}" class="rounded-circle me-2" width="30" height="30">
                                    <div>
                                        <span class="fw-bold me-2">{{ $post->user->username }}</span>
                                        <span>{{ $post->content }}</span>
                                    </div>
                                </div>
                                <hr>

                                @forelse($post->comments as $comment)
                                <div class="d-flex mb-3">
                                    <img src="{{ $comment->user->avatar_url ?? 'https://i.pravatar.cc/30' }}" class="rounded-circle me-2 border" width="32" height="32">
                                    <div class="bg-white p-2 rounded-3 shadow-sm border" style="min-width: 150px;">
                                        <span class="fw-bold d-block" style="font-size: 0.85rem;">{{ $comment->user->fullname }}</span>
                                        <span style="font-size: 0.9rem; color: #333;">{{ $comment->content }}</span>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center text-muted mt-5">
                                    <i class="bi bi-chat-dots fs-1 d-block mb-2"></i>
                                    Chưa có bình luận nào. Hãy là người đầu tiên!
                                </div>
                                @endforelse
                            </div>

                            <div class="p-3 border-top bg-white">
                                <form action="{{ route('comments.store', $post->id) }}" method="POST">
                                    @csrf
                                    <div class="input-group">
                                        <input type="text" name="content" class="form-control border-0 bg-light shadow-none" placeholder="Viết bình luận..." required>
                                        <button class="btn btn-outline-primary border-0 fw-bold" type="submit">Đăng</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> @endforeach
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            document.body.appendChild(modal);
        });
    });
</script>
@endsection