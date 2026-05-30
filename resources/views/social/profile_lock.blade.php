@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/profile-lock.css') }}">

<div class="profile-lock-page">

    <div class="profile-lock-card">

        <div class="profile-lock-cover">
            <button type="button" class="profile-lock-close" onclick="window.history.back()">
                ×
            </button>

            <div class="profile-lock-avatar-wrap">
                <img
                    src="{{ $user->avatar_url ? asset($user->avatar_url) : asset('img/user/user.jpg') }}"
                    alt="avatar"
                    class="profile-lock-avatar"
                >

                <div class="profile-lock-shield">
                    🛡
                </div>
            </div>
        </div>

        <div class="profile-lock-content">

            <h1>Khóa bảo vệ trang cá nhân</h1>

            <p class="profile-lock-desc">
                Giữ cho ảnh và bài viết riêng tư hơn chỉ trong một bước.
            </p>

            @if(session('success'))
                <div class="profile-lock-alert">
                    {{ session('success') }}
                </div>
            @endif

            <hr>

            <h2>Khóa bảo vệ hoạt động như thế nào</h2>

            <div class="profile-lock-info">
                <div class="profile-lock-row">
                    <div class="profile-lock-icon">👥</div>
                    <p>
                        Chỉ bạn bè mới nhìn thấy ảnh, bài viết và thông tin trên trang cá nhân của bạn.
                    </p>
                </div>

                <div class="profile-lock-row">
                    <div class="profile-lock-icon">👤</div>
                    <p>
                        Người lạ khi truy cập trang cá nhân sẽ không xem được nội dung chi tiết.
                    </p>
                </div>

                <div class="profile-lock-row">
                    <div class="profile-lock-icon">🌐</div>
                    <p>
                        Một số thông tin cơ bản như tên và ảnh đại diện có thể vẫn hiển thị để bạn bè tìm thấy bạn.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.lock.toggle') }}">
                @csrf

                <button type="submit" class="profile-lock-submit">
                    @if($user->profile_locked)
                        Tắt khóa bảo vệ trang cá nhân
                    @else
                        Khóa bảo vệ trang cá nhân
                    @endif
                </button>
            </form>

        </div>

    </div>

</div>
@endsection