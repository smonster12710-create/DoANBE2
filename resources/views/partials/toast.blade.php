{{-- Container cố định ở góc dưới bên phải --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">

    {{-- KHUNG TOAST DÙNG CHUNG CHO JS --}}
    <div id="js-toast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" style="font-size: 15px;">
                <i id="js-toast-icon" class="fas fa-check-circle me-2"></i>
                <span id="js-toast-text"></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                </div>

    {{-- TOAST DÀNH CHO BACKEND SESSION (Tự động hiện) --}}
    @if(session('success') || session('error'))
        @php
            $isSuccess = session('success') ? true : false;
            $bgClass = $isSuccess ? 'text-bg-success' : 'text-bg-danger';
            $iconClass = $isSuccess ? 'fa-check-circle' : 'fa-exclamation-triangle';
            $message = session('success') ?? session('error');
        @endphp

        <div class="toast align-items-center border-0 {{ $bgClass }} show" role="alert" aria-live="assertive" aria-atomic="true"
            data-bs-delay="3000">
            <div class="d-flex">
                <div class="toast-body" style="font-size: 15px;">
                    <i class="fas {{ $iconClass }} me-2"></i>
                    <span>{{ $message }}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    @endif
    </div>
    {{-- SCRIPT XỬ LÝ TOAST NẰM GỌN TRONG NÀY LUÔN --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tự động bật Toast nếu có session từ Backend truyền qua
            var toastElList = [].slice.call(document.querySelectorAll('.toast.show'))
            var toastList = toastElList.map(function (toastEl) {
                return new bootstrap.Toast(toastEl, { delay: 3000 }).show();
            });

            // Định nghĩa hàm showToastJS dùng chung cho toàn hệ thống
            window.showToastJS = function (message, type = 'success') {
                const toastEl = document.getElementById('js-toast');
                if (!toastEl) {
                    console.error("Không tìm thấy thẻ HTML của Toast!");
                    return;
                }

                const toastText = document.getElementById('js-toast-text');
                const toastIcon = document.getElementById('js-toast-icon');

                // Đổi màu và Icon theo type
                if (type === 'success') {
                    toastEl.className = 'toast align-items-center border-0 text-bg-success';
                    toastIcon.className = 'fas fa-check-circle me-2';
                } else {
                    toastEl.className = 'toast align-items-center border-0 text-bg-danger';
                    toastIcon.className = 'fas fa-exclamation-triangle me-2';
                }

                toastText.innerText = message;

                // Gọi API của Bootstrap 5 để hiển thị
                if (typeof bootstrap !== 'undefined') {
                    const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });
                    bsToast.show();
                } else {
                    console.error("Thiếu thư viện Bootstrap JS, Toast không chạy được!");
                    alert(message); // Fallback: Nếu lỗi JS thì dùng Alert tạm
                }
            };
    });
</script>