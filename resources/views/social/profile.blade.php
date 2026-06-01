    @extends('dashboard')

    @section('content')
        <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
        <link rel="stylesheet" href="{{ asset('css/social.css') }}">
        <div class="profile-page">
            <div class="profile-container">

                @include('partials.profile_header', [
            'user' => $user,
            'friendsCount' => $friendsCount ?? 0,
            'postsCount' => $postsCount ?? 0,
            'followersCount' => $followersCount ?? 0,
            'followingCount' => $followingCount ?? 0,
        ])

                <div class="profile-body">

                    {{-- CỘT TRÁI: GIỚI THIỆU --}}
                    <div class="profile-left">
                        @include('partials.profile_sidebar', ['user' => $user])
                    </div>
                    {{-- CỘT PHẢI: BÀI VIẾT --}}
                    <div class="profile-right">
                        @if(isset($posts) && count($posts) > 0)

                        @foreach ($posts as $post)
                        {{-- 1. Hiển thị card bài viết --}}
                        @include('posts.post_card', ['post' => $post])

                        {{-- 2. Đẩy modal ra ngoài layout tổng --}}
                        @push('modals')
                        {{-- SỬA dòng này từ 'posts.post_modals' thành 'partials.post_modals' --}}
                        @include('partials.post_modals', ['post' => $post])
                        @endpush
                        @endforeach

                        @else
                        <div class="empty-post-box">
                            Người dùng chưa có bài viết nào.
                        </div>
                        @endif
                    </div>

                </div>

            </div>
        </div>
    @endsection