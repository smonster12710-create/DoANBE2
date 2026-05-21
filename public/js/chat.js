// ========================
// INIT ELEMENTS
// ========================
let chatBox = document.getElementById('chat-box');
let conversationId = chatBox.dataset.conversation;
let currentUserId = chatBox.dataset.user;

let lastMessageId = Number(chatBox.dataset.lastId) || 0;
let firstMessageId = Number(chatBox.dataset.firstId) || 0;

let isSending = false;
let isLoadingOlder = false;
let messageInterval = null;

// ========================
// SCROLL LOGIC
// ========================
function forceScrollBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}

function scrollBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}

// load tin cũ khi kéo lên
chatBox.addEventListener('scroll', function () {

    if (
        chatBox.scrollTop <= 10 &&
        !isLoadingOlder &&
        firstMessageId > 0
    ) {
        loadOlderMessages();
    }

});

// ========================
// LOAD NEW MESSAGES
// ========================
function loadMessages() {

    fetch(`/messages/${conversationId}?last_id=${lastMessageId}`)

        .then(res => {

            if (!res.ok) {

                if (
                    res.status === 401 ||
                    res.status === 403
                ) {

                    stopPolling();

                    window.location.href =
                        "/list_messages";
                }

                return null;
            }

            return res.json();
        })

        .then(data => {

            if (!data || data.length === 0) return;

            let hasNew = false;

            data.forEach(msg => {

                // tránh append trùng
                if (
                    !document.querySelector(
                        `.message-wrapper[data-id="${msg.id}"]`
                    )
                ) {

                    appendMessage(msg);

                    hasNew = true;
                }

                if (msg.id > lastMessageId) {
                    lastMessageId = msg.id;
                }

            });

            if (hasNew) {

                scrollBottom();
                markAsRead();
                updateSidebar();
            }

        })

        .catch(err => {
            console.log("Chat error:", err);
        });

}

// ========================
// LOAD OLDER MESSAGES
// ========================
function loadOlderMessages() {

    if (
        isLoadingOlder ||
        firstMessageId <= 0
    ) return;

    isLoadingOlder = true;

    let oldHeight = chatBox.scrollHeight;

    fetch(
        `/messages/${conversationId}/older?first_id=${firstMessageId}`
    )

        .then(res => res.json())

        .then(data => {

            if (!data || data.length === 0) {

                firstMessageId = 0;

                return;
            }

            data.reverse().forEach(msg => {

                // tránh load trùng
                if (
                    !document.querySelector(
                        `.message-wrapper[data-id="${msg.id}"]`
                    )
                ) {
                    prependMessage(msg);
                }

            });

            firstMessageId = Math.min(
                ...data.map(m => m.id)
            );

            // giữ vị trí scroll
            chatBox.scrollTop =
                chatBox.scrollHeight - oldHeight;

        })

        .catch(err => {
            console.error(
                "Lỗi load tin cũ:",
                err
            );
        })

        .finally(() => {
            isLoadingOlder = false;
        });

}

// ========================
// SEND MESSAGE
// ========================
document.querySelector('.chat-input')
    .addEventListener('submit', function (e) {

        e.preventDefault();

        // chặn spam
        if (isSending) return;

        isSending = true;

        let formData = new FormData(this);

        let input = this.querySelector(
            'input[name="content"]'
        );

        let submitBtn = this.querySelector(
            'button[type="submit"]'
        );

        submitBtn.disabled = true;
        input.disabled = true;
        fetch('/messages/send', {

            method: 'POST',

            headers: {

                'X-CSRF-TOKEN':
                    this.querySelector(
                        'input[name="_token"]'
                    ).value

            },

            body: formData

        })

            .then(res => res.json())

            .then(() => {

                // reset text
                input.value = '';

                // reset ảnh
                document.getElementById(
                    'image-input'
                ).value = '';
                // xoá preview ảnh
                const previewContainer =
                    document.getElementById(
                        'image-preview-container'
                    );

                if (previewContainer) {
                    previewContainer.innerHTML =
                        '';
                }
                loadMessages();

                updateSidebar();

            })

            .catch(err => {

                console.log(
                    'Send error:',
                    err
                );

            })

            .finally(() => {

                // mở lại sau 700ms
                setTimeout(() => {

                    isSending = false;
                    input.disabled = false;

                    submitBtn.disabled = false;

                }, 700);

            });

    });

