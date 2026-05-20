<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

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
            'fullname' => 'nullable|string|max:100',
            'username' => 'required|string|max:100|unique:users,username',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,user',
            'is_active' => 'required|in:0,1',
        ]);

        $user = new User();

        $user->fullname = $request->fullname;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->role = $request->role;
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
        $user = User::findOrFail($id);

        return view('admin.users.form', compact('user'));
    }

    /**
     * Cap nhat thong tin user da co.
     * Neu password bi bo trong thi giu nguyen mat khau cu.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'fullname' => 'nullable|string|max:100',
            'username' => 'required|string|max:100|unique:users,username,' . $user->id,
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role' => 'required|in:admin,user',
            'is_active' => 'required|in:0,1',
        ]);

        $user->fullname = $request->fullname;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->role = $request->role;
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
        $user = User::findOrFail($id);

        if ($user->id == auth()->id()) {
            return back()->with('error', 'Bạn không thể khóa chính tài khoản của mình!');
        }

        $user->is_active = $user->is_active ? 0 : 1;
        $user->save();

        return back()->with('success', 'Cập nhật trạng thái tài khoản thành công!');
    }

    /**
     * Xoa tai khoan user thuong.
     * Chan xoa tai khoan dang dang nhap va chan xoa tai khoan admin.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Không cho admin xóa chính tài khoản đang đăng nhập
        if ($user->id == auth()->id()) {
            return back()->with('error', 'Bạn không thể xóa chính tài khoản của mình!');
        }

        // Không cho xóa tài khoản admin
        if ($user->role === 'admin') {
            return back()->with('error', 'Không thể xóa tài khoản admin!');
        }

        // Xóa user thường
        $user->delete();

        return back()->with('success', 'Xóa người dùng thành công!');
    }
}
