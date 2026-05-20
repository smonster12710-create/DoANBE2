@php
$user = Auth::user();

$avatar = $user && $user->avatar_url
? asset($user->avatar_url)
: asset('img/user/user.jpg');
$switchAccountIds = session('switch_accounts', [$user->id]);

$switchAccounts = \App\Models\User::whereIn('id', $switchAccountIds)->get();
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ESPACE</title>
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/dashbroad.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/search.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    @yield('styles')
    <style>
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 270px;
            display: flex;
            flex-direction: column;
            background: #fff;
            border-right: 1px solid #eee;
            z-index: 1000;
            overflow: visible;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 20px 10px;
        }

        .profile {
            position: absolute;
            bottom: 0;
            background: #fff;
            padding: 10px;
            border-top: 1px solid #f0f0f0;
            z-index: 10;
        }

        .profile-btn {
            border: none;
            background: transparent;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 190px;
            cursor: pointer;
            text-align: left;
        }

        .profile-btn img,
        .avatar-header img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
        }

        .avatar-dropdown {
            display: none;
            position: fixed;

            left: 20px;
            bottom: 90px;

            width: 270px;
            min-width: 270px;
            max-width: 340px;

            background: white;
            border-radius: 16px;
            padding: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.18);
            z-index: 99999;
        }

        .avatar-dropdown.show {
            display: block;
        }

        .avatar-header {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f5f5f5;
            padding: 12px;
            border-radius: 14px;
        }

        .avatar-dropdown a {
            display: block;
            text-decoration: none;
            color: #222;
            padding: 11px 12px;
            border-radius: 10px;
            font-size: 14px;
        }

        .avatar-dropdown a:hover {
            background: #f2f2f2;
        }

        .avatar-dropdown .logout-link {
            color: crimson;
            font-weight: bold;
        }

        .avatar-header div {
            min-width: 0;
            flex: 1;
        }

        .avatar-header small {
            display: block;
            color: #555;
            font-size: 14px;

            white-space: nowrap;
            overflow: visible;
            text-overflow: unset;
            max-width: none;
        }

        .text-truncate-custom {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }

        .profile-btn div {
            min-width: 0;
            flex: 1;
        }

        .account-header {
            display: flex;
            align-items: center;
            gap: 12px;

            background: #f5f5f5;
            border-radius: 14px;

            padding: 12px;
            padding-right: 50px;

            position: relative;

            width: 100%;
            min-width: 0;
            max-width: none;
        }

        .account-header img {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .account-header div {
            min-width: 0;
        }

        .account-header strong,
        .account-header small {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .switch-toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);

            width: 36px;
            height: 36px;

            border: none;
            border-radius: 50%;

            background: #e4e6eb;
            color: #111;

            font-size: 18px;
            font-weight: bold;

            cursor: pointer;
        }

        .switch-toggle-btn:hover {
            background: #d8dadf;
        }

        .switch-account-panel {
            display: none;
            position: fixed;
            left: 20px;
            bottom: 90px;
            width: 300px;
            background: #fff;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.18);
            z-index: 100000;
        }

        .switch-account-panel.show {
            display: block;
        }

        .switch-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }

        .switch-header button {
            border: none;
            background: transparent;
            font-size: 26px;
            cursor: pointer;
        }

        .switch-header h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            color: #111;
        }

        .switch-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .switch-list form {
            margin: 0;
        }

        .switch-account-item {
            width: 100%;
            border: none;
            background: transparent;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            border-radius: 12px;
            cursor: pointer;
            text-align: left;
        }

        .switch-account-item:hover {
            background: #f2f2f2;
        }

        .switch-account-item img {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            object-fit: cover;
        }

        .switch-account-item div {
            flex: 1;
            min-width: 0;
        }

        .switch-account-item strong {
            display: block;
            font-size: 16px;
            color: #111;
        }

        .switch-account-item small {
            display: block;
            font-size: 13px;
            color: #555;
        }

        .active-check {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #1877f2;
            color: white;
            font-size: 14px;
            font-weight: bold;
        }

        .create-page-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 10px;
            margin-top: 10px;
            border-top: 1px solid #eee;
            cursor: pointer;
        }

        .create-page-row span {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #e4e6eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
        }

        .create-page-row {
            text-decoration: none;
            color: #111;
        }

        .create-page-row:hover {
            background: #f2f2f2;
            color: #111;
        }
    </style>
