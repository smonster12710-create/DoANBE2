
document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('click', function (e) {

            // ❌ Nếu click vào button, form, link thì bỏ qua
            if (
                e.target.closest('button') ||
                e.target.closest('form') ||
                e.target.closest('a')
            ) {
                return;
            }

            // ✅ chuyển trang
            window.location.href = this.dataset.url;
        });
});
