document.addEventListener("DOMContentLoaded", function () {
    document.addEventListener("submit", function (e) {
        const form = e.target;
        if (!form || !form.classList.contains("like-form")) return;

        e.preventDefault(); 

        const button = form.querySelector(".btn-like-ajax");
        const icon = form.querySelector(".like-icon");
        const countText = form.querySelector(".like-count-text");
        const token = form.querySelector('input[name="_token"]').value;

        // --- BƯỚC TỐI ƯU: Optimistic UI (Cập nhật giả lập trước) ---
        const isCurrentlyLiked = button.classList.contains("text-danger");
        let currentCount = parseInt(countText.innerText) || 0;

        // Đảo ngược trạng thái trên UI ngay lập tức
        if (isCurrentlyLiked) {
            button.classList.remove("text-danger");
            icon.setAttribute("fill", "none");
            countText.innerText = Math.max(0, currentCount - 1);
        } else {
            button.classList.add("text-danger");
            icon.setAttribute("fill", "currentColor");
            countText.innerText = currentCount + 1;
        }

        // Khóa click để tránh gửi nhiều request cùng lúc
        button.style.pointerEvents = "none";

        fetch(form.action, {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": token,
                "Accept": "application/json",
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            // Cập nhật lại số chuẩn từ server (để đồng bộ nếu có người khác cũng like)
            button.classList.toggle("text-danger", data.isLiked);
            icon.setAttribute("fill", data.isLiked ? "currentColor" : "none");
            countText.innerText = data.likeCount;
        })
        .catch(err => {
            console.error("Lỗi:", err);
            // HOÀN TÁC (Rollback) UI nếu lỗi xảy ra
            button.classList.toggle("text-danger", isCurrentlyLiked);
            icon.setAttribute("fill", isCurrentlyLiked ? "currentColor" : "none");
            countText.innerText = currentCount;
            alert("Không thể kết nối đến máy chủ!");
        })
        .finally(() => {
            button.style.pointerEvents = "auto";
        });
    });
});