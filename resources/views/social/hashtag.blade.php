@extends('dashboard')

@section('content')

    <link rel="stylesheet" href="{{ asset('css/social.css') }}">
    <style>
        /* CSS cho hashtag để nó có màu xanh dương chuẩn MXH */
        a.hashtag-link {
            color: #1877f2 !important;
            font-weight: 600 !important;
            text-decoration: none;
        }

        a.hashtag-link:hover {
            text-decoration: underline;
            color: #166fe5 !important;
        }

        /* Style cho cái Header kết quả */
        .hashtag-header {
            background: linear-gradient(135deg, #f0f2f5 0%, #ffffff 100%);
            border: 1px solid #e4e6eb;
        }
    </style>

    <!-- HEADER HIỂN THỊ KẾT QUẢ TÌM KIẾM -->

    <div class="grid">
        {{-- Dùng forelse: Có bài thì in, không có thì nhảy xuống @empty --}}
        @forelse ($posts as $post)

            <!-- BẮT ĐẦU 1 BÀI VIẾT (Y chang trang chủ) -->
            <div class="card mb-4 shadow-sm">

                {{-- HEADER CỦA BÀI VIẾT --}}
                <div class="card-header">
                    <img class="avatar" src="{{ $post->user->avatar ?? 'https://i.pravatar.cc/40?u=' . $post->user_id }}">

                    <div class="info">
                        <span class="name">{{ $post->user->fullname ?? 'Người dùng' }}</span>
                        <span class="time">{{ $post->created_at->diffForHumans() }}</span>
                    </div>

                    {{-- DROPDOWN MENU (Chỉ hiện nếu là bài của mình, nếu Pro có logic check user thì chêm vô nha) --}}
                    <div class="dropdown">
                        <button class="more-btn" type="button" data-bs-toggle="dropdown">⋯</button>
                        <ul class="dropdown-menu">
                            <li>
                                <button class="dropdown-item" data-bs-toggle="modal"
                                    data-bs-target="#editPostModal{{ $post->id }}">Sửa bài viết</button>
                            </li>
                            <li>
                                <button class="dropdown-item text-danger" data-bs-toggle="modal"
                                    data-bs-target="#deletePostModal{{ $post->id }}">Xóa bài viết</button>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- NỘI DUNG CHỮ --}}
                <div class="card-text text-dark">
                    {!! $post->formatted_content !!}
                </div>

                <div class="mt-2">
                    <a href="{{ route('posts.show', $post->id) }}" class="text-muted text-decoration-none"
                        style="font-size: 0.85rem;">
                        <small>Xem chi tiết bài viết</small>
                    </a>
                </div>

                {{-- HÌNH ẢNH --}}
                @if($post->media->count())
                    <a href="{{ route('posts.show', $post->id) }}">
                        <img class="card-img mt-2" src="{{ asset($post->media->first()->media_url) }}"
                            style="width: 100%; border-radius: 8px;">
                    </a>
                @endif

                {{-- THANH HÀNH ĐỘNG (LIKE, COMMENT...) --}}
                <div class="actions-container mt-3">
                    <div class="card-actions">
                        <div class="left-actions d-flex align-items-center gap-3">

                            {{-- LIKE --}}
                            <form action="{{ route('post.like', $post->id) }}" method="POST">
                                @csrf
                                @php
                                    $userId = auth()->id() ?? 1;
                                    $checkLike = $post->likes->contains('user_id', $userId);
                                @endphp
                                <button type="submit"
                                    class="btn-action {{ $checkLike ? 'is-liked' : '' }} border-0 bg-transparent">
                                    <svg class="icon-svg" width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.313 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                    </svg>
                                    <span class="like-count">{{ $post->likes->count() }}</span>
                                </button>
                            </form>

                            {{-- COMMENT --}}
                            <div class="btn-action">💬 <span>0</span></div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- KẾT THÚC 1 BÀI VIẾT -->

        @empty
            <!-- NẾU KHÔNG CÓ BÀI VIẾT NÀO THÌ NHẢY VÔ ĐÂY -->
            <div class="text-center py-5">
                <div style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;">📭</div>
                <h4 class="text-muted fw-bold">Chưa có bài viết nào</h4>
                <p class="text-secondary">Hãy là người đầu tiên đăng bài với hashtag <b>#{{ $cleanKeyword }}</b> nhé!</p>
                <!-- Nút bấm quay về trang chủ -->
                <a href="{{ url('/') }}" class="btn btn-primary rounded-pill mt-2 px-4 py-2">Quay lại bảng tin</a>
            </div>
        @endforelse
    </div>

@endsection