// ========================
// RENDER HELPERS
// ========================
function createMessageHTML(msg) {
    let isMe = msg.sender_id == currentUserId;
    let wrapperClass = isMe ? 'me' : 'them';

    // 1. Trường hợp tin nhắn đã bị thu hồi
    if (msg.is_deleted == 1) {
        return `
            <div class="message-wrapper ${wrapperClass}" data-id="${msg.id}">
                <div class="message-recalled">
                    Tin nhắn đã được thu hồi
                </div>
            </div>
        `;
    }

    // 2. Trường hợp tin nhắn hiển thị bình thường (Có bọc message-container)
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
                        <div class="message-content">
                            ${msg.content}
                        </div>
                    </div>
                ` : ''}

                ${msg.image_url || (msg.content && String(msg.content).trim() !== '') ? `
                    <div class="message-actions">
                        <button type="button" class="dots-btn">⋯</button>
                        <div class="message-menu">
                            ${isMe ? `
                                <button type="button" class="recall-btn" data-id="${msg.id}">
                                    Thu hồi
                                </button>
                            ` : ''}
                            <button type="button" class="delete-btn" data-id="${msg.id}">
                                Xoá ở phía bạn
                            </button>
                        </div>
                    </div>
                ` : ''}

            </div> <!-- Hết message-container -->

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
// ========================
// Xoá tin nhắn 1 chiều
// ========================
document.addEventListener('click', function (e) {

    if (e.target.classList.contains('delete-btn')) {

        let messageId = e.target.dataset.id;

        fetch(`/messages/delete-for-me/${messageId}`, {

            method: 'POST',

            headers: {

                'X-CSRF-TOKEN':
                    document.querySelector(
                        'input[name="_token"]'
                    ).value

            }

        })

            .then(res => res.json())

            .then(data => {

                if (data.success) {

                    let message = document.querySelector(
                        `.message-wrapper[data-id="${messageId}"]`
                    );

                    if (message) {
                        message.remove();
                    }

                    updateSidebar();
                }

            });

    }

});
// ========================
// APPEND / PREPEND
// ========================
function appendMessage(msg) {

    let div = document.createElement('div');

    div.innerHTML =
        createMessageHTML(msg);

    chatBox.appendChild(
        div.firstElementChild
    );
}

function prependMessage(msg) {

    let div = document.createElement('div');

    div.innerHTML =
        createMessageHTML(msg);

    chatBox.insertBefore(
        div.firstElementChild,
        chatBox.firstChild
    );
}

// ========================
// TOGGLE MENU
// ========================
document.addEventListener('click', function (e) {
    // mở menu hành động (đổ xúc xắc ⋯)
    if (e.target.classList.contains('dots-btn')) {
        e.stopPropagation();
        let menu = e.target.nextElementSibling;

        // đóng tất cả menu khác
        document.querySelectorAll('.message-menu').forEach(m => {
            if (m !== menu) {
                m.classList.remove('show');
            }
        });

        // Bật/tắt menu hiện tại
        if (menu) {
            menu.classList.toggle('show');
        }
    } else {
        // Click ra ngoài thì đóng toàn bộ menu đang mở
        document.querySelectorAll('.message-menu').forEach(m => {
            m.classList.remove('show');
        });
    }
});

// ========================
// RECALL MESSAGE
// ========================
function recallMessage(messageId) {

    fetch(
        `/messages/recall/${messageId}`,
        {

            method: 'POST',

            headers: {

                'X-CSRF-TOKEN':
                    document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,

                'Accept':
                    'application/json'
            }

        }
    )

        .then(res => res.json())

        .then(data => {

            if (!data.success) return;

            let messageWrapper =
                document.querySelector(
                    `.message-wrapper[data-id="${messageId}"]`
                );

            if (!messageWrapper) return;

            let isMe =
                messageWrapper.classList.contains(
                    'me'
                );

            messageWrapper.innerHTML = `
                <div class="message-recalled">
                    Tin nhắn đã được thu hồi
                </div>
            `;

            updateSidebar();

        })

        .catch(err => {
            console.log(
                "Recall error:",
                err
            );
        });

}

// click recall
document.addEventListener('click', function (e) {

    if (
        e.target.classList.contains(
            'recall-btn'
        )
    ) {

        let messageId =
            e.target.dataset.id;

        recallMessage(messageId);
    }

});

// ========================
// DELETE FOR ME
// ========================
document.addEventListener('click', function (e) {

    if (
        e.target.classList.contains(
            'delete-btn'
        )
    ) {

        let messageId =
            e.target.dataset.id;

        fetch(
            `/messages/delete-for-me/${messageId}`,
            {

                method: 'POST',

                headers: {

                    'X-CSRF-TOKEN':
                        document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,

                    'Accept':
                        'application/json'
                }

            }
        )

            .then(res => res.json())

            .then(data => {

                if (!data.success) return;

                let message =
                    document.querySelector(
                        `.message-wrapper[data-id="${messageId}"]`
                    );

                if (message) {
                    message.remove();
                }

                updateSidebar();

            })

            .catch(err => {

                console.log(
                    "Delete error:",
                    err
                );

            });

    }

});

// ========================
// SIDEBAR UPDATE
// ========================
function updateSidebar() {

    fetch(
        `/messages/conversations/${conversationId}`
    )

        .then(res =>
            res.ok
                ? res.text()
                : null
        )

        .then(html => {

            if (!html) return;

            const list =
                document.querySelector(
                    '.scrollable-list'
                );

            if (
                list &&
                list.innerHTML.trim() !==
                html.trim()
            ) {

                list.innerHTML = html;

            }

        });

}

// ========================
// POLLING
// ========================
function startPolling() {

    if (
        !window.location.pathname.includes(
            'chat-messages'
        )
    ) return;

    messageInterval = setInterval(() => {

        loadMessages();

        updateSidebar();

        syncReadStatus();

    }, 2000);

}

function stopPolling() {

    clearInterval(
        messageInterval
    );

}

// ========================
// INIT
// ========================
// ========================
// INIT (Code Mới)
// ========================
document.addEventListener('DOMContentLoaded', function () {
    // 1. Đánh dấu đã xem
    markAsRead();

    // 2. Ép cuộn xuống đáy sau khi DOM đã ổn định
    setTimeout(() => {
        forceScrollBottom();
    }, 100);

    // 3. Nếu trong chat có nhiều ảnh, đợi ảnh tải xong rồi cuộn lại phát nữa cho chắc
    window.addEventListener('load', () => {
        forceScrollBottom();
    });

    // 4. Bắt đầu gọi vòng lặp lấy tin nhắn mới
    startPolling();
});
function syncReadStatus() {

    fetch(`/messages/${conversationId}/read-status`)

        .then(res => res.json())

        .then(data => {

            data.forEach(msg => {

                let wrapper = document.querySelector(
                    `.message-wrapper[data-id="${msg.id}"]`
                );

                if (!wrapper) return;

                let status = wrapper.querySelector(
                    '.message-status'
                );

                if (!status) return;

                status.innerText =
                    msg.is_read ? 'Đã xem' : 'Đã gửi';

            });

        });

}
function markAsRead() {

    fetch(`/messages/${conversationId}/mark-read`, {

        method: 'POST',

        headers: {

            'X-CSRF-TOKEN':
                document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,

            'Accept': 'application/json'
        }

    });

}
document.addEventListener(
    'DOMContentLoaded',
    function () {

        const imageBtn =
            document.getElementById(
                'image-btn'
            );

        const imageInput =
            document.getElementById(
                'image-input'
            );

        const previewContainer =
            document.getElementById(
                'image-preview-container'
            );

        // mở file explorer
        if (
            imageBtn &&
            imageInput
        ) {

            imageBtn.addEventListener(
                'click',
                function () {

                    imageInput.click();

                }
            );

        }

        // preview ảnh
        if (
            imageInput &&
            previewContainer
        ) {

            imageInput.addEventListener(
                'change',
                function () {

                    previewContainer.innerHTML =
                        '';

                    const file =
                        this.files[0];

                    if (!file) return;

                    const wrapper =
                        document.createElement(
                            'div'
                        );

                    wrapper.className =
                        'preview-wrapper';

                    const img =
                        document.createElement(
                            'img'
                        );

                    img.src =
                        URL.createObjectURL(
                            file
                        );

                    img.className =
                        'preview-image';

                    // nút xoá
                    const removeBtn =
                        document.createElement(
                            'button'
                        );

                    removeBtn.type =
                        'button';

                    removeBtn.innerHTML =
                        '✕';

                    removeBtn.className =
                        'remove-preview';

                    removeBtn.addEventListener(
                        'click',
                        function () {

                            imageInput.value =
                                '';

                            previewContainer.innerHTML =
                                '';

                        }
                    );

                    wrapper.appendChild(
                        img
                    );

                    wrapper.appendChild(
                        removeBtn
                    );

                    previewContainer.appendChild(
                        wrapper
                    );

                }
            );

        }

    }
);