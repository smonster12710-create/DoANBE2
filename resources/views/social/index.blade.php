@extends('dashboard')

@section('content')

<link rel="stylesheet" href="{{ asset('css/social.css') }}">

<div class="grid">
    @foreach ($posts as $post)
    <div class="card">

        {{-- HEADER --}}
        <div class="card-header">
            <img class="avatar" src="{{ $post->user->avatar ?? 'https://i.pravatar.cc/40?u='.$post->user_id }}">

            <div class="info">
                <span class="name">{{ $post->user->fullname ?? 'Người dùng' }}</span>
                <span class="time">{{ $post->created_at->diffForHumans() }}</span>
            </div>

            {{-- DROPDOWN --}}
            <div class="dropdown">
                <button class="more-btn" type="button" data-bs-toggle="dropdown">
                    ⋯
                </button>

                <ul class="dropdown-menu">
                    <li>
                        <button class="dropdown-item"
                            data-bs-toggle="modal"
                            data-bs-target="#editPostModal{{ $post->id }}">
                            Sửa bài viết
                        </button>
                    </li>

                    <li>
                        <button class="dropdown-item text-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#deletePostModal{{ $post->id }}">
                            Xóa bài viết
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        {{-- TEXT --}}
        <div class="card-text">
            <a href="{{ route('posts.show', $post->id) }}">
                {!! nl2br(e($post->content)) !!}
            </a>
        </div>

        {{-- IMAGE --}}
        @if($post->media->count())
        <a href="{{ route('posts.show', $post->id) }}">
            <img class="card-img"
                src="{{ asset($post->media->first()->media_url) }}">
        </a>
        @endif

        {{-- ACTIONS --}}
        <div class="actions-container">
            <div class="card-actions">

                <div class="left-actions">

                    {{-- LIKE --}}
                    <form action="{{ route('post.like', $post->id) }}" method="POST">
                        @csrf
                        @php
                        $userId = auth()->id() ?? 1;
                        $checkLike = $post->likes->contains('user_id', $userId);
                        @endphp

                        <button type="submit" class="btn-action {{ $checkLike ? 'is-liked' : '' }}">
                            <svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                            </svg>

                            <span class="like-count">{{ $post->likes->count() }}</span>
                        </button>
                    </form>

                    {{-- COMMENT --}}
                    <button class="btn-action" data-bs-toggle="modal"
                                data-bs-target="#instagramModal{{ $post->id }}"
                                style="background:none; border:none; padding:0;">
                                <svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48l-1.115 2.91a.45.45 0 0 0 .584.584l2.91-1.115A8.97 8.97 0 0 0 12 20.25Z" />
                                </svg>
                                <span>{{ $post->comments->count() }}</span>
                            </button>

                    {{-- SAVE (chưa xử lý) --}}
                    <div class="btn-action">
                        🔖
                    </div>
                    <form action="{{ route('post.pin', $post->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="btn-action pin-btn {{ $post->is_pinned ? 'is-pinned' : '' }}">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                width="20" height="20"
                                fill="currentColor"
                                viewBox="0 0 16 16">
                                <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1z" />
                                <path d="M8 4a.5.5 0 0 1 .5.5V6H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V7H6a.5.5 0 0 1 0-1h1.5V4.5A.5.5 0 0 1 8 4" />
                            </svg>

                        </button>
                    </form>
                </div>

                <div class="share-btn">🔗</div>
            </div>

            {{-- LIKERS --}}
            @if($post->likes->count() > 0)
            <div class="likers-row">
                <a href="{{ route('post.likers', $post->id) }}" class="likers-link">
                    Xem tất cả người đã thích
                </a>
            </div>
            @endif

        </div>
    </div>

    {{-- MODAL SỬA --}}
    <div class="modal fade" id="editPostModal{{ $post->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Sửa bài viết</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('posts.update', $post->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="modal-body">
                        <textarea name="content" class="form-control" rows="5" required>{{ $post->content }}</textarea>

                        <br>

                        @if($post->media->count())
                        <img src="{{ asset($post->media->first()->media_url) }}" style="width:100%; border-radius:10px;">
                        @endif

                        <br><br>

                        <input type="file" name="image" class="form-control">
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
    <div class="modal fade" id="deletePostModal{{ $post->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title text-danger">Xóa bài viết</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    Bạn có chắc muốn xóa bài viết này không?
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>

                    <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger">Xóa</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    {{-- MODAL PHONG CÁCH INSTAGRAM --}}
            <div class="modal fade" id="instagramModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content" style="border-radius: 4px; overflow: hidden; height: 85vh; border:none;">
                        <div class="d-flex flex-row h-100">

                            {{-- TRÁI: ẢNH --}}
                            <div
                                style="flex: 1.5; background: #000; display: flex; align-items: center; justify-content: center;">
                                @if ($post->media->count())
                                    <img src="{{ asset($post->media->first()->media_url) }}"
                                        style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                @else
                                    <div class="text-white">Không có ảnh</div>
                                @endif
                            </div>

                            {{-- PHẢI: BÌNH LUẬN --}}
                            <div style="flex: 1; background: #fff; display: flex; flex-direction: column; width: 400px;">

                                {{-- Header người đăng --}}
                                <div class="p-3 d-flex align-items-center border-bottom">
                                    <img src="{{ $post->user->avatar ? asset('storage/' . $post->user->avatar) : 'https://i.pravatar.cc/40?u=' . $post->user_id }}"
                                        class="rounded-circle me-2" width="32" height="32"
                                        style="object-fit: cover;">
                                    <strong style="font-size: 14px;">{{ $post->user->fullname ?? 'Người dùng' }}</strong>
                                </div>

                                {{-- List bình luận --}}
                                <div class="flex-grow-1 p-3" style="overflow-y: auto;">
                                    {{-- Caption bài viết --}}
                                    <div class="d-flex mb-3">
                                        <img src="{{ $post->user->avatar ? asset('storage/' . $post->user->avatar) : 'https://i.pravatar.cc/40?u=' . $post->user_id }}"
                                            class="rounded-circle me-2" width="32" height="32"
                                            style="object-fit: cover;">
                                        <div style="font-size: 14px;">
                                            <strong>{{ $post->user->fullname ?? 'Người dùng' }}</strong>
                                            {{ $post->content }}
                                        </div>
                                    </div>
                                    <hr>

                                    {{-- Danh sách các comment --}}
                        @foreach($post->comments as $comment)
                            <div class="d-flex mb-3">
                                <img src="{{ $comment->user->avatar ? asset('storage/' . $comment->user->avatar) : 'https://i.pravatar.cc/40?u='.$comment->user_id }}" 
                                    class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                                <div style="font-size: 13px;">
                                    <strong>{{ $comment->user->fullname ?? 'Người dùng' }}</strong> {{ $comment->content }}
                                    <div class="text-muted" style="font-size: 11px;">
                                        @php \Carbon\Carbon::setLocale('vi'); @endphp
                                        {{ $comment->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                                {{-- Form đăng bình luận --}}
                                <div class="border-top p-3">
                                    <form action="{{ route('comments.store', $post->id) }}" method="POST"
                                        class="d-flex align-items-center">
                                        @csrf
                                        <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://i.pravatar.cc/40?u=' . auth()->id() }}"
                                            class="rounded-circle me-2" width="28" height="28"
                                            style="object-fit: cover;">

                                        <input type="text" name="content"
                                            class="form-control border-0 shadow-none p-0" placeholder="Thêm bình luận..."
                                            style="font-size: 14px;">
                                        <button type="submit" class="btn text-primary fw-bold shadow-none">Đăng</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    @endforeach
</div>

@endsection