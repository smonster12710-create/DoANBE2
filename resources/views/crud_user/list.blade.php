@extends('dashboard')

@section('content')
<div class="main-container">
    <link href="{{ asset('css/list.css') }}" rel="stylesheet">
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
            <a href="{{ route('user.updateUser', ['id' => $user->id]) }}">
                <svg style="height: 20px; width: 20px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                    <path d="M416.9 85.2L372 130.1L509.9 268L554.8 223.1C568.4 209.6 576 191.2 576 172C576 152.8 568.4 134.4 554.8 120.9L519.1 85.2C505.6 71.6 487.2 64 468 64C448.8 64 430.4 71.6 416.9 85.2zM338.1 164L122.9 379.1C112.2 389.8 104.4 403.2 100.3 417.8L64.9 545.6C62.6 553.9 64.9 562.9 71.1 569C77.3 575.1 86.2 577.5 94.5 575.2L222.3 539.7C236.9 535.6 250.2 527.9 261 517.1L476 301.9L338.1 164z" />
                </svg>
            </a>

            <form id="delete-form-{{ $user->id }}" action="{{ route('user.deleteUser', $user->id) }}" method="POST">
                @csrf
                @method('DELETE')

                <button type="button" onclick="showDeleteModal('{{ $user->id }}')"><svg style="height: 20px; width: 20px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                        <path d="M232.7 69.9L224 96L128 96C110.3 96 96 110.3 96 128C96 145.7 110.3 160 128 160L512 160C529.7 160 544 145.7 544 128C544 110.3 529.7 96 512 96L416 96L407.3 69.9C402.9 56.8 390.7 48 376.9 48L263.1 48C249.3 48 237.1 56.8 232.7 69.9zM512 208L128 208L149.1 531.1C150.7 556.4 171.7 576 197 576L443 576C468.3 576 489.3 556.4 490.9 531.1L512 208z" />
                    </svg></button>
            </form>
        </div>

    </div>
    <hr>
    @endforeach
</div>
<div id="deleteModal" class="delete-modal">
    <div class="delete-box">
        <h3>Bạn có muốn xóa?</h3>

        <div class="modal-actions">
            <button class="btn-yes" onclick="confirmDelete()">
                Có, tôi chắc chắn
            </button>
            <button class="btn-no" onclick="closeModal()">
                Không, tôi không muốn
            </button>
        </div>
    </div>
</div>
<script src="{{ asset('js/delete.js') }}"></script>
@endsection