<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    /**
     * 1. TRANG CHỦ HỘI NHÓM: Hiển thị danh sách nhóm đã tham gia và nhóm gợi ý
     */
    public function index()
    {
        $me = Auth::user();

        // Lấy danh sách các nhóm mà tôi ĐÃ tham gia chính thức (status = approved)
        $myGroups = $me->joinedGroups()->wherePivot('status', 'approved')->get();

        // Gợi ý nhóm: Lấy các nhóm mà tôi CHƯA tham gia để hiển thị khám phá
        $joinedGroupIds = $me->joinedGroups()->pluck('groups.id')->toArray();
        $suggestedGroups = Group::whereNotIn('id', $joinedGroupIds)->limit(6)->get();

        // ✨ ĐÃ SỬA: Thay đổi đường dẫn gọi file 'groups.group_home' (bỏ chữ social)
        return view('groups.group_home', compact('myGroups', 'suggestedGroups'));
    }

    /**
     * 2. XỬ LÝ TẠO NHÓM MỚI
     */
    public function store(Request $request)
    {
        // Xác thực dữ liệu đầu vào khi tạo nhóm
        $request->validate([
            'name' => 'required|string|max:255|unique:groups,name',
            'privacy' => 'required|in:public,private',
            'description' => 'nullable|string|max:1000'
        ], [
            'name.required' => 'Vui lòng nhập tên nhóm.',
            'name.unique' => 'Tên nhóm này đã tồn tại, vui lòng chọn tên khác.'
        ]);

        $me = Auth::user();

        // Tạo nhóm mới vào bảng `groups`
        $group = Group::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name), // Tự động chuyển "Nhóm Học Tập" thành "nhom-hoc-tap"
            'description' => $request->description,
            'privacy' => $request->privacy,
            'creator_id' => $me->id
        ]);

        // Người tạo nhóm sẽ mặc định trở thành "Admin" nhóm và được duyệt thẳng (approved)
        $group->members()->attach($me->id, [
            'role' => 'admin',
            'status' => 'approved'
        ]);

        return redirect()->route('groups.show', $group->slug)->with('success', 'Tạo hội nhóm thành công!');
    }

    /**
     * 3. TRANG CHI TIẾT CỦA MỘT NHÓM (Giao diện bảng tin nhóm)
     */
    public function show($slug)
    {
        // Tìm nhóm theo slug, nếu không thấy trả về trang 404
        $group = Group::where('slug', $slug)->firstOrFail();
        $me = Auth::user();

        // Kiểm tra xem người đang xem đã đăng ký tham gia nhóm chưa và lấy thông tin quyền (role, status)
        $membership = DB::table('group_members')
            ->where('group_id', $group->id)
            ->where('user_id', $me->id)
            ->first();

        // Quy tắc bảo mật cho nhóm Riêng tư (Private): 
        // Nếu là nhóm Private và người xem chưa được duyệt vào nhóm thì không cho xem bài viết
        $canViewContent = true;
        if ($group->privacy === 'private' && (!$membership || $membership->status !== 'approved')) {
            $canViewContent = false;
        }

        // Nếu có quyền xem, tiến hành lấy các bài viết thuộc nhóm này, kèm theo thông tin người đăng
        $posts = $canViewContent
            ? $group->posts()->with(['user', 'media', 'likes', 'comments'])->latest()->get()
            : collect();

        // Lấy danh sách thành viên chính thức để hiển thị ở thanh bên (Sidebar nhóm)
        $memberCount = $group->members()->wherePivot('status', 'approved')->count();

        // ✨ BỔ SUNG: Lấy mảng ID của những thành viên ĐÃ ĐƯỢC DUYỆT trong nhóm để Blade check ẩn/hiện nút Kick
        $approvedMemberIds = $group->members()
            ->wherePivot('status', 'approved')
            ->pluck('users.id') // Hãy đổi thành 'user_id' nếu chạy lỗi cột không tồn tại nhé pro
            ->toArray();

        // ✨ ĐÃ SỬA: Thêm 'approvedMemberIds' vào compact
        return view('groups.show', compact('group', 'membership', 'posts', 'canViewContent', 'memberCount', 'approvedMemberIds'));
    }

    /**
     * 4. XỬ LÝ BẤM NÚT THAM GIA NHÓM (Join Group)
     */
    public function join($slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $me = Auth::user();

        // Kiểm tra xem đã bấm tham gia trước đó chưa để tránh trùng lặp dữ liệu
        $exists = $group->members()->where('user_id', $me->id)->exists();
        if ($exists) {
            return back()->with('error', 'Bạn đã gửi yêu cầu hoặc đã là thành viên nhóm này.');
        }

        // Tùy biến trạng thái dựa trên chế độ nhóm công khai hay riêng tư
        // Nếu nhóm Public -> Thành viên chính thức luôn. Nếu nhóm Private -> Chờ duyệt (pending)
        $status = ($group->privacy === 'public') ? 'approved' : 'pending';

        $group->members()->attach($me->id, [
            'role' => 'member',
            'status' => $status
        ]);

        // ✨ TẬN DỤNG BẢNG NOTIFICATIONS THỰC TẾ CỦA BẠN: Nếu nhóm Private, bắn thông báo cho Admin nhóm duyệt
        if ($status === 'pending') {
            DB::table('notifications')->insert([
                'user_id' => $group->creator_id,       // Người nhận thông báo (Chủ nhóm / Admin)
                'actor_id' => $me->id,                 // Người thực hiện hành động (Người xin gia nhập)
                'type' => 'comment',        // Loại thông báo: Xin vào nhóm
                'reference_id' => $group->id,          // ID của nhóm để biết xin vào nhóm nào
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->with('success', 'Yêu cầu tham gia nhóm đã được gửi, vui lòng chờ Admin phê duyệt.');
        }

        return back()->with('success', 'Gia nhập hội nhóm thành công!');
    }

    /**
     * 5. XỬ LÝ RỜI NHÓM HOẶC HỦY YÊU CẦU THAM GIA
     */
    public function leave($slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $me = Auth::user();

        // Admin sáng lập nhóm thì không được rời nhóm kiểu này (Tránh nhóm vô chủ)
        if ($group->creator_id === $me->id) {
            return back()->with('error', 'Bạn là Admin sáng lập, không thể rời nhóm. Hãy giải tán nhóm nếu muốn.');
        }

        // 2. Kiểm tra xem user còn là thành viên không (Đây là bước quan trọng nhất)
        $isMember = $group->members()->where('user_id', $me->id)->exists();

        if (!$isMember) {
            // Nếu không còn tồn tại liên kết, báo lỗi và load lại trang để xóa nút "Rời nhóm" đi
            return back()->with('error', 'Bạn đã rời khỏi nhóm này từ trước hoặc không phải là thành viên.');
        }

        // Xóa liên kết khỏi bảng trung gian group_members
        $group->members()->detach($me->id);

        // Tiện tay dọn dẹp luôn thông báo xin vào nhóm cũ nếu có
        DB::table('notifications')
            ->where('user_id', $group->creator_id)
            ->where('actor_id', $me->id)
            ->where('type', 'group_join_request')
            ->where('reference_id', $group->id)
            ->delete();

        return back()->with('success', 'Đã rời khỏi hội nhóm.');
    }

    /**
     * 6. TRANG QUẢN LÝ CỦA ADMIN: Duyệt thành viên chờ (Chỉ dành cho nhóm Private)
     */
    public function manageRequests($slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $me = Auth::user();

        // Bảo mật: Chỉ Admin nhóm mới được quyền vào trang duyệt này
        if ($group->creator_id !== $me->id) {
            abort(403, 'Bạn không có quyền quản lý nhóm này.');
        }

        // Lấy danh sách thành viên đang ở trạng thái 'pending'
        $pendingMembers = $group->members()->wherePivot('status', 'pending')->get();

        // ✨ ĐÃ SỬA: Thay đổi đường dẫn thành 'groups.requests' (bỏ chữ social)
        return view('groups.requests', compact('group', 'pendingMembers'));
    }

    /**
     * 7. XỬ LÝ DUYỆT THÀNH VIÊN VÀO NHÓM (Admin bấm Chấp nhận)
     */
    public function approveMember($slug, $userId)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $me = Auth::user();

        if ($group->creator_id !== $me->id) {
            return back()->with('error', 'Hành động không hợp lệ.');
        }

        // 1. KIỂM TRA TRẠNG THÁI TRƯỚC KHI DUYỆT
        $membership = $group->members()
            ->where('user_id', $userId)
            ->wherePivot('status', 'pending')
            ->first();

        // Nếu không tìm thấy bản ghi nào ở trạng thái 'pending', nghĩa là:
        // - Đã được duyệt ở tab khác rồi
        // - Hoặc user đã hủy yêu cầu/rời nhóm
        if (!$membership) {
            return back()->with('error', 'Yêu cầu này đã được xử lý hoặc không còn tồn tại.');
        }

        // Cập nhật trạng thái của user được duyệt từ pending -> approved
        $group->members()->updateExistingPivot($userId, ['status' => 'approved']);

        // ✨ TẬN DỤNG BẢNG NOTIFICATIONS CỦA BẠN: Bắn thông báo chúc mừng cho người xin vào nhóm
        DB::table('notifications')->insert([
            'user_id' => $userId,                   // Người nhận thông báo (Thành viên mới)
            'actor_id' => $me->id,                  // Người thực hiện duyệt (Admin)
            'type' => 'comment',       // Loại thông báo: Đã được duyệt vào nhóm
            'reference_id' => $group->id,           // ID của nhóm
            'is_read' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Xóa thông báo xin vào nhóm chờ duyệt ban đầu của Admin đi cho sạch máy
        DB::table('notifications')
            ->where('user_id', $me->id)
            ->where('actor_id', $userId)
            ->where('type', 'comment')
            ->where('reference_id', $group->id)
            ->delete();

        return back()->with('success', 'Đã duyệt thành viên vào nhóm!');
    }

    /**
     * 8. XỬ LÝ GIẢI TÁN NHÓM (Chỉ dành cho Admin sáng lập)
     */
    public function destroy($slug)
    {
        // Tìm nhóm, nếu không tìm thấy (đã bị xóa ở tab khác) thì không bắn 404
        $group = Group::where('slug', $slug)->first();

        // Kiểm tra: Nếu nhóm không tồn tại, có nghĩa là đã bị giải tán rồi
        if (!$group) {
            return redirect()->route('groups.index')
                ->with('error', 'Nhóm này đã bị giải tán hoặc không tồn tại.');
        }

        // Chỉ có người tạo nhóm mới được giải tán
        if ($group->creator_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Bạn không có quyền giải tán nhóm này!');
        }

        // Xóa dữ liệu liên quan và xóa nhóm
        $group->posts()->delete();
        $group->members()->detach();
        $group->delete();

        return redirect()->route('groups.index')->with('success', 'Đã giải tán nhóm thành công!');
    }

    /**
     * 9. XỬ LÝ KICK THÀNH VIÊN KHỎI NHÓM (Chỉ dành cho Admin sáng lập)
     */
    public function kickMember($slug, $userId)
    {
        $group = Group::where('slug', $slug)->firstOrFail();

        // Kiểm tra quyền Admin
        if ($group->creator_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Bạn không có quyền kick thành viên!');
        }

        // Không được tự kick chính mình
        if ($userId == auth()->id()) {
            return redirect()->back()->with('error', 'Bạn không thể tự kick chính mình, hãy dùng nút Giải tán nhóm!');
        }

        // ✨ ĐÃ SỬA: Gỡ thành viên ra khỏi nhóm thông qua quan hệ members() có sẵn của pro
        $group->members()->detach($userId);

        return redirect()->back()->with('success', 'Đã kick thành viên ra khỏi nhóm!');
    }

    public function update(Request $request, $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();

        // 1. Bảo mật: Chỉ chủ nhóm mới có quyền sửa
        if ($group->creator_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Bạn không có quyền chỉnh sửa nhóm này!');
        }

        // 2. Validate dữ liệu đầu vào
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'privacy' => 'required|in:public,private',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Giới hạn 2MB
        ]);

        // 3. Cập nhật các thông tin cơ bản
        $group->name = $request->name;
        $group->description = $request->description;
        $group->privacy = $request->privacy;

        // 4. Xử lý upload ẢNH NỀN nếu có file mới
        if ($request->hasFile('cover')) {
            // Xóa ảnh cũ nếu có để tránh rác server (tùy chọn)
            if ($group->cover && Storage::disk('public')->exists($group->cover)) {
                Storage::disk('public')->delete($group->cover);
            }

            // Lưu ảnh mới vào thư mục storage/app/public/groups/covers
            $path = $request->file('cover')->store('groups/covers', 'public');
            $group->cover = $path; // Giả sử tên cột trong DB của bạn là 'cover'
        }

        $group->save();

        return redirect()->route('groups.show', $group->slug)->with('success', 'Cập nhật thông tin nhóm thành công!');
    }
}
