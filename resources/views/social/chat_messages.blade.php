@extends('dashboard')
@if(session('error'))
<div class="alert alert-danger mx-3 mt-3">
    {{ session('error') }}
</div>
@endif
@section('content')
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

<link rel="stylesheet" href="{{ asset('css/chat_messages.css') }}">

<div class="main-container">

    {{-- SIDEBAR --}}
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
    {{-- KHUNG CHAT --}}
    <div class="chat-main-area">

        <div class="chat-header">
            <div class="header-info">

                @if($conversation->type === 'group')
                @if(!empty($conversation->image_url))
                <img src="{{ asset('storage/' . $conversation->image_url) }}"
                    style="width:45px; height:45px; border-radius:50%; object-fit: cover;">
                @else
                <img src="https://ui-avatars.com/api/?name={{ urlencode($conversation->name ?? 'Nhóm') }}&background=0084ff&color=fff&size=100&bold=true"
                    style="width:45px; height:45px; border-radius:50%;">
                @endif

                <div>
                    <h4 style="margin:0;">{{ $conversation->name ?? 'Nhóm chat' }}</h4>
                    <small style="color:gray;">
                        {{ $conversation->participants->count() }} thành viên
                    </small>
                </div>

                @elseif($activePartner)

                <img src="{{ $activePartner->avatar_url ?? 'https://i.pravatar.cc/45' }}"
                    style="width:45px;height:45px;border-radius:50%;">

                <div>
                    <h4 style="margin:0;">{{ $activePartner->fullname }}</h4>
                    <small style="color:gray;">@ {{ $activePartner->username }}</small>
                </div>

                @else

                <img src="https://i.pravatar.cc/45"
                    style="width:45px;height:45px;border-radius:50%;">

                <div>
                    <h4 style="margin:0;">Tin nhắn đã lưu</h4>
                    <small style="color:gray;">Ghi chú cá nhân</small>
                </div>

                @endif

            </div>
        </div>

        <br>
        <div class="chat-messages" id="chat-box"
            data-conversation="{{ $conversation->id ?? '' }}"
            data-user="{{ auth()->id() }}"
            {{-- Lấy ID của tin nhắn cuối cùng trong danh sách --}}
            data-last-id="{{ $messages->count() > 0 ? $messages->last()->id : 0 }}"
            {{-- Lấy ID của tin nhắn đầu tiên (để sau này load older messages) --}}
            data-first-id="{{ $messages->count() > 0 ? $messages->first()->id : 0 }}">


            @forelse($messages as $msg)

            @if(
            !empty(trim($msg->content ?? ''))
            || !empty($msg->image_url)
            )

            <div class="message-wrapper {{ $msg->sender_id == auth()->id() ? 'me' : 'them' }}"
                data-id="{{ $msg->id }}">

                @if($msg->is_deleted)

                <div class="message-recalled">
                    Tin nhắn đã được thu hồi
                </div>

                @else
                <div class="message-container">
                    {{-- IMAGE BLOCK --}}
                    @if($msg->image_url)
                    <div class="message-media">
                        <img
                            src="{{ asset('storage/' . $msg->image_url) }}"
                            class="chat-image">

                        {{-- ACTION FOR IMAGE --}}

                    </div>
                    @endif

                    {{-- BUBBLE --}}
                    @if(
                    (isset($msg->content) && trim($msg->content) !== '')
                    )
                    <div class="message-bubble">

                        {{-- TEXT --}}
                        @if(isset($msg->content) && trim($msg->content) !== '')
                        <div class="message-content">
                            {{ $msg->content }}
                        </div>
                        @endif

                    </div>

                    @endif
                    @if($msg->image_url || !empty(trim($msg->content)))

                    <div class="message-actions">

                        <button type="button" class="dots-btn">⋯</button>

                        <div class="message-menu">

                            @if($msg->sender_id == auth()->id())
                            <button type="button" class="recall-btn" data-id="{{ $msg->id }}">
                                Thu hồi
                            </button>
                            @endif

                            <button type="button" class="delete-btn" data-id="{{ $msg->id }}">
                                Xoá ở phía bạn
                            </button>

                        </div>

                    </div>

                    @endif
                </div>
                {{-- STATUS --}}
                @if($msg->sender_id == auth()->id())
                <div class="message-status-row">
                    <small class="message-status" data-id="{{ $msg->id }}">
                        {{ $msg->is_read ? 'Đã xem' : 'Đã gửi' }}
                    </small>
                </div>
                @endif

                @endif

            </div>
            @endif



            @empty

            <div id="empty-message"
                style="text-align:center;color:#aaa;margin-top:20px;">
                Chưa có tin nhắn nào. Hãy chào nhau đi!
            </div>

            @endforelse
        </div>
        <div id="image-preview-container"></div>

        <div style="position: relative; width: 100%;">
            <div id="emoji-picker-container" style="position: absolute; bottom: 65px; right: 20px; display: none; z-index: 99999;"></div>

            <form class="chat-input" enctype="multipart/form-data" style="position: relative; display: flex; align-items: center; gap: 10px; width: 100%;">
                @csrf

                <input
                    type="hidden"
                    name="conversation_id"
                    value="{{ $conversation->id }}">

                <label for="image-input">
                    <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                        <path fill="rgb(0, 0, 0)" d="M160 96C124.7 96 96 124.7 96 160L96 480C96 515.3 124.7 544 160 544L480 544C515.3 544 544 515.3 544 480L544 160C544 124.7 515.3 96 480 96L160 96zM224 176C250.5 176 272 197.5 272 224C272 250.5 250.5 272 224 272C197.5 272 176 250.5 176 224C176 197.5 197.5 176 224 176zM368 288C376.4 288 384.1 292.4 388.5 299.5L476.5 443.5C481 450.9 481.2 460.2 477 467.8C472.8 475.4 464.7 480 456 480L184 480C175.1 480 166.8 475 162.7 467.1C158.6 459.2 159.2 449.6 164.3 442.3L220.3 362.3C224.8 355.9 232.1 352.1 240 352.1C247.9 352.1 255.2 355.9 259.7 362.3L286.1 400.1L347.5 299.6C351.9 292.5 359.6 288.1 368 288.1z" />
                    </svg>
                </label>

                <div class="emoji-wrapper-box" style="position: relative; display: flex; justify-content: center; align-items: center; width: 40px; height: 40px; flex-shrink: 0;">
                    <button type="button" id="emoji-trigger-btn" style="background: none; border: none; font-size: 20px; cursor: pointer; padding: 0; margin: 0; display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;">
                        😊
                    </button>

                    <div id="emoji-picker-container" style="position: absolute; bottom: 55px; right: 0; left: auto !important; display: none; z-index: 99999;"></div>
                </div>

                <input
                    id="image-input"
                    name="image"
                    type="file"
                    accept="image/*"
                    hidden>

                <input
                    id="chat-input"
                    name="content"
                    type="text"
                    placeholder="Aa"
                    autocomplete="off"
                    style="flex: 1;">

                <button
                    type="submit"
                    style="background:none;border:none;font-size:20px;cursor:pointer; display: flex; align-items: center; margin: 0;">
                    🚀
                </button>
            </form>
        </div>
    </div>

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
                                <img src="{{ $friend->avatar_url ?? 'https://i.pravatar.cc/40' }}" class="friend-avatar">
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

    <!-- Thêm thư viện emoji-mart từ CDN -->
    <script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>
    @endsection