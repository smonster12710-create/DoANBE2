@extends('dashboard')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/social.css') }}">
    <!-- TOPBAR -->
    <div class="topbar" style="display: flex; gap: 15px; align-items: center;">
        <div style="position: relative; flex: 1;">
            <input id="search-input" class="search" style="width: 100%;" placeholder="Tìm kiếm....." autocomplete="off">
            <div id="search-results" class="search-results-dropdown"></div>
        </div>
        <!-- 2 Nút bên phải -->
        <div style="display: flex; gap: 10px;">
            <button class="btn-top">Bạn Bè</button>
            <button class="btn-top">Theo Dõi</button>
        </div>
    </div>
    <div class="grid">
        @foreach ($posts as $post)
            @include('posts.post_card', ['post' => $post])
        @endforeach
    </div>

    @foreach ($posts as $post)
        @include('partials.post_modals', ['post' => $post])
    @endforeach
@endsection
