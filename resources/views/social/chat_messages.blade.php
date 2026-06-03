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

        <div class="chat-header" style="display: flex; align-items: center; justify-content: space-between; padding: 10px; border-bottom: 1px solid #eee;">

            <div class="header-info" style="display: flex; align-items: center; gap: 10px;">
                @if($conversation->type === 'group')
                {{-- ... Avatar nhóm ... --}}
                @if(!empty($conversation->image_url))
                <img src="{{ asset('storage/' . $conversation->image_url) }}" style="width:45px; height:45px; border-radius:50%; object-fit: cover;">
                @else
                <img src="https://ui-avatars.com/api/?name={{ urlencode($conversation->name ?? 'Nhóm') }}&background=0084ff&color=fff&size=100&bold=true" style="width:45px; height:45px; border-radius:50%;">
                @endif
                <div>
                    <h4 style="margin:0;">{{ $conversation->name ?? 'Nhóm chat' }}</h4>
                    <small style="color:gray;">{{ $conversation->participants->count() }} thành viên</small>
                </div>
                @elseif($activePartner)
                {{-- ... Avatar người dùng ... --}}
                <div class="avatar-online-wrap" style="position: relative;">
                    <img src="{{ $activePartner->avatar_url ? asset($activePartner->avatar_url) : asset('images/default-avatar.png') }}" style="width:45px;height:45px;border-radius:50%;">
                    @if($activePartner->canShowActivityTo(auth()->user()))
                    <span class="online-dot"></span>
                    @endif
                </div>
                <div>
                    <h4 style="margin:0;">{{ $activePartner->fullname }}</h4>
                    <small style="color:gray;">@ {{ $activePartner->username }}</small>
                </div>
                @else
                {{-- ... Tin nhắn đã lưu ... --}}
                <img src="https://i.pravatar.cc/45" style="width:45px;height:45px;border-radius:50%;">
                <div>
                    <h4 style="margin:0;">Tin nhắn đã lưu</h4>
                    <small style="color:gray;">Ghi chú cá nhân</small>
                </div>
                @endif
            </div>

            <div class="header-menu-container" style="position: relative;">
                <button type="button" class="btn-menu-dots" style="background:none; border:none; font-size: 20px; cursor:pointer; padding: 0 10px;">⋯</button>
                <div class="dropdown-content" style="display: none; position: absolute; right: 0; top: 100%; background: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 1000; min-width: 150px;">
                    @if($conversation->type === 'group')
                    <button class="menu-item"
                        onclick="event.stopPropagation(); showMembersModal('{{ route('conversations.members', $conversation->id) }}')">
                        Xem thành viên
                    </button>
                    <button class="menu-item" onclick="event.stopPropagation(); openAddMembersModal('{{ $conversation->id }}')">Thêm thành viên</button>
                    <button class="menu-item" onclick="openConfirmLeaveModal({{ $conversation->id }})" style="color: red;">Rời nhóm</button>
                    @else
                    <button class="menu-item" onclick="viewProfile('{{ $activePartner->username ?? '' }}')">
                        Xem trang cá nhân
                    </button>
                    <button class="menu-item" onclick="blockUser('{{ $activePartner->id ?? '' }}')">Chặn</button>
                    @endif
                </div>
            </div>
        </div>

        <br>
        <div class="chat-messages" id="chat-box"
            data-conversation="{{ $conversation->id ?? '' }}"
            data-user="{{ auth()->id() }}"
            data-last-id="{{ $messages->count() > 0 ? $messages->last()->id : 0 }}"
            data-first-id="{{ $messages->count() > 0 ? $messages->first()->id : 0 }}">

            @forelse($messages as $msg)
            @php
            $isMe = $msg->sender_id == auth()->id();
            $senderName = $msg->user->fullname ?? ($msg->user->username ?? 'Thành viên');
            $senderAvatar = $msg->user && $msg->user->avatar_url
            ? asset($msg->user->avatar_url)
            : "https://ui-avatars.com/api/?name=" . urlencode($senderName) . "&background=0084ff&color=fff&size=100";
            @endphp

            {{-- Wrapper gốc trái/phải --}}
            <div class="message-wrapper {{ $isMe ? 'me' : 'them' }}" data-id="{{ $msg->id }}">

                @if($isMe)
                {{-- ========================================================= --}}
                {{-- TRƯỜNG HỢP 1: TIN NHẮN CỦA BẠN (CỦA "ME")                  --}}
                {{-- ========================================================= --}}
                <div class="message-container">
                    @if($msg->is_deleted)
                    {{-- Bong bóng thu hồi phía mình --}}
                    <div class="message-bubble" style="background: #f1f0f0; color: #999; font-style: italic; border: 1px dashed #ccc;">
                        <div class="message-content">Tin nhắn đã được thu hồi</div>
                    </div>
                    @else
                    {{-- Tin nhắn bình thường phía mình --}}
                    @if($msg->image_url)
                    <div class="message-media">
                        <img src="{{ asset('storage/' . $msg->image_url) }}" class="chat-image">
                    </div>
                    @endif

                    @if(isset($msg->content) && trim($msg->content) !== '')
                    <div class="message-bubble">
                        <div class="message-content">{{ $msg->content }}</div>
                    </div>
                    @endif

                    <div class="message-actions">
                        <button type="button" class="dots-btn">⋯</button>
                        <div class="message-menu">
                            <button type="button" class="recall-btn" data-id="{{ $msg->id }}">Thu hồi</button>
                            <button type="button" class="delete-btn" data-id="{{ $msg->id }}">Xoá ở phía bạn</button>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Trạng thái Đã gửi / Đã xem luôn hiển thị --}}
                <div class="message-status-row">
                    <small class="message-status" data-id="{{ $msg->id }}">
                        {{ $msg->is_read ? 'Đã xem' : 'Đã gửi' }}
                    </small>
                </div>

                @else
                {{-- ========================================================= --}}
                {{-- TRƯỜNG HỢP 2: TIN NHẮN ĐỐI PHƯƠNG (CỦA "THEM")            --}}
                {{-- ========================================================= --}}
                <div class="group-chat-layout">

                    {{-- 1. Tên người gửi ở trên cùng (Chỉ hiện ở Group và không quan trọng thu hồi hay chưa) --}}
                    @if($conversation->type === 'group')
                    <span class="group-chat-sender-name">{{ $senderName }}</span>
                    @endif

                    {{-- Hàng ngang chứa Avatar + Bóng chat --}}
                    <div class="group-chat-row">

                        {{-- 2. Avatar nằm bên trái (Luôn luôn giữ lại) --}}
                        <div class="chat-avatar-wrapper">
                            <img src="{{ $senderAvatar }}" class="chat-group-avatar" title="{{ $senderName }}">
                        </div>

                        {{-- 3. Khung bóng chat đối phương --}}
                        <div class="message-container-them">
                            @if($msg->is_deleted)
                            {{-- Bong bóng thu hồi phía đối phương --}}
                            <div class="message-bubble" style="background: #f1f0f0; color: #999; font-style: italic; border: 1px dashed #ccc;">
                                <div class="message-content">Tin nhắn đã được thu hồi</div>
                            </div>
                            @else
                            {{-- Tin nhắn bình thường phía đối phương --}}
                            @if($msg->image_url)
                            <div class="message-media">
                                <img src="{{ asset('storage/' . $msg->image_url) }}" class="chat-image">
                            </div>
                            @endif

                            @if(isset($msg->content) && trim($msg->content) !== '')
                            <div class="message-bubble">
                                <div class="message-content">{{ $msg->content }}</div>
                            </div>
                            @endif

                            <div class="message-actions">
                                <button type="button" class="dots-btn">⋯</button>
                                <div class="message-menu">
                                    <button type="button" class="delete-btn" data-id="{{ $msg->id }}">Xoá ở phía bạn</button>
                                </div>
                            </div>
                            @endif
                        </div> {{-- Hết .message-container-them --}}

                    </div> {{-- Hết .group-chat-row --}}
                </div> {{-- Hết .group-chat-layout --}}
                @endif

            </div>
            @empty
            <div id="empty-message" style="text-align:center;color:#aaa;margin-top:20px;">
                Chưa có tin nhắn nào. Hãy chào nhau đi!
            </div>
            @endforelse
        </div>

        <div id="image-preview-container"></div>

        <div style="position: relative; width: 100%;">
            <div id="emoji-picker-container" style="position: absolute; bottom: 65px; right: 20px; display: none; z-index: 99999;"></div>

            <form class="chat-input" enctype="multipart/form-data" style="position: relative; display: flex; align-items: center; gap: 10px; width: 100%;">
                @csrf
                <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">

                <label for="image-input">
                    <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                        <path fill="rgb(0, 0, 0)" d="M160 96C124.7 96 96 124.7 96 160L96 480C96 515.3 124.7 544 160 544L480 544C515.3 544 544 515.3 544 480L544 160C544 124.7 515.3 96 480 96L160 96zM224 176C250.5 176 272 197.5 272 224C272 250.5 250.5 272 224 272C197.5 272 176 250.5 176 224C176 197.5 197.5 176 224 176zM368 288C376.4 288 384.1 292.4 388.5 299.5L476.5 443.5C481 450.9 481.2 460.2 477 467.8C472.8 475.4 464.7 480 456 480L184 480C175.1 480 166.8 475 162.7 467.1C158.6 459.2 159.2 449.6 164.3 442.3L220.3 362.3C224.8 355.9 232.1 352.1 240 352.1C247.9 352.1 255.2 355.9 259.7 362.3L286.1 400.1L347.5 299.6C351.9 292.5 359.6 288.1 368 288.1z" />
                    </svg>
                </label>

                <div class="emoji-wrapper-box" style="position: relative; display: flex; justify-content: center; align-items: center; width: 40px; height: 40px; flex-shrink: 0;">
                    <button type="button" id="emoji-trigger-btn" style="background: none; border: none; font-size: 20px; cursor: pointer; padding: 0; margin: 0; display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;">😊</button>
                    <div id="emoji-picker-container" style="position: absolute; bottom: 55px; right: 0; left: auto !important; display: none; z-index: 99999;"></div>
                </div>

                <input id="image-input" name="image" type="file" accept="image/*" hidden>
                <input id="chat-input" name="content" type="text" placeholder="Aa" autocomplete="off" style="flex: 1;">
                <button type="submit" style="background:none;border:none;font-size:20px;cursor:pointer; display: flex; align-items: center; margin: 0;">🚀</button>
            </form>
        </div>
    </div>
    <!-- MODAL XEM THÀNH VIÊN NHÓM -->
    <div id="viewMembersModal" class="members-modal-overlay">
        <div class="members-modal-box">
            <div class="members-modal-header">
                <h3>Thành viên nhóm</h3>
                <button type="button" class="members-modal-close" onclick="closeMembersModal()">&times;</button>
            </div>
            <div class="members-modal-body">
                <div id="members-list-container"></div>
            </div>
        </div>
    </div>
    {{-- MODAL XÁC NHẬN RỜI NHÓM --}}
    <div id="confirmLeaveModal" class="add-members-modal-overlay">
        <div class="add-members-modal-box" style="max-width: 400px;">
            <div class="add-members-modal-header">
                <h3>Xác nhận rời nhóm</h3>
                <button class="add-members-modal-close" onclick="closeConfirmLeaveModal()">&times;</button>
            </div>
            <div class="add-members-modal-body" style="padding: 20px; text-align: center; color: #4b4b4b; font-size: 15px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 40px; color: #ff4d4f; margin-bottom: 15px; display: block;"></i>
                Cậu có chắc chắn muốn rời khỏi nhóm chat này không? Bạn bè sẽ không thấy cậu trong cuộc trò chuyện này nữa đâu á.
            </div>
            <div class="add-members-modal-footer" style="background: #fff;">
                <button class="btn-add-member-secondary" onclick="closeConfirmLeaveModal()">Hủy bỏ</button>
                <button id="btn-confirm-leave-submit" class="btn-add-member-primary" style="background-color: #ff4d4f;">Rời nhóm</button>
            </div>
        </div>
    </div>
    {{-- MODAL THÊM THÀNH VIÊN VÀO NHÓM --}}
    <div id="addMembersModal" class="add-members-modal-overlay">
        <div class="add-members-modal-box">
            <div class="add-members-modal-header">
                <h3>Thêm thành viên vào nhóm</h3>
                <button type="button" class="add-members-modal-close" onclick="closeAddMembersModal()">&times;</button>
            </div>

            <div style="padding: 12px 20px; border-bottom: 1px solid #eee;">
                <input type="text" id="search-friend-input" placeholder="Tìm nhanh bạn bè..."
                    style="width: 100%; padding: 8px 15px; border: 1px solid #ddd; border-radius: 20px; outline: none; font-size: 14px;">
            </div>

            <div class="add-members-modal-body" id="friends-list-container">
            </div>

            <div class="add-members-modal-footer">
                <button type="button" class="btn-add-member-secondary" onclick="closeAddMembersModal()">Hủy</button>
                <button type="button" class="btn-add-member-primary" onclick="submitAddMembers()">Xác nhận</button>
            </div>
        </div>
    </div>
    {{-- MODAL TẠO NHÓM --}}
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
                            <p style="padding: 15px; text-align: center; color: #999; font-size: 14px;">Không có người dùng nào khả dụng.</p>
                            @endif
                            <p id="search-empty-message" style="display: none; padding: 15px; text-align: center; color: #ff4d4f; font-size: 14px; margin: 0;">Không tìm thấy thành viên nào phù hợp!</p>
                        </div>
                    </div>
                </div>

                <div id="group-step-2" class="custom-modal-body" style="display: none;">
                    <div class="form-group-chat" style="text-align: center;">
                        <label>Ảnh đại diện nhóm</label>
                        <div style="position: relative; display: inline-block; margin: 10px auto;">
                            <img id="group-avatar-preview" src="https://ui-avatars.com/api/?name=Group&background=0084ff&color=fff&size=100" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #0084ff;">
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
            transform: translateX(120%);
            transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55), opacity 0.3s;
            opacity: 0;
        }

        .custom-toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .custom-toast.toast-error {
            background: #ff4d4f;
        }

        .custom-toast.toast-success {
            background: #52c41a;
        }

        .custom-toast.toast-warning {
            background: #faad14;
        }
    </style>

    <script src="/js/chat.js?v={{ time() }}"></script>
    <script src="/js/list_chat.js?v={{ time() }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>
    @endsection