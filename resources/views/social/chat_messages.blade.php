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
        <div class="search-box">
            <input type="text" id="sidebar-search" placeholder="Tìm kiếm ....">
        </div>

        <div class="scrollable-list">
            {{-- Gọi file partial lúc load trang lần đầu --}}
            @include('partials.list_chat', ['conversations' => $conversations])
        </div>
    </div>
    {{-- KHUNG CHAT --}}
    <div class="chat-main-area">

        <div class="chat-header">
            <div class="header-info">

                @if($conversation->type === 'group')
                <img src="{{ $conversation->image_url ?? 'https://i.pravatar.cc/45' }}"
                    style="width:45px;height:45px;border-radius:50%;">

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

        <form class="chat-input" enctype="multipart/form-data">
            @csrf

            <input
                type="hidden"
                name="conversation_id"
                value="{{ $conversation->id }}">

            <!-- nút thêm ảnh -->
            <label
                for="image-input"
                style="background:none;border:none;font-size:20px;cursor:pointer; margin-top:5px">
                <svg width="30" height="30" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                    <path fill="rgb(0, 0, 0)" d="M160 96C124.7 96 96 124.7 96 160L96 480C96 515.3 124.7 544 160 544L480 544C515.3 544 544 515.3 544 480L544 160C544 124.7 515.3 96 480 96L160 96zM224 176C250.5 176 272 197.5 272 224C272 250.5 250.5 272 224 272C197.5 272 176 250.5 176 224C176 197.5 197.5 176 224 176zM368 288C376.4 288 384.1 292.4 388.5 299.5L476.5 443.5C481 450.9 481.2 460.2 477 467.8C472.8 475.4 464.7 480 456 480L184 480C175.1 480 166.8 475 162.7 467.1C158.6 459.2 159.2 449.6 164.3 442.3L220.3 362.3C224.8 355.9 232.1 352.1 240 352.1C247.9 352.1 255.2 355.9 259.7 362.3L286.1 400.1L347.5 299.6C351.9 292.5 359.6 288.1 368 288.1z" />
                </svg>
            </label>

            <input
                id="image-input"
                name="image"
                type="file"
                accept="image/*"
                hidden>

            <input
                name="content"
                type="text"
                placeholder="Aa">

            <button
                type="submit"
                style="background:none;border:none;font-size:20px;cursor:pointer;">
                🚀
            </button>
        </form>
    </div>
    <script src="/js/chat.js?v={{ time() }}"></script>
    <script src="/js/list_chat.js?v={{ time() }}"></script>
    @endsection