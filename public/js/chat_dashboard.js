document.addEventListener('DOMContentLoaded', function () {

    function loadUnreadCount() {

        fetch('/messages/unread-count')
            .then(response => {

                console.log('Status:', response.status);

                return response.json();
            })
            .then(data => {

                console.log('DATA:', data);

                const badge = document.getElementById('message-badge');

                if (!badge) {
                    console.log('Không tìm thấy badge');
                    return;
                }

                // lấy count
                let count = Number(data.count);

                console.log('COUNT:', count);

                if (count > 0) {

                    badge.style.display = 'inline-block';

                    badge.innerText = count > 99
                        ? '99+'
                        : count;

                } else {

                    badge.style.display = 'none';
                }

            })
            .catch(error => {
                console.error('ERROR:', error);
            });
    }

    loadUnreadCount();

    setInterval(loadUnreadCount, 3000);

});