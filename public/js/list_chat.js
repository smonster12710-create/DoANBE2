// TỰ ĐỘNG CHẠY NGAY KHI TRANG TẢI XONG
document.addEventListener('DOMContentLoaded', function () {
    console.log("🚀 [Sidebar] File list_chat.js độc lập đã được kích hoạt thành công!");

    // 1. Kiểm tra Laravel Echo có tồn tại không
    if (typeof window.Echo === 'undefined') {
        console.error("❌ [Sidebar Error] Không tìm thấy Laravel Echo. Hãy đảm bảo đã nhúng app.js hoặc pusher.js ở layout tổng!");
        return;
    }

    // 2. Lấy User ID đã được ép từ Blade vào window
    const currentLoggedUserId = window.currentUserId;
    if (!currentLoggedUserId) {
        console.warn("⚠️ [Sidebar Warn] Không thể kết nối vì không tìm thấy window.currentUserId (Chưa đăng nhập).");
        return;
    }

    // 3. Tiến hành kết nối Websocket kênh riêng tư của User
    window.Echo.private(`user.${currentLoggedUserId}`)
        .subscribed(() => {
            console.log(`✅ [Sidebar] KẾT NỐI REALTIME THÀNH CÔNG tại kênh: user.${currentLoggedUserId}`);
        })
        .error((error) => {
            console.error("❌ [Sidebar Websocket Error] Lỗi xác thực kênh private:", error);
        })
        .listen('.MessageSent', (e) => {
            console.log("📢 [Sidebar Realtime] Có tin nhắn mới bay về:", e.message);
            updateSidebarGiaoDien(e.message);
        });
});

// =========================================================================
// HÀM XỬ LÝ CẬP NHẬT GIAO DIỆN DOM SIDEBAR độc lập
// =========================================================================
function updateSidebarGiaoDien(msg) {
    if (!msg || !msg.conversation_id) return;

    // Tìm thẻ <a> của cuộc trò chuyện dựa theo data-conversation-id cậu đặt ở Blade
    const conversationLink = document.querySelector(`.message-item-link[data-conversation-id="${msg.conversation_id}"]`);
    const listContainer = document.querySelector('.scrollable-list');

    if (conversationLink) {

        // -----------------------------------------------------------------
        // [LUỒNG 1]: CẬP NHẬT CHỮ & ĐẨY LÊN ĐẦU (Ai nhắn cũng phải chạy)
        // -----------------------------------------------------------------
        const lastMsgLabel = conversationLink.querySelector('.last-message');
        if (lastMsgLabel) {
            if (msg.is_deleted == 1) {
                lastMsgLabel.innerHTML = '<i class="text-muted">Tin nhắn đã được thu hồi</i>';
            } else {
                let contentText = msg.content ? String(msg.content).trim() : '';
                if (contentText.length > 30) contentText = contentText.substring(0, 30) + '...';

                // Thêm chữ "Bạn: " nếu chính cậu là người gửi tin nhắn đó
                let prefix = (msg.sender_id == window.currentUserId) ? 'Bạn: ' : '';
                lastMsgLabel.innerText = msg.image_url ? `${prefix}📷 Đã gửi một ảnh` : `${prefix}${contentText}`;
            }
        }

        // Đẩy cuộc trò chuyện vừa có tin nhắn lên đầu danh sách Sidebar (.scrollable-list)
        if (listContainer && conversationLink !== listContainer.firstElementChild) {
            listContainer.prepend(conversationLink);
        }

        // -----------------------------------------------------------------
        // [LUỒNG 2]: TĂNG BADGE THÔNG BÁO ĐỎ (Chỉ tăng khi người khác nhắn vào phòng đang đóng)
        // -----------------------------------------------------------------
        if (!conversationLink.classList.contains('active-chat')) {
            let badge = conversationLink.querySelector('.unread-badge');
            if (!badge) {
                const msgTop = conversationLink.querySelector('.message-top');
                if (msgTop) {
                    msgTop.insertAdjacentHTML('beforeend', `<span class="unread-badge">1</span>`);
                }
            } else {
                let currentCount = parseInt(badge.innerText.trim()) || 0;
                badge.innerText = currentCount + 1;
            }
        }
    }
}