@extends('dashboard')
@section('styles')
<link rel="stylesheet" href="{{ asset('css/save.css') }}">
@endsection

@section('content')
<div class="saved-page">
    <h1 class="saved-title">Bài viết đã lưu</h1>

    @if($posts->count())
    <div class="grid">
        @foreach($posts as $post)
        @include('posts.post_card')
        @include('partials.post_modals', ['post' => $post])
        @endforeach
    </div>
    @else
    <div class="saved-empty-container">
        <p class="saved-empty-text">Chưa có bài viết nào được lưu.</p>
    </div>
    @endif
</div>

@endsection