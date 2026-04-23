@extends('dashboard')

@section('content')
<main class="signup-form">
    <link href="{{ asset('css/add.css') }}" rel="stylesheet">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card custom-card">

                    <h3 class="card-header text-center custom-header">Edit user</h3>

                    <div class="card-body">
                        <form action="{{ route('user.postUpdateUser') }}" method="POST">
                            @csrf

                            <input type="hidden" name="id" value="{{ $user->id }}">

                            {{-- NAME --}}
                            <div class="form-group mb-3">
                                <label>Họ và tên</label>
                                <input type="text" id="name" class="form-control custom-input"
                                    name="name" value="{{ $user->name }}">
                                @error('name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- EMAIL --}}
                            <div class="form-group mb-3">
                                <label>Email</label>
                                <input type="text" id="email_address"
                                    class="form-control custom-input"
                                    name="email" value="{{ $user->email }}">
                                @error('email')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- PASSWORD --}}
                            <div class="form-group mb-3">
                                <label>Mật khẩu</label>
                                <input type="password" id="password"
                                    class="form-control custom-input"
                                    name="password"
                                    placeholder="Nhập nếu muốn đổi mật khẩu">

                                <!-- strength bar -->
                                <div class="password-strength mt-2">
                                    <div class="password-bar"></div>
                                </div>

                                <ul class="password-rules mt-2">
                                    <li id="rule-length">Ít nhất 6 ký tự</li>
                                    <li id="rule-uppercase">Có chữ hoa</li>
                                    <li id="rule-number">Có số</li>
                                    <li id="rule-special">Có ký tự đặc biệt</li>
                                </ul>

                                <small class="text-warning">Độ mạnh mật khẩu:</small>

                                @error('password')
                                <span class="text-danger d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- CONFIRM --}}
                            <div class="form-group mb-3">
                                <label>Xác nhận mật khẩu</label>
                                <input type="password"
                                    id="password_confirmation"
                                    class="form-control custom-input"
                                    name="password_confirmation">

                                <small id="confirm-message"></small>
                            </div>

                            {{-- BUTTON --}}
                            <div class="d-grid mx-auto">
                                <button type="submit" class="btn custom-btn">
                                    Sửa thông tin người dùng
                                </button>
                            </div>

                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>
@endsection