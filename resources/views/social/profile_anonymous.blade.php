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
                    <img src="{{ asset('img/user/user.jpg') }}" alt="avatar" class="profile-lock-avatar">

                    <div class="profile-lock-shield">
                        🔒
                    </div>
                </div>
            </div>

            <div class="profile-lock-content">

                <h1>Chế độ ẩn danh</h1>

                <p class="profile-lock-desc">
                    Đăng bài viết mà không muốn tiết lộ danh tính. Bạn bè chỉ thấy "Ẩn danh" thay vì tên của bạn.
                </p>

                @if(session('success'))
                    <div class="profile-lock-alert">
                        {{ session('success') }}
                    </div>
                @endif

                <hr>

                <h2>Chế độ ẩn danh hoạt động như thế nào</h2>

                <div class="profile-lock-info">
                    <div class="profile-lock-row">
                        <div class="profile-lock-icon">👤</div>
                        <p>
                            Mọi bài viết mới sẽ hiển thị tên "Ẩn danh" thay vì tên thực của bạn.
                        </p>
                    </div>

                    <div class="profile-lock-row">
                        <div class="profile-lock-icon">🖼</div>
                        <p>
                            Ảnh đại diện mặc định sẽ được sử dụng thay vì ảnh profile của bạn.
                        </p>
                    </div>

                    <div class="profile-lock-row">
                        <div class="profile-lock-icon">🔓</div>
                        <p>
                            Bạn vẫn có thể bỏ tích ẩn danh trên từng bài viết để đăng công khai nếu muốn.
                        </p>
                    </div>

                    <div class="profile-lock-row">
                        <div class="profile-lock-icon">👨‍💼</div>
                        <p>
                            <strong>Lưu ý:</strong> Quản trị viên vẫn có thể xem tên thực của bạn.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.anonymous.update') }}">
                    @csrf

                    <button type="submit" class="profile-lock-submit">
                        @if($user->anonymous_posts)
                            Tắt chế độ ẩn danh
                        @else
                            Bật chế độ ẩn danh
                        @endif
                    </button>
                </form>

            </div>

        </div>

    </div>
@endsection