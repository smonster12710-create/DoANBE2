<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\alert;

class AdminUserController extends Controller
{
    /**
     * Hien thi danh sach user trong trang quan tri.
     * Ho tro tim kiem theo fullname, username va email.
     */
    public function index(Request $request)
    {
        $keyword = $request->keyword;

        $users = User::query()
            ->where('role', 'user')
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('fullname', 'like', "%{$keyword}%")
                        ->orWhere('username', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.users.list', compact('users', 'keyword'));
    }
    /**
     * Mo form tao user moi trong khu vuc admin.
     */
    public function create()
    {
        $user = new User();

        return view('admin.users.form', compact('user'));
    }

    /**
     * Xu ly tao tai khoan moi tu form admin.
     * Password duoc luu theo cot dang ton tai trong bang users.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fullname' => 'nullable|string|max:30',
            'username' => 'required|string|max:30|unique:users,username',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|min:6',
            'is_active' => 'required|in:0,1',
        ], $this->validationMessages());

        $user = new User();

        $user->fullname = $request->fullname;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->role = 'user';
        $user->is_active = $request->is_active;

        if (Schema::hasColumn('users', 'password')) {
            $user->password = Hash::make($request->password);
        }

        if (Schema::hasColumn('users', 'password_hash')) {
            $user->password_hash = Hash::make($request->password);
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Thêm người dùng thành công!');
    }

    /**
     * Mo form sua thong tin user theo id.
     */
    public function edit($id)
    {
        $user = $this->findManagedUser($id);

        if (!$user) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Dữ liệu đã thay đổi, vui lòng load lại trang.');
        }

        return view('admin.users.form', compact('user'));
    }

    /**
     * Cap nhat thong tin user da co.
     * Neu password bi bo trong thi giu nguyen mat khau cu.
     */
    public function update(Request $request, $id)
    {
        $user = $this->findManagedUser($id);

        if (!$user || $this->isStaleRequest($request, $user)) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Dữ liệu đã thay đổi, vui lòng load lại trang.');
        }

        $request->validate([
            'fullname' => 'nullable|string|max:30',
            'username' => 'required|string|max:30|unique:users,username,' . $user->id,
            'email' => ['required', 'email', 'max:100', 'regex:/^[A-Za-z0-9._%+\-]+@gmail\.com$/i', 'unique:users,email,' . $user->id],
            'password' => 'nullable|min:6|max:32',
            'is_active' => 'required|in:0,1',
        ], $this->validationMessages());

        $user->fullname = $request->fullname;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->role = 'user';
        $user->is_active = $request->is_active;

        if ($request->filled('password')) {
            if (Schema::hasColumn('users', 'password')) {
                $user->password = Hash::make($request->password);
            }

            if (Schema::hasColumn('users', 'password_hash')) {
                $user->password_hash = Hash::make($request->password);
            }
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Cập nhật người dùng thành công!');
    }

    /**
     * Khoa hoac mo khoa tai khoan user.
     */
    public function toggleStatus($id)
    {
        $user = $this->findManagedUser($id);

        if (!$user || $this->isStaleRequest(request(), $user)) {
            return back()->with('error', 'Dữ liệu đã thay đổi, vui lòng load lại trang.');
        }

        $user->is_active = $user->is_active ? 0 : 1;
        $user->save();

        return back()->with('success', 'Cập nhật trạng thái tài khoản thành công!');
    }

    /**
     * Xoa tai khoan user thuong trong danh sach quan tri.
     */
    public function destroy($id)
    {
        $user = $this->findManagedUser($id);

        if (!$user || $this->isStaleRequest(request(), $user)) {
            return back()->with('error', 'Dữ liệu đã thay đổi, vui lòng load lại trang.');
        }

        $user->delete();

        return back()->with('success', 'Xóa người dùng thành công!');
    }

    /**
     * Thông báo validate bằng tiếng Việt cho form quản trị user.
     */
    private function validationMessages()
    {
        return [
            'email.regex' => 'Email phai la dia chi @gmail.com.',
            'fullname.string' => 'Họ tên phải là chuỗi ký tự.',
            'fullname.max' => 'Họ tên không được vượt quá 30 ký tự.',
            'username.required' => 'Vui lòng nhập username.',
            'username.string' => 'Username phải là chuỗi ký tự.',
            'username.max' => 'Username không được vượt quá 30 ký tự.',
            'username.unique' => 'Username này đã được sử dụng.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 100 ký tự.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'is_active.required' => 'Vui lòng chọn trạng thái.',
            'is_active.in' => 'Trạng thái không hợp lệ.',
        ];
    }

    /**
     * Chi lay tai khoan user thuong trong khu vuc quan tri nguoi dung.
     */
    private function findManagedUser($id)
    {
        return User::where('role', 'user')->find($id);
    }

    /**
     * Kiem tra form/action co dang thao tac tren du lieu cu hay khong.
     */
    private function isStaleRequest(Request $request, User $user)
    {
        $token = $request->input('updated_at_token');

        return !$token || $token !== (string) optional($user->updated_at)->timestamp;
    }
}
