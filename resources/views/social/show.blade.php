@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">

<div class="detail-container">

    <div class="detail-card">

        {{-- HEADER --}}
        <div class="detail-header">
            <img class="detail-avatar"
                src="{{ $post->user->avatar ?? 'https://i.pravatar.cc/40?u='.$post->user_id }}"
                alt="avatar">

            <div class="detail-info">
                <span class="detail-name">
                    {{ $post->user->fullname ?? 'Người dùng' }}
                </span>
                <span class="detail-time">
                    {{ $post->created_at->diffForHumans() }}
                </span>
            </div>
        </div>

        {{-- CONTENT --}}
        <div class="detail-text">
            {!! nl2br(e($post->content)) !!}
        </div>

        {{-- IMAGE --}}
        @if($post->media->count())
        <img class="detail-img"
            src="{{ asset($post->media->first()->media_url) }}"
            alt="post image">
        @endif

        {{-- ACTIONS --}}
        <div class="detail-actions">

            <div class="detail-left">

                {{-- LIKE --}}
                <form action="{{ route('post.like', $post->id) }}" method="POST">
                    @csrf
                    @php
                    $userId = auth()->id() ?? 1;
                    $checkLike = $post->likes->contains('user_id', $userId);
                    @endphp

                    <button type="submit"
                        class="detail-btn {{ $checkLike ? 'detail-liked' : '' }}">
                        ❤️ <span>{{ $post->likes->count() }}</span>
                    </button>
                </form>

                {{-- SHARE --}}
                <button class="detail-btn">
                    🔗 <span>Chia sẻ</span>
                </button>

            </div>

        </div>

        {{-- LIKERS --}}
        @if($post->likes->count() > 0)
        <div class="detail-likers">
            <a href="{{ route('post.likers', $post->id) }}">
                Xem tất cả người đã thích
            </a>
        </div>
        @endif

    </div>

</div>
@endsection