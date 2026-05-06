@extends('dashboard')

@section('content')
@if(session('error'))
<div id="toast-error" style="
    position: fixed;
    bottom: 25px;
    right: 25px;
    background: #ff4d4f;
    color: white;
    padding: 14px 22px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    z-index: 9999;
    font-weight: 500;
    animation: slideIn 0.4s ease;
">
    {{ session('error') }}
</div>

<style>
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(50px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
</style>

<script>
    setTimeout(() => {
        const toast = document.getElementById('toast-error');
        if (toast) {
            toast.style.transition = '0.4s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(50px)';
            setTimeout(() => toast.remove(), 400);
        }
    }, 3000);
</script>
@endif
<link rel="stylesheet" href="{{ asset('css/list_messages.css') }}">

<div class="main-container">
    <div class="messages-sidebar">
        <div class="search-box">
            <input type="text" placeholder="Tìm kiếm ....">
        </div>

        <div class="scrollable-list">
            @foreach($conversations as $chat)

            <a href="{{ route('chat_messages', $chat->id) }}" class="message-item-link">
                <div class="message-item">
                    <div class="avatar-wrapper">

                        @if($chat->type === 'group')
                        <img src="{{ $chat->image_url ?? 'https://i.pravatar.cc/40' }}" class="chat-avatar">
                        @else
                        <img src="{{ $chat->partner->avatar_url ?? 'https://i.pravatar.cc/40' }}" class="chat-avatar">
                        @endif

                    </div>

                    <div class="message-info">
                        <h4 class="user-name">

                            @if($chat->type === 'group')
                            {{ $chat->name ?? 'Nhóm chat' }}
                            @else
                            {{ $chat->partner?->fullname ?? 'Tin nhắn đã lưu' }}
                            @endif

                        </h4>

                        <p class="last-message">
                            {{ $chat->lastMessage->content ?? 'Bắt đầu trò chuyện ngay' }}
                        </p>
                    </div>
                </div>
            </a>

            @endforeach
        </div>
    </div>
    <div class="grid">
        {{-- VÒNG LẶP 1: CHỈ HIỂN THỊ DANH SÁCH BÀI VIẾT --}}
        @foreach ($posts as $post)
        <div class="card mb-4">
            {{-- HEADER --}}
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img class="avatar" src="{{ $post->user->avatar ?? 'https://i.pravatar.cc/40?u=' . $post->user_id }}"
                        style="width:40px; height:40px; border-radius:50%; object-fit:cover;">

                    <div class="info ms-2">
                        <strong class="name d-block">{{ $post->user->fullname ?? 'Người dùng' }}</strong>
                        <span class="time text-muted small">{{ $post->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                <div class="dropdown">
                    <button class="more-btn border-0 bg-transparent" type="button" data-bs-toggle="dropdown">⋯</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><button class="dropdown-item" data-bs-toggle="modal"
                                data-bs-target="#editPostModal{{ $post->id }}">Sửa bài viết</button></li>
                        <li><button class="dropdown-item text-danger" data-bs-toggle="modal"
                                data-bs-target="#deletePostModal{{ $post->id }}">Xóa bài viết</button></li>
                    </ul>
                </div>
            </div>

            {{-- BODY CONTENT --}}
            <div class="card-body">
                <div class="card-text text-dark mb-2">
                    {!! $post->formatted_content ?? nl2br(e($post->content)) !!}
                </div>

                @if($post->media->count())
                <div class="card-img-container mb-3" style="cursor: pointer;" data-bs-toggle="modal"
                    data-bs-target="#instagramModal{{ $post->id }}">
                    <img class="card-img w-100 rounded" src="{{ asset($post->media->first()->media_url) }}"
                        style="max-height: 500px; object-fit: cover;">
                </div>
                @endif

                {{-- ACTIONS (Like, Comment, Pin) --}}
                <div class="card-actions d-flex justify-content-between align-items-center border-top pt-2">
                    <div class="d-flex align-items-center gap-3">
                        <form action="{{ route('post.like', $post->id) }}" method="POST" class="m-0">
                            @csrf
                            @php $userId = auth()->id();
                            $checkLike = $userId ? $post->likes->contains('user_id', $userId) : false; @endphp
                            <button type="submit"
                                class="btn-action border-0 bg-transparent {{ $checkLike ? 'text-danger' : '' }}">
                                ❤️ <span class="ms-1">{{ $post->likes->count() }}</span>
                            </button>
                        </form>

                        <button class="btn-action border-0 bg-transparent" data-bs-toggle="modal"
                            data-bs-target="#instagramModal{{ $post->id }}">
                            💬 <span>{{ $post->comments->count() }}</span>
                        </button>
                    </div>

                    <form action="{{ route('post.pin', $post->id) }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit"
                            class="btn-action border-0 bg-transparent {{ $post->is_pinned ? 'text-primary' : '' }}">📌</button>
                    </form>
                </div>
            </div>
        </div> {{-- KẾT THÚC CARD --}}
        @endforeach
        @foreach ($posts as $post)
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
                            @if($post->media->count())
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
                        <div style="flex: 1.5; background: #000; display: flex; align-items: center; justify-content: center;">
                            @if ($post->media->count())
                            <img src="{{ asset($post->media->first()->media_url) }}"
                                style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            @else
                            <div class="text-white">Không có ảnh</div>
                            @endif
                        </div>
                        <div style="flex: 1; background: #fff; display: flex; flex-direction: column; width: 400px;">
                            <div class="p-3 d-flex align-items-center border-bottom">
                                <img src="{{ $post->user->avatar ? asset('storage/' . $post->user->avatar) : 'https://i.pravatar.cc/40?u=' . $post->user_id }}"
                                    class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
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
        @endforeach
    </div>
</div>
</div>
@endsection