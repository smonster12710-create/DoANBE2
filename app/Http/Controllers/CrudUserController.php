<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    /**
     * User submit form login
     */
    public function authUser(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return redirect()->intended('list')
                ->withSuccess('Signed in');
        }

        return redirect("login")->withSuccess('Login details are not valid');
    }

    /**
     * Registration page
     */
    public function createUser()
    {
        return view('crud_user.create');
    }

    /**
     * User submit form register
     */
    public function postUser(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $data = $request->all();
        $check = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            //anh - id 
            


        ]);


        return redirect("login");
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

        return Redirect('login');
    }

    //Đếm like
    public function toggleLike($postId)
    {
    // 1. Xác định User
    $userId = \Illuminate\Support\Facades\Auth::id() ?? 1;
    
    // 2. Tìm bài viết
    $post = Post::findOrFail($postId);

    // 3. Kiểm tra xem user này đã like bài này chưa
    $like = Like::where('post_id', $postId)
                ->where('user_id', $userId)
                ->first();

    if ($like) {
        // Xóa trực tiếp bằng Query Builder để tránh lỗi Primary Key
        Like::where('post_id', $postId)
            ->where('user_id', $userId)
            ->delete();

        // Chỉ giảm số lượng nếu like_count đang lớn hơn 0
        if ($post->like_count > 0) {
            $post->decrement('like_count');
        }
    } else {
        // Thêm mới lượt like
        Like::create([
            'post_id' => $postId,
            'user_id' => $userId
        ]);
        
        // Tăng số lượng like
        $post->increment('like_count');
    }

    return redirect()->back();
    }

    public function index()
    {
    // Lấy dữ liệu thật từ DB
    $posts = Post::with(['user', 'likes'])->latest()->get(); 
    
    // Trả về file social_home.blade.php
    return view('social.index', compact('posts')); 
    }
}
