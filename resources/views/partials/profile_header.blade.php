<div class="profile-card">
    {{-- cover_src/avatar_src đã xử lý URL local để không phụ thuộc APP_URL. --}}
    <img class="cover-img" src="{{ $user->cover_src }}" alt="cover">

    <div class="profile-info">
        <div class="profile-avatar-wrap profile-avatar-online-wrap">
            <img class="profile-avatar" src="{{ $user->avatar_src }}" alt="avatar">
            @include('partials.activity_dot', ['user' => $user])
        </div>
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

        {{-- TRƯỜNG HỢP 1: TRANG CỦA CHÍNH MÌNH --}}
        @if (auth()->id() == $user->id)
            {{-- Ép thêm cái div action-buttons vô đây để nó nằm ngang hàng cho đẹp --}}
            <div class="profile-action-buttons">
                {{-- Đổi class edit-btn thành class của Bootstrap cho nó đồng bộ form --}}
                <a href="{{ route('profile.edit') }}" class="btn btn-danger rounded-pill">
                    <i class="fas fa-pen"></i> Chỉnh sửa
                </a>

                <button type="button" class="btn btn-outline-secondary rounded-pill"
                    onclick="shareLinkJS('{{ url()->current() }}')">
                    <i class="fas fa-share"></i> Chia sẻ
                </button>
            </div>

            {{-- TRƯỜNG HỢP 2: TRANG CỦA NGƯỜI KHÁC --}}
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
                <form action="{{ route('follow.toggle', $user->id) }}" method="POST" class="friend-action-form">
                    @csrf
                    @php
                        $isFollowing = auth()->user()->isFollowing($user->id);
                    @endphp

                    <input type="hidden" name="expected_status" value="{{ $isFollowing ? '1' : '0' }}">

                    <button type="submit" class="profile-action-btn {{ $isFollowing ? 'secondary' : 'primary' }}">
                        {{ $isFollowing ? 'Đang theo dõi' : 'Theo dõi' }}
                    </button>
                </form>

                {{-- NÚT CHIA SẺ DÀNH CHO TRANG NGƯỜI KHÁC --}}
                @if (auth()->check())
                    <button type="button" class="btn btn-outline-secondary rounded-pill"
                        onclick="shareLinkJS('{{ url()->current() }}')">
                        <i class="fas fa-share"></i> Chia sẻ
                    </button>
                @endif

                {{-- NÚT CHẶN --}}
                @if (auth()->check() && auth()->id() !== $user->id)
                    <form action="{{ route('user.block', $user->id) }}" method="POST" class="friend-action-form"
                        onsubmit="return confirm('Bạn có chắc muốn chặn người dùng này?')">
                        @csrf
                        <button type="submit" class="profile-action-btn secondary"
                            style="color: #dc3545; border-color: #dc3545;">
                            {{ auth()->user()->isBlocking($user->id) ? 'Bỏ chặn' : 'Chặn' }}
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>
