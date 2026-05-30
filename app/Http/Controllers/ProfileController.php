<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;

class ProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Profile Display
    |--------------------------------------------------------------------------
    | Cac ham hien thi profile, thong ke ban be, follow va danh sach lien quan.
    */

    /**
     * Hiển thị trang cá nhân (Cho cả mình và người khác)
     */
    public function show($username)
    {
        /** @var \App\Models\User $user */
        $user = User::where('username', $username)->firstOrFail();

        $posts = \App\Models\Post::with(['user', 'media', 'likes', 'comments'])
            ->where('user_id', $user->id)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $authId = Auth::id();

        // 🌟 FIX LOGIC: Chỉ đếm số lượng bạn bè THỰC SỰ (đã accepted)
        $friendsCount = DB::table('friendships')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('friend_id', $user->id);
            })
            ->where('status', 'accepted')
            ->count();

        $postsCount = $posts->count();

        $followersCount = DB::table('follows')
            ->where('following_id', $user->id)
            ->count();

        $followingCount = DB::table('follows')
            ->where('follower_id', $user->id)
            ->count();

        // 🌟 FIX LOGIC: Biến $isFriend chỉ true khi trạng thái là 'accepted'
        $isFriend = DB::table('friendships')
            ->where('status', 'accepted')
            ->where(function ($q) use ($authId, $user) {
                $q->where(function ($inner) use ($authId, $user) {
                    $inner->where('user_id', $authId)->where('friend_id', $user->id);
                })->orWhere(function ($inner) use ($authId, $user) {
                    $inner->where('user_id', $user->id)->where('friend_id', $authId);
                });
            })
            ->exists();

        $isFollowing = DB::table('follows')
            ->where('follower_id', $authId)
            ->where('following_id', $user->id)
            ->exists();

        // KHÓA BẢO VỆ TRANG CÁ NHÂN
        // Chủ tài khoản, bạn bè và admin vẫn được xem.
        // Người lạ sẽ bị chặn.
        $isOwner = $authId == $user->id;

        $isAdmin = Auth::check() && Auth::user()->role === 'admin';

        if ($user->profile_locked && !$isOwner && !$isFriend && !$isAdmin) {
            return view('social.profile_private', compact('user'));
        }
        
        return view('social.profile', compact(
            'user',
            'posts',
            'friendsCount',
            'postsCount',
            'followersCount',
            'followingCount',
            'isFriend',
            'isFollowing'
        ));
    }
    /**
     * Them hoac huy ket ban voi user duoc xem profile.
     */
    // public function toggleFriend($username)
    // {
    //     $targetUser = User::where('username', $username)->firstOrFail();

    //     if (Auth::id() == $targetUser->id) {
    //         return back();
    //     }

    //     $authId = Auth::id();

    //     $friendship = DB::table('friendships')
    //         ->where(function ($q) use ($authId, $targetUser) {
    //             $q->where('user_id', $authId)
    //                 ->where('friend_id', $targetUser->id);
    //         })
    //         ->orWhere(function ($q) use ($authId, $targetUser) {
    //             $q->where('user_id', $targetUser->id)
    //                 ->where('friend_id', $authId);
    //         })
    //         ->first();

    //     if ($friendship) {
    //         DB::table('friendships')->where('id', $friendship->id)->delete();
    //     } else {
    //         DB::table('friendships')->insert([
    //             'user_id' => $authId,
    //             'friend_id' => $targetUser->id,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);
    //     }

    //     return back();
    // }

    // public function toggleFollow($username)
    // {
    //     $targetUser = User::where('username', $username)->firstOrFail();

    //     if (auth()->id() == $targetUser->id) {
    //         return back();
    //     }

    //     $authId = auth()->id();

    //     $follow = DB::table('follows')
    //         ->where('follower_id', $authId)
    //         ->where('following_id', $targetUser->id)
    //         ->first();

    //     if ($follow) {
    //         DB::table('follows')->where('id', $follow->id)->delete();
    //     } else {
    //         DB::table('follows')->insert([
    //             'follower_id' => $authId,
    //             'following_id' => $targetUser->id,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);
    //     }

    //     return back();
    // }
    /**
     * Hien thi danh sach ban be cua user theo username.
     */
    // public function friends($username)
    // {
    //     $user = User::where('username', $username)->firstOrFail();

    //     $friendIds = DB::table('friendships')
    //         ->where('user_id', $user->id)
    //         ->pluck('friend_id')
    //         ->merge(
    //             DB::table('friendships')
    //                 ->where('friend_id', $user->id)
    //                 ->pluck('user_id')
    //         );

    //     $users = User::whereIn('id', $friendIds)->get();

    //     $title = 'Bạn bè của bạn';

    //     return view('social.profile_list', compact('user', 'users', 'title'));
    // }

    /**
     * Hien thi danh sach nguoi dang theo doi user.
     */
    public function followers($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $ids = DB::table('follows')
            ->where('following_id', $user->id)
            ->pluck('follower_id');

        $users = User::whereIn('id', $ids)->get();

        $title = 'Người theo dõi của bạn';
        return view('social.profile_list', compact('user', 'users', 'title'));
    }

    /*
    |--------------------------------------------------------------------------
    | Profile Edit
    |--------------------------------------------------------------------------
    | Cac ham sua thong tin ca nhan va upload anh dai dien/anh bia.
    */

    /**
     * Hiển thị trang chỉnh sửa (Chỉ cho chính mình)
     */
    public function edit()
    {
        $user = Auth::user();
        return view('social.edit-profile', compact('user'));
    }

    /**
     * Xử lý cập nhật thông tin và upload ảnh
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cập nhật thông tin cơ bản
        $user->fill($request->only([
            'fullname',
            'username',
            'email',
            'bio',
            'birthday',
            'phone',
            'gender',
            'address'
        ]));

        // Xử lý Upload Avatar
        // Xử lý upload avatar: ảnh sẽ được resize và nén trước khi lưu.
        if ($request->hasFile('avatar')) {
            $avatarName = $this->saveCompressedProfileImage(
                $request->file('avatar'),
                public_path('uploads_profile/avatar'),
                'avatar',
                600,
                82
            );
            $user->avatar_url = 'uploads_profile/avatar/' . $avatarName;
        }

        // Xử lý Upload Cover
        // Xử lý upload ảnh bìa: cover cần rộng hơn avatar nên cho phép tối đa 1600px chiều ngang.
        if ($request->hasFile('cover')) {
            $coverName = $this->saveCompressedProfileImage(
                $request->file('cover'),
                public_path('uploads_profile/cover'),
                'cover',
                1600,
                82
            );
            $user->cover_url = 'uploads_profile/cover/' . $coverName;
        }

        $user->save();

        // Quay lại trang cá nhân của chính mình sau khi update
        return redirect()->route('profile.show', ['username' => $user->username])
            ->with('success', 'Đã cập nhật profile thành công!');
    }

    /**
     * Khóa bảo vệ trang cá nhân: Khi bật, chỉ bạn bè mới có thể xem được trang cá nhân của bạn.
     *  Người khác sẽ thấy thông báo "Trang cá nhân này được bảo vệ".
     *  Đây là tính năng bổ sung để tăng cường quyền riêng tư cho người dùng.
     */
    public function profileLock()
    {
        $user = auth()->user();

        return view('social.profile_lock', compact('user'));
    }

    public function toggleProfileLock()
    {
        $user = auth()->user();

        $user->profile_locked = !$user->profile_locked;
        $user->save();

        if ($user->profile_locked) {
            return back()->with('success', 'Đã bật khóa bảo vệ trang cá nhân!');
        }

        return back()->with('success', 'Đã tắt khóa bảo vệ trang cá nhân!');
    }
    /**
     * Nén và resize ảnh profile trước khi lưu.
     *
     * Lý do cần hàm này:
     * - Ảnh người dùng chọn có thể rất nặng, nếu lưu trực tiếp sẽ tốn dung lượng và tải chậm.
     * - Ảnh avatar/cover không cần giữ kích thước gốc quá lớn để hiển thị trên web.
     * - Hàm này chuyển JPG/PNG/WebP về JPG, resize theo chiều ngang tối đa và giảm chất lượng xuống mức hợp lý.
     * - Nếu server không có GD hoặc ảnh không đọc được, hàm sẽ fallback về upload file gốc để tránh lỗi trang.
     */
    private function saveCompressedProfileImage(
        UploadedFile $image,
        string $destination,
        string $prefix,
        int $maxWidth,
        int $quality = 82
    ): string {
        // Tạo thư mục lưu ảnh nếu thư mục chưa tồn tại.
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        // Ảnh sau khi nén được lưu dưới dạng JPG để giảm dung lượng tốt hơn.
        $fileName = time() . '_' . uniqid($prefix . '_', true) . '.jpg';
        $targetPath = $destination . DIRECTORY_SEPARATOR . $fileName;

        // Nếu PHP chưa bật extension GD thì không thể resize/nén ảnh, vì vậy lưu file gốc.
        if (!function_exists('imagecreatetruecolor')) {
            return $this->moveOriginalProfileImage($image, $destination, $prefix);
        }

        // Đọc kích thước và mime type của ảnh upload.
        $info = @getimagesize($image->getRealPath());

        if (!$info) {
            return $this->moveOriginalProfileImage($image, $destination, $prefix);
        }

        [$width, $height] = $info;
        $mime = $info['mime'] ?? '';

        // Tạo source image theo đúng loại file đầu vào.
        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($image->getRealPath()),
            'image/png' => @imagecreatefrompng($image->getRealPath()),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($image->getRealPath()) : false,
            default => false,
        };

        if (!$source) {
            return $this->moveOriginalProfileImage($image, $destination, $prefix);
        }

        // Nếu ảnh lớn hơn maxWidth thì thu nhỏ lại, nếu nhỏ hơn thì giữ nguyên kích thước.
        $newWidth = min($width, $maxWidth);
        $newHeight = (int) round($height * ($newWidth / $width));
        $canvas = imagecreatetruecolor($newWidth, $newHeight);

        // Tô nền trắng để ảnh PNG trong suốt khi chuyển sang JPG không bị nền đen.
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Lưu ảnh JPG với quality được truyền vào, mặc định 82 là mức cân bằng giữa nét và nhẹ.
        imagejpeg($canvas, $targetPath, $quality);

        imagedestroy($source);
        imagedestroy($canvas);

        return $fileName;
    }

    private function moveOriginalProfileImage(UploadedFile $image, string $destination, string $prefix): string
    {
        // Hàm fallback: chỉ dùng khi không nén được ảnh, để tính năng upload vẫn hoạt động.
        $fileName = time() . '_' . uniqid($prefix . '_', true) . '.' . $image->extension();
        $image->move($destination, $fileName);

        return $fileName;
    }
}
