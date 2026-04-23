@extends('dashboard')

@section('content')
<div class="main-container">

    <!-- Header -->
    <div class="header">
        <h3>User List</h3>
        <a href="{{ route('user.createUser') }}" class="btn-add">Add New User</a>
    </div>

    <!-- Header cột -->
    <div class="card-header">
        <div class="col img">Ảnh hồ sơ</div>
        <div class="col name">Tên</div>
        <div class="col author">Username</div>
        <div class="col detail">Email</div>
        <div class="col date">Ngày tạo</div>
        <div class="col actions">Action</div>
    </div>

    <!-- Data -->
    @foreach($users as $user)
    <div class="card-item">

        <!-- Ảnh -->
        <div class="col img">
            <img src="https://via.placeholder.com/60">
        </div>

        <!-- Tên -->
        <div class="col name">
            {{ $user->name }}
        </div>

        <!-- Username -->
        <div class="col author">
            {{ $user->username ?? $user->email }}
        </div>

        <!-- Email -->
        <div class="col detail">
            {{ $user->email }}
        </div>

        <!-- Ngày tạo -->
        <div class="col date">
            {{ $user->created_at->format('d-M, Y') }}
        </div>

        <!-- Action -->
        <div class="col actions">
            <a href="{{ route('user.updateUser', ['id' => $user->id]) }}">✏️</a>

            <form action="{{ route('user.deleteUser', $user->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit">🗑️</button>
            </form>
        </div>

    </div>
    @endforeach

</div>
@endsection