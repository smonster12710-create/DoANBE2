@extends('dashboard')

@section('content')
{{-- Đảm bảo bạn đã tách CSS ra public/css/chat_messages.css như bước trước --}}
<link rel="stylesheet" href="{{ asset('css/chat_messages.css') }}">

<div class="main-container">
    {{-- CỘT TRÁI: DANH SÁCH CHAT --}}
    <div class="messages-sidebar">
        <div class="search-box">
            <input type="text" placeholder="Tìm kiếm ....">
        </div>

        <div class="scrollable-list">
            @foreach($conversations as $chat)
            @php $partner = $chat->partner; @endphp

            @if($partner)

            <a href="{{ route('chat_messages', $chat->id) }}" class="message-item-link">
                <div class="message-item">
                    <div class="avatar-wrapper">
                        {{-- Sửa image_url thành avatar_url --}}
                        <img src="{{ $partner->avatar_url ?? 'https://i.pravatar.cc/40' }}" class="chat-avatar">
                    </div>

                    <div class="message-info">
                        <h4 class="user-name">
                            {{-- Sửa name thành fullname --}}
                            {{ $partner->fullname ?? 'Người dùng không tên' }}
                        </h4>

                        <p class="last-message">
                            {{ $chat->lastMessage->content ?? 'Bắt đầu trò chuyện ngay' }}
                        </p>
                    </div>
                </div>
            </a>
            @endif
            @endforeach
        </div>
    </div>

    {{-- CỘT PHẢI: CHI TIẾT TIN NHẮN --}}
    <div class="chat-main-area">
        <div class="chat-header">
            <div class="header-info">
                <img src="{{ $activePartner->avatar_url }}" style="width: 45px; height: 45px; border-radius: 50%;">
                <div>
                    <h4 style="margin: 0;">{{ $activePartner->fullname }}</h4>
                    <small style="color: gray;">@ {{ $activePartner->username }}</small>
                </div>
            </div>
        </div>
        <hr>
        <div class="chat-messages">
            @if($messages && $messages->count() > 0)
            @foreach($messages as $msg)
            {{-- Kiểm tra sender_id với người đang đăng nhập --}}
            <div class="message-wrapper {{ $msg->sender_id == auth()->id() ? 'me' : 'them' }}">
                <div class="message-bubble">
                    {{ $msg->content }}
                </div>
            </div>
            @endforeach
            @else
            <div style="text-align: center; color: #aaa; margin-top: 20px;">
                Chưa có tin nhắn nào. Hãy chào nhau đi!
            </div>
            @endif
        </div>

        {{-- FORM GỬI TIN NHẮN --}}
        <form class="chat-input" action="" method="POST" style="margin-top: 20px; display: flex; gap: 10px;">
            @csrf
            <input name="content" type="text" placeholder="Aa" required style="flex: 1; padding: 12px 15px; border-radius: 25px; border: 1px solid #ddd; outline: none;">
            <button type="submit" style="background: none; border: none; font-size: 20px; cursor: pointer;">🚀</button>
        </form>
    </div>
</div>
@endsection