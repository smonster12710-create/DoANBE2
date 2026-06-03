@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/social.css') }}?v={{ time() }}">

@include('partials.social_topbar')

<div class="grid">
    @foreach ($posts as $post)
    @include('posts.post_card', ['post' => $post])
    @endforeach
</div>

@foreach ($posts as $post)
@include('partials.post_modals', ['post' => $post])
@endforeach
@endsection

@push('scripts')
<script src="{{ asset('js/index.js') }}?v={{ time() }}"></script>
@endpush