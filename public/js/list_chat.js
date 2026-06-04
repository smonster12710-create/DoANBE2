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

document.addEventListener('DOMContentLoaded', function () {
    // Khai báo các phần tử điều khiển modal và form
    const modal = document.getElementById('createGroupModal');
    const formCreateGroup = document.getElementById('formCreateGroup');
    const btnOpen = document.getElementById('openCreateGroupModal');
    const btnClose = document.getElementById('closeCreateGroupModal');
    const btnCancel = document.getElementById('btnCancelModal');

    // Khai báo cấu trúc các bước (Steps)
    const step1 = document.getElementById('group-step-1');
    const step2 = document.getElementById('group-step-2');
    const btnNext = document.getElementById('btnNextStep');
    const btnBack = document.getElementById('btnBackStep');
    const btnSubmit = document.getElementById('btnSubmitGroup');
    const modalTitle = document.getElementById('modal-group-title');

    // Khai báo tìm kiếm và upload ảnh
    const searchInput = document.getElementById('modal-search-friends');
    const friendItems = document.querySelectorAll('.friend-select-item');
    const emptyMessage = document.getElementById('search-empty-message');
    const avatarInput = document.getElementById('group_avatar');
    const avatarPreview = document.getElementById('group-avatar-preview');

    // 🔥 HÀM TẠO THÔNG BÁO POP-UP (TOAST) TỰ ĐỘNG ẨN SAU 3 GIÂY
    window.showToast = function (message, type = 'warning') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        // Tạo thẻ div thông báo mới
        const toast = document.createElement('div');
        toast.className = `custom-toast toast-${type}`;

        // Thêm icon nhỏ cho sinh động
        let icon = '⚠️';
        if (type === 'success') icon = '✅';
        if (type === 'error') icon = '❌';

        toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
        container.appendChild(toast);

        // Kích hoạt hiệu ứng trượt vào sau 10ms
        setTimeout(() => toast.classList.add('show'), 10);

        // Tự động biến mất và xóa bỏ sau 3 giây
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300); // Đợi hiệu ứng ẩn xong thì xóa thẻ HTML
        }, 3000);
    };

    // --- HÀM CHUYỂN ĐỔI GIAO DIỆN GIỮA CÁC BƯỚC ---
    const goToStep = (stepNumber) => {
        if (stepNumber === 1) {
            step1.style.display = 'block';
            step2.style.display = 'none';
            btnCancel.style.display = 'block';
            btnNext.style.display = 'block';
            btnBack.style.display = 'none';
            btnSubmit.style.display = 'none';
            if (modalTitle) modalTitle.textContent = 'Tạo nhóm chat mới';
        } else if (stepNumber === 2) {
            step1.style.display = 'none';
            step2.style.display = 'block';
            btnCancel.style.display = 'none';
            btnNext.style.display = 'none';
            btnBack.style.display = 'block';
            btnSubmit.style.display = 'block';
            if (modalTitle) modalTitle.textContent = 'Thông tin nhóm chat';

            // Tự động focus vào ô nhập tên nhóm cho mượt
            setTimeout(() => {
                const groupNameInput = document.getElementById('group_name');
                if (groupNameInput) groupNameInput.focus();
            }, 100);
        }
    };

    // --- HÀM ĐÓNG MODAL VÀ HOÀN TÁC TOÀN BỘ FORM ---
    const closeModalAndReset = () => {
        if (modal) modal.classList.remove('show');
        if (formCreateGroup) formCreateGroup.reset(); // Xóa sạch dữ liệu đã nhập/tích chọn
        if (avatarPreview) avatarPreview.src = "https://ui-avatars.com/api/?name=Group&background=0084ff&color=fff&size=100";

        // Reset bộ lọc tìm kiếm
        if (searchInput) searchInput.value = '';
        if (friendItems) {
            friendItems.forEach(item => item.style.setProperty('display', 'flex', 'important'));
        }
        if (emptyMessage) emptyMessage.style.display = 'none';

        // Trả form về bước 1
        goToStep(1);
    };

    // --- XỬ LÝ ĐÓNG / MỞ MODAL ---
    if (btnOpen && modal) {
        btnOpen.addEventListener('click', () => modal.classList.add('show'));
        if (btnClose) btnClose.addEventListener('click', closeModalAndReset);
        if (btnCancel) btnCancel.addEventListener('click', closeModalAndReset);
        window.addEventListener('click', (e) => { if (e.target === modal) closeModalAndReset(); });
    }

    // --- XỬ LÝ LOGIC ĐI TIẾP / QUAY LẠI ---
    if (btnNext) {
        btnNext.addEventListener('click', function () {
            // Kiểm tra xem người dùng đã chọn ít nhất 1 thành viên chưa
            const checkedMembers = document.querySelectorAll('input[name="user_ids[]"]:checked');
            if (checkedMembers.length === 0) {
                showToast('Cậu phải chọn ít nhất 1 thành viên để tạo nhóm nhé!', 'warning'); // Đã dọn sạch chữ S thừa ở đây
                return;
            }
            goToStep(2); // Thỏa mãn điều kiện thì qua bước đặt tên
        });
    }

    if (btnBack) {
        btnBack.addEventListener('click', () => goToStep(1));
    }

    // --- XỬ LÝ PREVIEW ẢNH ĐẠI DIỆN KHI CHỌN FILE ---
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    avatarPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // --- CHẶN SUBMIT TRANG VÀ BẮN AJAX LÊN SERVER ---
    // --- CHẶN SUBMIT TRANG VÀ BẮN AJAX LÊN SERVER (CÓ TÍCH HỢP NÉN ẢNH NHÓM LỚN) ---
    if (formCreateGroup) {
        formCreateGroup.addEventListener('submit', async function (e) { // 🌟 Thêm chữ async ở đây để dùng await
            e.preventDefault();

            // Kiểm tra tên nhóm lại lần cuối
            const groupNameInput = document.getElementById('group_name');
            if (!groupNameInput || !groupNameInput.value.trim()) {
                showToast('Cậu chưa nhập tên nhóm kìa!', 'error');
                if (groupNameInput) groupNameInput.focus();
                return;
            }

            // Vô hiệu hóa nút bấm tạm thời để tránh người dùng nhấn Double-click tạo nhóm trùng lặp
            if (btnSubmit) btnSubmit.disabled = true;
            showToast('Đang tiến hành tạo nhóm, cậu đợi xíu nhé...', 'success');

            // Gom toàn bộ dữ liệu từ Form (bao gồm chữ, ảnh và mảng checkbox thành viên)
            const formData = new FormData(this);

            // 🔥 LOGIC KIỂM TRA VÀ NÉN ẢNH ĐẠI DIỆN NHÓM TẠI TRÌNH DUYỆT
            if (avatarInput && avatarInput.files.length > 0) {
                const avatarFile = avatarInput.files[0];

                // Chỉ nén nếu dung lượng file lớn hơn 1.5 MB để tiết kiệm thời gian
                if (avatarFile.size > 1.5 * 1024 * 1024) {
                    console.log('⚡ Ảnh đại diện nhóm > 1.5MB, tiến hành nén...');

                    const options = {
                        maxSizeMB: 0.8,          // Đưa dung lượng về dưới 0.8 MB
                        maxWidthOrHeight: 800,   // Ảnh nhóm chỉ cần tối đa 800px là siêu nét rồi
                        useWebWorker: true
                    };

                    try {
                        // Gọi hàm nén an toàn qua đối tượng window toàn cục
                        const compressor = window.imageCompression || imageCompression;

                        if (typeof compressor === 'function') {
                            const compressedAvatar = await compressor(avatarFile, options);

                            console.log('✅ Nén ảnh nhóm thành công! Cũ:', (avatarFile.size / 1024 / 1024).toFixed(2), 'MB');
                            console.log('🎉 Mới gửi lên server:', (compressedAvatar.size / 1024 / 1024).toFixed(2), 'MB');

                            // Ghi đè file ảnh đã nén vào FormData (trùng tên với name="group_avatar" ở input)
                            formData.set('group_avatar', compressedAvatar, avatarFile.name);
                        } else {
                            console.warn('⚠️ Thư viện nén ảnh chưa sẵn sàng, giữ nguyên file gốc.');
                        }
                    } catch (error) {
                        console.error('❌ Lỗi nén ảnh nhóm bằng JS:', error);
                    }
                }
            }

            // Gửi dữ liệu bằng Fetch API lên Route Laravel
            fetch('/chat/group/create', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    // Bốc mã bảo mật CSRF Token gắn trực tiếp trên website để Laravel thông qua bảo mật
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');

                        // Tạo thành công thì đóng modal và dọn dẹp form ngay lập tức
                        closeModalAndReset();

                        // Chuyển hướng người dùng vào thẳng trang chat chi tiết của phòng vừa tạo sau 1 giây
                        setTimeout(() => {
                            window.location.href = '/chat-messages/' + data.room_id;
                        }, 1000);
                    } else {
                        showToast(data.message || 'Tạo nhóm thất bại!', 'error');
                        if (btnSubmit) btnSubmit.disabled = false; // Mở lại nút nếu lỗi
                    }
                })
                .catch(error => {
                    console.error('Lỗi AJAX:', error);
                    showToast('Không thể kết nối đến máy chủ!', 'error');
                    if (btnSubmit) btnSubmit.disabled = false; // Mở lại nút nếu lỗi mạng
                });
        });
    }
    // --- HỆ THỐNG TÌM KIẾM THÀNH VIÊN ---
    if (searchInput) {
        searchInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') e.preventDefault(); });
        searchInput.addEventListener('input', function () {
            const keyword = this.value.toLowerCase().trim();
            let hasResults = false;

            friendItems.forEach(function (item) {
                const nameText = item.querySelector('.friend-name').textContent.toLowerCase();
                if (nameText.includes(keyword)) {
                    item.style.setProperty('display', 'flex', 'important');
                    hasResults = true;
                } else {
                    item.style.setProperty('display', 'none', 'important');
                }
            });
            if (emptyMessage) emptyMessage.style.display = hasResults ? 'none' : 'block';
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    // Định danh ô tìm kiếm dựa vào ID cậu vừa gửi
    const searchInput = document.getElementById('sidebar-search');

    // Hàm hỗ trợ bỏ dấu tiếng Việt để gõ "tuan" vẫn ra "Tuấn"
    function removeVietnameseTones(str) {
        return str.normalize('NFD')
                  .replace(/[\u0300-\u036f]/g, '')
                  .replace(/đ/g, 'd').replace(/Đ/g, 'd');
    }

    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            const keyword = removeVietnameseTones(e.target.value.toLowerCase().trim());
            
            // Lấy toàn bộ danh sách các item chat đang hiển thị ở file partial
            const chatItems = document.querySelectorAll('.message-item-link');

            chatItems.forEach(item => {
                // Lấy tên chat từ data attribute
                const chatName = removeVietnameseTones(item.getAttribute('data-chat-name') || '');

                if (chatName.includes(keyword)) {
                    item.style.display = ''; // Khớp thì hiện
                } else {
                    item.style.display = 'none'; // Không khớp thì ẩn đi
                }
            });
        });
    }
});
