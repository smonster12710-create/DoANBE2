@extends('dashboard')
@section('content')

    {{-- Khai báo xíu CSS cho hiệu ứng di chuột (Hover) --}}
    <style>
        .noti-hover:hover {
            background-color: #f0f2f5;
        }

        .transition-fast {
            transition: background-color 0.2s ease-in-out;
        }

        /* Chỉnh nhẹ nút filter cho nó mượt */
        .btn-filter {
            transition: all 0.2s;
        }
    </style>

    {{-- Container chính dùng Card của Bootstrap --}}
    <div class="card border-0 shadow-sm rounded-3 p-2 mx-auto" style="max-width: 600px;">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-2 px-2 pt-2">
            <h4 class="m-0 fw-bold">Thông báo</h4>
            <button type="button" class="btn btn-sm text-primary fw-medium" onclick="markAllAsRead()">Đánh dấu tất cả đã
                đọc</button>
        </div>

    {{-- THANH LỌC --}}
    <div class="d-flex gap-2 px-2 mb-3">
        {{-- Nút Tất cả --}}
        <a href="{{ route('notifications.index', ['filter' => 'all']) }}"
            class="btn rounded-pill px-3 py-1 {{ $filter == 'all' ? 'btn-primary active' : 'btn-light text-muted fw-medium border' }}">
            Tất cả
        </a>

        {{-- Nút Chưa đọc --}}
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
            class="btn rounded-pill px-3 py-1 {{ $filter == 'unread' ? 'btn-primary active' : 'btn-light text-muted fw-medium border' }}">
            Chưa đọc
        </a>

        {{-- Nút Đã đọc --}}
        <a href="{{ route('notifications.index', ['filter' => 'read']) }}"
            class="btn rounded-pill px-3 py-1 {{ $filter == 'read' ? 'btn-primary active' : 'btn-light text-muted fw-medium border' }}">
            Đã đọc
        </a>
    </div>

        {{-- Danh sách thông báo --}}
        <div id="noti-list-container">
            @forelse($notifications as $noti)
                @php
                    // Setup màu sắc Bootstrap tùy theo loại thông báo
                    $badgeColor = 'bg-primary';
                    $icon = '<i class="fas fa-heart"></i>';
                    $actionText = 'đã thích bài viết của bạn.';

                    if ($noti->type == 'comment') {
                        $badgeColor = 'bg-success';
                        $icon = '<i class="fas fa-comment-dots"></i>';
                        $actionText = 'đã bình luận về bài viết của bạn.';
                    } elseif ($noti->type == 'mention') {
                        $badgeColor = 'bg-danger';
                        $icon = '<i class="fas fa-at"></i>';
                        $actionText = 'đã nhắc đến bạn trong một bình luận.';
                    } elseif ($noti->type == 'follow') {
                        $badgeColor = 'bg-info text-white';
                        $icon = '<i class="fas fa-user-plus"></i>';
                        $actionText = 'đã bắt đầu theo dõi bạn.';
                    } elseif ($noti->type == 'friend_request') {
                        $badgeColor = 'bg-warning text-dark';
                        $icon = '<i class="fas fa-user-friends"></i>';
                        $actionText = 'đã gửi cho bạn một lời mời kết bạn.';
                    }

                    // Xử lý class cho trạng thái Chưa đọc / Đã đọc
                    $bgClass = $noti->is_read == 0 ? 'bg-primary bg-opacity-10' : 'noti-hover';
                    $timeClass = $noti->is_read == 0 ? 'text-primary fw-bold' : 'text-muted fw-medium';

                    // Gắn nhãn để thằng JS dễ nhận diện đem đi lọc
                    $statusLabel = $noti->is_read == 0 ? 'unread' : 'read';
                @endphp

                {{-- Bổ sung data-status vô đây để Filter nghen Pro --}}
                <div class="noti-item d-flex align-items-center p-2 rounded-3 mb-1 transition-fast {{ $bgClass }}"
                    data-noti-id="{{ $noti->id }}" data-status="{{ $statusLabel }}">

                    <a onclick="removeUnreadUI(this)" data-href="{{ route('notifications.read', $noti->id) }}"
                        href="javascript:void(0);" class="d-flex align-items-center flex-grow-1 text-decoration-none text-dark"
                        style="min-width: 0;">

                        {{-- Cục Avatar + Badge Icon --}}
                        <div class="position-relative me-3 flex-shrink-0">
                            <img src="{{ asset($noti->actor->avatar_url ?? 'images/default-avatar.png') }}" class="rounded-circle"
                                style="width: 56px; height: 56px; object-fit: cover;" alt="avatar">

                            <div class="position-absolute bottom-0 end-0 rounded-circle d-flex justify-content-center align-items-center border border-2 border-white text-white {{ $badgeColor }}"
                                style="width: 28px; height: 28px; transform: translate(15%, 15%);">
                                <span style="font-size: 12px;">{!! $icon !!}</span>
                            </div>
                        </div>

                        {{-- Cục Nội dung chữ --}}
                        <div class="flex-grow-1 pe-2" style="font-size: 15px; line-height: 1.4;">
                            <p class="m-0 text-dark"><strong>{{ $noti->actor->fullname }}</strong> {{ $actionText }}</p>
                            <div class="small {{ $timeClass }}">{{ $noti->created_at->diffForHumans() }}</div>
                        </div>
                    </a>

                    {{-- PHẦN 2: KHU VỰC TRẠNG THÁI & HÀNH ĐỘNG (Bên phải) --}}
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">

                        @if($noti->is_read == 0)
                            <div class="flex-shrink-0 bg-primary rounded-circle" style="width: 12px; height: 12px;"></div>
                        @endif

                        {{-- MENU 3 CHẤM --}}
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted text-decoration-none border-0 p-1 shadow-none" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size: 14px;">
                                @if($noti->is_read)
                                    <li>
                                        <form action="{{ route('notifications.unread', $noti->id) }}" method="POST"
                                            class="m-0 ajax-noti-form" data-type="unread">
                                            @csrf
                                            <button type="submit" class="dropdown-item py-2">
                                                <i class="fas fa-circle text-primary me-2" style="font-size: 10px;"></i> Đánh dấu
                                                chưa đọc
                                            </button>
                                        </form>
                                    </li>
                                @endif

                                <li>
                                    <form action="{{ route('notifications.destroy', $noti->id) }}" method="POST"
                                        class="m-0 ajax-noti-form" data-type="delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item py-2 text-danger">
                                            <i class="fas fa-trash me-2"></i> Xóa thông báo
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            @empty
                {{-- Trạng thái trống --}}
                <div class="text-center py-4 text-muted">
                    <img src="https://cdn-icons-png.flaticon.com/512/1178/1178479.png" width="60" class="mb-3 opacity-50">
                    <p class="fw-medium">Bạn không có thông báo mới nào.</p>
                </div>
            @endforelse
        </div>
        </div>

        {{-- BÙA JS ĐỂ XỬ LÝ LỌC REAL-TIME NÈ PRO --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const filterBtns = document.querySelectorAll('.btn-filter');
                const notiItems = document.querySelectorAll('.noti-item');

                filterBtns.forEach(btn => {
                    btn.addEventListener('click', function () {
                        // 1. Reset class dọn dẹp mấy cái nút khác
                        filterBtns.forEach(b => {
                            b.classList.remove('btn-primary', 'active');
                            b.classList.add('btn-light', 'text-muted', 'border');
                        });

                        // 2. Ép màu xanh lè cho cái nút Pro vừa bấm vô
                        this.classList.remove('btn-light', 'text-muted', 'border');
                        this.classList.add('btn-primary', 'active');

                        // 3. Lấy cái nhãn cần lọc ('all', 'unread', 'read')
                        const filterValue = this.getAttribute('data-filter');

                        // 4. Bắt đầu lùa gà: Ẩn/Hiện thông báo
                        notiItems.forEach(item => {
                            const itemStatus = item.getAttribute('data-status');

                        // Nếu bấm Tất cả, hoặc đúng trạng thái thì cho nó lòi mặt ra (d-none của Bootstrap là ẩn)
                        if (filterValue === 'all' || filterValue === itemStatus) {
                            item.classList.remove('d-none');
                        } else {
                            item.classList.add('d-none'); // Khác hệ thì tiễn vong giấu đi
                        }
                    });
                });
            });
        });
                        </script>
@endsection