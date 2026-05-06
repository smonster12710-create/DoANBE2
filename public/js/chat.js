// ========================
// INIT ELEMENTS
// ========================
let chatBox = document.getElementById('chat-box');
let conversationId = chatBox.dataset.conversation;
let currentUserId = chatBox.dataset.user;

let lastMessageId = chatBox.dataset.lastId || 0; // Lấy ID cuối cùng đã có sẵn trong HTML
let isSending = false;
// Hàm cuộn xuống đáy không điều kiện (dùng cho lần đầu vào trang)
function forceScrollBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}
let isLoadingOlder = false;
let firstMessageId = chatBox.dataset.firstId; // Lấy từ data-first-id bạn đã đặt trong Blade

chatBox.addEventListener('scroll', function () {
    // Nếu cuộn lên đỉnh và không đang trong quá trình load
    if (chatBox.scrollTop === 0 && !isLoadingOlder && firstMessageId > 0) {
        loadOlderMessages();
    }
});

function loadOlderMessages() {
    isLoadingOlder = true;

    // Lưu lại chiều cao trước khi chèn tin nhắn mới
    const oldScrollHeight = chatBox.scrollHeight;

    fetch(`/messages/${conversationId}/older?first_id=${firstMessageId}`)
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                // Duyệt ngược data để chèn vào đầu chat-box
                data.reverse().forEach(msg => {
                    prependMessage(msg);
                });

                // Cập nhật lại firstMessageId là ID của tin nhắn cũ nhất vừa tải
                firstMessageId = data[data.length - 1].id;

                // GIỮ VỊ TRÍ CUỘN: 
                // Chiều cao mới - Chiều cao cũ = Khoảng cách cần bù đắp
                const newScrollHeight = chatBox.scrollHeight;
                chatBox.scrollTop = newScrollHeight - oldScrollHeight;
            } else {
                firstMessageId = 0; // Hết tin nhắn cũ để tải
            }
        })
        .finally(() => {
            isLoadingOlder = false;
        });
}

// Hàm chèn tin nhắn vào đầu danh sách
function prependMessage(msg) {
    let isMe = msg.sender_id == currentUserId;
    let div = document.createElement('div');
    div.className = 'message-wrapper ' + (isMe ? 'me' : 'them');
    div.innerHTML = `<div class="message-bubble">${msg.content}</div>`;

    // Chèn vào vị trí đầu tiên của chatBox
    chatBox.insertBefore(div, chatBox.firstChild);
}
// Khi trang vừa load xong
document.addEventListener('DOMContentLoaded', function () {
    forceScrollBottom();

    // Nếu bạn có dùng hình ảnh trong tin nhắn, 
    // hãy đợi ảnh load xong rồi cuộn lần nữa cho chắc chắn
    window.onload = forceScrollBottom;
});
document.querySelector('.chat-input').addEventListener('submit', function (e) {
    e.preventDefault();

    const input = this.querySelector('input[name="content"]');
    const messageText = input.value.trim(); // Lấy giá trị và trim luôn

    // Nếu đang gửi hoặc nội dung trống thì thoát ngay
    if (isSending || messageText === "") return;

    isSending = true;
    input.value = ""; // Xóa giao diện ngay lập tức cho người dùng rảnh tay
    input.disabled = true; // Khóa ô nhập liệu tạm thời

    let formData = new FormData();
    formData.append('content', messageText); // Dùng giá trị đã lưu trong biến, không lấy từ input nữa
    formData.append('conversation_id', this.querySelector('input[name="conversation_id"]').value);
    formData.append('_token', this.querySelector('input[name="_token"]').value);

    fetch('/messages/send', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            // Chỉ thêm vào nếu data trả về có nội dung (tránh server trả về null)
            if (data && data.content) {
                loadMessages();
            }
        })
        .finally(() => {
            isSending = false;
            input.disabled = false;
            input.focus();
        });
});
// ========================
// LOAD MESSAGES FUNCTION
function loadMessages() {
    fetch(`/messages/${conversationId}?last_id=${lastMessageId}`)
        .then(res => res.json())
        .then(data => {
            // Kiểm tra xem có tin nhắn mới thực sự không
            if (data.length > 0) {
                let hasNewMessage = false;

                data.forEach(msg => {
                    if (msg.id > lastMessageId && msg.content) {
                        appendMessage(msg);
                        lastMessageId = msg.id;
                        hasNewMessage = true; // Xác nhận có tin mới
                    }
                });

                // Nếu có tin mới thì mới kích hoạt hàm cuộn
                if (hasNewMessage) {
                    scrollBottom();
                }
            }
        });
}
// ========================
// SEND MESSAGE (Giữ nguyên của bạn)
// ========================
document.querySelector('.chat-input').addEventListener('submit', function (e) {
    e.preventDefault();
    let formData = new FormData(this);
    let input = this.querySelector('input[name="content"]');

    fetch('/messages/send', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': this.querySelector('input[name="_token"]').value
        },
        body: formData
    })
        .then(res => res.json())
        .then(() => {
            input.value = '';
            loadMessages(); // Load ngay lập tức sau khi gửi
        });
});

function appendMessage(msg) {
    // CHẶN TRIỆT ĐỂ: Nếu nội dung null, rỗng hoặc chỉ có khoảng trắng thì biến mất luôn
    if (!msg.content || msg.content.trim() === "") {
        return;
    }

    let isMe = msg.sender_id == currentUserId;
    let div = document.createElement('div');
    div.className = 'message-wrapper ' + (isMe ? 'me' : 'them');
    div.innerHTML = `<div class="message-bubble">${msg.content}</div>`;
    chatBox.appendChild(div);
}

function scrollBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}

// KHỞI CHẠY
loadMessages(); // Chạy lần đầu
setInterval(loadMessages, 500); // Cứ 0.5 giây check một lần