</head>

<body>
    <div class="container-flex">
        <div class="sidebar">
            <h2 class="logo">ESPACE</h2>

            <div class="sidebar-content">
                <div class="menu">
                    <div class="menu-item">
                        <a class="danh_muc {{ request()->is('social') ? 'active' : '' }}" href="{{ url('/social') }}">
                            @if(request()->is('social'))
                            <svg style="width: 30px; height: 30px;" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 640 640">
                                <path
                                    d="M341.8 72.6C329.5 61.2 310.5 61.2 298.3 72.6L74.3 280.6C64.7 289.6 61.5 303.5 66.3 315.7C71.1 327.9 82.8 336 96 336L112 336L112 512C112 547.3 140.7 576 176 576L464 576C499.3 576 528 547.3 528 512L528 336L544 336C557.2 336 569 327.9 573.8 315.7C578.6 303.5 575.4 289.5 565.8 280.6L341.8 72.6zM304 384L336 384C362.5 384 384 405.5 384 432L384 528L256 528L256 432C256 405.5 277.5 384 304 384z" />
                            </svg>
                            @else
                            <svg style="width: 30px; height: 30px;" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 640 640">
                                <path
                                    d="M304 70.1C313.1 61.9 326.9 61.9 336 70.1L568 278.1C577.9 286.9 578.7 302.1 569.8 312C560.9 321.9 545.8 322.7 535.9 313.8L527.9 306.6L527.9 511.9C527.9 547.2 499.2 575.9 463.9 575.9L175.9 575.9C140.6 575.9 111.9 547.2 111.9 511.9L111.9 306.6L103.9 313.8C94 322.6 78.9 321.8 70 312C61.1 302.2 62 287 71.8 278.1L304 70.1zM320 120.2L160 263.7L160 512C160 520.8 167.2 528 176 528L224 528L224 424C224 384.2 256.2 352 296 352L344 352C383.8 352 416 384.2 416 424L416 528L464 528C472.8 528 480 520.8 480 512L480 263.7L320 120.3zM272 528L368 528L368 424C368 410.7 357.3 400 344 400L296 400C282.7 400 272 410.7 272 424L272 528z" />
                            </svg>
                            @endif
                            <span>Trang chủ</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="danh_muc" data-bs-toggle="modal" data-bs-target="#createPostModal">
                            <svg style="width: 30px; height: 30px;" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 640 640">
                                <path fill="rgb(0, 0, 0)"
                                    d="M160 144C151.2 144 144 151.2 144 160L144 480C144 488.8 151.2 496 160 496L480 496C488.8 496 496 488.8 496 480L496 160C496 151.2 488.8 144 480 144L160 144zM96 160C96 124.7 124.7 96 160 96L480 96C515.3 96 544 124.7 544 160L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 160zM296 408L296 344L232 344C218.7 344 208 333.3 208 320C208 306.7 218.7 296 232 296L296 296L296 232C296 218.7 306.7 208 320 208C333.3 208 344 218.7 344 232L344 296L408 296C421.3 296 432 306.7 432 320C432 333.3 421.3 344 408 344L344 344L344 408C344 421.3 333.3 432 320 432C306.7 432 296 421.3 296 408z" />
                            </svg>
                            <span>Đăng bài</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a href="{{ route('notifications.index') }}"
                            class="danh_muc {{ request()->is('notifications*') ? 'active' : '' }}"
                            style="position: relative;">

                            {{-- Bọc icon để gắn badge --}}
                            <div style="position: relative; display:inline-block;">

                                @if(request()->is('notifications*'))

                                {{-- ICON ACTIVE --}}
                                <svg
                                    style="width: 30px; height: 30px;"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 640 640">
                                    <!--!Font Awesome Free v7.2.0 by @fontawesome -->
                                    <path d="M320 64C302.3 64 288 78.3 288 96L288 99.2C215 114 160 178.6 160 256L160 277.7C160 325.8 143.6 372.5 113.6 410.1L103.8 422.3C98.7 428.6 96 436.4 96 444.5C96 464.1 111.9 480 131.5 480L508.4 480C528 480 543.9 464.1 543.9 444.5C543.9 436.4 541.2 428.6 536.1 422.3L526.3 410.1C496.4 372.5 480 325.8 480 277.7L480 256C480 178.6 425 114 352 99.2L352 96C352 78.3 337.7 64 320 64zM258 528C265.1 555.6 290.2 576 320 576C349.8 576 374.9 555.6 382 528L258 528z" />
                                </svg>

                                @else

                                {{-- ICON NORMAL --}}
                                <svg style="width: 30px; height: 30px;"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 640 640">
                                    <path fill="rgb(0,0,0)"
                                        d="M320 64C306.7 64 296 74.7 296 88L296 97.7C214.6 109.3 152 179.4 152 264L152 278.5C152 316.2 142 353.2 123 385.8L101.1 423.2C97.8 429 96 435.5 96 442.2C96 463.1 112.9 480 133.8 480L506.2 480C527.1 480 544 463.1 544 442.2C544 435.5 542.2 428.9 538.9 423.2L517 385.7C498 353.1 488 316.1 488 278.4L488 263.9C488 179.3 425.4 109.2 344 97.6L344 87.9C344 74.6 333.3 63.9 320 63.9zM488.4 432L151.5 432L164.4 409.9C187.7 370 200 324.6 200 278.5L200 264C200 197.7 253.7 144 320 144C386.3 144 440 197.7 440 264L440 278.5C440 324.7 452.3 370 475.5 409.9L488.4 432zM252.1 528C262 556 288.7 576 320 576C351.3 576 378 556 387.9 528L252.1 528z" />
                                </svg>

                                @endif

                                {{-- Đếm thông báo chưa đọc --}}
                                @php
                                    $unreadCount = auth()->check()
                                        ? \App\Models\Notification::where('user_id', auth()->id())
                                            ->where('is_read', 0)
                                            ->count()
                                        : 0;
                                @endphp

                                {{-- Badge đỏ --}}
                                    {{-- Sửa class thành ID để JS tìm cho chuẩn --}}
                                    <span id="notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                        style="font-size:10px; display: {{ $unreadCount > 0 ? 'inline-block' : 'none' }}">
                                        {{ $unreadCount }}
                                    </span>
                                    </div>

                                    <span>Thông báo</span>

                                    </a>
                                    </div>

                                    <div class="menu-item">

                                        @php
