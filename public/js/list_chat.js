document.addEventListener('DOMContentLoaded', function () {
    console.log("🚀 [Sidebar] File list_chat.js độc lập đã được kích hoạt thành công!");

    // =========================================================================
    // 💡 1. XỬ LÝ XOÁ BADGE KHI CẬP NHẬT TRANG LẦN ĐẦU
    // =========================================================================
    const activeConversation = document.querySelector('.message-item-link.active-chat');
    if (activeConversation) {
        const badge = activeConversation.querySelector('.unread-badge');
        if (badge) badge.remove();
    }

    // =========================================================================
    // 🔥 2. SỰ KIỆN CLICK TOÀN BỘ SIDEBAR (XÓA BADGE LẬP TỨC CHỐNG AJAX)
    // =========================================================================
    const listContainer = document.querySelector('.scrollable-list');
    if (listContainer) {
        listContainer.addEventListener('click', function (event) {
            const clickedLink = event.target.closest('.message-item-link');
            if (clickedLink) {
                const badge = clickedLink.querySelector('.unread-badge');
                if (badge) {
                    badge.remove();
                    console.log(`🧼 [Sidebar Click] Đã xóa badge đỏ của phòng vừa click: ${clickedLink.dataset.conversationId}`);
                }
            }
        });
    }

    // =========================================================================
    // 📡 3. ĐOẠN LẮNG NGHE WEBSOCKET ECHO REALTIME
    // =========================================================================
    if (typeof window.Echo === 'undefined') return;
    const currentLoggedUserId = window.currentUserId;
    if (!currentLoggedUserId) return;

    window.Echo.private(`user.${currentLoggedUserId}`)
        // --- LUỒNG A: Nhận tin nhắn mới HOẶC tin nhắn bị Thu Hồi ---
        .listen('.MessageSent', (e) => {
            console.log("📢 [Sidebar Realtime] Có sự kiện MessageSent bay về:", e.message);
            updateSidebarGiaoDien(e.message);
        })

        // --- LUỒNG B: Bắt sự kiện Xóa cuộc trò chuyện / Xóa 1 chiều ---
        .listen('.ChatReadStatusUpdated', (e) => {
            console.log("🧼 [Sidebar Realtime] Nhận tín hiệu xóa 1 chiều / đọc phòng:", e);

            // Nếu sự kiện này do mình xóa 1 chiều (vì ở controller ta truyền [Auth::id()] vào mảng người nhận)
            // Hãy tìm cuộc trò chuyện đó trên sidebar và đá nó bay màu luôn!
            if (e.conversationId) {
                const sidebarItem = document.querySelector(`.message-item-link[data-conversation-id="${e.conversationId}"]`);
                if (sidebarItem) {
                    // Nếu phòng bị xóa đang là phòng đang mở (active), ta có thể ẩn text đi hoặc xóa luôn item sidebar
                    sidebarItem.remove();
                    console.log(`🧼 [Sidebar Realtime] Đã xóa hẳn cuộc trò chuyện ${e.conversationId} khỏi Sidebar!`);
                }
            }
        });
});

// =========================================================================
// HÀM XỬ LÝ CẬP NHẬT GIAO DIỆN DOM SIDEBAR ĐỘC LẬP
// =========================================================================
function updateSidebarGiaoDien(msg) {
    if (!msg || !msg.conversation_id) return;

    // Tìm thẻ <a> của cuộc trò chuyện dựa theo data-conversation-id cậu đặt ở Blade
    const conversationLink = document.querySelector(`.message-item-link[data-conversation-id="${msg.conversation_id}"]`);
    const listContainer = document.querySelector('.scrollable-list');

    if (conversationLink) {

        // -----------------------------------------------------------------
        // [LUỒNG 1]: CẬP NHẬT CHỮ & ĐẨY LÊN ĐẦU (Thu hồi hay nhắn mới đều chạy)
        // -----------------------------------------------------------------
        const lastMsgLabel = conversationLink.querySelector('.last-message');
        if (lastMsgLabel) {
            if (msg.is_deleted == 1) {
                // Nếu tin nhắn mang trạng thái bị thu hồi, lập tức đổi chữ hiển thị ngoài sidebar
                lastMsgLabel.innerHTML = '<i class="text-muted">Tin nhắn đã được thu hồi</i>';
            } else {
                let contentText = msg.content ? String(msg.content).trim() : '';
                if (contentText.length > 30) contentText = contentText.substring(0, 30) + '...';

                // Thêm chữ "Bạn: " nếu chính cậu là người gửi tin nhắn đó
                let prefix = (msg.sender_id == window.currentUserId) ? 'Bạn: ' : '';
                lastMsgLabel.innerText = msg.image_url ? `${prefix}📷 Đã gửi một ảnh` : `${prefix}${contentText}`;
            }
        }

        // Đẩy cuộc trò chuyện vừa có biến động lên đầu danh sách Sidebar (.scrollable-list)
        if (listContainer && conversationLink !== listContainer.firstElementChild) {
            listContainer.prepend(conversationLink);
        }

        // -----------------------------------------------------------------
        // [LUỒNG 2]: TĂNG BADGE THÔNG BÁO ĐỎ (Chỉ tăng khi tin nhắn MỚI và của NGƯỜI KHÁC)
        // -----------------------------------------------------------------
        // Không tăng badge đỏ nếu đó là tin nhắn bị thu hồi (msg.is_deleted == 1)
        if (msg.is_deleted != 1 && msg.sender_id != window.currentUserId && !conversationLink.classList.contains('active-chat')) {

            let badge = conversationLink.querySelector('.unread-badge');

            if (!badge) {
                const msgTop = conversationLink.querySelector('.message-top');
                if (msgTop) {
                    msgTop.insertAdjacentHTML('beforeend', `<span class="unread-badge">1</span>`);
                } else {
                    conversationLink.insertAdjacentHTML('beforeend', `<span class="unread-badge">1</span>`);
                }
                console.log(`🔴 [Sidebar Badge] Đã tạo mới badge thông báo cho phòng: ${msg.conversation_id}`);
            } else {
                let currentCount = parseInt(badge.innerText.trim()) || 0;
                badge.innerText = currentCount + 1;
                console.log(`🔴 [Sidebar Badge] Tăng số thông báo lên: ${currentCount + 1}`);
            }
        }
    }
}