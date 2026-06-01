function shareLinkJS(profileUrl) {
    // 1. Nếu trình duyệt có hỗ trợ Web Share API (Mobile hoặc Trình duyệt xịn)
    if (navigator.share) {
        navigator.share({
            title: 'Xem trang cá nhân',
            text: 'Tui thấy trang cá nhân này xịn nè, vô xem thử đi!',
            url: profileUrl
        })
            .then(() => console.log('Chia sẻ thành công!'))
            .catch((error) => console.log('User hủy chia sẻ', error));
    }
    // 2. Nếu xài PC hoặc trình duyệt không hỗ trợ -> Fallback về Copy Link
    else {
        navigator.clipboard.writeText(profileUrl).then(function () {
            // Tận dụng lại hàm Toast
            if (typeof showToastJS === 'function') {
                showToastJS('Đã sao chép liên kết!', 'success');
            } else {
                alert('Đã sao chép liên kết!');
            }
        }).catch(function (err) {
            if (typeof showToastJS === 'function') {
                showToastJS('Không thể sao chép liên kết!', 'error');
            }
            console.error('Lỗi chép Clipboard: ', err);
        });
    }
}