$isMessaging = request()->is('list_messages*') || request()->is('chat-messages*');

$unreadMessageCount = \App\Models\Message::where('is_read', 0)
    ->where('sender_id', '!=', auth()->id())
    ->whereHas('conversation.participants', function ($q) {
        $q->where('user_id', auth()->id());
    })
    ->count();
                                        @endphp


                        <a class="danh_muc {{ $isMessaging ? 'active' : '' }}"
                            href="{{ url('/list_messages') }}"
                            style="position: relative;">

                            {{-- Bọc icon để gắn badge --}}
                            <div style="position: relative; display:inline-block;">

                                @if($isMessaging)

                                {{-- Icon Bold --}}
                                <svg style="width: 30px; height: 30px;"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 640 640">
                                    <path d="M320 544C461.4 544 576 436.5 576 304C576 171.5 461.4 64 320 64C178.6 64 64 171.5 64 304C64 358.3 83.2 408.3 115.6 448.5L66.8 540.8C62 549.8 63.5 560.8 70.4 568.3C77.3 575.8 88.2 578.1 97.5 574.1L215.9 523.4C247.7 536.6 282.9 544 320 544zM192 272C209.7 272 224 286.3 224 304C224 321.7 209.7 336 192 336C174.3 336 160 321.7 160 304C160 286.3 174.3 272 192 272zM320 272C337.7 272 352 286.3 352 304C352 321.7 337.7 336 320 336C302.3 336 288 321.7 288 304C288 286.3 302.3 272 320 272zM416 304C416 286.3 430.3 272 448 272C465.7 272 480 286.3 480 304C480 321.7 465.7 336 448 336C430.3 336 416 321.7 416 304z" />
                                </svg>

                                @else

                                {{-- Icon Outline --}}
                                <svg style="width: 30px; height: 30px;"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 640 640">
                                    <path d="M64 304C64 358.4 83.3 408.6 115.9 448.9L67.1 538.3C65.1 542 64 546.2 64 550.5C64 564.6 75.4 576 89.5 576C93.5 576 97.3 575.4 101 573.9L217.4 524C248.8 536.9 283.5 544 320 544C461.4 544 576 436.5 576 304C576 171.5 461.4 64 320 64C178.6 64 64 171.5 64 304zM158 471.9C167.3 454.8 165.4 433.8 153.2 418.7C127.1 386.4 112 346.8 112 304C112 200.8 202.2 112 320 112C437.8 112 528 200.8 528 304C528 407.2 437.8 496 320 496C289.8 496 261.3 490.1 235.7 479.6C223.8 474.7 210.4 474.8 198.6 479.9L140 504.9L158 471.9zM208 336C225.7 336 240 321.7 240 304C240 286.3 225.7 272 208 272C190.3 272 176 286.3 176 304C176 321.7 190.3 336 208 336zM352 304C352 286.3 337.7 272 320 272C302.3 272 288 286.3 288 304C288 321.7 302.3 336 320 336C337.7 336 352 321.7 352 304zM432 336C449.7 336 464 321.7 464 304C464 286.3 449.7 272 432 272C414.3 272 400 286.3 400 304C400 321.7 414.3 336 432 336z" />
                                </svg>

                                @endif

                                {{-- Badge đỏ --}}
                                <span
                                    id="message-badge"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                    style="font-size:10px; @if($unreadMessageCount <= 0) display: none; @endif">
                                    @if($unreadMessageCount > 99)
                                    99+
                                    @elseif($unreadMessageCount > 0)
                                    {{ $unreadMessageCount }}
                                    @endif
                                </span>

                            </div>

                            <span>Tin nhắn</span>

                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="danh_muc {{ request()->is('saved') ? 'active' : '' }}"
                            href="{{ route('posts.saved') }}">
                            @if(request()->is('saved'))
                            {{-- ACTIVE ICON --}}
                            <svg style="width: 30px; height: 30px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M6 3h12a1 1 0 0 1 1 1v17l-7-5-7 5V4a1 1 0 0 1 1-1z" />
                            </svg>
                            @else
                            {{-- NORMAL ICON --}}
                            <svg style="width: 30px; height: 30px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 3h12a1 1 0 0 1 1 1v17l-7-5-7 5V4a1 1 0 0 1 1-1z" />
                            </svg>
                            @endif
                            <span>Đã lưu</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="danh_muc">
                            <svg style="width: 30px; height: 30px;" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12 21a8.985 8.985 0 0 1-1.755-.173 1 1 0 0 1-.791-.813l-.273-1.606a6.933 6.933 0 0 1-1.32-.762l-1.527.566a1 1 0 0 1-1.1-.278 8.977 8.977 0 0 1-1.756-3.041 1 1 0 0 1 .31-1.092l1.254-1.04a6.979 6.979 0 0 1 0-1.524L3.787 10.2a1 1 0 0 1-.31-1.092 8.977 8.977 0 0 1 1.756-3.042 1 1 0 0 1 1.1-.278l1.527.566a6.933 6.933 0 0 1 1.32-.762l.274-1.606a1 1 0 0 1 .791-.813 8.957 8.957 0 0 1 3.51 0 1 1 0 0 1 .791.813l.273 1.606a6.933 6.933 0 0 1 1.32.762l1.527-.566a1 1 0 0 1 1.1.278 8.977 8.977 0 0 1 1.756 3.041 1 1 0 0 1-.31 1.092l-1.254 1.04a6.979 6.979 0 0 1 0 1.524l1.254 1.04a1 1 0 0 1 .31 1.092 8.977 8.977 0 0 1-1.756 3.041 1 1 0 0 1-1.1.278l-1.527-.566a6.933 6.933 0 0 1-1.32.762l-.273 1.606a1 1 0 0 1-.791.813A8.985 8.985 0 0 1 12 21zm-.7-2.035a6.913 6.913 0 0 0 1.393 0l.247-1.451a1 1 0 0 1 .664-.779 4.974 4.974 0 0 0 1.696-.975 1 1 0 0 1 1.008-.186l1.381.512a7.012 7.012 0 0 0 .7-1.206l-1.133-.939a1 1 0 0 1-.343-.964 5.018 5.018 0 0 0 0-1.953 1 1 0 0 1 .343-.964l1.124-.94a7.012 7.012 0 0 0-.7-1.206l-1.38.512a1 1 0 0 1-1-.186 4.974 4.974 0 0 0-1.688-.976 1 1 0 0 1-.664-.779l-.248-1.45a6.913 6.913 0 0 0-1.393 0l-.25 1.45a1 1 0 0 1-.664.779A4.974 4.974 0 0 0 8.7 8.24a1 1 0 0 1-1 .186l-1.385-.512a7.012 7.012 0 0 0-.7 1.206l1.133.939a1 1 0 0 1 .343.964 5.018 5.018 0 0 0 0 1.953 1 1 0 0 1-.343.964l-1.128.94a7.012 7.012 0 0 0 .7 1.206l1.38-.512a1 1 0 0 1 1 .186 4.974 4.974 0 0 0 1.688.976 1 1 0 0 1 .664.779zm.7-3.725a3.24 3.24 0 0 1 0-6.48 3.24 3.24 0 0 1 0 6.48zm0-4.48A1.24 1.24 0 1 0 13.24 12 1.244 1.244 0 0 0 12 10.76z" />
                            </svg>
                            <span>Cài đặt</span>
                        </a>
                    </div>
                </div>

                <div class="trending">
                    <h4>Xu hướng</h4>
                    <div class="trend">
                        <span>#Food</span> <span>19k</span>
                    </div>
                    <div class="trend">
                        <span>#Du_Lich</span> <span>12.5k</span>
                    </div>
                    <div class="trend">
                        <span>#Sach</span> <span>9.7k</span>
                    </div>
                    <div class="trend">
                        <span>#Chill</span> <span>8.6k</span>
                    </div>
                </div>

                <div class="profile avatar-menu">
                    <button type="button" class="profile-btn" onclick="toggleAvatarMenu()">
                        <img src="{{ $avatar }}" alt="avatar">
                        <div>
                            <strong class="text-truncate-custom">{{ $user->fullname ?? 'Người dùng' }}</strong>
                            <small class="text-truncate-custom">{{ '@' . ($user->username ?? 'user') }}</small>
                        </div>
                    </button>

                    <div id="avatarDropdown" class="avatar-dropdown sidebar-dropdown">
                        <div class="avatar-header account-header">
                            <img src="{{ $avatar }}" alt="avatar">

                            <div>
                                <strong>{{ $user->fullname ?? 'Người dùng' }}</strong>
                                <small title="{{ $user->email }}">{{ $user->email ?? '' }}</small>
                            </div>

                            <button type="button" id="switchToggleBtn" class="switch-toggle-btn" onclick="toggleSwitchAccount(event)">
                                ⌄
                            </button>
                        </div>
                        <div id="switchAccountPanel" class="switch-account-panel">

                            <div class="switch-header">
                                <button type="button" onclick="closeSwitchPanel(event)">←</button>
                                <h3>Chọn trang cá nhân</h3>
                            </div>

                            <div class="switch-list">
                                @foreach($switchAccounts as $account)
                                <form method="POST" action="{{ route('account.switch') }}">
                                    @csrf

                                    <input type="hidden" name="user_id" value="{{ $account->id }}">

                                    <button type="submit" class="switch-account-item">
                                        <img
                                            src="{{ $account->avatar_url ? asset($account->avatar_url) : asset('img/user/user.jpg') }}"
                                            alt="avatar">

                                        <div>
                                            <strong>{{ $account->fullname ?? $account->name ?? 'Người dùng' }}</strong>
                                            <small>{{ '@' . ($account->username ?? 'user') }}</small>
                                        </div>

                                        @if($account->id == auth()->id())
                                        <span class="active-check">✓</span>
                                        @endif
                                    </button>
                                </form>
                                @endforeach
                            </div>

                            <a href="{{ route('signout') }}" class="create-page-row">
                                <span>＋</span>
                                <strong>Thêm tài khoản</strong>
                            </a>
                        </div>
                        <a href="{{ route('profile') }}">Xem trang cá nhân</a>
                        <a href="#">Cài đặt và quyền riêng tư</a>
                        <a href="#">Trợ giúp và hỗ trợ</a>
                        <a href="#">Màn hình và trợ năng</a>
                        <a href="#">Đóng góp ý kiến</a>
                        <a href="{{ route('signout') }}" class="logout-link">Đăng xuất</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="main">
            <div class="content">
                @yield('content')
            </div>
        </div>
    </div>

    <div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel" style="color: black;">Tạo bài viết mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <textarea name="content" class="form-control" rows="4" placeholder="Bạn đang nghĩ gì?" required
                                style="border: none; resize: none;"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="expires_in" class="form-label" style="font-weight: bold; color: #555;">Tự động xóa
                                sau (tùy chọn)</label>
                            <select name="expires_in" id="expires_in" class="form-select">
                                <option value="">Không giới hạn</option>
                                <option value="1">1 Phút (Để test)</option> <!-- Đổi thành 1 phút -->
                                <option value="2">2 Phút (Để test)</option>
                                <option value="60">1 Giờ</option>
                                <option value="1440">1 Ngày</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="formFile" class="form-label" style="font-weight: bold; color: #555;">Thêm ảnh
                                vào bài viết</label>
                        <input class="form-control" type="file" name="images[]" id="postImages" accept="image/*" multiple>
                        <div id="preview-images" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary" style="background-color: #007bff;">Đăng
                                bài</button>
                        </div>
                        </form>
                        </div>
                        </div>
                        </div>

