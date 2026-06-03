<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoryController extends Controller
{
    public function store(Request $request)
    {
        // 1. Kiểm duyệt dữ liệu đầu vào (Max 20MB cho cả hình và video ngắn)
        $request->validate([
            'media' => 'required|file|mimes:jpeg,png,jpg,mp4,mov|max:20480',
            'content' => 'nullable|string|max:255',
        ], [
            // Đắp bùa Tiếng Việt vô đây nè Pro
            'media.required' => 'Ê, chưa chọn hình hay video kìa!',
            'media.file' => 'File không hợp lệ!',
            'media.mimes' => 'Chỉ chấp nhận hình ảnh (jpg, png, webp...) hoặc video (mp4, mov, avi).',
            'media.max' => 'Giới hạn dưới 20MB .',
        ]);

        $file = $request->file('media');

        // 2. Tự động nhận diện nó là video hay hình ảnh
        $mediaType = str_contains($file->getMimeType(), 'video') ? 'video' : 'image';

        // 3. Quăng file vô ổ cứng (storage/app/public/stories)
        $path = $file->store('stories', 'public');

        // 4. Ghi vô Database kèm theo mốc thời gian tử thần (Đúng 24h sau)
        Story::create([
            'user_id' => auth()->id(),
            'media_path' => $path,
            'media_type' => $mediaType,
            'content' => $request->content,
            'expires_at' => now()->addMinute(24)
        ]);

        // 5. Quay xe về trang cũ và báo thành công
        return back()->with('success', 'Story đã lên sóng thành công!');
    }
}