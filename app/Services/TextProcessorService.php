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
}