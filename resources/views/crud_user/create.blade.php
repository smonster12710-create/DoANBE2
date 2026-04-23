@extends('dashboard')

@section('content')

<main class="signup-form">
    <link href="{{ asset('css/add.css') }}" rel="stylesheet">
    <script src="{{ asset('js/password.js') }}"></script>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card custom-card">

                    <h3 class="card-header text-center custom-header">Add user</h3>

                    <div class="card-body">
                        <form action="{{ route('user.postUser') }}" method="POST">
                            @csrf

                            <div class="form-group mb-3">
                                <label>Họ và tên</label>
                                <input type="text" placeholder="Nhập họ và tên" id="name"
                                    class="form-control custom-input" name="name">
                                @if ($errors->has('name'))
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                                @endif
                            </div>

                            <div class="form-group mb-3">
                                <label>Email</label>
                                <input type="text" placeholder="vd: user@gmail.com"
                                    id="email_address" class="form-control custom-input" name="email">
                                @if ($errors->has('email'))
                                <span class="text-danger">{{ $errors->first('email') }}</span>
                                @endif
                            </div>

                            <div class="form-group mb-3">
                                <label>Mật khẩu</label>
                                <input type="password" placeholder="Tạo mật khẩu mạnh"
                                    id="password" class="form-control custom-input" name="password">

                                <!-- thanh strength -->
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

                                @if ($errors->has('password'))
                                <span class="text-danger d-block">{{ $errors->first('password') }}</span>
                                @endif
                            </div>

                            <div class="form-group mb-3">
                                <label>Xác nhận mật khẩu</label>
                                <input type="password" placeholder="Nhập lại mật khẩu"
                                    id="password_confirmation"
                                    class="form-control custom-input"
                                    name="password_confirmation">

                                <small id="confirm-message"></small>
                            </div>

                            <div class="d-grid mx-auto">
                                <button type="submit" class="btn custom-btn">
                                    Thêm người dùng mới
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