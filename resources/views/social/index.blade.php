@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/social.css') }}">
<div class="grid">

    @foreach ($posts as $post)
        <div class="card">

            <div class="card-header">
                <img class="avatar" src="{{ $post->user->avatar ?? 'https://i.pravatar.cc/40?u='.$post->user_id }}">
                <div class="info">
                    <span class="name">{{ $post->user->name ?? 'Người dùng' }}</span>
                    <span class="time">{{ $post->created_at->diffForHumans() }}</span>
                </div>
                <div class="more">⋯</div>
            </div>

            <div class="card-text">
                {!! nl2br(e($post->content)) !!}
            </div>

            {{-- Ảnh cố định theo ID bài viết để không bị nhảy khi load lại trang --}}
            <img class="card-img" src="{{ $post->image_url ?? 'https://picsum.photos/seed/'.$post->id.'/300/400' }}">

            <div class="card-actions">
                
                {{-- NÚT LIKE --}}
                <form action="{{ route('post.like', $post->id) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" style="background: none; border: none; cursor: pointer; display: inline-flex; align-items: center; padding: 0;">
                        @php
                            // Lấy ID người dùng (mặc định 0 nếu chưa login)
                            $userId = auth()->id() ?? 1;
                            // Kiểm tra chính xác trạng thái đã like
                            $checkLike = $post->likes->contains('user_id', $userId);
                        @endphp
                        
                        {{-- Sử dụng biến $checkLike để tô màu --}}
                        <svg style="height: 20px; width: 20px; fill: {{ $checkLike ? 'red' : 'none' }}; stroke: {{ $checkLike ? 'red' : 'black' }};" 
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                            <path d="M442.9 144C415.6 144 389.9 157.1 373.9 179.2L339.5 226.8C335 233 327.8 236.7 320.1 236.7C312.4 236.7 305.2 233 300.7 226.8L266.3 179.2C250.3 157.1 224.6 144 197.3 144C150.3 144 112.2 182.1 112.2 229.1C112.2 279 144.2 327.5 180.3 371.4C221.4 421.4 271.7 465.4 306.2 491.7C309.4 494.1 314.1 495.9 320.2 495.9C326.3 495.9 331 494.1 334.2 491.7C368.7 465.4 419 421.3 460.1 371.4C496.3 327.5 528.2 279 528.2 229.1C528.2 182.1 490.1 144 443.1 144zM335 151.1C360 116.5 400.2 96 442.9 96C516.4 96 576 155.6 576 229.1C576 297.7 533.1 358 496.9 401.9C452.8 455.5 399.6 502 363.1 529.8C350.8 539.2 335.6 543.9 320 543.9C304.4 543.9 289.2 539.2 276.9 529.8C240.4 502 187.2 455.5 143.1 402C106.9 358.1 64 297.7 64 229.1C64 155.6 123.6 96 197.1 96C239.8 96 280 116.5 305 151.1L320 171.8L335 151.1z" />
                        </svg>
                        
                        {{-- Ưu tiên hiển thị like_count từ bảng posts cho nhanh --}}
                        <span style="margin-left: 5px; font-weight: bold; color: {{ $checkLike ? 'red' : 'black' }};">
                            {{ $post->like_count ?? $post->likes->count() }}
                        </span>
                    </button>
                </form>

                <a href="#" style="text-decoration: none; color: inherit; margin-left: 10px;">
                    <small style="color: #666;">(Xem ai đã like)</small>
                </a>
            </div>

        </div>
    @endforeach

</div>
@endsection