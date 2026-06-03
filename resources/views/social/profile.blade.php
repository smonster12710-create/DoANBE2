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
                {{-- KHUNG KÍCH HOẠT ĐĂNG BÀI NHANH --}}
                @if(auth()->check())
                <div class="card mb-4 shadow-sm border-0 wall-post-card">
                    <div class="d-flex gap-15 align-items-start">

                        <img src="{{ auth()->user()->avatar_src }}"
                            alt="Avatar" class="rounded-circle wall-avatar">

                        <button type="button" class="btn text-start text-muted rounded-pill flex-grow-1 py-2 px-3 wall-fake-input"
                            data-bs-toggle="modal" data-bs-target="#createWallPostModal">
                            @if(auth()->id() == $user->id)
                            Bạn đang nghĩ gì thế?
                            @else
                            Viết gì đó cho {{ $user->name ?? 'Người dùng' }}...
                            @endif
                        </button>
                    </div>
                </div>
                @endif

                {{-- DANH SÁCH BÀI VIẾT --}}
                @if(isset($posts) && count($posts) > 0)
                @foreach ($posts as $post)
                @if(!$post->is_anonymous || auth()->id() == $post->user_id)
                @include('posts.post_card', ['post' => $post])

                @push('modals')
                @include('partials.post_modals', ['post' => $post])
                @endpush
                @endif
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

@push('modals')
<div class="modal fade" id="createWallPostModal" tabindex="-1" aria-labelledby="wallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title wall-modal-title" id="wallModalLabel">Tạo bài viết mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" id="createWallPostForm">
                @csrf
                <input type="hidden" name="wall_user_id" value="{{ $user->id }}">
                {{-- ĐÃ BỔ SUNG: Gửi kèm username lên Server phục vụ tạo route redirect --}}
                <input type="hidden" name="wall_username" value="{{ $user->username ?? $user->name }}">

                <div class="modal-body">
                    <div class="mb-3">
                        <textarea name="content" id="wallPostContent" class="form-control wall-textarea" rows="4"
                            placeholder="@if(auth()->id() == $user->id) Bạn đang nghĩ gì? @else Viết gì đó lên tường của {{ $user->name }}... @endif"
                            required maxlength="500"></textarea>

                        <div class="text-end text-muted small mt-1">
                            <span id="wallCharCount">0</span>/500 ký tự
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="wallPostImages" class="form-label fw-bold text-secondary">Thêm ảnh vào bài viết</label>
                        <input class="form-control" type="file" name="images[]" id="wallPostImages" accept="image/*" multiple>
                        <div id="wall-preview-images" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary wall-btn-submit">Đăng bài</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

{{-- Đẩy file JS vừa tạo vào hệ thống layout --}}
@push('scripts')
<script src="{{ asset('js/profile.js') }}"></script>
@endpush