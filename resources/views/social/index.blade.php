@extends('dashboard')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/social.css') }}">
    <div class="gird">
        @foreach ($posts as $post)
            @include('posts.post_card', ['post' => $post])
        @endforeach
            </div>
    @foreach ($posts as $post)
        @include('partials.post_modals', ['post' => $post])

    @endforeach
@endsection