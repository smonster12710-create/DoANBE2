@foreach($conversations as $chat)
@php
// XỬ LÝ CHUẨN: Lọc đối phương trên Collection dữ liệu User đã được nạp sẵn
$partner = null;
if ($chat->type === 'private') {
$myId = (int) Auth::id();
$partner = $chat->participants->filter(function ($user) use ($myId) {
return (int) $user->id !== $myId;
})->first();
}
@endphp
<a href="{{ route('chat_messages', $chat->id) }}"
    data-conversation-id="{{ $chat->id }}"
    {{-- CHỈ CẦN THÊM DÒNG NÀY VÀO THẺ A HIỆN TẠI CỦA CẬU --}}
    data-chat-name="{{ strtolower($chat->type === 'group' ? ($chat->name ?? 'Nhóm chat') : ($partner?->fullname ?? 'Tin nhắn đã lưu')) }}"
    class="message-item-link {{ (isset($conversation) && $conversation->id == $chat->id) || (request()->route('id') == $chat->id) ? 'active-chat' : '' }}">
    <div class="message-item">

        <div class="avatar-wrapper">
            @if($chat->type === 'group')
            @if(!empty($chat->image_url))
            <img src="{{ asset('storage/' . $chat->image_url) }}" class="chat-avatar">
            @else
            <img src="https://ui-avatars.com/api/?name={{ urlencode($chat->name) }}&background=0084ff&color=fff&size=100&bold=true" class="chat-avatar">
            @endif
            @else
            <div class="avatar-online-wrap">
                <img src="{{ $partner && $partner->avatar_url ? asset($partner->avatar_url) : 'https://i.pravatar.cc/40' }}"
                    class="chat-avatar"
                    style="width:45px; height:45px; border-radius:50%; object-fit: cover;">

                @include('partials.activity_dot', ['user' => $partner])
            </div>
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
            // Tạo tiền tố "Bạn: " nếu mình là người gửi tin nhắn cuối
            $prefix = ($last && $last->sender_id == Auth::id()) ? 'Bạn: ' : '';
            @endphp
            @php
            $last = $chat->last_visible_message;
            @endphp

            <p class="last-message">
                @if($last)
                @if($last->is_deleted)
                <i class="text-muted">Tin nhắn đã được thu hồi</i>
                @elseif(!empty($last->image_url))
                <span class="text-muted">{{ $prefix }}📷 Đã gửi một ảnh</span>
                @elseif(!empty($last->content))
                {{ $prefix }}{{ Str::limit($last->content, 30) }}
                @else
                <span class="text-muted">Tin nhắn trống</span>
                @endif
                @else
                <span class="text-primary">Bắt đầu trò chuyện ngay</span>
                @endif
            </p>
        </div>

    </div>
</a>
@endforeach