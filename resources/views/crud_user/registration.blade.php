<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - ESPACE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .text-brand {
            color: #e60023 !important;
        }

        .btn-brand {
            background-color: #e60023 !important;
            color: white !important;
            border: none;
        }

        .btn-brand:hover {
            background-color: #ad081b !important;
        }

        .form-control:focus {
            border-color: #e60023;
            box-shadow: 0 0 0 0.25rem rgba(230, 0, 35, 0.25);
        }

        .custom-toast {
            position: fixed;
            top: 24px;
            right: 24px;
            min-width: 280px;
            max-width: 420px;
            padding: 14px 18px;
            border-radius: 12px;
            color: white;
            font-size: 15px;
            font-weight: 600;
            z-index: 999999;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.35s ease;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.18);
        }

        .custom-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast-success {
            background: #16a34a;
        }

        .toast-error {
            background: #e60023;
        }
    </style>
</head>

<body style="background-color: #fafafa;">
    @if(session('success') || session('error') || $errors->any())
    <div id="toast-message"
        class="custom-toast {{ session('success') ? 'toast-success' : 'toast-error' }}">
        {{ session('success') ?? session('error') ?? $errors->first() }}
    </div>
    @endif


    <main class="d-flex align-items-center py-5" style="min-height: 100vh;">
        <div class="container">
            <div class="row align-items-center justify-content-center">

                <div class="col-lg-5 col-md-7 col-sm-10">
                    <div class="card shadow border-0 rounded-4 p-2">
                        <div class="card-body p-4 text-center">
                            <h1 class="text-brand fw-bolder mb-3">ESPACE</h1>
                            <h3 class="fw-bold mb-4">Tạo tài khoản</h3>

                            <form action="{{ route('user.postUser') }}" method="POST">
                                @csrf
                                <div class="form-floating mb-3 text-start">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                                        placeholder="Họ và tên" value="{{ old('name') }}" required>
                                    <label for="name">Họ và tên</label>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-floating mb-3 text-start">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                                        placeholder="Email" value="{{ old('email') }}" required>
                                    <label for="email">Email</label>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3 text-start">
                                        <div class="form-floating">
                                            <select class="form-select" id="gender" name="gender">
                                                <option value="" {{ old('gender') == '' ? 'selected' : '' }}>Chọn</option>
                                                <option value="1" {{ old('gender') == '1' ? 'selected' : '' }}>Nam</option>
                                                <option value="2" {{ old('gender') == '2' ? 'selected' : '' }}>Nữ</option>
                                                <option value="3" {{ old('gender') == '3' ? 'selected' : '' }}>Khác</option>
                                            </select>
                                            <label for="gender">Giới tính</label>
                                            @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3 text-start">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="phone" name="phone" placeholder="SĐT" value="{{ old('phone') }}"> <label for="phone">SĐT</label>
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="form-floating mb-3 text-start">
                                    <input type="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        id="password"
                                        name="password"
                                        placeholder="Mật khẩu"
                                        required>
                                    <label for="password">Mật khẩu</label>
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-floating mb-4 text-start">
                                    <input type="password"
                                        class="form-control @error('password_confirmation') is-invalid @enderror"
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        placeholder="Xác nhận"
                                        required>
                                    <label for="password_confirmation">Xác nhận mật khẩu</label>
                                    @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-brand btn-lg fw-bold rounded-pill">Tiếp
                                        tục</button>
                                </div>
                                <p class="small text-muted mt-3">Bằng cách tiếp tục, bạn đồng ý với Điều khoản dịch vụ
                                    của ESPACE.</p>
                                <hr>
                                <a href="{{ route('login') }}" class="text-decoration-none fw-bold text-dark">Đã có tài
                                    khoản? Đăng nhập</a>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('toast-message');

            if (toast) {
                setTimeout(() => {
                    toast.classList.add('show');
                }, 100);

                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3500);
            }
        });
    </script>
</body>

</html>