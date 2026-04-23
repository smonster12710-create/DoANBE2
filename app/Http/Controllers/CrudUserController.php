<?php

namespace App\Http\Controllers;

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

    /**
     * User submit form login
     */
    public function authUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = DB::table('users')->where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password_hash)) {
            Auth::loginUsingId($user->id);
            $request->session()->regenerate();

            return redirect()->intended('list')->withSuccess('Signed in');
        }

        return redirect('/social')->withSuccess('Signed in');   
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
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $existingUser = DB::table('users')->where('email', $request->email)->first();

        if ($existingUser) {
            DB::table('users')->where('id', $existingUser->id)->update([
                'fullname' => $request->name,
                'password_hash' => Hash::make($request->password),
                'updated_at' => now(),
            ]);

            return redirect('login')->withSuccess('Đã cập nhật tài khoản mẫu');
        }

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

        DB::table('users')->insert([
            'username' => $username,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'fullname' => $request->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('login')->withSuccess('Đăng ký thành công');
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
}
