@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">

@include('partials.social_topbar')

{{-- Trang thêm/sửa user trong khu vực quản trị --}}
<div class="admin-page">
    <div class="admin-container">

        {{-- Header: tiêu đề trang và nút quay lại danh sách --}}
        <div class="admin-header">
            <div>
                <div class="admin-logo">ESPACE</div>

                <h1>
                    {{ $user->exists ? 'Sửa người dùng' : 'Thêm người dùng' }}
                </h1>

                <p>Quản lý thông tin tài khoản trong hệ thống.</p>
            </div>

            <a href="{{ route('admin.users.index') }}" class="admin-btn gray">
                ← Quay lại
            </a>
        </div>

        <div class="admin-card">

            {{-- Hiển thị lỗi validate đầu tiên nếu form submit không hợp lệ --}}
            @if($errors->any())
                <div class="admin-toast error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form
                method="POST"
                action="{{ $user->exists ? route('admin.users.update', $user->id) : route('admin.users.store') }}"
                class="admin-form"
            >
                @csrf
                @if($user->exists)
                    <input type="hidden" name="updated_at_token" value="{{ optional($user->updated_at)->timestamp }}">
                @endif

                {{-- Nhóm input thông tin tài khoản --}}
                <div class="form-grid">
                    <div class="form-group">
                        <label>Họ tên</label>
                        <input type="text" name="fullname" value="{{ old('fullname', $user->fullname) }}">
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="form-group">
                        <label>
                            Mật khẩu
                            @if($user->exists)
                                <span>Không nhập nếu không đổi</span>
                            @endif
                        </label>

                        <input type="password" name="password" {{ $user->exists ? '' : 'required' }}>
                    </div>

                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select name="is_active" required>
                            <option value="1" {{ old('is_active', $user->is_active ?? 1) == 1 ? 'selected' : '' }}>
                                Hoạt động
                            </option>

                            <option value="0" {{ old('is_active', $user->is_active ?? 1) == 0 ? 'selected' : '' }}>
                                Khóa
                            </option>
                        </select>
                    </div>
                </div>

                {{-- Nút hủy và nút submit form --}}
                <div class="form-actions">
                    <a href="{{ route('admin.users.index') }}" class="admin-btn gray">
                        Hủy
                    </a>

                    <button type="submit" class="admin-btn primary">
                        {{ $user->exists ? 'Lưu thay đổi' : 'Thêm người dùng' }}
                    </button>
                </div>
            </form>

        </div>

    </div>
</div>
@endsection
