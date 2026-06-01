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
    // KẾT NỐI WEBSOCKET REALTIME QUA LARAVEL ECHO
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
            .listen('.MessageSent', (e) => {
                let msg = e.message;
                if (!msg) return;

                // Xử lý realtime khi tin nhắn bị thu hồi
                if (msg.is_deleted == 1) {
                    let wrapper = document.querySelector(`.message-wrapper[data-id="${msg.id}"]`);
                    if (wrapper) {
                        // Bốc lại tên và avatar cũ trên màn hình để tái sử dụng, tránh bị lỗi mất ảnh
                        let oldAvatar = wrapper.querySelector('.chat-group-avatar')?.getAttribute('src') || '';
                        let oldName = wrapper.querySelector('.group-chat-sender-name')?.innerText || 'Thành viên';

                        // Tạo object dữ liệu giả lập có đầy đủ thông tin để hàm render vẽ lại đúng cấu trúc
                        let fakeMsg = {
                            id: msg.id,
                            sender_id: msg.sender_id,
                            is_deleted: 1,
                            sender: {
                                avatar_url: oldAvatar,
                                fullname: oldName
                            }
                        };

                        // Gọi hàm tạo HTML chuẩn để thay thế khối cũ, bảo toàn tên và avatar bên trái
                        let newHTML = createMessageHTML(fakeMsg);
                        let parser = new DOMParser();
                        let doc = parser.parseFromString(newHTML, 'text/html');
                        let newElement = doc.body.firstElementChild;

                        if (newElement) {
                            wrapper.replaceWith(newElement);
                        }
                    }
                } else {
                    // Tin nhắn bình thường gửi đến
                    const existed = document.querySelector(`.message-wrapper[data-id="${msg.id}"]`);
                    if (!existed) {
                        appendMessageRealtime(msg);
                        scrollBottom();

                        // SỬA LỖI: Nếu đang mở khung chat và tin nhắn tới là của đối phương -> Tự động đánh dấu đã xem realtime
                        if (msg.sender_id != currentUserId) {
                            markAsRead();
                        }
                    }
                }
            })
            .listen('.ChatReadStatusUpdated', (e) => {
                console.log('🔥 ĐÃ THẤY TÍN HIỆU ĐÃ XEM VỀ REALTIME:', e);

                // Cập nhật trạng thái đã xem realtime theo mảng tin nhắn trả về
                if (e.updatedMessages && e.updatedMessages.length > 0) {
                    e.updatedMessages.forEach(msg => {
                        let status = document.querySelector(`.message-status[data-id="${msg.id}"]`);
                        if (status) {
                            status.innerText = 'Đã xem';
                        }
                    });
                } else {
                    // Dự phòng quét sạch các chữ "Đã gửi" trên màn hình thành "Đã xem"
                    const allStatuses = document.querySelectorAll('.message-status');
                    allStatuses.forEach(status => {
                        if (status.innerText.trim() === 'Đã gửi') {
                            status.innerText = 'Đã xem';
                        }
                    });
                }
            });
    }

    // Tự động cuộn xuống dưới khi load trang xong
    setTimeout(() => { forceScrollBottom(); }, 100);
    window.addEventListener('load', () => { forceScrollBottom(); });

    // -----------------------------------------------------------------
    // LOGIC XỬ LÝ PREVIEW ẢNH
    // -----------------------------------------------------------------
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

    // -----------------------------------------------------------------
    // LOGIC EMOJI PICKER
    // -----------------------------------------------------------------
    const emojiTriggerBtn = document.getElementById('emoji-trigger-btn');
    const emojiContainer = document.getElementById('emoji-picker-container');
    const chatInput = document.getElementById('chat-input');

    if (emojiTriggerBtn && emojiContainer && chatInput) {
        const pickerOptions = {
            onEmojiSelect: function (emoji) {
                const startPos = chatInput.selectionStart;
                const endPos = chatInput.selectionEnd;
                const text = chatInput.value;

                chatInput.value = text.substring(0, startPos) + emoji.native + text.substring(endPos);
                chatInput.focus();
                chatInput.selectionStart = chatInput.selectionEnd = startPos + emoji.native.length;
                chatInput.dispatchEvent(new Event('input'));
            },
            theme: 'light',
            set: 'facebook',
            locale: 'vi'
        };
        const picker = new EmojiMart.Picker(pickerOptions);
        emojiContainer.appendChild(picker);

        emojiTriggerBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (emojiContainer.style.display === 'none' || emojiContainer.style.display === '') {
                emojiContainer.style.display = 'block';
            } else {
                emojiContainer.style.display = 'none';
            }
        });

        document.addEventListener('click', function (e) {
            if (!emojiContainer.contains(e.target) && e.target !== emojiTriggerBtn) {
                emojiContainer.style.display = 'none';
            }
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
// 4. GỬI TIN NHẮN MỚI (CÓ TÍCH HỢP NÉN ẢNH)
// =========================================================================
const chatInputForm = document.querySelector('.chat-input');
if (chatInputForm) {
    chatInputForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (isSending) return;
        isSending = true;

        let input = this.querySelector('input[name="content"]');
        let submitBtn = this.querySelector('button[type="submit"]');
        let imageInput = document.getElementById('image-input');

        let formData = new FormData(this);

        submitBtn.disabled = true;
        input.disabled = true;

        // XỬ LÝ NÉN ẢNH TẠI TRÌNH DUYỆT
        if (imageInput && imageInput.files.length > 0) {
            const imageFile = imageInput.files[0];

            if (imageFile.size > 1.5 * 1024 * 1024) {
                console.log('⚡ Ảnh lớn hơn 1.5MB, bắt đầu nén tại trình duyệt...');

                const options = {
                    maxSizeMB: 0.8,
                    maxWidthOrHeight: 1000,
                    useWebWorker: true
                };

                try {
                    const compressor = window.imageCompression || imageCompression;

                    if (typeof compressor === 'function') {
                        const compressedFile = await compressor(imageFile, options);
                        formData.set('image', compressedFile, imageFile.name);
                    } else {
                        console.warn('⚠️ Thư viện nén ảnh chưa sẵn sàng, giữ nguyên file gốc.');
                    }
                } catch (error) {
                    console.error('❌ Lỗi nén ảnh JS, giữ nguyên file gốc:', error);
                }
            }
        }

        // BẮN REQUEST LÊN SERVER
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
                imageInput.value = '';

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
// 5. HÀM TẠO GIAO DIỆN TIN NHẮN ĐỒNG BỘ 100% VỚI HTML BLADE
// =========================================================================
function createMessageHTML(msg) {
    let isMe = msg.sender_id == currentUserId;
    let wrapperClass = isMe ? 'me' : 'them';

    // XỬ LÝ ĐỒNG BỘ DỮ LIỆU NGƯỜI GỬI (LUÔN CHẠY KỂ CẢ KHI THU HỒI)
    let userData = msg.sender || msg.user || null;

    let senderName = 'Thành viên';
    if (userData) {
        senderName = userData.fullname || userData.username || 'Thành viên';
    }

    // Xử lý hiển thị ảnh đại diện chính xác từ thư mục uploads_profile
    let senderAvatar = '';
    if (userData && userData.avatar_url) {
        if (userData.avatar_url.startsWith('http')) {
            senderAvatar = userData.avatar_url;
        } else {
            senderAvatar = userData.avatar_url.startsWith('/') ? userData.avatar_url : `/${userData.avatar_url}`;
        }
    } else {
        senderAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(senderName)}&background=0084ff&color=fff&size=100`;
    }

    // KHỞI TẠO NỘI DUNG BÊN TRONG BONG BÓNG CHAT
    let chatContentHTML = '';

    if (msg.is_deleted == 1) {
        // Nếu tin nhắn BỊ THU HỒI: Biến nội dung thành bong bóng màu xám mờ chứa thông báo
        chatContentHTML = `
            <div class="message-bubble" style="background: #f1f0f0; color: #999; font-style: italic; border: 1px dashed #ccc;">
                <div class="message-content">Tin nhắn đã được thu hồi</div>
            </div>
        `;
    } else {
        // Nếu tin nhắn BÌNH THƯỜNG: Hiện hình ảnh và chữ như cũ
        let mediaHTML = msg.image_url ? `
            <div class="message-media">
                <img src="/storage/${msg.image_url}" class="chat-image">
            </div>
        ` : '';

        let bubbleHTML = msg.content && String(msg.content).trim() !== '' ? `
            <div class="message-bubble">
                <div class="message-content">${msg.content}</div>
            </div>
        ` : '';

        chatContentHTML = mediaHTML + bubbleHTML;
    }

    // PHÂN CHIA LAYOUT THEO ĐỐI TƯỢNG GỬI (CẤU TRÚC AVATAR LUÔN GIỮ NGUYÊN)
    if (isMe) {
        return `
            <div class="message-wrapper me" data-id="${msg.id}">
                <div class="message-container">
                    ${chatContentHTML}
                    
                    ${msg.is_deleted != 1 ? `
                        <div class="message-actions">
                            <button type="button" class="dots-btn">⋯</button>
                            <div class="message-menu">
                                <button type="button" class="recall-btn" data-id="${msg.id}">Thu hồi</button>
                                <button type="button" class="delete-btn" data-id="${msg.id}">Xoá ở phía bạn</button>
                            </div>
                        </div>
                    ` : ''}
                </div>
                <div class="message-status-row">
                    <small class="message-status" data-id="${msg.id}">
                        ${msg.is_read ? 'Đã xem' : 'Đã gửi'}
                    </small>
                </div>
            </div>
        `;
    } else {
        return `
            <div class="message-wrapper them" data-id="${msg.id}">
                <div class="group-chat-layout">
                    <span class="group-chat-sender-name">${senderName}</span>
                    <div class="group-chat-row">
                        <div class="chat-avatar-wrapper">
                            <img src="${senderAvatar}" class="chat-group-avatar" title="${senderName}">
                        </div>
                        <div class="message-container-them">
                            ${chatContentHTML}
                            ${msg.is_deleted != 1 ? `
                                <div class="message-actions">
                                    <button type="button" class="dots-btn">⋯</button>
                                    <div class="message-menu">
                                        <button type="button" class="delete-btn" data-id="${msg.id}">Xoá ở phía bạn</button>
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
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

        if (menu) {
            menu.classList.toggle('show');

            if (menu.classList.contains('show')) {
                let rect = e.target.getBoundingClientRect();

                if (rect.top > (window.innerHeight / 2)) {
                    menu.style.top = 'auto';
                    menu.style.bottom = '36px';
                } else {
                    menu.style.bottom = 'auto';
                    menu.style.top = '36px';
                }
            }
        }
    } else {
        document.querySelectorAll('.message-menu').forEach(m => {
            m.classList.remove('show');
        });
    }
});

// =========================================================================
// 8. THU HỒI TIN NHẮN (ĐÃ SỬA ĐỂ KHÔNG LÀM MẤT AVATAR VÀ TÊN)
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
                    // Lấy lại thông tin avatar và tên đang hiện hữu
                    let oldAvatar = wrapper.querySelector('.chat-group-avatar')?.getAttribute('src') || '';
                    let oldName = wrapper.querySelector('.group-chat-sender-name')?.innerText || 'Thành viên';

                    let fakeMsg = {
                        id: messageId,
                        sender_id: currentUserId, // Chắc chắn là của mình vì mình bấm thu hồi
                        is_deleted: 1,
                        sender: {
                            avatar_url: oldAvatar,
                            fullname: oldName
                        }
                    };

                    // Vẽ lại giao diện qua hàm render để giữ trọn vẹn khung layout
                    let newHTML = createMessageHTML(fakeMsg);
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(newHTML, 'text/html');
                    let newElement = doc.body.firstElementChild;

                    if (newElement) {
                        wrapper.replaceWith(newElement);
                    }
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
// 9. XOÁ TIN NHẮN 1 CHIỀU
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
// 10. ĐÁNH DẤU ĐÃ XEM
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
            }
        });
}