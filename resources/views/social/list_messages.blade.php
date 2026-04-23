@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/list_messages.css') }}">

<div class="main-container">
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
    <div class="grid">
        @for ($i = 0; $i < 15; $i++)
            <div class="card">
            <div class="card-header">
                <img class="avatar" src="https://i.pravatar.cc/40?img={{ $i }}">
                <div class="info">
                    <span class="name">Xi Trum Dinh</span>
                    <span class="time">Vừa xong</span>
                </div>
                <div class="more">⋯</div>
            </div>
            <div class="card-text">
                Con mèo kêu sao??<br>Mèo mèo mèo mèo meo
            </div>
            <img class="card-img" src="https://picsum.photos/300/{{ rand(250,600) }}">
    </div>
    @endfor
</div>
</div>
@endsection