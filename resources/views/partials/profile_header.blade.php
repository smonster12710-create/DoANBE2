<div class="profile-card">
    <img class="cover-img" src="{{ $user->cover_url ? asset($user->cover_url) : asset('img/cover/cover.jpg') }}"
        alt="cover">

    <div class="profile-info">
        <img class="profile-avatar"
            src="{{ $user->avatar_url ? asset($user->avatar_url) : asset('img/user/user.jpg') }}" alt="avatar">

        <div class="profile-main">
            <h1 class="profile-name">{{ $user->fullname ?? 'Người dùng' }}</h1>
            <div class="profile-username">{{ '@' . $user->username }}</div>

            <div class="profile-stats-line">
                {{-- Những con số này Pro sẽ truyền từ Controller qua --}}
                <span><strong>{{ $friendsCount ?? 0 }}</strong> bạn bè</span>
                <span>·</span>
                <span><strong>{{ $postsCount ?? 0 }}</strong> bài viết</span>
                <span>·</span>
                <span><strong>{{ $followersCount ?? 0 }}</strong> người theo dõi</span>
            </div>

            <div class="profile-bio">{{ $user->bio ?? 'Chưa có tiểu sử' }}</div>
        </div>

        @if(auth()->id() == $user->id)
            <a href="{{ route('profile.edit') }}" class="edit-btn">✎ Chỉnh sửa</a>
        @else
            <button class="btn btn-primary rounded-pill px-4">Theo dõi</button>
        @endif
    </div>
</div>