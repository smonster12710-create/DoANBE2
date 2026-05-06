<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * CRUD User controller
 */
class CrudUserController extends Controller
{

    /**
     * Login page
     */
    public function login()
    {
        return view('crud_user.login');
    }

    public function dashboard()
    {
        // Trỏ tới file view dashboard của Pro 
        // (Tui đoán là Pro có file resources/views/dashboard.blade.php dựa theo cái file login lúc nãy)
        return view('dashboard');
    }

    /**
     * User submit form login
     */
    public function authUser(Request $request)
    {
        // 1. Validate dữ liệu đầu vào cho chắc cú
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Móc hàm Auth::attempt() thần thánh ra xài
        // Nó sẽ tự động tìm email, check luôn is_active, 
        // và tự lấy password so sánh với cái password_hash trong DB
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'is_active' => 1 // Phải active mới cho vô nghen
        ];

        if (Auth::attempt($credentials)) {
            // Đăng nhập thành công thì tạo lại session để chống lỗi Fixation
            $request->session()->regenerate();

            // Đẩy thẳng vô trang đích
            return redirect()->intended('/social')->with('success', 'Đăng nhập thành công, vô việc thôi Pro!');
        }

        // Đăng nhập thất bại thì đá về trang cũ kèm câu chửi, giữ lại cái email đã gõ
        return back()->withErrors([
            'email' => 'Email/Mật khẩu không đúng, hoặc tài khoản đang bị khóa nghen.'
        ])->onlyInput('email');
    }
    /**
     * Registration page
     */
    public function createUser()
    {
        return view('crud_user.registration');
    }

    /**
     * User submit form register
     */
    public function postUser(Request $request)
    {
        // 1. Validate: Thêm cái 'unique:users,email' để chặn đứng mấy tay xài email cũ
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'gender' => 'nullable|in:male,female,other',
            'phone' => 'nullable|max:20',
        ], [
            // Dịch câu chửi lỗi cho nó thân thiện
            'email.unique' => 'Email này đã có người xài rồi nghen Pro!'
        ]);

        // KHÔNG CÓ CHECK VÀ UPDATE USER CŨ Ở ĐÂY NỮA NHA!

        // 2. Tạo username tự động
        $baseUsername = Str::slug(strtolower(strtok($request->email, '@')), '_');

        if (empty($baseUsername)) {
            $baseUsername = 'user';
        }

        $username = $baseUsername;
        $count = 1;

        while (DB::table('users')->where('username', $username)->exists()) {
            $username = $baseUsername . '_' . $count;
            $count++;
        }

        // 3. Insert xuống DB và chộp ngay cái ID vừa tạo bằng insertGetId
        $newUserId = DB::table('users')->insertGetId([
            'username' => $username,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'fullname' => $request->name,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'avatar_url' => 'https://api.dicebear.com/7.x/adventurer/svg?seed=' . urlencode($username),
            'cover_url' => 'img/cover/default-cover.jpg' . urlencode($username),
            'role' => 'user',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Ép đăng nhập tự động luôn cho mượt
        Auth::loginUsingId($newUserId);
        $request->session()->regenerate();

        // 5. Đẩy thẳng vô trang Social
        return redirect('/social')->with('success', 'Đăng ký và tự động đăng nhập thành công!');
    }   
    /**
     * View user detail page
     */
    public function readUser(Request $request)
    {
        $user_id = $request->get('id');
        $user = User::find($user_id);

        return view('crud_user.read', ['messi' => $user]);
    }

    /**
     * Delete user by id
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('user.list')->with('success', 'Deleted successfully');
    }

    /**
     * Form update user page
     */
    public function updateUser($id)
    {
        $user = User::findOrFail($id);
        return view('crud_user.update', compact('user'));
    }

    /**
     * Submit form update user
     */
    public function postUpdateUser(Request $request)
    {
        $input = $request->all();

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,id,' . $input['id'],
            'password' => 'nullable|min:6|confirmed',
        ]);

        $user = User::find($input['id']);
        $user->name = $input['name'];
        $user->email = $input['email'];
        $user->password = $input['password'];
        $user->save();

        return redirect("list")->withSuccess('You have signed-in');
    }

    /**
     * List of users
     */
    public function listUser()
    {
        //        $users = [
        //                'users' => User::all()
        //        ];
        //        return view('crud_user.ronaldo', $users);

        if (Auth::check()) {
            $users = User::all();
            return view('crud_user.list', ['users' => $users]);
        }

        return redirect("login")->withSuccess('You are not allowed to access');
    }

    /**
     * Sign out
     */
    public function signOut()
    {
        Session::flush();
        Auth::logout();

        return Redirect('/');    }
}
