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
    if (!chatBox) return; // Không có khung chat thì dừng toàn bộ logic file này

    // Gọi hàm markAsRead ngay khi vừa mở trang
    markAsRead();

    // -----------------------------------------------------------------
    // KẾT NỐI WEBSOCKET REALTIME QUA LARAVEL ECHO (ĐOẠN CHÍNH)
    // -----------------------------------------------------------------
    if (typeof window.Echo !== 'undefined' && conversationId) {
        const channelName = `chat-conversation.${conversationId}`;

        window.Echo.private(channelName)
            .subscribed(() => {
                console.log('SUBSCRIBED OK VIA DOM:', channelName);
            })
            .error((error) => {
                console.error('CHANNEL ERROR:', error);
            })
            // Tìm đoạn .listen('.MessageSent'...) ở MỤC 11 và thay bằng đoạn này:
            .listen('.MessageSent', (e) => {
                let msg = e.message;
                if (!msg) return;

                // Chỗ này xử lý realtime thu hồi cho đối phương nè cậu:
                if (msg.is_deleted == 1) {
                    let wrapper = document.querySelector(`.message-wrapper[data-id="${msg.id}"]`);
                    if (wrapper) {
                        let wrapperClass = msg.sender_id == currentUserId ? 'me' : 'them';
                        wrapper.className = `message-wrapper ${wrapperClass}`;
                        wrapper.innerHTML = `<div class="message-recalled">Tin nhắn đã được thu hồi</div>`;
                    }
                } else {
                    // Tin nhắn bình thường thì giữ nguyên logic append cũ của cậu
                    const existed = document.querySelector(`.message-wrapper[data-id="${msg.id}"]`);
                    if (!existed) {
                        appendMessageRealtime(msg);
                        scrollBottom();

                        if (msg.sender_id != currentUserId) {
                            markAsRead();
                        }
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
const chatInputForm = document.querySelector('.chat-input');
if (chatInputForm) {
    chatInputForm.addEventListener('submit', function (e) {
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
}

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
// 9. XOÁ TIN NHẮN 1 CHIỀU (ĐÃ LỌC BỎ LOGIC SIDEBAR)
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
                    messageElement.remove();
                }
            })
            .catch(err => console.error("Lỗi xóa 1 chiều:", err));
    }
});

// =========================================================================
// 10. ĐÁNH DẤU ĐÃ XEM (CHỈ XỬ LÝ KHUNG CHAT CHI TIẾT)
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
                // Đã xử lý DB thành công
                console.log('Đã cập nhật DB thành đã đọc');
            }
        });
}

// =========================================================================
// 11. ĐOẠN ECHO THỨ 2 (GIỮ NGUYÊN CẤU TRÚC GỐC, KHÔNG CÓ SIDEBAR)
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
    const chatBox = document.getElementById('chat-box');
    if (!chatBox) return;

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