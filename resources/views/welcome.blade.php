<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Trang chủ</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>

    <!-- HEADER -->
    <header class="header">
        <div class="logo">ESPACE</div>

        <nav class="menu">
            <a href="#">Giới thiệu</a>
            <a href="#">Tin tức</a>
            <button class="button-login" type="button" onclick="openLogin()">Đăng nhập</button>
            <button class="button-register" type="button" onclick="openRegister()">Đăng ký</button>

        </nav>
    </header>

    <main>

        <!-- HERO -->
        <section class="hero">

            <div class="hero-left">
                <div class="bg-box"></div>

                <img src="/img/food1.jpg" class="img main">
                <img src="/img/food2.jpg" class="img small top">
                <img src="/img/food3.jpg" class="img small left">
                <img src="/img/food4.jpg" class="img small bottom">

                <div class="overlay">
                    🔍 bữa tối với món gà dễ làm
                </div>
            </div>

            <div class="hero-right">
                <h1>Tìm kiếm ý tưởng</h1>
                <p>
                    Bạn muốn thử điều gì tiếp theo? Hãy nghĩ về ý tưởng bạn yêu thích—
                    như "bữa tối với món gà dễ làm"—và xem bạn tìm thấy gì.
                </p>
                <button>Khám phá</button>
            </div>

        </section>

        <!-- SECTION 2 -->
        <section class="section section-blue">

            <div class="content left">
                <h2>Lưu ý ý tưởng bạn thích</h2>
                <p>
                    Thu thập nội dung bạn yêu thích<br>
                    để bạn có thể quay lại xem sau.
                </p>
                <button>Khám phá</button>
            </div>

            <div class="gallery">
                <img src="/img/car.jpg" class="item tall">
                <img src="/img/cat1.jpg" class="item">
                <img src="/img/cat2.jpg" class="item">
                <img src="/img/city.jpg" class="item wide">

            </div>

        </section>

    </main>

    <footer class="footer">
        <a href="#">Về chúng tôi</a>
        <a href="#">Điều khoản</a>
        <a href="#">Chính sách</a>
        <a href="#">Trợ giúp</a>
    </footer>
    <!-- Popup đăng nhập (đăng nhập thành công ->trang mxh) -->
    <div id="loginPopup" class="popup">
        <div class="popup-box">

            <span class="close" onclick="closePopup()">×</span>

            <h2>Đăng nhập</h2>

            <form method="POST" action="/login">
                @csrf
                <input name="email" placeholder="Email">
                <input name="password" type="password" placeholder="Mật khẩu">
                <button>Đăng nhập</button>
            </form>

            <p>Chưa có tài khoản?
                <a href="#" onclick="switchToRegister()">Đăng ký</a>
            </p>

        </div>
    </div>
    <!-- Popup đăng ký -->
    <div id="registerPopup" class="popup">
        <div class="popup-box">

            <span class="close" onclick="closePopup()">×</span>

            <h2>Đăng ký</h2>

            <form method="POST" action="/register">
                @csrf
                <input name="name" placeholder="Tên">
                <input name="email" placeholder="Email">
                <input name="password" type="password" placeholder="Mật khẩu">
                <button>Đăng ký</button>
            </form>

            <p>Đã có tài khoản?
                <a href="#" onclick="switchToLogin()">Đăng nhập</a>
            </p>

        </div>
    </div>
    <script>
        function openLogin() {
            document.getElementById('loginPopup').style.display = 'flex';
        }

        function openRegister() {
            document.getElementById('registerPopup').style.display = 'flex';
        }

        function closePopup() {
            document.getElementById('loginPopup').style.display = 'none';
            document.getElementById('registerPopup').style.display = 'none';
        }

        function switchToRegister() {
            closePopup();
            openRegister();
        }

        function switchToLogin() {
            closePopup();
            openLogin();
        }
    </script>
</body>

</html>