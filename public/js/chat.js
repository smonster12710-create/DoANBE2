// =========================================================================
// 1. KHỞI TẠO CÁC BIẾN BAN ĐẦU (INIT ELEMENTS)
// =========================================================================
let chatBox = document.getElementById('chat-box'); // Khung chứa toàn bộ nội dung tin nhắn
let conversationId = chatBox ? chatBox.dataset.conversation : null;
let currentUserId = chatBox ? chatBox.dataset.user : null;

// Quản lý ID tin nhắn phục vụ việc kéo lên tải tin cũ
let lastMessageId = chatBox ? (Number(chatBox.dataset.lastId) || 0) : 0;
let firstMessageId = chatBox ? (Number(chatBox.dataset.firstId) || 0) : 0;

// Các trạng thái flag chống spam request
let isSending = false;
let isLoadingOlder = false;

// KHỞI CHẠY KHI DOM SẴN SÀNG
document.addEventListener('DOMContentLoaded', function () {
    const chatBox = document.getElementById('chat-box');
    if (!chatBox) return;

    // Gọi hàm markAsRead ngay khi vừa mở trang
    markAsRead();

    // -----------------------------------------------------------------
    // KẾT NỐI WEBSOCKET REALTIME QUA LARAVEL ECHO (ĐOẠN CHÍNH)
    // -----------------------------------------------------------------
    if (typeof window.Echo !== 'undefined') {
        const channelName = `chat-conversation.${conversationId}`;

        window.Echo.private(channelName)
            .subscribed(() => {
                console.log('SUBSCRIBED OK VIA DOM:', channelName);
            })
            .error((error) => {
                console.error('CHANNEL ERROR:', error);
            })
            .listen('.MessageSent', (e) => {
                let msg = e.message;
                if (!msg) return;

                // Tránh append trùng tin nhắn trong khung chat
                const existed = document.querySelector(`.message-wrapper[data-id="${msg.id}"]`);
                if (!existed) {
                    appendMessageRealtime(msg);
                    scrollBottom();

                    // Cập nhật List Chat bằng WebSocket DOM (Khớp class theo file Blade)
                    handleListChatRealtime(msg);

                    // SỬA LỖI ĐỌC TIN: Người nhận đang mở chat thì tự động báo đã đọc
                    if (msg.sender_id != currentUserId) {
                        markAsRead();
                    }
                }
            })
            .listen('.ChatReadStatusUpdated', (e) => {
                console.log('🔥 ĐÃ THẤY TÍN HIỆU ĐÃ XEM VỀ REALTIME:', e);

                // Quét sạch các chữ "Đã gửi" trên màn hình của mình và biến thành "Đã xem"
                const allStatuses = document.querySelectorAll('.message-status');
                allStatuses.forEach(status => {
                    if (status.innerText.trim() === 'Đã gửi') {
                        status.innerText = 'Đã xem';
                    }
                });
            });
    }
});

// =========================================================================
// 2. LOGIC ĐIỀU KHIỂN THANH CUỘN (SCROLL LOGIC)
// =========================================================================
function forceScrollBottom() {
    let box = document.getElementById('chat-box');
    if (box) box.scrollTop = box.scrollHeight;
}

function scrollBottom() {
    let box = document.getElementById('chat-box');
    if (box) box.scrollTop = box.scrollHeight;
}

// Lazy load tin nhắn cũ khi cuộn lên đỉnh
let boxScroll = document.getElementById('chat-box');
if (boxScroll) {
    boxScroll.addEventListener('scroll', function () {
        if (
            boxScroll.scrollTop <= 10 &&
            !isLoadingOlder &&
            firstMessageId > 0
        ) {
            loadOlderMessages();
        }
    });
}

// =========================================================================
// 3. TẢI TIN NHẮN CŨ HƠN (LAZY LOAD VIA API)
// =========================================================================
function loadOlderMessages() {
    let box = document.getElementById('chat-box');
    if (isLoadingOlder || firstMessageId <= 0 || !box) return;

    isLoadingOlder = true;
    let oldHeight = box.scrollHeight;

    fetch(`/messages/${conversationId}/older?first_id=${firstMessageId}`)
        .then(res => res.json())
        .then(data => {
            if (!data || data.length === 0) {
                firstMessageId = 0;
                return;
            }

            data.reverse().forEach(msg => {
                if (!document.querySelector(`.message-wrapper[data-id="${msg.id}"]`)) {
                    prependMessage(msg);
                }
            });

            firstMessageId = Math.min(...data.map(m => m.id));
            box.scrollTop = box.scrollHeight - oldHeight;
        })
        .catch(err => {
            console.error("Lỗi load tin cũ:", err);
        })
        .finally(() => {
            isLoadingOlder = false;
        });
}

