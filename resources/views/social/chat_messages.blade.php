@if(session('error'))
<div class="alert alert-danger mx-3 mt-3">
    {{ session('error') }}
</div>
@endif
@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/chat_messages.css') }}">

<div class="main-container">

    {{-- SIDEBAR --}}
    <div class="messages-sidebar">
        <div class="search-box">
            <input type="text" placeholder="Tìm kiếm ....">
        </div>

        <div class="scrollable-list">
            @foreach($conversations as $chat)

            <a href="{{ route('chat_messages', $chat->id) }}"
                class="message-item-link {{ isset($conversation) && $conversation->id == $chat->id ? 'active-chat' : '' }}">
                <div class="message-item">

                    <div class="avatar-wrapper">
                        @if($chat->type === 'group')
                        <img src="{{ $chat->image_url ?? 'https://i.pravatar.cc/40' }}" class="chat-avatar">
                        @else
                        <img src="{{ $chat->partner?->avatar_url ?? 'https://i.pravatar.cc/40' }}" class="chat-avatar">
                        @endif
                    </div>

                    <div class="message-info">
                        <h4 class="user-name">
                            @if($chat->type === 'group')
                            {{ $chat->name ?? 'Nhóm chat' }}
                            @else
                            {{ $chat->partner?->fullname ?? 'Tin nhắn đã lưu' }}
                            @endif
                        </h4>

                        <p class="last-message">
                            @if($chat->lastMessage)
                            @if($chat->lastMessage->is_deleted)
                            <i class="text-muted">Tin nhắn đã được thu hồi</i>
                            @else
                            {{ Str::limit($chat->lastMessage->content, 30) }}
                            {{-- Dùng Str::limit để tránh tin nhắn quá dài làm vỡ giao diện --}}
                            @endif
                            @else
                            <span class="text-primary">Bắt đầu trò chuyện ngay</span>
                            @endif
                        </p>
                    </div>

                </div>
            </a>

            @endforeach
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

            @if(!empty(trim($msg->content)))

            <div class="message-wrapper {{ $msg->sender_id == auth()->id() ? 'me' : 'them' }}"
                data-id="{{ $msg->id }}">

                @if($msg->is_deleted)

                <div class="message-recalled">
                    Tin nhắn đã được thu hồi
                </div>

                @else

                <div class="message-bubble">

                    <div class="message-content">
                        {{ $msg->content }}
                    </div>

                    @if($msg->sender_id == auth()->id())

                    <div class="message-actions">

                        <button type="button" class="dots-btn">
                            ⋯
                        </button>

                        <div class="message-menu">

                            <button
                                type="button"
                                class="recall-btn"
                                data-id="{{ $msg->id }}">
                                Thu hồi
                            </button>

                            <button
                                type="button"
                                class="delete-btn"
                                data-id="{{ $msg->id }}">
                                Xoá ở phía bạn
                            </button>

                        </div>

                    </div>

                    @endif

                </div>

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
        <form class="chat-input">
            @csrf
            <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">

            <input name="content" type="text" placeholder="Aa" required>
            <button type="submit"
                style="background:none;border:none;font-size:20px;cursor:pointer;">
                🚀
            </button>
        </form>
    </div>
    <script src="/js/chat.js"></script>
    @endsection