@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">

@php
    $user = Auth::user();

    $avatar = $user && $user->avatar_url
        ? asset($user->avatar_url)
        : asset('img/user/user.jpg');

    $cover = $user && $user->cover_url
        ? asset($user->cover_url)
        : asset('img/cover/cover.jpg');
@endphp
<div class="profile-page">
    <div class="profile-container">

        <div class="profile-card">
            <img class="cover-img" src="{{ $cover }}" alt="cover">

            <div class="profile-info">
                <img class="profile-avatar" src="{{ $avatar }}" alt="avatar">

                <div class="profile-main">
                    <h1 class="profile-name">
                        {{ $user->fullname ?? $user->name ?? 'Lam Pham' }}
                    </h1>

                    <div class="profile-username">
                        {{ '@' . ($user->username ?? 'phamlam0375') }}
                    </div>

                    <div class="profile-stats-line">
                        <span><strong>0</strong> bạn bè</span>
                        <span>·</span>
                        <span><strong>0</strong> bài viết</span>
                        <span>·</span>
                        <span><strong>0</strong> người theo dõi</span>
                        <span>·</span>
                        <span><strong>0</strong> đang theo dõi</span>
                    </div>

                    <div class="profile-bio">
                        {{ $user->bio ?? 'Chưa có tiểu sử' }}
                    </div>
                </div>

                    <a href="{{ route('profile.edit') }}" class="edit-btn">✎ Chỉnh sửa</a>
            </div>
        </div>

        <div class="profile-body">
            <div class="info-box">
                <div class="box-title"><span>●</span> Giới thiệu</div>

                <div class="info-list">
                    <div class="info-row">
                        <div class="info-icon">▦</div>
                        <div>
                            <div class="info-label">Ngày sinh</div>
                            <div class="info-value">{{ $user->birthday ?? 'Chưa có' }}</div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon">☎</div>
                        <div>
                            <div class="info-label">Số điện thoại</div>
                            <div class="info-value">{{ $user->phone ?? 'Chưa có' }}</div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon">✉</div>
                        <div>
                            <div class="info-label">Email</div>
                            <div class="info-value">{{ $user->email ?? 'Chưa có' }}</div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon">⚥</div>
                        <div>
                            <div class="info-label">Giới tính</div>
                            <div class="info-value">
                                @if($user->gender == 1)
                                    Nam
                                @elseif($user->gender == 2)
                                    Nữ
                                @elseif($user->gender == 3)
                                    Khác
                                @else
                                    Chưa có
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon">⌖</div>
                        <div>
                            <div class="info-label">Địa chỉ</div>
                            <div class="info-value">{{ $user->address ?? 'Chưa có' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection