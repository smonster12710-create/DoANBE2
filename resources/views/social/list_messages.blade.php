@extends('dashboard')

@section('content')

@if(session('error'))
<div id="toast-error" style="
    position: fixed;
    bottom: 25px;
    right: 25px;
    background: #ff4d4f;
    color: white;
    opacity: 0.9;
    padding: 14px 22px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    z-index: 9999;
    font-weight: 500;
    animation: slideIn 0.4s ease;
">
    {{ session('error') }}
</div>


<style>
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(50px);
        }

        to {
            opacity: 0.5;
            transform: translateX(0);
        }
    }
</style>



<script>
    setTimeout(() => {
        const toast = document.getElementById('toast-error');
        if (toast) {
            toast.style.transition = '0.4s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(50px)';
            setTimeout(() => toast.remove(), 400);
        }
    }, 3000);
</script>

@endif
<!-- TOPBAR -->
<div class="topbar" style="display: flex; gap: 15px; align-items: center;">
    <div style="position: relative; flex: 1;">
        <input id="search-input" class="search" style="width: 100%;" placeholder="Tìm kiếm....." autocomplete="off">
        <div id="search-results" class="search-results-dropdown"></div>
    </div>
    <!-- 2 Nút bên phải -->
    <div style="display: flex; gap: 10px;">
        <button class="btn-top">Bạn Bè</button>
        <button class="btn-top">Theo Dõi</button>
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/list_messages.css') }}">

<div class="main-container">
    <div class="messages-sidebar">
        <div class="sidebar-header-actions">

            <div class="search-box">
                <input type="text" id="sidebar-search" placeholder="Tìm kiếm ....">
            </div>

            <button type="button" id="openCreateGroupModal" class="btn-create-group" title="Tạo nhóm chat">
                <i class="fas fa-plus"></i>
            </button>

        </div>

        <div class="scrollable-list">
            @include('partials.list_chat', ['conversations' => $conversations])
        </div>
    </div>
    <link rel="stylesheet" href="{{ asset('css/social.css') }}">

    <div class="grid">
        @foreach ($posts as $post)
        @include('posts.post_card', ['post' => $post])
        @endforeach
    </div>

    @foreach ($posts as $post)
    @include('partials.post_modals', ['post' => $post])

    @endforeach
    <div id="createGroupModal" class="custom-modal-overlay">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h3 id="modal-group-title">Tạo nhóm chat mới</h3>
                <button type="button" id="closeCreateGroupModal" class="btn-close-modal">&times;</button>
            </div>

            <form id="formCreateGroup" enctype="multipart/form-data">
                @csrf

                <div id="group-step-1" class="custom-modal-body">
                    <div class="form-group-chat">
                        <label for="modal-search-friends">Tìm kiếm thành viên</label>
                        <input type="text" id="modal-search-friends" placeholder="Nhập tên người dùng để tìm nhanh..." autocomplete="off">
                    </div>

                    <div class="form-group-chat" style="margin-top: 15px;">
                        <label>Chọn thành viên muốn thêm</label>
                        <div class="friends-select-list">
                            @if(isset($friends) && $friends->count() > 0)
                            @foreach($friends as $friend)
                            <label class="friend-select-item">
                                <div class="avatar-online-wrap">
                                    <img src="{{ $friend->avatar_url ?? 'https://i.pravatar.cc/40' }}" class="friend-avatar">

                                    @if($friend->canShowActivityTo(auth()->user()))
                                        <span class="online-dot"></span>
                                    @endif
                                </div>
                                <span class="friend-name">{{ $friend->fullname ?? $friend->username }}</span>
                                <input type="checkbox" name="user_ids[]" value="{{ $friend->id }}" class="friend-checkbox">
                            </label>
                            @endforeach
                            @else
                            <p style="padding: 15px; text-align: center; color: #999; font-size: 14px;">
                                Không có người dùng nào khả dụng.
                            </p>
                            @endif

                            <p id="search-empty-message" style="display: none; padding: 15px; text-align: center; color: #ff4d4f; font-size: 14px; margin: 0;">
                                Không tìm thấy thành viên nào phù hợp!
                            </p>
                        </div>
                    </div>
                </div>

                <div id="group-step-2" class="custom-modal-body" style="display: none;">
                    <div class="form-group-chat" style="text-align: center;">
                        <label>Ảnh đại diện nhóm</label>
                        <div style="position: relative; display: inline-block; margin: 10px auto;">
                            <img id="group-avatar-preview" src="https://ui-avatars.com/api/?name=Group&background=0084ff&color=fff&size=100"
                                style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #0084ff;">
                            <label for="group_avatar" style="position: absolute; bottom: 0; right: 0; background: #0084ff; color: #fff; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; margin: 0; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                                <i class="fas fa-camera" style="font-size: 14px;"></i>
                            </label>
                            <input type="file" id="group_avatar" name="group_avatar" accept="image/*" style="display: none;">
                        </div>
                    </div>

                    <div class="form-group-chat">
                        <label for="group_name">Tên nhóm chat</label>
                        <input type="text" id="group_name" name="group_name" placeholder="Nhập tên nhóm của cậu...">
                    </div>
                </div>

                <div class="custom-modal-footer">
                    <button type="button" id="btnCancelModal" class="btn-chat-secondary">Hủy</button>
                    <button type="button" id="btnNextStep" class="btn-chat-primary">Tiếp theo</button>

                    <button type="button" id="btnBackStep" class="btn-chat-secondary" style="display: none;">Quay lại</button>
                    <button type="submit" id="btnSubmitGroup" class="btn-chat-primary" style="display: none;">Tạo nhóm</button>
                </div>
            </form>
        </div>
    </div>
    <div id="toast-container" style="position: fixed; bottom: 25px; right: 25px; z-index: 100000; display: flex; flex-direction: column-reverse; gap: 10px;"></div>

    <style>
        /* CSS cho từng hộp thông báo */
        .custom-toast {
            background: #333;
            color: #fff;
            padding: 12px 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 250px;

            /* Hiệu ứng trượt từ phải qua trái */
            transform: translateX(120%);
            transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55), opacity 0.3s;
            opacity: 0;
        }

        /* Trạng thái hiển thị */
        .custom-toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        /* Các màu sắc định dạng theo loại thông báo */
        .custom-toast.toast-error {
            background: #ff4d4f;
            /* Màu đỏ cho lỗi vặt */
        }

        .custom-toast.toast-success {
            background: #52c41a;
            /* Màu xanh lá cho thành công */
        }

        .custom-toast.toast-warning {
            background: #faad14;
            /* Màu vàng cảnh báo */
        }
    </style>
    <script src="/js/chat.js?v={{ time() }}"></script>
    <script src="/js/list_chat.js?v={{ time() }}"></script>

    @endsection
