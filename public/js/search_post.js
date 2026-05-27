document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('search-input');
    const resultsBox = document.getElementById('search-results');

    if (!searchInput || !resultsBox) return;

    let debounceTimer;
    let lastQuery = '';
    let lastPostHTML = '';

    // SEARCH POSTS
    async function searchPosts(query) {

        try {

            const response = await fetch(
                `/ajax/posts?q=${encodeURIComponent(query)}`,
                {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );

            const result = await response.json();

            const posts = result.data?.data || [];

            // Xóa section post cũ
            const oldPostSection =
                resultsBox.querySelector('.post-search-section');

            if (oldPostSection) {
                oldPostSection.remove();
            }

            // Không có post
            if (!posts.length) {
                lastPostHTML = '';
                return;
            }

            // Xóa dòng "Không tìm thấy kết quả nào"
            const noResult =
                resultsBox.querySelector('.text-muted');

            if (noResult) {
                noResult.remove();
            }

            let html = `
                <div class="post-search-section">

                    <div style="
                        padding:8px 12px;
                        font-size:12px;
                        font-weight:bold;
                        color:#888;
                        background:#f8f8f8;
                        border-bottom:1px solid #eee;
                    ">
                        BÀI VIẾT
                    </div>
            `;

            posts.forEach(post => {

                const cleanContent = post.content
                    ? post.content.replace('[#LOCK_COMMENT#]', '')
                    : '';

                const preview = cleanContent
                    ? cleanContent.substring(0, 45)
                    : 'Không có nội dung';

                const avatar =
                    post.user?.avatar_url ||
                    '/img/user/user.jpg';

                html += `
                    <div class="result-item"
                        onclick="window.location.href='/posts/${post.id}'"
                        style="
                            padding:10px 12px;
                            cursor:pointer;
                            border-bottom:1px solid #eee;
                            display:flex;
                            gap:10px;
                            align-items:flex-start;
                            transition:0.2s;
                        "
                    >

                        <img src="${avatar}"
                            style="
                                width:38px;
                                height:38px;
                                border-radius:50%;
                                object-fit:cover;
                                flex-shrink:0;
                            "
                        >

                        <div style="flex:1; min-width:0;">

                            <div style="
                                font-weight:600;
                                color:#222;
                                font-size:14px;
                            ">
                                ${post.user?.fullname ?? 'Người dùng'}
                            </div>

                            <div style="
                                color:#666;
                                font-size:13px;
                                margin-top:2px;
                                white-space:nowrap;
                                overflow:hidden;
                                text-overflow:ellipsis;
                            ">
                                ${preview}
                            </div>

                        </div>

                    </div>
                `;
            });

            html += `</div>`;

            // cache HTML
            lastPostHTML = html;

            // render mới
            resultsBox.insertAdjacentHTML(
                'beforeend',
                html
            );

            resultsBox.style.display = 'block';

        } catch (err) {
            console.error('Search post error:', err);
        }
    }

    // INPUT SEARCH
    searchInput.addEventListener('input', function () {

        clearTimeout(debounceTimer);

        const query = this.value.trim();

        debounceTimer = setTimeout(() => {

            // chưa đủ ký tự thì thôi
            if (query.length < 1) {

                const oldPostSection =
                    resultsBox.querySelector('.post-search-section');

                if (oldPostSection) {
                    oldPostSection.remove();
                }

                return;
            }

            // tránh search lại query cũ
            if (query === lastQuery) return;

            lastQuery = query;

            searchPosts(query);

        }, 80); // mượt hơn 300ms
    });

    // CLICK INPUT => hiện lại kết quả
    let typingTimer;

    searchInput.addEventListener('keyup', function () {

        clearTimeout(typingTimer);

        const query = this.value.trim();

        // ít hơn 2 ký tự thì khỏi search
        if (query.length < 2) {
            return;
        }

        typingTimer = setTimeout(() => {

            // chỉ search sau khi ngừng gõ
            searchPosts(query);

        }, 350);
    });

    // CLICK NGOÀI => ẨN
    document.addEventListener('click', function (e) {

        if (
            !searchInput.contains(e.target) &&
            !resultsBox.contains(e.target)
        ) {
            resultsBox.style.display = 'none';
        }
    });

});