document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // 1. HÀM CẬP NHẬT CHUÔNG
    window.updateBellBadge = function (change) {
        let bellBadge = document.getElementById('notification-badge');
        if (!bellBadge) return;
        let currentCount = parseInt(bellBadge.innerText.replace('99+', '99')) || 0;
        let newCount = currentCount + change;
        if (newCount > 0) {
            bellBadge.innerText = newCount > 99 ? '99+' : newCount;
            bellBadge.style.display = 'inline-block';
        } else {
            bellBadge.innerText = '0';
            bellBadge.style.display = 'none';
        }
    };

    // 2. HÀM CLICK ĐỌC THÔNG BÁO VÀ CHUYỂN TRANG
    window.removeUnreadUI = function (element, event) {
        if (event) event.preventDefault(); // Chốt chặn chống văng JSON

        const url = element.getAttribute('data-href') || element.getAttribute('href');

        if (!url || url === 'javascript:void(0);') return;

        fetch(url, {
            method: 'GET', // Click link đọc là GET
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // UI: Tước màu xanh báo hiệu đã đọc
                    element.classList.remove('bg-primary', 'bg-opacity-10');
                    element.classList.add('noti-hover');
                    element.querySelector('.text-primary')?.classList.replace('text-primary', 'text-muted');
                    element.querySelector('.fw-bold')?.classList.replace('fw-bold', 'fw-medium');
                    element.querySelector('.bg-primary.rounded-circle')?.remove();

                    updateBellBadge(-1);

                    // Chuyển trang tới bài viết
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    }
                } else {
                    // BÀI VIẾT BỊ XÓA -> BUNG TOAST BÁO LỖI
                    if (typeof showToastJS === 'function') {
                        showToastJS(data.message, 'error');
                    }

                    // DỌN RÁC GIAO DIỆN
                    const notiItem = element.closest('.noti-item');
                    if (notiItem) {
                        if (notiItem.classList.contains('bg-opacity-10')) {
                            updateBellBadge(-1);
                        }
                        notiItem.remove();
                    }
                }
            })
            .catch(err => console.error("Lỗi fetch đọc thông báo:", err));
    }

    // 3. ĐÁNH DẤU ĐỌC TẤT CẢ
    window.markAllAsRead = function () {
        fetch('/notifications/mark-as-read', {
            method: 'POST', // Đổi data thì xài POST
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    let bellBadge = document.getElementById('notification-badge');
                    if (bellBadge) {
                        bellBadge.innerText = '0';
                        bellBadge.style.display = 'none';
                    }
                    document.querySelectorAll('.noti-item').forEach(item => {
                        if (item.classList.contains('bg-opacity-10')) {
                            item.classList.remove('bg-primary', 'bg-opacity-10');
                            item.classList.add('noti-hover');
                            item.querySelector('.text-primary')?.classList.replace('text-primary', 'text-muted');
                            item.querySelector('.fw-bold')?.classList.replace('fw-bold', 'fw-medium');
                            item.querySelector('.rounded-circle[style*="width: 12px"]')?.remove();

                            let dropdownMenu = item.querySelector('.dropdown-menu');
                            let notiId = item.dataset.notiId;
                            if (dropdownMenu && !dropdownMenu.querySelector('[data-type="unread"]')) {
                                let li = document.createElement('li');
                                li.innerHTML = `<form action="/notifications/${notiId}/unread" method="POST" class="m-0 ajax-noti-form" data-type="unread"><input type="hidden" name="_token" value="${csrfToken}"><button type="submit" class="dropdown-item py-2"><i class="fas fa-circle text-primary me-2" style="font-size: 10px;"></i> Đánh dấu chưa đọc</button></form>`;
                                dropdownMenu.prepend(li);
                            }
                        }
                    });

                    if (typeof showToastJS === 'function') {
                        showToastJS('Đã đánh dấu đọc tất cả!', 'success');
                    }
                }
            })
            .catch(err => console.error("Lỗi fetch đọc tất cả:", err));
    }

    // 4. XỬ LÝ FORM XÓA & CHƯA ĐỌC
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form.classList.contains('ajax-noti-form')) return;

        e.preventDefault();

        const notiItem = form.closest('.noti-item');
        const type = form.dataset.type;

        if (type === 'delete' && !confirm('Pro chắc muốn xóa hông?')) return;

        fetch(form.action, {
            method: 'POST', // Form submit bắt buộc phải là POST
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (type === 'delete') {
                        notiItem.remove();
                        updateBellBadge(-1);
                        if (typeof showToastJS === 'function') showToastJS('Xóa thông báo thành công!', 'success');
                    } else if (type === 'unread') {
                        notiItem.classList.add('bg-primary', 'bg-opacity-10');
                        notiItem.classList.remove('noti-hover');
                        if (!notiItem.querySelector('.rounded-circle[style*="width: 12px"]')) {
                            let dot = document.createElement('div');
                            dot.className = 'bg-primary rounded-circle flex-shrink-0';
                            dot.style.cssText = "width: 12px; height: 12px; margin-right: 10px;";
                            notiItem.querySelector('.dropdown').before(dot);
                        }
                        form.remove();
                        updateBellBadge(1);
                        if (typeof showToastJS === 'function') showToastJS('Đã chuyển sang chưa đọc!', 'success');
                    }
                } else {
                    if (typeof showToastJS === 'function') showToastJS(data.message || 'Lỗi rồi Pro ơi!', 'error');
                    if (type === 'delete') notiItem.remove();
                }
            })
            .catch(err => console.error("Lỗi fetch submit form:", err));
    });
});