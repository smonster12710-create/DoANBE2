@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">

<div class="profile-page">
    <div class="profile-container">

        <div class="list-card">

            <div class="list-header">
                <h2>{{ $title }}</h2>

                <div class="list-search-box">
                    <input type="text" id="userSearchInput" placeholder="Tìm kiếm...">
                </div>
            </div>

            @if($users->count() > 0)

            <div class="user-list-grid" id="userListGrid">
                @foreach($users as $item)
                <a href="{{ route('profile.show', $item->username) }}" class="user-list-item">
                    <img src="{{ $item->avatar_url ? asset($item->avatar_url) : asset('img/user/user.jpg') }}" alt="avatar">

                    <div>
                        <strong>{{ $item->fullname ?? 'Người dùng' }}</strong>
                        <span>{{ '@' . $item->username }}</span>
                    </div>
                </a>
                @endforeach
            </div>

            @else

            <div class="empty-post-box">
                Chưa có dữ liệu.
            </div>

            @endif

        </div>
        <script>
            const searchInput = document.getElementById('userSearchInput');
            const userItems = document.querySelectorAll('.user-list-item');

            searchInput.addEventListener('input', function() {
                const keyword = this.value.toLowerCase().trim();

                userItems.forEach(function(item) {
                    const text = item.innerText.toLowerCase();

                    if (text.includes(keyword)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        </script>
    </div>
</div>
@endsection