@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">

@include('partials.social_topbar')

<style>
    /* CSS giữ lại cho hệ thống chuyển đổi Tab Bạn bè */
    .friends-tabs-nav {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e4e6eb;
        padding-bottom: 10px;
    }
    .tab-nav-btn {
        background: none;
        border: none;
        font-size: 15px;
        font-weight: 700;
        color: #65676b;
        cursor: pointer;
        padding: 8px 16px;
        border-radius: 6px;
        transition: 0.2s;
    }
    .tab-nav-btn:hover { background: #f2f3f5; color: #111; }
    .tab-nav-btn.active { color: #e51f28; background: rgba(229, 31, 40, 0.1); }
    .friend-tab-content { display: none; }
    .friend-tab-content.active { display: block; }
    .request-actions-inline { display: flex; gap: 8px; margin-left: auto; }
</style>

<div class="profile-page">
    <div class="profile-container">

        <div class="list-card">

            {{-- KIỂM TRA: NẾU LÀ TRANG THEO DÕI (Controller truyền biến $users) --}}
            @if(isset($users))
                
                {{-- KHÔI PHỤC: Giao diện nguyên bản của trang Theo dõi giống ảnh ba29c5.png --}}
                <div class="list-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="font-size: 18px; font-weight: bold; margin: 0;">{{ $title }}</h2>
                    <div class="list-search-box">
                        <input type="text" id="userSearchInput" placeholder="Tìm kiếm..." style="border-radius: 20px; padding: 6px 15px; border: 1px solid #e4e6eb;">
                    </div>
                </div>

                @if($users->count() > 0)
                    <div class="user-list-grid" id="userListGrid">
                        @foreach($users as $item)
                        <a href="{{ route('profile.show', $item->username) }}" class="user-list-item">
                            <div class="avatar-online-wrap">
                                <img src="{{ $item->avatar_url ? asset($item->avatar_url) : asset('img/user/user.jpg') }}" alt="avatar">

                                @include('partials.activity_dot', ['user' => $item])
                            </div>
                            <div>
                                <strong>{{ $item->fullname ?? 'Người dùng' }}</strong>
                                <span>{{ '@' . $item->username }}</span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                @else
                    <div class="empty-post-box">Chưa có dữ liệu.</div>
                @endif

            {{-- NGƯỢC LẠI: NẾU LÀ TRANG BẠN BÈ (Controller truyền biến $friends) --}}
            @else

                <div class="list-header">
                    <h2>{{ $title }}</h2>
                    <div class="list-search-box" id="searchBoxContainer">
                        <input type="text" id="userSearchInput" placeholder="Tìm kiếm bạn bè...">
                    </div>
                </div>

                {{-- THANH MENU CHUYỂN TAB BẠN BÈ --}}
                @if(auth()->check() && auth()->id() === $user->id)
                <div class="friends-tabs-nav">
                    <button class="tab-nav-btn active" onclick="switchFriendTab('all-friends-tab')">
                        Tất cả bạn bè ({{ $friends->count() }})
                    </button>
                    <button class="tab-nav-btn" onclick="switchFriendTab('requests-tab')">
                        Lời mời kết bạn ({{ $friendRequests->count() }})
                    </button>
                </div>
                @endif

                {{-- TAB 1: TẤT CẢ BẠN BÈ --}}
                <div id="all-friends-tab" class="friend-tab-content active">
                    @if($friends->count() > 0)
                        <div class="user-list-grid" id="userListGrid">
                            @foreach($friends as $item)
                            <a href="{{ route('profile.show', $item->username) }}" class="user-list-item">
                                <div class="avatar-online-wrap">
                                    <img src="{{ $item->avatar_url ? asset($item->avatar_url) : asset('img/user/user.jpg') }}" alt="avatar">

                                    @include('partials.activity_dot', ['user' => $item])
                                </div>
                                <div>
                                    <strong>{{ $item->fullname ?? 'Người dùng' }}</strong>
                                    <span>{{ '@' . $item->username }}</span>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-post-box">Chưa có bạn bè nào trong danh sách.</div>
                    @endif
                </div>

                {{-- TAB 2: LỜI MỜI KẾT BẠN --}}
                @if(auth()->check() && auth()->id() === $user->id)
                <div id="requests-tab" class="friend-tab-content">
                    @if($friendRequests->count() > 0)
                        <div class="user-list-grid">
                            @foreach($friendRequests as $item)
                            <div class="user-list-item" style="justify-content: space-between; cursor: default;">
                                <a href="{{ route('profile.show', $item->username) }}" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit;">
                                    <div class="avatar-online-wrap">
                                        <img src="{{ $item->avatar_url ? asset($item->avatar_url) : asset('img/user/user.jpg') }}" alt="avatar">

                                        @include('partials.activity_dot', ['user' => $item])
                                    </div>
                                    <div>
                                        <strong>{{ $item->fullname ?? 'Người dùng' }}</strong>
                                        <span>{{ '@' . $item->username }}</span>
                                    </div>
                                </a>
                                <div class="request-actions-inline">
                                    <form method="POST" action="{{ route('friend.accept', $item->username) }}">
                                        @csrf
                                        <button type="submit" class="profile-action-btn primary" style="padding: 6px 12px; font-size: 13px;">Chấp nhận</button>
                                    </form>
                                    <form method="POST" action="{{ route('friend.remove', $item->username) }}">
                                        @csrf
                                        <button type="submit" class="profile-action-btn secondary" style="padding: 6px 12px; font-size: 13px;">Từ chối</button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-post-box">Bạn không có lời mời kết bạn nào mới.</div>
                    @endif
                </div>
                @endif

            @endif

        </div>

        <script>
            // HỆ THỐNG JAVASCRIPT ĐIỀU KHIỂN CHUNG
            const searchInput = document.getElementById('userSearchInput');
            
            // Tìm kiếm linh hoạt dựa trên giao diện đang hiển thị
            searchInput.addEventListener('input', function() {
                const keyword = this.value.toLowerCase().trim();
                const isFollowPage = {{ isset($users) ? 'true' : 'false' }};
                
                // Xác định vùng chọn phần tử để tìm kiếm không bị lẫn lộn
                const targetSelector = isFollowPage ? '#userListGrid .user-list-item' : '#all-friends-tab .user-list-item';
                const items = document.querySelectorAll(targetSelector);

                items.forEach(function(item) {
                    const text = item.innerText.toLowerCase();
                    item.style.display = text.includes(keyword) ? 'flex' : 'none';
                });
            });

            // Hàm chuyển đổi Tab dành riêng cho trang Bạn bè
            function switchFriendTab(tabId) {
                document.querySelectorAll('.friend-tab-content').forEach(tab => tab.classList.remove('active'));
                document.querySelectorAll('.tab-nav-btn').forEach(btn => btn.classList.remove('active'));

                document.getElementById(tabId).classList.add('active');
                event.currentTarget.classList.add('active');

                const searchBox = document.getElementById('searchBoxContainer');
                if (searchBox) {
                    searchBox.style.visibility = (tabId === 'requests-tab') ? 'hidden' : 'visible';
                }
            }
        </script>
    </div>
</div>
@endsection
