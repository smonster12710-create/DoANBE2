@extends('dashboard')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">

    <div class="profile-page">
        <div class="profile-container">

            @include('partials.profile_header', ['user' => $user])

            <div class="profile-body">
                <div style="flex: 1;">
                    @include('partials.profile_sidebar', ['user' => $user])
                </div>
                <div style="flex: 2;">
                    <div class="gird">
                        @foreach ($posts as $post)
                            @include('posts.post_card', ['post' => $post])
                        @endforeach
                    </div>

                </div>


            </div>
        </div>
@endsection