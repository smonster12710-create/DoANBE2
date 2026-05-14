@extends('dashboard')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4 fw-bold">Người đang theo dõi</h4>
    
    <div class="row">
        @forelse ($followings as $following)
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card p-3 d-flex flex-row align-items-center justify-content-between shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <img src="{{ $following->avatar_url ? asset($following->avatar_url) : 'https://i.pravatar.cc/50?u='.$following->id }}" 
                             class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                        <div class="ms-3">
                            <strong class="d-block">{{ $following->fullname }}</strong>
                            <small class="text-muted">{{ $following->posts->count() }} bài viết</small>
                        </div>
                    </div>
                    
                    {{-- Nút Hủy Theo Dõi nhanh --}}
                    <form action="{{ route('follow.toggle', $following->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary fw-bold">
                            Đang theo dõi
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <p class="text-muted">Bạn chưa theo dõi ai cả.</p>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $followings->links() }}
    </div>
</div>
@endsection