<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - ESPACE</title>
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
            box-shadow: 0 0 0 0.25 row rgba(230, 0, 35, 0.25);
        }
    </style>
</head>

<body style="background-color: #fafafa;">

    <main class="d-flex align-items-center" style="min-height: 100vh;">
        <div class="container">
            <div class="row align-items-center justify-content-center">

                <div class="col-lg-6 col-md-5 d-none d-md-block text-start pe-lg-5">
                    <h1 class="text-brand display-4 fw-bolder mb-2" style="letter-spacing: -1.5px;">ESPACE</h1>
                    <p class="fs-4 fw-normal text-dark">Tìm kiếm những ý tưởng mới mẻ và lưu lại những gì bạn yêu thích.
                    </p>
                </div>

                <div class="col-lg-4 col-md-7 col-sm-9">
                    <div class="card shadow border-0 rounded-4 p-2">
                        <div class="card-body p-4 text-center">
                            <h2 class="fw-bold mb-4">Đăng nhập</h2>
                            <form method="POST" action="{{ route('user.authUser') }}">
                                @csrf
                                <div class="form-floating mb-3 text-start">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                                        placeholder="Email" value="{{ old('email') }}" required autofocus>
                                    <label for="email">Email</label>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-floating mb-3 text-start">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password"
                                        placeholder="Mật khẩu" required>
                                    <label for="password">Mật khẩu</label>
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-brand btn-lg fw-bold rounded-pill">Đăng
                                        nhập</button>
                                </div>
                                <a href="#" class="text-decoration-none small text-secondary">Quên mật khẩu?</a>
                                <hr class="my-4">
                                <p class="mb-0">Chưa có tài khoản?</p>
                                <a href="{{ route('user.createUser') }}" class="btn btn-outline-secondary btn-md fw-bold rounded-pill mt-2 px-4">Đăng ký
                                    ngay</a>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</body>

</html>