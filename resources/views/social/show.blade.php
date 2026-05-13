@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">

<div class="detail-container">
    <div class="detail-card">

        {{-- HEADER --}}
        <div class="detail-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <img class="detail-avatar"
                    src="{{ $post->user->avatar_url ? asset($post->user->avatar_url) : 'https://i.pravatar.cc/40?u='.$post->user_id }}"
                    alt="avatar" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">

                <div class="detail-info ms-2">
                    <strong class="detail-name d-block">{{ $post->user->fullname ?? 'Người dùng' }}</strong>
                    <span class="detail-time text-muted small">{{ $post->created_at->diffForHumans() }}</span>
                </div>
            </div>

            {{-- DROPDOWN (Sửa/Xóa - Copy từ social.php) --}}
            @if (auth()->id() == $post->user_id)
            <div class="dropdown">
                <button class="more-btn border-0 bg-transparent" type="button" data-bs-toggle="dropdown">⋯</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editPostModal{{ $post->id }}">Sửa bài viết</button></li>
                    <li><button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deletePostModal{{ $post->id }}">Xóa bài viết</button></li>
                </ul>
            </div>
            @endif
        </div>

        {{-- CONTENT --}}
        <div class="detail-text mt-3">
            {{-- Sử dụng formatted_content để hiển thị hashtag/link nếu có --}}
            {!! $post->formatted_content ?? nl2br(e($post->content)) !!}
        </div>

        {{-- IMAGE --}}
        @if($post->media->count())
        <div class="detail-img-container mt-3">
            <img class="detail-img w-100 rounded"
                src="{{ asset($post->media->first()->media_url) }}"
                alt="post image" style="max-height: 600px; object-fit: contain; background: #f8f9fa;">
        </div>
        @endif

        {{-- ACTIONS (Đã đồng bộ đủ Like, Comment, Save, Pin) --}}
        <div class="detail-actions d-flex justify-content-between align-items-center border-top pt-3 mt-3">
            <div class="d-flex align-items-center gap-3">

                {{-- LIKE --}}
                <form action="{{ route('post.like', $post->id) }}" method="POST" class="m-0">
                    @csrf
                    @php
                    $userId = auth()->id();
                    $checkLike = $userId ? $post->likes->contains('user_id', $userId) : false;
                    @endphp
                    <button type="submit" class="btn-action border-0 bg-transparent p-0 d-flex align-items-center {{ $checkLike ? 'text-danger' : '' }}" style="gap: 5px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="{{ $checkLike ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                        <span class="fw-bold" style="font-size: 14px;">{{ $post->likes->count() }}</span>
                    </button>
                </form>

                {{-- COMMENT BUTTON (Đã sửa để mở được Modal) --}}
                <button type="button"
                    class="btn-action border-0 bg-transparent p-0 d-flex align-items-center"
                    data-bs-toggle="modal"
                    data-bs-target="#instagramModal{{ $post->id }}"
                    style="gap: 5px; color: #555; cursor: pointer;">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48l-1.115 2.91a.45.45 0 0 0 .584.584l2.91-1.115A8.97 8.97 0 0 0 12 20.25Z" />
                    </svg>
                    <span class="fw-bold" style="font-size: 14px;">{{ $post->comments->count() }}</span>
                </button>

                {{-- SAVE --}}
                @php
                $isSaved = auth()->check() && auth()->user()->savedPosts->contains($post->id);
                @endphp
                <form action="{{ route('posts.save', $post->id) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn-action save-btn {{ $isSaved ? 'saved' : '' }} border-0 bg-transparent p-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="{{ $isSaved ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 3h12a1 1 0 0 1 1 1v17l-7-5-7 5V4a1 1 0 0 1 1-1z" />
                        </svg>
                    </button>
                </form>

                {{-- PIN (Chỉ chủ bài viết mới thấy) --}}
                @if(auth()->id() == $post->user_id)
                <form action="{{ route('post.pin', $post->id) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn-action pin-btn {{ $post->is_pinned ? 'is-pinned' : '' }} border-0 bg-transparent p-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 640 640">
                            <path d="M288.6 76.8C344.8 20.6 436 20.6 492.2 76.8C548.4 133 548.4 224.2 492.2 280.4L328.2 444.4C293.8 478.8 238.1 478.8 203.7 444.4C169.3 410 169.3 354.3 203.7 319.9L356.5 167.3C369 154.8 389.3 154.8 401.8 167.3C414.3 179.8 414.3 200.1 401.8 212.6L249 365.3C239.6 374.7 239.6 389.9 249 399.2C258.4 408.5 273.6 408.6 282.9 399.2L446.9 235.2C478.1 204 478.1 153.3 446.9 122.1C415.7 90.9 365 90.9 333.8 122.1L169.8 286.1C116.7 339.2 116.7 425.3 169.8 478.4C222.9 531.5 309 531.5 362.1 478.4L492.3 348.3C504.8 335.8 525.1 335.8 537.6 348.3C550.1 360.8 550.1 381.1 537.6 393.6L407.4 523.6C329.3 601.7 202.7 601.7 124.6 523.6C46.5 445.5 46.5 318.9 124.6 240.8L288.6 76.8z" />
                        </svg>
                    </button>
                </form>
                @endif
            </div>

            <div class="share-btn" style="cursor: pointer;">🔗</div>
        </div>

        {{-- LIKERS --}}
        @if ($post->likes->count() > 0)
        <div class="detail-likers mt-2">
            <a href="{{ route('post.likers', $post->id) }}" class="text-muted small" style="text-decoration: none; font-weight: 500;">
                Xem tất cả người đã thích
            </a>
        </div>
        @endif

    </div>
</div>
{{-- MODAL BÌNH LUẬN --}}
<div class="modal fade" id="instagramModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="row g-0">
                {{-- Bên trái: Hình ảnh --}}
                <div class="col-md-7 bg-black d-flex align-items-center justify-content-center">
                    @if($post->media->count())
                    <img src="{{ asset($post->media->first()->media_url) }}" class="img-fluid">
                    @endif
                </div>

                {{-- Bên phải: Nội dung & Bình luận --}}
                <div class="col-md-5 d-flex flex-column bg-white" style="height: 80vh;">
                    <div class="p-3 border-bottom">
                        <strong>{{ $post->user->fullname }}</strong>
                    </div>

                    <div class="flex-grow-1 p-3" style="overflow-y: auto;">
                        {{-- Vòng lặp hiển thị bình luận --}}
                        @foreach($post->comments as $comment)
                        <div class="mb-2">
                            <strong>{{ $comment->user->fullname }}:</strong> {{ $comment->content }}
                        </div>
                        @endforeach
                    </div>

                    {{-- Form gửi bình luận --}}
                    <div class="p-3 border-top">
                        <form action="{{ route('comments.store', $post->id) }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="text" name="content" class="form-control" placeholder="Thêm bình luận...">
                                <button class="btn btn-outline-primary" type="submit">Đăng</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection