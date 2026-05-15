<?php

namespace App\Services;

class TextProcessorService
{
    /**
     * Dịch vụ 1: Lấy danh sách Mention (@)
     */
    public function getMentions($content)
    {
        return $this->extractBySymbol($content, '@');
    }

    /**
     * Dịch vụ 2: Lấy danh sách Hashtag (#)
     */
    public function getHashtags($content)
    {
        return $this->extractBySymbol($content, '#');
    }

    /**
     * Lò phản ứng hạt nhân (Hàm nội bộ chạy ngầm cho 2 hàm trên)
     */
    private function extractBySymbol($content, $symbol)
    {
        // Ghép cái ký hiệu (@ hoặc #) vô Regex chuẩn có hỗ trợ tiếng Việt
        $pattern = '/(?<=^|\s)' . $symbol . '([\p{L}\p{N}_]+)/u';

        preg_match_all($pattern, $content, $matches);

        // Trả về mảng đã loại bỏ trùng lặp
        return array_unique($matches[1]);
    }

    /**
     * DỊCH VỤ 3: TRANG ĐIỂM VĂN BẢN (Make up chữ đen thành Link màu xanh)
     * Đã sửa lại để nhận biến $content truyền vào từ bên ngoài
     */
    public function formatContent($content)
    {
        if (empty($content)) {
            return '';
        }

        // 1. Chống hack XSS
        $escapedContent = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

        // 2. Giữ nguyên định dạng xuống dòng
        $nl2brContent = nl2br($escapedContent);

        // 3. Regex an toàn hơn
        $regex = '/(^|\s)#([\p{L}\p{N}_]+)/u';

        // Xài url() để link luôn đúng dù Pro có đổi tên miền hay thư mục
        $hashtagUrl = url('/hashtag');

        // 4. THAY THẾ VÀ ĐẮP BÙA CHỐNG SỦI BỌT
        // Nhét onclick="event.stopPropagation();" vô thẻ a
        $replacement = '$1<a href="' . $hashtagUrl . '?q=$2" class="hashtag-link text-primary text-decoration-none fw-bold" onclick="event.stopPropagation();">#$2</a>';

        return preg_replace($regex, $replacement, $nl2brContent);
    }
}