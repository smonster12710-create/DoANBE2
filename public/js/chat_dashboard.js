document.addEventListener('DOMContentLoaded', function () {

    // 1. LẤY USER ID AN TOÀN TRÁNH LỖI NOT DEFINED
    let currentUserId = null;
    let chatBoxEl = document.getElementById('chat-box');

    if (chatBoxEl && chatBoxEl.dataset.user) {
        currentUserId = chatBoxEl.dataset.user;
    } else {
        currentUserId = window.currentUserId || null;
    }
    document.addEventListener('DOMContentLoaded', function () {

        // 🎯 LẤY ID USER TỪ THẺ META TOÀN CỤC
        const userMeta = document.querySelector('meta[name="user-id"]');
        let currentUserId = userMeta ? userMeta.getAttribute('content') : null;

        // Dự phòng nếu không tìm thấy meta thì tìm ở chat-box
        if (!currentUserId) {
            const chatBoxEl = document.getElementById('chat-box');
            if (chatBoxEl && chatBoxEl.dataset.user) {
                currentUserId = chatBoxEl.dataset.user;
            }
        }

        // Đẩy vào biến window toàn cục để Laravel Echo xài
        window.currentUserId = currentUserId;

        console.log('📡 [Websocket] Đã tóm được ID User từ Meta:', window.currentUserId);

        if (window.currentUserId) {
            // --- Đoạn code khởi tạo Echo.private('user.' + window.currentUserId) giữ nguyên phía dưới ---
            console.log(`📡 Đang đăng ký lắng nghe kênh: user.${window.currentUserId}`);
        } else {
            console.warn('⚠️ Không tìm thấy ID User hợp lệ để bật kết nối Realtime.');
        }
    });
    // Hàm gọi API lấy tổng số tin nhắn chưa đọc từ Controller
    function loadUnreadCount() {
        fetch('/messages/unread-count')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('message-badge');
                if (!badge) return;

                let count = Number(data.count);

                if (count > 0) {
                    badge.style.display = 'inline-block';
                    badge.innerText = count > 99 ? '99+' : count;
                } else {
                    badge.style.display = 'none';
                    badge.innerText = '';
                }
            })
            .catch(error => {
                console.error("Lỗi lấy số lượng tin nhắn chưa đọc:", error);
            });
    }

    // Load số lượng lần đầu tiên khi vừa tải xong trang
    loadUnreadCount();

    // 2. KẾT NỐI WEBSOCKET REAL-TIME CẬP NHẬT BADGE SỐ THÔNG BÁO
    if (typeof window.Echo !== 'undefined' && currentUserId) {

        // Đã đồng bộ kênh user. trùng với backend
        window.Echo.private(`user.${currentUserId}`)
            // 🔥 SỬA TẠI ĐÂY: Thêm dấu chấm . trước MessageSent để Echo bắt trúng tên sự kiện custom
            .listen('.MessageSent', (e) => {
                console.log('🔔 Dashboard nhận tín hiệu tin nhắn mới real-time:', e);

                // Chỉ xử lý nếu người gửi tin nhắn KHÔNG phải là chính mình
                if (e.message && e.message.sender_id != currentUserId) {

                    // Tối ưu tốc độ: Tăng số badge hiển thị ngay lập tức trên giao diện (Không cần đợi fetch)
                    const badge = document.getElementById('message-badge');
                    if (badge) {
                        let currentCount = parseInt(badge.innerText) || 0;
                        if (badge.style.display === 'none') currentCount = 0;

                        let newCount = currentCount + 1;
                        badge.style.display = 'inline-block';
                        badge.innerText = newCount > 99 ? '99+' : newCount;
                    }

                    // Gọi backup để đảm bảo số liệu đồng bộ chính xác hoàn toàn với database
                    loadUnreadCount();
                }
            });
    }
});