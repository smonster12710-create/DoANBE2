
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