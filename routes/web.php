<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CrudUserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- TRANG CHỦ ---
Route::get('/', function () {
    // Check coi ông này có đang đăng nhập không
    if (Auth::check()) {
        // Đăng nhập rồi thì đá thẳng vô trang mạng xã hội (hoặc '/dashboard' tuỳ ý Pro)
        return redirect('/social');
    }

    // Chưa đăng nhập thì mới cho đứng ngoài cửa dòm trang welcome
    return view('welcome');
});

// ==========================================
// KHU VỰC KHÁCH (CHƯA ĐĂNG NHẬP)
// Dùng middleware 'guest' để chặn user đã login quay lại form này
// ==========================================
Route::middleware('guest')->group(function () {
    Route::controller(CrudUserController::class)->group(function () {
        // GIỮ NGUYÊN TÊN KHÔNG ĐỔI 1 CHỮ
        Route::get('login', 'login')->name('login');
        Route::post('login', 'authUser')->name('user.authUser');

        Route::get('create', 'createUser')->name('user.createUser');
        Route::post('create', 'postUser')->name('user.postUser');
    });
});

// ==========================================
// KHU VỰC BẢO MẬT (BẮT BUỘC ĐĂNG NHẬP)
// ==========================================
Route::middleware('auth')->group(function () {

    // --- QUẢN LÝ USER ---
    Route::controller(CrudUserController::class)->group(function () {
        Route::get('read', 'readUser')->name('user.readUser');
        Route::delete('delete/{id}', 'deleteUser')->name('user.deleteUser');
        Route::get('update/{id}', 'updateUser')->name('user.updateUser');
        Route::post('update', 'postUpdateUser')->name('user.postUpdateUser');
        Route::get('list', 'listUser')->name('user.list');
        Route::get('signout', 'signOut')->name('signout');
    });

    // --- MẠNG XÃ HỘI & POSTS ---
    Route::controller(PostController::class)->group(function () {
        // giữ lại resource nhưng loại trừ mấy hàm đã tự viết ở dưới để không bị đụng nhau
        Route::resource('posts', PostController::class)->except(['index', 'store', 'show']);

        // Các route 
        Route::get('/newsfeed', 'index')->name('posts.index');
        Route::post('/posts', 'store')->name('posts.store');
        Route::get('/posts/{id}', 'show')->name('posts.show');
        Route::get('/social', 'index')->name('social.index');

        // ============================PROFILE=========================
        Route::middleware(['auth'])->group(function () {

          // Chỉnh sửa trang cá nhân
  Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
            // Xem trang cá nhân bất kỳ (Dùng cho Tìm kiếm)
            Route::get('/profile/{username}', [ProfileController::class, 'show'])->name('profile.show');

          
          
          

            // Route phụ: Nếu gõ domain.com/profile thì tự nhảy về profile của mình
            Route::get('/profile', function () {
                return redirect()->route('profile.show', ['username' => auth()->user()->username]);
            })->name('profile');

        });

        // Tương tác Bài viết (Giữ lại cả 2 version chữ 's' và không có chữ 's' 
        Route::post('/posts/{id}/like', 'toggleLike')->name('posts.like');
        Route::get('/posts/{id}/likers', 'listLikers')->name('posts.likers');

        Route::post('/post/{id}/like', 'toggleLike')->name('post.like');
        Route::get('/post/{id}/likers', 'listLikers')->name('post.likers');
        Route::post('/post/{id}/pin', [PostController::class, 'togglePin'])->name('post.pin');
    });

    // --- BÌNH LUẬN ---
    Route::post('/posts/{id}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // --- TÌM KIẾM ---
    Route::prefix('ajax')->group(function () {
        Route::get('/users', [SearchController::class, 'searchUsers']);
        Route::get('/hashtags', [SearchController::class, 'searchHashtags']);
    });
    // --- 2. Cụm Giao diện cho người dùng (View) ---
    Route::get('/hashtag', [SearchController::class, 'searchHashtag'])->name('hashtag.search');

    // ============================== TIN NHẮN ================================

    Route::controller(MessageController::class)->group(function () {
        Route::get('/list_messages', 'index')->name('messages.index');
        Route::get('/chat-messages/{id}', 'show')->name('chat_messages');
        Route::post('/messages/send', [MessageController::class, 'send']);
        Route::get('/messages/{conversationId}', [MessageController::class, 'fetch']);
        Route::get('/messages/{conversationId}/older', [MessageController::class, 'loadOlder']);
        Route::post('/messages/recall/{id}', [MessageController::class, 'recall']);
        
    });
});
