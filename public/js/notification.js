document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

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

    window.removeUnreadUI = function (element, event) {
        if (event) event.preventDefault();
        fetch(element.getAttribute('href'), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    element.classList.remove('bg-primary', 'bg-opacity-10');
                    element.classList.add('noti-hover');
                    element.querySelector('.text-primary')?.classList.replace('text-primary', 'text-muted');
                    element.querySelector('.fw-bold')?.classList.replace('fw-bold', 'fw-medium');
                    element.querySelector('.bg-primary.rounded-circle')?.remove();
                    updateBellBadge(-1);
                }
            });
    }

    window.markAllAsRead = function () {
        fetch('/notifications/mark-as-read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
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
                }
            });
    }

    // BẮT FORM XÓA / UNREAD MƯỢT MÀ
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form.classList.contains('ajax-noti-form')) return;

        e.preventDefault();

        const notiItem = form.closest('.noti-item');
        const type = form.dataset.type;

        if (type === 'delete' && !confirm('Pro chắc muốn xóa hông?')) return;

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (type === 'delete') {
                        notiItem.remove();
                        updateBellBadge(-1);
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
                    }
                }
            })
            .catch(err => console.error("Lỗi:", err));
    });
});