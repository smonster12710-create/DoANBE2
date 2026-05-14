@extends('dashboard')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">

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
                            @include('posts.post_card', ['post' => $post])
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