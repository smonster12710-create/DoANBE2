@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/profile-lock.css') }}">

<div class="profile-private-page">

    <div class="profile-private-card">

        <img
            src="{{ $user->avatar_url ? asset($user->avatar_url) : asset('img/user/user.jpg') }}"
            alt="avatar"
            class="profile-private-avatar"
        >

        <h1>{{ $user->fullname ?? 'Người dùng' }}</h1>

        <p class="profile-private-username">
            {{ '@' . $user->username }}
        </p>

        <div class="profile-private-icon">
            🔒
        </div>

        <h2>Trang cá nhân này đã được khóa</h2>

        <p>
            Chỉ bạn bè mới có thể xem ảnh, bài viết và thông tin chi tiết trên trang cá nhân này.
        </p>

        <a href="{{ route('profile.show', auth()->user()->username) }}" class="profile-private-btn">
            Quay về trang cá nhân của bạn
        </a>

    </div>

</div>
@endsection