@extends('dashboard')

@section('content')
<div class="container" style="max-width: 600px; margin-top: 20px;">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Những người đã thích bài viết</h5>
        </div>
        <div class="card-body p-0">
            <ul class="list-group list-group-flush">
                @forelse($post->likedByUsers as $user)
                    <li class="list-group-item d-flex align-items-center">
                        <img src="{{ $user->avatar ?? 'https://i.pravatar.cc/40?u='.$user->id }}" 
                            class="rounded-circle me-3" 
                            style="width: 40px; height: 40px; object-fit: cover;">
                        <div>
                            <strong class="d-block">{{ $user->username }}</strong>
                            <small class="text-muted">{{ $user->fullname }}</small>
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-center py-4">
                        <p class="text-muted mb-0">Chưa có ai thích bài viết này.</p>
                    </li>
                @endforelse
            </ul>
        </div>
        <div class="card-footer bg-white border-top-0">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">Quay lại</a>
        </div>
    </div>
</div>
@endsection