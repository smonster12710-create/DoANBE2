@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/edit-profile.css') }}">

@php
    $user = Auth::user();

    $avatar = $user && $user->avatar_url
        ? asset($user->avatar_url)
        : asset('img/user/user.jpg');

    $cover = $user && $user->cover_url
        ? asset($user->cover_url)
        : asset('img/cover/default-cover.jpg');
@endphp
<div class="edit-profile-page">
    <div class="edit-profile-container">

        <div class="edit-header">
            <img
                src="{{ $cover }}"
                class="edit-cover clickable-image"
                alt="cover"
                onclick="document.getElementById('coverInput').click()"
            >
            <div class="edit-user">
            <img
                src="{{ $avatar }}"
                class="edit-avatar clickable-image"
                alt="avatar"
                onclick="document.getElementById('avatarInput').click()"
            >
                <div>
                    <h1>{{ $user->fullname ?? $user->name ?? 'Lam Pham' }}</h1>
                    <p>{{ '@' . ($user->username ?? 'phamlam0375') }}</p>
                </div>
            </div>
        </div>

                <form
                    class="edit-form"
                    method="POST"
                    action="{{ route('profile.update') }}"
                    enctype="multipart/form-data"
                >
                @csrf
                <input type="file" id="avatarInput" name="avatar" hidden accept="image/*">

                <input type="file" id="coverInput" name="cover" hidden accept="image/*">

            <div class="edit-card">
                <h2>Thông tin cơ bản</h2>

                <div class="form-group">
                    <label>Họ tên</label>
                    <input type="text" name="fullname" value="{{ $user->fullname ?? $user->name ?? '' }}">
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="{{ $user->username ?? '' }}">
                </div>

                <div class="form-group">
                    <label>Tiểu sử</label>
                    <textarea name="bio" rows="3">{{ $user->bio ?? '' }}</textarea>
                </div>
            </div>

            <div class="edit-card">
                <h2>Thông tin cá nhân</h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Ngày sinh</label>
                        <input type="date" name="birthday" value="{{ $user->birthday ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" value="{{ $user->phone ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ $user->email ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label>Giới tính</label>
                            <select name="gender">
                                <option value="1" {{ ($user->gender ?? '') == 1 ? 'selected' : '' }}>Nam</option>
                                <option value="2" {{ ($user->gender ?? '') == 2 ? 'selected' : '' }}>Nữ</option>
                                <option value="3" {{ ($user->gender ?? '') == 3 ? 'selected' : '' }}>Khác</option>
                            </select>
                    </div>

                    <div class="form-group full">
                        <label>Địa chỉ</label>
                        <input type="text" name="address" value="{{ $user->address ?? '' }}">
                    </div>
                </div>
            </div>

            <div class="edit-actions">
                <a href="{{ route('profile') }}" class="cancel-btn">Hủy</a>
                <button type="submit" class="save-btn">Lưu thay đổi</button>
            </div>
        </form>

    </div>
</div>
<script>

document.getElementById('avatarInput')
.addEventListener('change', function (e) {

    const file = e.target.files[0];

    if (file) {

        document.querySelector('.edit-avatar').src =
            URL.createObjectURL(file);
    }
});

document.getElementById('coverInput')
.addEventListener('change', function (e) {

    const file = e.target.files[0];

    if (file) {

        document.querySelector('.edit-cover').src =
            URL.createObjectURL(file);
    }
});

</script>
@endsection