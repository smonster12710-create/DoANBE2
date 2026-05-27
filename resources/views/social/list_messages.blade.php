@extends('dashboard')

@section('content')

@if(session('error'))
<div id="toast-error" style="
    position: fixed;
    bottom: 25px;
    right: 25px;
    background: #ff4d4f;
    color: white;
    opacity: 0.9;
    padding: 14px 22px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    z-index: 9999;
    font-weight: 500;
    animation: slideIn 0.4s ease;
">
    {{ session('error') }}
</div>


<style>
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(50px);
        }

        to {
            opacity: 0.5;
            transform: translateX(0);
        }
    }
</style>



<script>
    setTimeout(() => {
        const toast = document.getElementById('toast-error');
        if (toast) {
            toast.style.transition = '0.4s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(50px)';
            setTimeout(() => toast.remove(), 400);
        }
    }, 3000);
</script>

@endif
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

<link rel="stylesheet" href="{{ asset('css/list_messages.css') }}">

<div class="main-container">
    <div class="messages-sidebar">
        <div class="search-box">
            <input type="text" id="sidebar-search" placeholder="Tìm kiếm ....">
        </div>

        <div class="scrollable-list">
            {{-- Gọi file partial lúc load trang lần đầu --}}
            @include('partials.list_chat', ['conversations' => $conversations])
        </div>
    </div>
    <link rel="stylesheet" href="{{ asset('css/social.css') }}">

    <div class="grid">
        @foreach ($posts as $post)
        @include('posts.post_card', ['post' => $post])
        @endforeach
    </div>

    @foreach ($posts as $post)
    @include('partials.post_modals', ['post' => $post])

    @endforeach
    <script src="/js/chat.js?v={{ time() }}"></script>
    @endsection