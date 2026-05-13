@extends('dashboard');
@section('content');
{{-- Khai báo xíu CSS cho hiệu ứng di chuột (Hover) --}}
<style>
    .noti-hover:hover {
        background-color: #f0f2f5;
    }

    .transition-fast {
        transition: background-color 0.2s ease-in-out;
    }
</style>

{{-- Container chính dùng Card của Bootstrap --}}
<div class="card border-0 shadow-sm rounded-3 p-2 mx-auto" style="max-width: 600px;">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-2 px-2 pt-2">
        <h4 class="m-0 fw-bold">Thông báo</h4>
        <button class="btn btn-sm text-primary fw-medium" onclick="markAllAsRead()">Đánh dấu tất cả đã đọc</button>
    </div>

    {{-- Danh sách thông báo --}}
    @forelse($notifications as $noti)
        @php
            // Setup màu sắc Bootstrap tùy theo loại thông báo
            $badgeColor = 'bg-primary'; // Mặc định là Like (Xanh dương)
            $icon = '👍';
            $actionText = 'đã thích bài viết của bạn.';

            if ($noti->type == 'comment') {
                $badgeColor = 'bg-success'; // Màu xanh lá
                $icon = '💬';
                $actionText = 'đã bình luận về bài viết của bạn.';
            } elseif ($noti->type == 'mention') {
                $badgeColor = 'bg-danger';  // Màu đỏ
                $icon = '📌';
                $actionText = 'đã nhắc đến bạn trong một bình luận.';
            }

            // Xử lý class cho trạng thái Chưa đọc / Đã đọc
            $bgClass = $noti->is_read == 0 ? 'bg-primary bg-opacity-10' : 'noti-hover';
            $timeClass = $noti->is_read == 0 ? 'text-primary fw-bold' : 'text-muted fw-medium';
        @endphp

        {{-- Thẻ a bọc từng thông báo (Dùng Flexbox của Bootstrap) --}}
        <a onclick="removeUnreadUI(this)" href="{{ route('notifications.read', $noti->id) }}"
            class="d-flex align-items-center p-2 rounded-3 text-decoration-none text-dark mb-1 transition-fast {{ $bgClass }}">

            {{-- Cục Avatar + Badge Icon --}}
            <div class="position-relative me-3 flex-shrink-0">
                {{-- Avatar --}}
                <img src="{{ asset($noti->actor->avatar_url ?? 'images/default-avatar.png') }}" class="rounded-circle"
                    style="width: 56px; height: 56px; object-fit: cover;" alt="avatar">

                {{-- Badge Icon (Dùng absolute đè lên góc phải dưới) --}}
                <div class="position-absolute bottom-0 end-0 rounded-circle d-flex justify-content-center align-items-center border border-2 border-white text-white {{ $badgeColor }}"
                    style="width: 28px; height: 28px; transform: translate(15%, 15%);">
                    <span style="font-size: 12px;">{{ $icon }}</span>
                </div>
            </div>

            {{-- Cục Nội dung chữ --}}
            <div class="flex-grow-1 pe-2" style="font-size: 15px; line-height: 1.4;">
                <p class="m-0 text-dark"><strong>{{ $noti->actor->fullname }}</strong> {{ $actionText }}</p>
                <div class="small {{ $timeClass }}">{{ $noti->created_at->diffForHumans() }}</div>
            </div>

            {{-- Dấu chấm xanh báo chưa đọc (Chỉ hiện khi is_read == 0) --}}
            @if($noti->is_read == 0)
                <div class="bg-primary rounded-circle flex-shrink-0" style="width: 12px; height: 12px;"></div>
            @endif
        </a>

    @empty
        {{-- Trạng thái trống --}}
        <div class="text-center py-4 text-muted">
            <img src="https://cdn-icons-png.flaticon.com/512/1178/1178479.png" width="60" class="mb-3 opacity-50">
            <p class="fw-medium">Bạn không có thông báo mới nào.</p>
        </div>
    @endforelse
</div>