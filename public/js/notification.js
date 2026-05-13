
function removeUnreadUI(element) {
    // 1. Tước cái nền xanh lơ, trả lại màu xám khi hover
    element.classList.remove('bg-primary', 'bg-opacity-10');
    element.classList.add('noti-hover');

    // 2. Chuyển chữ của cục thời gian từ in đậm màu xanh sang màu xám mờ
    let timeText = element.querySelector('.text-primary.fw-bold');
    if (timeText) {
        timeText.classList.remove('text-primary', 'fw-bold');
        timeText.classList.add('text-muted', 'fw-medium');
    }

    // 3. Đập bỏ cái dấu chấm xanh nhỏ xíu ở góc phải
    let blueDot = element.querySelector('.bg-primary.rounded-circle.flex-shrink-0');
    if (blueDot) {
        blueDot.remove();
    }

    // 4. (Tuyệt chiêu) Trừ đi 1 số trên cái chuông thông báo đỏ đỏ ở Menu
    let bellBadge = document.querySelector('.menu-item .bg-danger');
    if (bellBadge) {
        let currentCount = parseInt(bellBadge.innerText);
        if (currentCount > 1) {
            // Nếu còn nhiều hơn 1 thì trừ đi 1
            bellBadge.innerText = currentCount - 1;
        } else {
            // Nếu là thông báo cuối cùng thì gỡ luôn cái cục màu đỏ
            bellBadge.remove();
        }
    }
}

function markAllAsRead() {
    // Lấy token bảo mật của Laravel (bắt buộc phải có thẻ meta csrf-token trên thẻ <head>)
    let token = document.querySelector('meta[name="csrf-token"]');
    if (!token) {
        return;
    }

    // Gọi API ngầm xuống Controller bằng phương thức POST
    fetch('/notifications/mark-as-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token.getAttribute('content')
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // 1. DỌN SẠCH CHẤM ĐỎ Ở ICON CHUÔNG MENU
                let bellBadge = document.querySelector('.menu-item .bg-danger');
                if (bellBadge) bellBadge.remove();

                // 2. LỘT CÁI NỀN XANH LƠ CỦA TẤT CẢ THÔNG BÁO, TRẢ VỀ NỀN TRẮNG
                let unreadItems = document.querySelectorAll('.bg-opacity-10');
                unreadItems.forEach(item => {
                    item.classList.remove('bg-primary', 'bg-opacity-10');
                    item.classList.add('noti-hover'); // Gắn lại hiệu ứng hover bình thường

                    // Chữ thời gian đang in đậm màu xanh -> Đổi thành màu xám mờ
                    let timeText = item.querySelector('.text-primary.fw-bold');
                    if (timeText) {
                        timeText.classList.remove('text-primary', 'fw-bold');
                        timeText.classList.add('text-muted', 'fw-medium');
                    }
                });

                // 3. ĐẬP VỠ MẤY CÁI CHẤM XANH NHỎ XÍU GÓC PHẢI THÔNG BÁO
                // Lọc những cái div có class này trong vùng chứa thông báo
                let blueDots = document.querySelectorAll('.bg-primary.rounded-circle[style*="width: 12px"]');
                blueDots.forEach(dot => dot.remove());
            }
        })
        .catch(error => console.error('Lỗi gọi API nè Pro:', error));
}
