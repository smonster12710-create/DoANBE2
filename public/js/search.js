// public/js/search.js
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const resultsBox = document.getElementById('search-results');

    if (!searchInput || !resultsBox) return;

    searchInput.addEventListener('input', function () {
        let query = this.value.trim();

        if (query.length > 1) {
            let safeQuery = encodeURIComponent(query);
            Promise.all([
                fetch(`/user?q=${safeQuery}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(res => res.json()),
                fetch(`/hashtag?q=${safeQuery}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(res => res.json())
            ])
                .then(([userResult, hashtagResult]) => {
                    let html = '';

                    // Bóc tách dữ liệu từ phân trang của Laravel (result.data.data)
                    const users = userResult.data ? userResult.data.data : [];
                    const hashtags = hashtagResult.data ? hashtagResult.data.data : [];

                    // 1. RENDER HASHTAGS (XU HƯỚNG) - Cho ưu tiên nằm trên
                    if (hashtags && hashtags.length > 0) {
                        html += '<div style="padding: 8px 12px; font-size: 12px; font-weight: bold; color: #888; background: #f9f9f9;">XU HƯỚNG</div>';

                        hashtags.forEach(tag => {
                            html += `
                            <div class="result-item" onclick="window.location.href='/hashtag/${tag.name}'" style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; display: flex; align-items: center;">
                                <div style="background: #e8f5fe; color: #1DA1F2; width: 35px; height: 35px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; margin-right: 12px;">#</div>
                                <div>
                                    <div style="font-weight: bold; color: #333;">${tag.name}</div>
                                    <div style="font-size: 12px; color: #888;">${tag.usage_count || 0} bài viết</div>
                                </div>
                            </div>
                        `;
                        });
                    }

                    // 2. RENDER USERS (NGƯỜI DÙNG) - Nằm dưới
                    if (users && users.length > 0) {
                        html += '<div style="padding: 8px 12px; font-size: 12px; font-weight: bold; color: #888; background: #f9f9f9;">NGƯỜI DÙNG</div>';

                        users.forEach(user => {
                            html += `
                            <div class="result-item" onclick="window.location.href='#'" style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 12px;">
                                <img src="${user.avatar_url || '/images/default-avatar.png'}" alt="avatar" style="width:35px; height:35px; border-radius:50%; object-fit: cover;">
                                <div>
                                    <div style="font-weight: bold; color: #333;">${user.username}</div>
                                    <div style="font-size: 12px; color: #888;">Cá nhân</div>
                                </div>
                            </div>
                        `;
                        });
                    }

                    // 3. TRƯỜNG HỢP TRẮNG TAY (Không có cả 2)
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