<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>DoANBE2 - Chào mừng</title>
    <!-- Nhớ link tới file CSS của Pro nha -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>

    <!-- HEADER -->
    <header class="header">
        <div class="logo">ESPACE</div>

        <nav class="menu">
            <a href="#">Giới thiệu</a>
            <a href="#">Tin tức</a>
            <!-- Đổi button thành thẻ <a> và trỏ thẳng vô route của Laravel -->
            <a href="{{ route('login') }}" class="button-login" style="text-decoration: none;">Đăng nhập</a>
            <a href="{{ route('user.createUser') }}" class="button-register" style="text-decoration: none;">Đăng ký</a>
        </nav>
    </header>

    <main>

        <!-- HERO -->
        <section class="hero">
            <div class="hero-left">
                <div class="bg-box"></div>
                <img src="/img/welcome/food1.jpg" class="img main">
                <img src="/img/welcome/food2.jpg" class="img small top">
                <img src="/img/welcome/food3.jpg" class="img small left">
                <img src="/img/welcome/food4.jpg" class="img small bottom">
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
                <!-- Nút Khám phá cũng nên cho nhảy vô trang Login để bắt user đăng nhập mới cho coi -->
                <a href="{{ route('login') }}" class="button-register" style="text-decoration: none;">Khám phá ngay</a>
            </div>
        </section>

        <!-- SECTION 2 -->
        <section class="section section-blue">
            <div class="content left">
                <h2>Lưu lại ý tưởng bạn thích</h2>
                <p>
                    Thu thập nội dung bạn yêu thích<br>
                    để bạn có thể quay lại xem sau.
                </p>
                <a href="{{ route('login') }}" class="button-login" style="text-decoration: none;">Bắt đầu</a>
            </div>

            <div class="gallery">
                <img src="/img/welcome/car.jpg" class="item tall">
                <img src="/img/welcome/cat1.jpg" class="item">
                <img src="/img/welcome/cat2.jpg" class="item">
                <img src="/img/welcome/city.jpg" class="item wide">
            </div>
        </section>

    </main>

    <footer class="footer">
        <a href="#">Về chúng tôi</a>
        <a href="#">Điều khoản</a>
        <a href="#">Chính sách</a>
        <a href="#">Trợ giúp</a>
    </footer>

    <!-- Đã dọn sạch sẽ đống HTML và Javascript của Popup ở đây -->

</body>

</html>