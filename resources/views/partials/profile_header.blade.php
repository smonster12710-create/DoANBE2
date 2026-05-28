<div class="profile-card">
    <img class="cover-img" src="{{ $user->cover_url ? asset($user->cover_url) : asset('img/cover/cover.jpg') }}"
        alt="cover">

    <div class="profile-info">
        <img class="profile-avatar" src="{{ $user->avatar_url ? asset($user->avatar_url) : asset('img/user/user.jpg') }}"
            alt="avatar">

        <div class="profile-main">
            <h1 class="profile-name">{{ $user->fullname ?? 'Người dùng' }}</h1>
            <div class="profile-username">{{ '@' . $user->username }}</div>

            <div class="profile-stats-line">
                <a href="{{ route('profile.friends', $user->username) }}" class="profile-stat-link">
                    <strong>{{ $friendsCount ?? 0 }}</strong> bạn bè
                </a>

                <span>·</span>

                <span class="profile-stat-link">
                    <strong>{{ $postsCount ?? 0 }}</strong> bài viết
                </span>

                <span>·</span>

                <a href="{{ route('profile.followers', $user->username) }}" class="profile-stat-link">
                    <strong>{{ $followersCount ?? 0 }}</strong> người theo dõi
                </a>

                <span>·</span>

                <a href="{{ route('profile.following', $user->username) }}" class="profile-stat-link">
                    <strong>{{ $followingCount ?? 0 }}</strong> đang theo dõi
                </a>
            </div>
            <div class="profile-bio">{{ $user->bio ?? 'Chưa có tiểu sử' }}</div>
        </div>

        @if (auth()->id() == $user->id)

        <a href="{{ route('profile.edit') }}" class="edit-btn">
            ✎ Chỉnh sửa
        </a>
        @else
        <div class="profile-action-buttons">

            {{-- NÚT KẾT BẠN/HỦY KẾT BẠN/CHẤP NHẬN/TỪ CHỐI --}}
            @if (auth()->check() && auth()->id() !== $user->id)
            @php
            $friendStatus = auth()->user()->getFriendshipStatus($user->id);
            @endphp

            @if ($friendStatus === 'accepted')
            <form method="POST" action="{{ route('friend.remove', $user->username) }}"
                class="friend-action-form">
                @csrf
                <button type="submit" class="profile-action-btn secondary">Hủy kết bạn</button>
            </form>
            @elseif ($friendStatus === 'pending')
            <form method="POST" action="{{ route('friend.remove', $user->username) }}"
                class="friend-action-form">
                @csrf
                <button type="submit" class="profile-action-btn secondary">Đang chờ đồng ý</button>
            </form>
            @elseif ($friendStatus === 'requested')
            {{-- Gộp 2 form vào trong một cặp thẻ div để quản lý flex riêng --}}
            <div class="requested-actions">
                <form method="POST" action="{{ route('friend.accept', $user->username) }}"
                    class="friend-action-form">
                    @csrf
                    <button type="submit" class="profile-action-btn primary">Chấp nhận</button>
                </form>
                <form method="POST" action="{{ route('friend.remove', $user->username) }}"
                    class="friend-action-form">
                    @csrf
                    <button type="submit" class="profile-action-btn secondary">Từ chối</button>
                </form>
            </div>
            @else
            <form method="POST" action="{{ route('friend.send', $user->username) }}"
                class="friend-action-form">
                @csrf
                <button type="submit" class="profile-action-btn primary">Thêm bạn bè</button>
            </form>
            @endif
            @endif

            {{-- NÚT NHẮN TIN --}}
            <a href="{{ route('messages.start', $user->username) }}" class="profile-action-btn primary">
                Nhắn tin
            </a>

            {{-- NÚT THEO DÕI --}}
            @if (auth()->check() && auth()->id() !== $user->id)
            <form action="{{ route('follow.toggle', $user->id) }}" method="POST" class="friend-action-form">
                @csrf
                @php
                $isFollowing = auth()->user()->isFollowing($user->id);
                @endphp
                <button type="submit" class="profile-action-btn {{ $isFollowing ? 'secondary' : 'primary' }}">
                    {{ $isFollowing ? 'Đang theo dõi' : 'Theo dõi' }}
                </button>
            </form>
            @endif

        </div>

        @endif
    </div>
</div>