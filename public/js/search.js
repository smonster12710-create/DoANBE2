// public/js/search.js
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const resultsBox = document.getElementById('search-results');

    if (!searchInput || !resultsBox) return;

    searchInput.addEventListener('input', function () {
        let query = this.value.trim();

        if (query.length > 1) {
            let safeQuery = encodeURIComponent(query);

            // Gọi đúng các Route prefix 'ajax' anh em mình đã chia
            Promise.all([
                fetch(`/ajax/users?q=${safeQuery}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(res => res.json()),
                fetch(`/ajax/hashtags?q=${safeQuery}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(res => res.json())
            ])
                .then(([userResult, hashtagResult]) => {
                    let html = '';

                    // Laravel Paginate trả về dữ liệu trong data.data
                    const users = userResult.data ? userResult.data.data : [];
                    const hashtags = hashtagResult.data ? hashtagResult.data.data : [];

                    // 1. RENDER HASHTAGS (XU HƯỚNG)
                    if (hashtags && hashtags.length > 0) {
                        html += '<div style="padding: 8px 12px; font-size: 12px; font-weight: bold; color: #888; background: #f9f9f9;">HASHTAG LIÊN QUAN</div>';

                        hashtags.forEach(tag => {
                            // Link chuẩn: /hashtag?q=ten_hashtag
                            html += `
                            <div class="result-item" onclick="window.location.href='/hashtag?q=${tag.name}'" style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; display: flex; align-items: center;">
                                <div style="background: #e8f5fe; color: #1DA1F2; width: 35px; height: 35px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; margin-right: 12px;">#</div>
                                <div>
                                    <div style="font-weight: bold; color: #333;">${tag.name}</div>
                                    <div style="font-size: 12px; color: #888;">${tag.posts_count || 0} bài viết</div>
                                </div>
                            </div>
                        `;
                        });
                    }

                    // 2. RENDER USERS (NGƯỜI DÙNG)
                    if (users && users.length > 0) {
                        html += '<div style="padding: 8px 12px; font-size: 12px; font-weight: bold; color: #888; background: #f9f9f9;">NGƯỜI DÙNG</div>';

                        users.forEach(user => {
                            // Link chuẩn: /profile/username
                            // avatar_url hoặc avatar tùy theo cột Pro select trong Controller nhen
                            let userImg = user.avatar || user.avatar_url || '/images/default-avatar.png';

                            html += `
                            <div class="result-item" onclick="window.location.href='/profile/${user.username}'" style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 12px;">
                                <div class="avatar-online-wrap">
                                    <img src="${userImg}" alt="avatar" style="width:35px; height:35px; border-radius:50%; object-fit: cover;">
                                    ${user.can_show_activity ? '<span class="online-dot"></span>' : ''}
                                </div>
                                <div>
                                    <div style="font-weight: bold; color: #333;">${user.fullname}</div>
                                    <div style="font-size: 12px; color: #888;">@${user.username}</div>
                                </div>
                            </div>
                        `;
                        });
                    }

                    // 3. KHÔNG CÓ KẾT QUẢ
                    if (html === '') {
                        html = '<div class="p-3 text-muted text-center">Không tìm thấy kết quả nào</div>';
                    }

                    resultsBox.innerHTML = html;
                    resultsBox.style.display = 'block';
                })
                .catch(error => console.error('Lỗi khi fetch search data:', error));
        } else {
            resultsBox.style.display = 'none';
        }
    });

    // Ẩn khi click ra ngoài
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.style.display = 'none';
        }
    });
});
