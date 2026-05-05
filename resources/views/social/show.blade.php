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

                        <svg class="icon-svg" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">

                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>

                        <span>{{ $post->likes->count() }}</span>
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