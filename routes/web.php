<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CrudUserController;
use App\Http\Controllers\MessageController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('dashboard', [CrudUserController::class, 'dashboard']);

Route::get('login', [CrudUserController::class, 'login'])->name('login');
Route::post('login', [CrudUserController::class, 'authUser'])->name('user.authUser');

Route::get('create', [CrudUserController::class, 'createUser'])->name('user.createUser');
Route::post('create', [CrudUserController::class, 'postUser'])->name('user.postUser');

Route::get('read', [CrudUserController::class, 'readUser'])->name('user.readUser');

Route::delete('delete/{id}', [CrudUserController::class, 'deleteUser'])->name('user.deleteUser');

Route::get('update/{id}', [CrudUserController::class, 'updateUser'])->name('user.updateUser');
Route::post('update', [CrudUserController::class, 'postUpdateUser'])->name('user.postUpdateUser');

Route::get('list', [CrudUserController::class, 'listUser'])->name('user.list');

Route::get('signout', [CrudUserController::class, 'signOut'])->name('signout');

Route::get('/', function () {
    return view('welcome');
});

//social
Route::get('social', function () {
    return view('social.index');
});
Route::get('list_messages', function () {
    return view('social.list_messages');
});
// Route dành cho trang danh sách hoặc trang nhắn tin chi tiết

Route::get('/list_messages', [MessageController::class, 'index']);

Route::get('/chat-messages/{id}', [MessageController::class, 'show'])->name('chat_messages');