// =========================================================================
// 4. GỬI TIN NHẮN MỚI
// =========================================================================
document.querySelector('.chat-input').addEventListener('submit', function (e) {
    e.preventDefault();

    if (isSending) return;
    isSending = true;

    let formData = new FormData(this);
    let input = this.querySelector('input[name="content"]');
    let submitBtn = this.querySelector('button[type="submit"]');

    submitBtn.disabled = true;
    input.disabled = true;

    fetch('/messages/send', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': this.querySelector('input[name="_token"]').value
        },
        body: formData
    })
        .then(res => res.json())
        .then(response => {
            let msg = response.message;

            if (msg && !document.querySelector(`.message-wrapper[data-id="${msg.id}"]`)) {
                appendMessageRealtime(msg);
                scrollBottom();

                // Gửi tin thành công -> Tự cập nhật vị trí List Chat lên đầu bằng WebSocket DOM
                handleListChatRealtime(msg);
            }

            input.value = '';
            document.getElementById('image-input').value = '';

            const previewContainer = document.getElementById('image-preview-container');
            if (previewContainer) {
                previewContainer.innerHTML = '';
            }
        })
        .catch(err => {
            console.log('Send error:', err);
        })
        .finally(() => {
            setTimeout(() => {
                isSending = false;
                input.disabled = false;
                submitBtn.disabled = false;
                input.focus();
            }, 700);
        });
});

// =========================================================================
// 5. HÀM TẠO GIAO DIỆN TIN NHẮN (RENDER HELPERS)
// =========================================================================
function createMessageHTML(msg) {
    let isMe = msg.sender_id == currentUserId;
    let wrapperClass = isMe ? 'me' : 'them';

    if (msg.is_deleted == 1) {
        return `
            <div class="message-wrapper ${wrapperClass}" data-id="${msg.id}">
                <div class="message-recalled">Tin nhắn đã được thu hồi</div>
            </div>
        `;
    }

    return `
        <div class="message-wrapper ${wrapperClass}" data-id="${msg.id}">
            <div class="message-container">
                
                ${msg.image_url ? `
                    <div class="message-media">
                        <img src="/storage/${msg.image_url}" class="chat-image">
                    </div>
                ` : ''}

                ${msg.content && String(msg.content).trim() !== '' ? `
                    <div class="message-bubble">
                        <div class="message-content">${msg.content}</div>
                    </div>
                ` : ''}

                <div class="message-actions">
                    <button type="button" class="dots-btn">⋯</button>
                    <div class="message-menu">
                        ${isMe ? `
                            <button type="button" class="recall-btn" data-id="${msg.id}">Thu hồi</button>
                        ` : ''}
                        <button type="button" class="delete-btn" data-id="${msg.id}">Xoá ở phía bạn</button>
                    </div>
                </div>

            </div>

            ${isMe ? `
                <div class="message-status-row">
                    <small class="message-status" data-id="${msg.id}">
                        ${msg.is_read ? 'Đã xem' : 'Đã gửi'}
                    </small>
                </div>
            ` : ''}
        </div>
    `;
}

// =========================================================================
// 6. THAO TÁC APPEND / PREPEND DOM
// =========================================================================
function appendMessage(msg) {
    let box = document.getElementById('chat-box');
    if (!box) return;
    let div = document.createElement('div');
    div.innerHTML = createMessageHTML(msg);
    box.appendChild(div.firstElementChild);
}

function appendMessageRealtime(msg) {
    appendMessage(msg);
    if (msg.id > lastMessageId) {
        lastMessageId = msg.id;
    }
}

function prependMessage(msg) {
    let box = document.getElementById('chat-box');
    if (!box) return;
    let div = document.createElement('div');
    div.innerHTML = createMessageHTML(msg);
    box.insertBefore(div.firstElementChild, box.firstChild);
}

