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

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    | Cac ham trong nhom nay phu trach dang nhap va trang dashboard co ban.
    */

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
            if (Auth::user()->role !== 'admin') {
                $accounts = session()->get('switch_accounts', []);

                $accounts[] = Auth::id();

                $accounts = array_values(array_unique(array_map('intval', $accounts)));

                session(['switch_accounts' => $accounts]);
            }
            return redirect()->intended('/social')->with('success', 'Đăng nhập thành công!');
        }

        // Đăng nhập thất bại thì đá về trang cũ kèm thông báo, giữ lại cái email đã gõ
        return back()->withErrors([
            'email' => 'Email/Mật khẩu không đúng'
        ])->onlyInput('email');
    }
    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    | Tao tai khoan user thuong va chuan bi du lieu mac dinh cho profile.
    */

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
        /*
    |--------------------------------------------------------------------------
    | 1. Validate dữ liệu đăng ký
    |--------------------------------------------------------------------------
    | - name: bắt buộc nhập họ tên
    | - email: bắt buộc, đúng định dạng email, không được trùng trong bảng users
    | - password: bắt buộc, tối thiểu 6 ký tự, phải khớp với password_confirmation
    | - gender, phone: không bắt buộc, nhưng nếu có thì kiểm tra nhẹ
    */
        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:100|unique:users,email',
                'password' => 'required|min:6|confirmed',
                'gender' => 'nullable|in:1,2,3',
                'phone' => 'nullable|string|max:20',
            ], [
                'name.required' => 'Vui lòng nhập họ và tên!',
                'email.required' => 'Vui lòng nhập email!',
                'email.email' => 'Email không đúng định dạng!',
                'email.unique' => 'Email đã có người sử dụng!',
                'password.required' => 'Vui lòng nhập mật khẩu!',
                'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự!',
                'password.confirmed' => 'Xác nhận mật khẩu không khớp!',
                'gender.in' => 'Giới tính không hợp lệ!',
                'phone.max' => 'Số điện thoại không được quá 20 ký tự!',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            // Nếu validate lỗi thì quay lại trang đăng ký,
            // giữ lại dữ liệu đã nhập và gửi message lỗi để hiện toast.
            return back()
                ->withInput()
                ->with('error', $e->validator->errors()->first());
        }

        /*
    |--------------------------------------------------------------------------
    | 2. Tạo username tự động từ email
    |--------------------------------------------------------------------------
    | Ví dụ:
    | Email: abc@gmail.com
    | Username gốc: abc
    */
        $baseUsername = Str::slug(
            strtolower(strtok($request->email, '@')),
            '_'
        );

        // Nếu email bị lỗi phần trước @ hoặc username rỗng thì dùng mặc định là "user"
        if (empty($baseUsername)) {
            $baseUsername = 'user';
        }

        /*
    |--------------------------------------------------------------------------
    | 3. Chống trùng username
    |--------------------------------------------------------------------------
    | Nếu username đã tồn tại thì thêm số phía sau:
    | abc
    | abc_1
    | abc_2
    */
        $username = $baseUsername;
        $count = 1;

        while (DB::table('users')->where('username', $username)->exists()) {
            $username = $baseUsername . '_' . $count;
            $count++;
        }

        /*
    |--------------------------------------------------------------------------
    | 4. Thêm user mới vào database
    |--------------------------------------------------------------------------
    | Lưu ý:
    | - Bảng users của bạn dùng password_hash, không phải password
    | - avatar_url tạo avatar tự động bằng Dicebear
    | - cover_url dùng ảnh nền mặc định
    | - bio, birthday, address để null vì đăng ký chưa cần nhập
    */
        DB::table('users')->insert([
            'username' => $username,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'fullname' => $request->name,

            'gender' => $request->gender,
            'phone' => $request->phone,
            'bio' => null,
            'birthday' => null,
            'address' => null,

            'avatar_url' => 'https://api.dicebear.com/7.x/adventurer/svg?seed=' . urlencode($username),
            'cover_url' => 'img/cover/default-cover.jpg',

            'role' => 'user',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /*
    |--------------------------------------------------------------------------
    | 5. Đăng ký thành công
    |--------------------------------------------------------------------------
    | Theo yêu cầu:
    | Đăng ký xong -> chuyển sang trang đăng nhập -> hiện toast success
    |
    | Route login trong web.php của bạn là ->name('login')
    */
        return redirect()
            ->route('login')
            ->with('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
    }
    /**
     * Chuyen doi giua cac tai khoan da dang nhap tren cung trinh duyet.
     * Admin khong tham gia luong switch account de tranh nham quyen.
     */
    public function switchAccount(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
        ]);

        if (Auth::user()->role === 'admin') {
            return back()->with('error', 'Admin không được chuyển sang tài khoản người dùng!');
        }

        $userId = (int) $request->user_id;

        $accounts = session()->get('switch_accounts', []);
        $accounts = array_map('intval', $accounts);

        if (!in_array($userId, $accounts)) {
            return back()->with('error', 'Tài khoản này chưa được đăng nhập trên máy này!');
        }

        $user = User::findOrFail($userId);

        if ($user->role === 'admin') {
            return back()->with('error', 'Không thể chuyển sang tài khoản admin!');
        }

        Auth::login($user);

        $request->session()->regenerate();

        session(['switch_accounts' => $accounts]);

        return redirect('/social');
    }
    /*
    |--------------------------------------------------------------------------
    | Legacy User CRUD
    |--------------------------------------------------------------------------
    | Cac ham CRUD user cu van duoc giu lai cho nhung man hinh dang dung route cu.
    */

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
    /*
    |--------------------------------------------------------------------------
    | Forgot Password
    |--------------------------------------------------------------------------
    | Xu ly quen mat khau bang email va ma captcha luu trong session.
    */

    /**
     * Forgot PassWord
     */
    public function forgotPassword(Request $request)
    {
        if ($request->has('fresh')) {
            session()->forget(['forgot_email', 'forgot_captcha']);
        }

        return view('crud_user.forgot-password');
    }
    public function checkForgotEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = DB::table('users')
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            return back()
                ->withInput()
                ->with('error', 'Email chưa được đăng ký!');
        }

        session([
            'forgot_email' => $request->email,
            'forgot_captcha' => rand(1000, 9999),
        ]);

        return redirect()
            ->route('forgot.password')
            ->with('success', 'Email hợp lệ! Vui lòng nhập mật khẩu mới.');
    }

    public function updateForgotPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
            'captcha' => 'required',
        ]);

        if ($request->captcha != session('forgot_captcha')) {
            return back()->with('error', 'Mã captcha không đúng!');
        }

        DB::table('users')
            ->where('email', session('forgot_email'))
            ->update([
                'password_hash' => Hash::make($request->password),
            ]);

        session()->forget(['forgot_email', 'forgot_captcha']);

        return redirect()
            ->route('login')
            ->with('success', 'Đổi mật khẩu thành công!');
    }
    /**
     * Sign out
     */
    public function signOut()
    {
        Auth::logout();

        return Redirect('/');
    }
}
