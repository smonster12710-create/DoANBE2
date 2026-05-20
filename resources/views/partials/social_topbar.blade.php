<div class="topbar" style="display: flex; gap: 15px; align-items: center;">
    <div style="position: relative; flex: 1;">
        <input id="search-input" class="search" style="width: 100%;" placeholder="Tìm kiếm....." autocomplete="off">
        <div id="search-results" class="search-results-dropdown"></div>
    </div>

    <div style="display: flex; gap: 10px;">
        @auth
        <a href="{{ route('profile.friends', auth()->user()->username) }}" class="btn-top" style="text-decoration: none;">
            Bạn Bè
        </a>
        <a href="{{ route('profile.following', auth()->user()->username) }}" class="btn-top" style="text-decoration: none;">
            Theo Dõi
        </a>
        @endauth
    </div>
</div>
