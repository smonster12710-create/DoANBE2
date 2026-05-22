@foreach($conversations as $chat)
@php
// Xác định partner để hiển thị avatar/tên (giống logic show của cậu)
$partner = null;
if ($chat->type === 'private') {
$partner = $chat->participants->where('user_id', '!=', Auth::id())->first()?->user;
}

@endphp

<a href="{{ route('chat_messages', $chat->id) }}"
    class="message-item-link {{ (isset($conversation) && $conversation->id == $chat->id) || (request()->route('id') == $chat->id) ? 'active-chat' : '' }}">
    <div class="message-item">

        <div class="avatar-wrapper">
            @if($chat->type === 'group')
            <img src="{{ $chat->image_url ?? 'https://i.pravatar.cc/40' }}" class="chat-avatar">
            @else
            <img src="{{ $partner?->avatar_url ?? 'https://i.pravatar.cc/40' }}" class="chat-avatar">
            @endif
        </div>

        <div class="message-info">

            <div class="message-top">
                <h4 class="user-name">
                    @if($chat->type === 'group')
                    {{ $chat->name ?? 'Nhóm chat' }}
                    @else
                    {{ $partner?->fullname ?? 'Tin nhắn đã lưu' }}
                    @endif
                </h4>

                @if($chat->unread_count > 0)
                <span class="unread-badge">
                    {{ $chat->unread_count }}
                </span>
                @endif
            </div>

            @php
            $last = $chat->last_visible_message;
            @endphp

            <p class="last-message">
                @if($last)
                @if($last->is_deleted)
                <i class="text-muted">Tin nhắn đã được thu hồi</i>
                @else
                {{ Str::limit($last->content ?? '', 30) }}
                @endif
                @else
                <span class="text-primary">Bắt đầu trò chuyện ngay</span>
                @endif
            </p>
        </div>

    </div>
</a>
@endforeach