@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">

@include('partials.social_topbar')

{{-- Trang danh sach va quan ly user trong khu vuc admin --}}
<div class="admin-page">
    <div class="admin-container">

        {{-- HEADER --}}
        <div class="admin-header">
            <div>
                <div class="admin-logo">ESPACE</div>

                <h1>Quản trị người dùng</h1>

                <p>
                    Thêm, sửa, khóa và quản lý tài khoản người dùng.
                </p>
            </div>

            <a href="{{ route('admin.users.create') }}" class="admin-btn primary">
                + Thêm người dùng
            </a>
        </div>

        {{-- THÔNG BÁO --}}
        @if(session('success'))
            <div class="admin-toast success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="admin-toast error">
                {{ session('error') }}
            </div>
        @endif

        {{-- CARD DANH SÁCH --}}
        <div class="admin-card">

            {{-- TÌM KIẾM --}}
            <form method="GET" action="{{ route('admin.users.index') }}" class="admin-search">
                <input
                    type="text"
                    name="keyword"
                    value="{{ $keyword ?? '' }}"
                    placeholder="Tìm kiếm người dùng theo tên, username hoặc email..."
                >

                <button type="submit">
                    Tìm kiếm
                </button>
            </form>

            {{-- BẢNG NGƯỜI DÙNG --}}
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Người dùng</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($users as $item)
                            <tr>
                                {{-- NGƯỜI DÙNG --}}
                                <td>
                                    <div class="user-cell">
                                        <img
                                            src="{{ $item->avatar_url ? asset($item->avatar_url) : asset('img/user/user.jpg') }}"
                                            alt="avatar"
                                        >

                                        <div>
                                            <strong>
                                                {{ $item->fullname ?? $item->name ?? 'Người dùng' }}
                                            </strong>

                                            <span>
                                                {{ '@' . ($item->username ?? 'user') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- EMAIL --}}
                                <td>
                                    {{ $item->email }}
                                </td>

                                {{-- VAI TRÒ --}}
                                <td>
                                    @if($item->role == 'admin')
                                        <span class="badge red">
                                            Admin
                                        </span>
                                    @else
                                        <span class="badge gray">
                                            Người dùng
                                        </span>
                                    @endif
                                </td>

                                {{-- TRẠNG THÁI --}}
                                <td>
                                    @if($item->is_active)
                                        <span class="badge green">
                                            Hoạt động
                                        </span>
                                    @else
                                        <span class="badge dark">
                                            Đã khóa
                                        </span>
                                    @endif
                                </td>

                                {{-- NGÀY TẠO --}}
                                <td>
                                    {{ $item->created_at ? $item->created_at->format('d/m/Y') : 'Chưa có' }}
                                </td>

                                {{-- THAO TÁC --}}
                                <td>
                                    <div class="action-row">

                                        {{-- SỬA --}}
                                        <a
                                            href="{{ route('admin.users.edit', $item->id) }}"
                                            class="small-btn edit"
                                        >
                                            Sửa
                                        </a>

                                        {{-- KHÓA / MỞ KHÓA --}}
                                        <form
                                            method="POST"
                                            action="{{ route('admin.users.toggleStatus', $item->id) }}"
                                        >
                                            @csrf

                                            <button type="submit" class="small-btn lock">
                                                {{ $item->is_active ? 'Khóa' : 'Mở khóa' }}
                                            </button>
                                        </form>

                                        {{-- XÓA --}}
                                        <form
                                            method="POST"
                                            action="{{ route('admin.users.destroy', $item->id) }}"
                                            onsubmit="return confirm('Bạn có chắc muốn xóa người dùng này không?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="small-btn delete">
                                                Xóa
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-row">
                                    Không tìm thấy người dùng nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PHÂN TRANG CUSTOM --}}
            @if($users->lastPage() > 1)
                <div class="admin-pagination-custom">

                    @if ($users->onFirstPage())
                        <span class="page-btn disabled">
                            Trang trước
                        </span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}" class="page-btn">
                            Trang trước
                        </a>
                    @endif

                    <span class="page-info">
                        Trang {{ $users->currentPage() }} / {{ $users->lastPage() }}
                    </span>

                    @if ($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}" class="page-btn">
                            Trang sau
                        </a>
                    @else
                        <span class="page-btn disabled">
                            Trang sau
                        </span>
                    @endif

                </div>
            @endif

        </div>

    </div>
</div>
@endsection