</body>

<script>
    function toggleAvatarMenu() {
        document.getElementById('avatarDropdown').classList.toggle('show');
    }

    document.addEventListener('click', function (e) {
        const menu = document.querySelector('.avatar-menu');
        const dropdown = document.getElementById('avatarDropdown');

        if (menu && !menu.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });
</script>
<script>
    const switchToggleBtn = document.getElementById('switchToggleBtn');
    const switchAccountPanel = document.getElementById('switchAccountPanel');
    const avatarDropdown = document.getElementById('avatarDropdown');

    function toggleSwitchAccount(event) {
        event.stopPropagation();

        if (switchAccountPanel) {
            switchAccountPanel.classList.toggle('show');
        }
    }

    function closeSwitchPanel(event) {
        event.stopPropagation();

        if (switchAccountPanel) {
            switchAccountPanel.classList.remove('show');
        }
    }

    document.addEventListener('click', function(event) {
        if (
            switchAccountPanel &&
            switchToggleBtn &&
            !switchAccountPanel.contains(event.target) &&
            !switchToggleBtn.contains(event.target)
        ) {
            switchAccountPanel.classList.remove('show');
        }
    });
</script>
<script src="{{ asset('js/notification.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/search.js') }}"></script>
<script src="{{ asset('js/search_post.js') }}"></script>
<script src="{{ asset('js/index.js') }}"></script>
<script src="{{ asset('js/chat_dashboard.js') }}"></script>
<script src="{{ asset('js/like.js') }}"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>

@stack('modals')

</html>
