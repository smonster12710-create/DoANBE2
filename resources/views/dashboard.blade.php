@php
$user = Auth::user();


$avatar = $user && $user->avatar_url
? asset($user->avatar_url)

: asset('img/user/user.jpg');@endphp
<!DOCTYPE html>
<html>

<head>
    <title>ESPACE</title>
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/dashbroad.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/search.css') }}">
    <style>
        .profile {
            position: relative;
            margin-top: auto;
            padding: 12px;
        }

        .profile-btn {
            border: none;
            background: transparent;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
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
            position: absolute;
            left: 12px;
            bottom: 75px;
            width: 270px;
            background: white;
            border-radius: 14px;
            padding: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.18);
            z-index: 9999;
        }

        .avatar-dropdown.show {
            display: block;
        }

        .avatar-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 12px;
            margin-bottom: 10px;
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
    </style>
</head>

<body>
    <div class="container-flex">

        <!-- SIDEBAR -->
        <div class="sidebar">

            <h2 class="logo">ESPACE</h2>

            <div class="menu">
                <div class="menu-item">
                    <a class="danh_muc" href="{{ url('/social') }}"
                        class="menu-item {{ request()->is('social') ? 'active' : '' }}">

                        @if(request()->is('social'))
                            <!-- ICON ACTIVE -->
                            <svg style="width: 30px; height: 30px;" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                <path
                                    d="M341.8 72.6C329.5 61.2 310.5 61.2 298.3 72.6L74.3 280.6C64.7 289.6 61.5 303.5 66.3 315.7C71.1 327.9 82.8 336 96 336L112 336L112 512C112 547.3 140.7 576 176 576L464 576C499.3 576 528 547.3 528 512L528 336L544 336C557.2 336 569 327.9 573.8 315.7C578.6 303.5 575.4 289.5 565.8 280.6L341.8 72.6zM304 384L336 384C362.5 384 384 405.5 384 432L384 528L256 528L256 432C256 405.5 277.5 384 304 384z" />
                            </svg>
                        @else
                            <!-- ICON NORMAL -->

                            <svg style="width: 30px; height: 30px;" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                <path
                                    d="M304 70.1C313.1 61.9 326.9 61.9 336 70.1L568 278.1C577.9 286.9 578.7 302.1 569.8 312C560.9 321.9 545.8 322.7 535.9 313.8L527.9 306.6L527.9 511.9C527.9 547.2 499.2 575.9 463.9 575.9L175.9 575.9C140.6 575.9 111.9 547.2 111.9 511.9L111.9 306.6L103.9 313.8C94 322.6 78.9 321.8 70 312C61.1 302.2 62 287 71.8 278.1L304 70.1zM320 120.2L160 263.7L160 512C160 520.8 167.2 528 176 528L224 528L224 424C224 384.2 256.2 352 296 352L344 352C383.8 352 416 384.2 416 424L416 528L464 528C472.8 528 480 520.8 480 512L480 263.7L320 120.3zM272 528L368 528L368 424C368 410.7 357.3 400 344 400L296 400C282.7 400 272 410.7 272 424L272 528z" />
                            </svg>
                        @endif
                        <span>Trang chủ</span>
                    </a>

                </div>
                <div class="menu-item">
                    <a class="danh_muc">
                        <svg style="width: 30px; height: 30px;" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                            <path fill="rgb(0, 0, 0)"
                                d="M128 176C119.2 176 112 183.2 112 192L112 448C112 456.8 119.2 464 128 464L512 464C520.8 464 528 456.8 528 448L528 192C528 183.2 520.8 176 512 176L128 176zM64 192C64 156.7 92.7 128 128 128L512 128C547.3 128 576 156.7 576 192L576 448C576 483.3 547.3 512 512 512L128 512C92.7 512 64 483.3 64 448L64 192zM224 384C224 401.7 209.7 416 192 416C174.3 416 160 401.7 160 384C160 366.3 174.3 352 192 352C209.7 352 224 366.3 224 384zM192 288C174.3 288 160 273.7 160 256C160 238.3 174.3 224 192 224C209.7 224 224 238.3 224 256C224 273.7 209.7 288 192 288zM296 232L456 232C469.3 232 480 242.7 480 256C480 269.3 469.3 280 456 280L296 280C282.7 280 272 269.3 272 256C272 242.7 282.7 232 296 232zM296 360L456 360C469.3 360 480 370.7 480 384C480 397.3 469.3 408 456 408L296 408C282.7 408 272 397.3 272 384C272 370.7 282.7 360 296 360z" />
                        </svg>
                        <span>Bảng của bạn</span>
                    </a>
                </div>
                <div class="menu-item">


                    <a class="danh_muc" data-bs-toggle="modal" data-bs-target="#createPostModal">
                        <svg style="width: 30px; height: 30px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->

                            <path fill="rgb(0, 0, 0)" d="M160 144C151.2 144 144 151.2 144 160L144 480C144 488.8 151.2 496 160 496L480 496C488.8 496 496 488.8 496 480L496 160C496 151.2 488.8 144 480 144L160 144zM96 160C96 124.7 124.7 96 160 96L480 96C515.3 96 544 124.7 544 160L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 160zM296 408L296 344L232 344C218.7 344 208 333.3 208 320C208 306.7 218.7 296 232 296L296 296L296 232C296 218.7 306.7 208 320 208C333.3 208 344 218.7 344 232L344 296L408 296C421.3 296 432 306.7 432 320C432 333.3 421.3 344 408 344L344 344L344 408C344 421.3 333.3 432 320 432C306.7 432 296 421.3 296 408z" />

                    
                        </svg>
                        <span>Đăng bài</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="danh_muc">
                        <svg style="width: 30px; height: 30px;" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                            <path fill="rgb(0, 0, 0)"
                                d="M320 64C306.7 64 296 74.7 296 88L296 97.7C214.6 109.3 152 179.4 152 264L152 278.5C152 316.2 142 353.2 123 385.8L101.1 423.2C97.8 429 96 435.5 96 442.2C96 463.1 112.9 480 133.8 480L506.2 480C527.1 480 544 463.1 544 442.2C544 435.5 542.2 428.9 538.9 423.2L517 385.7C498 353.1 488 316.1 488 278.4L488 263.9C488 179.3 425.4 109.2 344 97.6L344 87.9C344 74.6 333.3 63.9 320 63.9zM488.4 432L151.5 432L164.4 409.9C187.7 370 200 324.6 200 278.5L200 264C200 197.7 253.7 144 320 144C386.3 144 440 197.7 440 264L440 278.5C440 324.7 452.3 370 475.5 409.9L488.4 432zM252.1 528C262 556 288.7 576 320 576C351.3 576 378 556 387.9 528L252.1 528z" />
                        </svg>
                        <span>Thông báo</span>
                    </a>
                </div>
                <div class="menu-item">


                    <a class="danh_muc" href="{{ url('/list_messages') }}" class="menu-item {{ request()->is('list_messages') ? 'active' : '' }}">
                        @if(request()->is('social'))
                        <!-- ICON ACTIVE -->

                        <svg style="width: 30px; height: 30px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                            <path fill="rgb(0, 0, 0)" d="M64 304C64 358.4 83.3 408.6 115.9 448.9L67.1 538.3C65.1 542 64 546.2 64 550.5C64 564.6 75.4 576 89.5 576C93.5 576 97.3 575.4 101 573.9L217.4 524C248.8 536.9 283.5 544 320 544C461.4 544 576 436.5 576 304C576 171.5 461.4 64 320 64C178.6 64 64 171.5 64 304zM158 471.9C167.3 454.8 165.4 433.8 153.2 418.7C127.1 386.4 112 346.8 112 304C112 200.8 202.2 112 320 112C437.8 112 528 200.8 528 304C528 407.2 437.8 496 320 496C289.8 496 261.3 490.1 235.7 479.6C223.8 474.7 210.4 474.8 198.6 479.9L140 504.9L158 471.9zM208 336C225.7 336 240 321.7 240 304C240 286.3 225.7 272 208 272C190.3 272 176 286.3 176 304C176 321.7 190.3 336 208 336zM352 304C352 286.3 337.7 272 320 272C302.3 272 288 286.3 288 304C288 321.7 302.3 336 320 336C337.7 336 352 321.7 352 304zM432 336C449.7 336 464 321.7 464 304C464 286.3 449.7 272 432 272C414.3 272 400 286.3 400 304C400 321.7 414.3 336 432 336z" />
                        </svg>

                        @else
                            <!-- ICON NORMAL -->

                            <svg style="width: 30px; height: 30px;" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                <path fill="rgb(0, 0, 0)"
                                    d="M320 544C461.4 544 576 436.5 576 304C576 171.5 461.4 64 320 64C178.6 64 64 171.5 64 304C64 358.3 83.2 408.3 115.6 448.5L66.8 540.8C62 549.8 63.5 560.8 70.4 568.3C77.3 575.8 88.2 578.1 97.5 574.1L215.9 523.4C247.7 536.6 282.9 544 320 544zM192 272C209.7 272 224 286.3 224 304C224 321.7 209.7 336 192 336C174.3 336 160 321.7 160 304C160 286.3 174.3 272 192 272zM320 272C337.7 272 352 286.3 352 304C352 321.7 337.7 336 320 336C302.3 336 288 321.7 288 304C288 286.3 302.3 272 320 272zM416 304C416 286.3 430.3 272 448 272C465.7 272 480 286.3 480 304C480 321.7 465.7 336 448 336C430.3 336 416 321.7 416 304z" />
                            </svg>
                        @endif
                        <span>Tin nhắn</span>
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
                        <strong>{{ $user->fullname ?? 'Người dùng' }}</strong><br>
                        <small>{{ '@' . ($user->username ?? 'user') }}</small>
                    </div>
                </button>

                <div id="avatarDropdown" class="avatar-dropdown sidebar-dropdown">
                    <div class="avatar-header">
                        <img src="{{ $avatar }}" alt="avatar">
                        <div>
                            <strong>{{ $user->fullname ?? 'Người dùng' }}</strong><br>
                            <small>{{ $user->email ?? '' }}</small>
                        </div>
                    </div>

                    <a href="#">Xem tất cả trang cá nhân</a>
                    <a href="#">Cài đặt và quyền riêng tư</a>
                    <a href="#">Trợ giúp và hỗ trợ</a>
                    <a href="#">Màn hình và trợ năng</a>
                    <a href="#">Đóng góp ý kiến</a>
                    <a href="{{ route('signout') }}" class="logout-link">Đăng Xuất</a>
                </div>
            </div>
        </div>

        <!-- MAIN -->
        <div class="main">
            <!-- TOPBAR -->
            <div class="topbar" style="display: flex; gap: 15px; align-items: center;">
                <div style="position: relative; flex: 1;">
                    <input id="search-input" class="search" style="width: 100%;" placeholder="Tìm kiếm....."
                        autocomplete="off">
                    <div id="search-results" class="search-results-dropdown"></div>
                </div>
                <!-- 2 Nút bên phải -->
                <div style="display: flex; gap: 10px;">
                    <button class="btn-top">Bạn Bè</button>
                    <button class="btn-top">Theo Dõi</button>
                </div>
            </div>
            <!-- CONTENT -->
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
                            <textarea name="content" class="form-control" rows="4" placeholder="Bạn đang nghĩ gì?" required style="border: none; resize: none;"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="formFile" class="form-label" style="font-weight: bold; color: #555;">Thêm ảnh vào bài viết</label>
                            <input class="form-control" type="file" name="image" id="formFile" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" style="background-color: #007bff;">Đăng bài</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<script>
    function toggleAvatarMenu() {
        document.getElementById('avatarDropdown').classList.toggle('show');
    }

    document.addEventListener('click', function(e) {
        const menu = document.querySelector('.avatar-menu');
        const dropdown = document.getElementById('avatarDropdown');

        if (menu && !menu.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });
</script>

</html>