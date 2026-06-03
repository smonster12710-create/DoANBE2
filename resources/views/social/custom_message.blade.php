@extends('dashboard')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="height: 80vh;">
    <div class="text-center">
        <h1 class="display-1 fw-bold text-muted">404</h1>
        <h3 class="text-dark">{{ $message }}</h3>
        <p class="text-muted">Rất tiếc, trang bạn đang tìm kiếm không tồn tại.</p>
        <a href="{{ route('social.index') }}" class="btn btn-primary mt-3">Quay lại trang chủ</a>
    </div>
</div>
@endsection