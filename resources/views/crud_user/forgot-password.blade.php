<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - ESPACE</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f4f4;
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .forgot-wrapper {
            width: 100%;
            max-width: 1200px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 80px;
            padding: 20px;
        }

        .left-content {
            flex: 1;
        }

        .logo {
            font-size: 72px;
            font-weight: bold;
            color: #ef0028;
            margin-bottom: 20px;
        }

        .description {
            font-size: 28px;
            color: #222;
            line-height: 1.5;
            max-width: 620px;
        }

        .forgot-box {
            width: 430px;
            background: white;
            border-radius: 20px;
            padding: 32px 38px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .forgot-title {
            text-align: center;
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #111;
            line-height: 1.1;
        }

        .forgot-subtitle {
            text-align: center;
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .input-group {
            margin-bottom: 22px;
        }

        .input-group input {
            width: 100%;
            height: 54px;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 0 18px;
            font-size: 17px;
            outline: none;
        }

        .input-group input:focus {
            border-color: #ef0028;
        }

        .forgot-btn {
            width: 100%;
            height: 56px;
            border: none;
            border-radius: 999px;
            background: #ef0028;
            color: white;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .forgot-btn:hover {
            opacity: 0.9;
        }

        .back-login {
            text-align: center;
            margin-top: 28px;
        }

        .back-login a {
            color: #666;
            text-decoration: none;
            font-size: 16px;
        }

        .back-login a:hover {
            color: #ef0028;
        }

        @media (max-width: 1000px) {

            .forgot-wrapper {
                flex-direction: column;
                justify-content: center;
                text-align: center;
            }

            .left-content {
                display: none;
            }

            .forgot-box {
                width: 100%;
                max-width: 460px;
            }

            .forgot-title {
                font-size: 44px;
            }
        }

        .toast {
            position: fixed;
            top: 24px;
            right: 24px;
            padding: 14px 20px;
            border-radius: 10px;
            color: #fff;
            font-weight: bold;
            z-index: 9999;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .toast.success {
            background: #16a34a;
        }

        .toast.error {
            background: #dc2626;
        }

        .captcha-box {
            height: 46px;
            border-radius: 10px;
            background: #f1f1f1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #ef0028;
        }
    </style>
</head>

<body>

    @if(session('success'))
    <div class="toast success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="toast error">
        {{ session('error') }}
    </div>
    @endif
    <div class="forgot-wrapper">

        <div class="left-content">
            <div class="logo">ESPACE</div>

            <div class="description">
                Khôi phục tài khoản của bạn và tiếp tục kết nối với cộng đồng yêu thích.
            </div>
        </div>

        <div class="forgot-box">

            <div class="forgot-title">
                Quên mật khẩu
            </div>

            <div class="forgot-subtitle">
                Nhập email đã đăng ký để đặt lại mật khẩu.
            </div>

            @if(session('forgot_email'))

            {{-- FORM ĐỔI MẬT KHẨU --}}

            <form method="POST"
                action="{{ route('forgot.password.update') }}">

                @csrf

                <div class="input-group">
                    <input
                        type="email"
                        value="{{ session('forgot_email') }}"
                        readonly>
                </div>

                <div class="input-group">
                    <input
                        type="password"
                        name="password"
                        placeholder="Mật khẩu mới"
                        required>
                </div>

                <div class="input-group">
                    <input
                        type="password"
                        name="password_confirmation"
                        placeholder="Nhập lại mật khẩu mới"
                        required>
                </div>

                <div class="captcha-box">
                    {{ session('forgot_captcha') }}
                </div>

                <div class="input-group">
                    <input
                        type="text"
                        name="captcha"
                        placeholder="Nhập mã captcha"
                        required>
                </div>

                <button type="submit" class="forgot-btn">
                    Đổi mật khẩu
                </button>

            </form>

            @else

            {{-- FORM KIỂM TRA EMAIL --}}

            <form method="POST"
                action="{{ route('forgot.password.check') }}">

                @csrf

                <div class="input-group">
                    <input
                        type="email"
                        name="email"
                        placeholder="Email"
                        value="{{ old('email') }}"
                        required>
                </div>

                <button type="submit" class="forgot-btn">
                    Kiểm tra email
                </button>

            </form>

            @endif
            <div class="back-login">
                <a href="{{ route('login') }}">
                    ← Quay lại đăng nhập
                </a>
            </div>

        </div>

    </div>

</body>
<script>
    setTimeout(() => {

        document.querySelectorAll('.toast')
            .forEach(toast => {

                toast.style.opacity = '0';

                setTimeout(() => {
                    toast.remove();
                }, 300);

            });

    }, 3000);
</script>

</html>