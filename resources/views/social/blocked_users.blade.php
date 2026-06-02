@extends('dashboard')
<div class="container">
    <h3>Danh sách người dùng đã chặn</h3>
    <ul class="list-group">
        @forelse($blockedUsers as $user)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <img src="{{ $user->avatar_src }}" width="40" class="rounded-circle">
                    <span>{{ $user->fullname }} ({{ $user->username }})</span>
                </div>
                
                <form action="{{ route('user.block', $user->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">Bỏ chặn</button>
                </form>
            </li>
        @empty
            <li class="list-group-item">Bạn chưa chặn ai cả.</li>
        @endforelse
    </ul>
</div>