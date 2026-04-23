@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/social.css') }}">

<div class="container" style="max-width: 800px; margin: 0 auto;">
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center;">
                <img class="avatar" src="{{ $post->user->avatar_url ?? 'https://i.pravatar.cc/40' }}" alt="avatar">
                <div class="info">
                    <span class="name"><strong>{{ $post->user->fullname ?? $post->user->username }}</strong></span>
                    <span class="time">{{ $post->created_at->diffForHumans() }}</span>
                </div>
            </div>
            <a href="{{ route('posts.index') }}" class="btn-close" aria-label="Close"></a>
        </div>

        <div class="card-text" style="padding: 15px; font-size: 1.1rem;">
            {!! nl2br(e($post->content)) !!}
        </div>

        @if($post->media && $post->media->count() > 0)
        <img class="card-img" src="{{ $post->media->first()->media_url }}" alt="post image" style="width: 100%; border-radius: 0;">
        @endif

        <div class="card-actions" style="border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <span>
                <svg style="height: 20px; width: 20px;" fill="currentColor" viewBox="0 0 640 640">
                    <path d="M442.9 144C415.6 144 389.9 157.1 373.9 179.2L339.5 226.8C335 233 327.8 236.7 320.1 236.7C312.4 236.7 305.2 233 300.7 226.8L266.3 179.2C250.3 157.1 224.6 144 197.3 144C150.3 144 112.2 182.1 112.2 229.1C112.2 279 144.2 327.5 180.3 371.4C221.4 421.4 271.7 465.4 306.2 491.7C309.4 494.1 314.1 495.9 320.2 495.9C326.3 495.9 331 494.1 334.2 491.7C368.7 465.4 419 421.3 460.1 371.4C496.3 327.5 528.2 279 528.2 229.1C528.2 182.1 490.1 144 443.1 144z" />
                </svg>
                {{ number_format($post->like_count) }} lượt thích
            </span>
            <span>
                <svg style="height: 20px; width: 20px;" fill="currentColor" viewBox="0 0 640 640">
                    <path d="M115.9 448.9C83.3 408.6 64 358.4 64 304C64 171.5 178.6 64 320 64C461.4 64 576 171.5 576 304C576 436.5 461.4 544 320 544C283.5 544 248.8 536.8 217.4 524L101 573.9C97.3 575.5 93.5 576 89.5 576C75.4 576 64 564.6 64 550.5C64 546.2 65.1 542 67.1 538.3L115.9 448.9z" />
                </svg>
                {{ number_format($post->comments->count()) }} bình luận
            </span>
        </div>

        <div class="comments-section" style="padding: 15px;">
            <h6 class="mb-3">Bình luận</h6>

            <form action="{{ route('comments.store', $post->id) }}" method="POST" class="mb-4">
                @csrf
                <div class="input-group">
                    <input type="text" name="content" class="form-control" placeholder="Viết bình luận..." required>
                    <button class="btn btn-primary" type="submit">Gửi</button>
                </div>
            </form>

            <div class="comment-list">
                @forelse($post->comments as $comment)
                <div class="comment-item" style="display: flex; margin-bottom: 15px;">
                    <img src="{{ $comment->user->avatar_url ?? 'https://i.pravatar.cc/30' }}"
                        style="width: 32px; height: 32px; border-radius: 50%; margin-right: 10px;">
                    <div style="background: #f0f2f5; padding: 8px 12px; border-radius: 15px; flex: 1;">
                        <div style="font-weight: bold; font-size: 0.9rem;">{{ $comment->user->username }}</div>
                        <div style="font-size: 0.9rem;">{{ $comment->content }}</div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">Chưa có bình luận nào.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection