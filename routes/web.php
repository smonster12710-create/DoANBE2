<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CrudUserController;
use App\Http\Controllers\PostController;

use App\Http\Controllers\CommentController;

use App\Http\Controllers\MessageController;
use App\Http\Controllers\SearchController;



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
<<<<<<< HEAD
Route::resource('posts', PostController::class);
Route::get('/newsfeed', [App\Http\Controllers\PostController::class, 'index'])->name('posts.index');
=======

// --- TRANG CHỦ ---
>>>>>>> master
Route::get('/', function () {
    return view('welcome');
});

<<<<<<< HEAD
//social
Route::get('/social', [PostController::class, 'index']);
Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
Route::put('/posts/{id}', [PostController::class, 'update'])->name('posts.update');
Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');
Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
Route::middleware(['auth'])->group(function () {
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    // Các route khác như update, destroy bài viết cũng nên để ở đây
});
=======
// --- SOCIAL NETWORK ---
Route::get('/social', [PostController::class, 'index'])->name('social.index');
Route::post('/post/{id}/like', [PostController::class, 'toggleLike'])->name('post.like');
Route::get('/post/{id}/likers', [PostController::class, 'listLikers'])->name('post.likers');

// --- SEARCH & MESSAGES ---
Route::get('/search', [SearchController::class, 'globalSearch'])->name('search.global');
Route::get('/list_messages', [MessageController::class, 'index'])->name('messages.index');
Route::get('/chat-messages/{id}', [MessageController::class, 'show'])->name('chat_messages');

// --- QUẢN LÝ USER (Ví dụ gộp nhóm cho gọn) ---
Route::get('login', [CrudUserController::class, 'login'])->name('login');
Route::post('login', [CrudUserController::class, 'authUser'])->name('user.authUser');
Route::get('dashboard', [CrudUserController::class, 'dashboard']);

>>>>>>> master