// =========================================================================
// 7. ĐIỀU KHIỂN ĐÓNG / MỞ MENU BA CHẤM
// =========================================================================
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('dots-btn')) {
        e.stopPropagation();
        let menu = e.target.nextElementSibling;

        document.querySelectorAll('.message-menu').forEach(m => {
            if (m !== menu) m.classList.remove('show');
        });

        if (menu) menu.classList.toggle('show');
    } else {
        document.querySelectorAll('.message-menu').forEach(m => {
            m.classList.remove('show');
        });
    }
});

// =========================================================================
// 8. THU HỒI TIN NHẮN
// =========================================================================
function recallMessage(messageId) {
    let tokenMeta = document.querySelector('meta[name="csrf-token"]');
    fetch(`/messages/recall/${messageId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': tokenMeta ? tokenMeta.content : '',
            'Accept': 'application/json'
        }
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let wrapper = document.querySelector(`.message-wrapper[data-id="${messageId}"]`);
                if (wrapper) {
                    wrapper.className = `message-wrapper me`;
                    wrapper.innerHTML = `<div class="message-recalled">Tin nhắn đã được thu hồi</div>`;
                }

                // Đồng bộ nội dung thu hồi lên list chat phía bên trái thông qua DOM
                let mockMsg = { conversation_id: conversationId, is_deleted: 1 };
                handleListChatRealtime(mockMsg);
            }
        })
        .catch(err => console.log("Recall error:", err));
}

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('recall-btn')) {
        let messageId = e.target.dataset.id;
        recallMessage(messageId);
    }
});

// =========================================================================
// 9. XOÁ TIN NHẮN 1 CHIỀU
// =========================================================================
// =========================================================================
// 9. XOÁ TIN NHẮN 1 CHIỀU (SỬA LẠI CHUẨN DOM THEO FILE CŨ CỦA CẬU)
// =========================================================================
// =========================================================================
// 9. XOÁ TIN NHẮN 1 CHIỀU (LẤY TIN NHẮN TRƯỚC ĐÓ ĐƯA LÊN SIDEBAR)
// =========================================================================
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('delete-btn')) {
        let messageId = e.target.dataset.id;
        let tokenMeta = document.querySelector('meta[name="csrf-token"]');

        fetch(`/messages/delete-for-me/${messageId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': tokenMeta ? tokenMeta.content : '',
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                let messageElement = document.querySelector(`.message-wrapper[data-id="${messageId}"]`);
                if (messageElement) {
                    // 1. 🔥 TÌM TIN NHẮN TRƯỚC ĐÓ NGAY TRÊN DOM MÀN HÌNH CHAT
                    // Tìm thẻ wrapper có class 'message-wrapper' nằm ngay phía trên tin nhắn vừa xóa
                    let prevMessageElement = messageElement.previousElementSibling;
                    while (prevMessageElement && !prevMessageElement.classList.contains('message-wrapper')) {
                        prevMessageElement = prevMessageElement.previousElementSibling;
                    }

                    // 2. Xóa tin nhắn hiện tại khỏi màn hình chat
                    messageElement.remove();

                    // 3. 🔥 ĐỒNG BỘ TIN CŨ LÊN SIDEBAR REALTIME
                    if (prevMessageElement) {
                        // Nếu tìm thấy tin nhắn trước đó, bóc tách dữ liệu của nó
                        let prevId = prevMessageElement.dataset.id;
                        let prevContent = '';
                        let isRecalled = prevMessageElement.querySelector('.message-recalled') !== null;
                        let hasImage = prevMessageElement.querySelector('.chat-image') !== null;

                        if (isRecalled) {
                            prevContent = 'Tin nhắn đã được thu hồi';
                        } else {
                            let contentNode = prevMessageElement.querySelector('.message-content');
                            prevContent = contentNode ? contentNode.innerText.trim() : '';
                        }

                        // Fake object tin nhắn cũ để truyền vào hàm của cậu
                        let mockMsg = {
                            conversation_id: conversationId,
                            id: prevId,
                            content: prevContent,
                            image_url: hasImage ? 'has_image_placeholder' : null, // Đánh dấu nếu có ảnh
                            is_deleted: isRecalled ? 1 : 0
                        };

                        if (typeof handleListChatRealtime === 'function') {
                            handleListChatRealtime(mockMsg);
                        }
                    } else {
                        // Nếu không còn tin nhắn nào trước đó nữa (trống trơn phòng chat)
                        let mockMsg = {
                            conversation_id: conversationId,
                            content: 'Không có tin nhắn nào',
                            is_deleted: 0
                        };
                        if (typeof handleListChatRealtime === 'function') {
                            handleListChatRealtime(mockMsg);
                        }
                    }
                }
            })
            .catch(err => console.error("Lỗi xóa 1 chiều:", err));
    }
});
// =========================================================================
// 10. ĐÁNH DẤU ĐÃ XEM VÀ LOGIC XỬ LÝ LIST CHAT REALTIME (DÙNG WEBSOCKET DOM)
// =========================================================================
// 10. ĐÁNH DẤU ĐÃ XEM VÀ LOGIC XỬ LÝ LIST CHAT REALTIME (DÙNG WEBSOCKET DOM)
// =========================================================================
function markAsRead() {
    if (!conversationId) return;
    let tokenMeta = document.querySelector('meta[name="csrf-token"]');
    fetch(`/messages/${conversationId}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': tokenMeta ? tokenMeta.content : '',
            'Accept': 'application/json'
        }
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                console.log('Đã cập nhật DB thành đã đọc');

                // 🎯 CƠ CHẾ ĐỘNG: Duyệt qua tất cả link chat, tìm thằng nào có ID trùng ở cuối href
                const allLinks = document.querySelectorAll('.message-item-link');
                let conversationLink = null;

                allLinks.forEach(link => {
                    const hrefStr = link.getAttribute('href') || '';
                    // Lấy số cuối cùng nằm sau dấu gạch chéo / trên URL
                    const segments = hrefStr.split('/');
                    const idFromHref = segments[segments.length - 1];

                    if (idFromHref == conversationId) {
                        conversationLink = link;
                    }
                });

                if (conversationLink) {
                    const badge = conversationLink.querySelector('.unread-badge');
                    if (badge) {
                        badge.style.setProperty('display', 'none', 'important'); // Ép ẩn số thông báo đỏ đi lập tức
                    }
                }
            }
        });
}

// 🎯 HÀM TỰ ĐỘNG CẬP NHẬT GIAO DIỆN SIDEBAR QUA WEBSOCKET DOM
// 🔥 HÀM MỚI: Không phụ thuộc vào biến conversationId của trang hiện tại nữa
function handleListChatRealtime(msg) {
    if (!msg || !msg.conversation_id) return;

    // Tìm thẻ <a> của ĐÚNG cuộc trò chuyện có ID là msg.conversation_id
    // Cách này giúp nó tìm đúng dù cậu đang ở trang nào
    const conversationLink = document.querySelector(`.message-item-link[href$="${msg.conversation_id}"]`);
    const listContainer = document.querySelector('.scrollable-list');

    if (conversationLink) {
        // 1. Cập nhật nội dung tin nhắn cuối
        const lastMsgLabel = conversationLink.querySelector('.last-message');
        if (lastMsgLabel) {
            if (msg.is_deleted == 1) {
                lastMsgLabel.innerHTML = '<i class="text-muted">Tin nhắn đã được thu hồi</i>';
            } else {
                let contentText = msg.content ? String(msg.content).trim() : '';
                if (contentText.length > 30) contentText = contentText.substring(0, 30) + '...';
                lastMsgLabel.innerText = msg.image_url ? '📷 Đã gửi một ảnh' : contentText;
            }
        }

        // 2. Cập nhật badge (Nếu đang ở trang ngoài thì không cần ẩn, hoặc nếu muốn thì giữ lại)
        const badge = conversationLink.querySelector('.unread-badge');
        if (badge) {
            // Nếu muốn nó tự tăng số lên khi có tin mới thì có thể làm ở đây
            badge.style.display = 'inline-block';
            let count = parseInt(badge.innerText) || 0;
            badge.innerText = count + 1;
        }

        // 3. Đẩy lên đầu danh sách
        if (listContainer) {
            listContainer.prepend(conversationLink);
        }
    }
}
// =========================================================================
// 11. ĐOẠN ECHO THỨ 2 (TỐI ƯU ĐỒNG BỘ REALTIME THEO FILE MỚI)
// =========================================================================
if (typeof window.Echo !== 'undefined' && chatBox) {
    const channelName = `chat-conversation.${conversationId}`;

    window.Echo.private(channelName)
        .listen('.MessageSent', (e) => {
            let msg = e.message;
            if (!msg) return;

            if (msg.is_deleted == 0) {
                const existed = document.querySelector(`.message-wrapper[data-id="${msg.id}"]`);
                if (!existed) {
                    appendMessageRealtime(msg);
                    scrollBottom();
                    handleListChatRealtime(msg);

                    if (msg.sender_id != currentUserId) {
                        markAsRead();
                    }
                }
            } else {
                let wrapper = document.querySelector(`.message-wrapper[data-id="${msg.id}"]`);
                if (wrapper) {
                    let wrapperClass = msg.sender_id == currentUserId ? 'me' : 'them';
                    wrapper.className = `message-wrapper ${wrapperClass}`;
                    wrapper.innerHTML = `<div class="message-recalled">Tin nhắn đã được thu hồi</div>`;
                }
                handleListChatRealtime(msg);
            }
        })
        .listen('.ChatReadStatusUpdated', (e) => {
            if (e.updatedMessages) {
                e.updatedMessages.forEach(msg => {
                    let status = document.querySelector(`.message-status[data-id="${msg.id}"]`);
                    if (status) {
                        status.innerText = 'Đã xem';
                    }
                });
            }
        });
}

// =========================================================================
// 12. KHỞI CHẠY PHỤ (PREVIEW IMAGE)
// =========================================================================
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(() => { forceScrollBottom(); }, 100);
    window.addEventListener('load', () => { forceScrollBottom(); });

    const imageBtn = document.getElementById('image-btn');
    const imageInput = document.getElementById('image-input');
    const previewContainer = document.getElementById('image-preview-container');

    if (imageBtn && imageInput) {
        imageBtn.addEventListener('click', function () {
            imageInput.click();
        });
    }

    if (imageInput && previewContainer) {
        imageInput.addEventListener('change', function () {
            previewContainer.innerHTML = '';
            const file = this.files[0];
            if (!file) return;

            const wrapper = document.createElement('div');
            wrapper.className = 'preview-wrapper';

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.className = 'preview-image';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.innerHTML = '✕';
            removeBtn.className = 'remove-preview';

            removeBtn.addEventListener('click', function () {
                imageInput.value = '';
                previewContainer.innerHTML = '';
            });

            wrapper.appendChild(img);
            wrapper.appendChild(removeBtn);
            previewContainer.appendChild(wrapper);
        });
    }
});

// ĐOẠN NÀY LẮNG NGHE CHO SIDEBAR (Chạy ở mọi trang)
if (typeof window.Echo !== 'undefined') {
    // Lấy ID user từ meta tag (Nhớ đặt trong layout nhé)
    const metaUserId = document.querySelector('meta[name="user-id"]');
    const userId = metaUserId ? metaUserId.content : null;

    if (userId) {
        window.Echo.private(`user.${userId}`)
            .listen('.MessageSent', (e) => {
                console.log('📢 Đã nhận tin nhắn qua kênh User:', e.message);

                // Cập nhật lại Sidebar
                handleListChatRealtime(e.message);

                // Cập nhật thêm số thông báo (nếu cậu muốn tăng badge lên)
                updateSidebarBadge(e.message.conversation_id);
            });
    }
}

// Hàm phụ để cập nhật badge số tin nhắn chưa đọc
function updateSidebarBadge(conversationId) {
    const link = document.querySelector(`.message-item-link[href$="${conversationId}"]`);
    if (link) {
        let badge = link.querySelector('.unread-badge');
        if (!badge) {
            // Nếu chưa có badge, tạo mới
            link.querySelector('.message-top').insertAdjacentHTML('beforeend', `<span class="unread-badge">1</span>`);
        } else {
            // Nếu đã có, tăng số lên
            let current = parseInt(badge.innerText) || 0;
            badge.style.display = 'inline-block';
            badge.innerText = current + 1;
        }
    }
}