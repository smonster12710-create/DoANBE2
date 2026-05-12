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
        @endforeach
    </div>
    @else
    <div class="saved-empty">
        Chưa có bài viết nào được lưu.
    </div>
    @endif
</div>
@endsection