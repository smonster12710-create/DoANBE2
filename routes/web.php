<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CrudUserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\BlockController;
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
})->name('social');

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

        Route::get('forgot-password', 'forgotPassword')->name('forgot.password');

        Route::post('forgot-password/check', 'checkForgotEmail')->name('forgot.password.check');

        Route::post('forgot-password/update', 'updateForgotPassword')->name('forgot.password.update');
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
    // --- KHU VỰC QUẢN TRỊ ---
    Route::middleware(['admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
            Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
            Route::get('/users/{id}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
            Route::post('/users/{id}', [AdminUserController::class, 'update'])->name('users.update');
            Route::post('/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus');
            Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');
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
            // tương tác thêm bạn bè theo dõi giữa các người dùng
            //Route::post('/profile/{username}/friend', [ProfileController::class, 'toggleFriend'])->name('profile.friend.toggle');
            //Route::post('/profile/{username}/follow', [ProfileController::class, 'toggleFollow'])->name('profile.follow.toggle');

            // BẠN BÈ
            Route::post('/profile/{username}/friend/send', [FriendController::class, 'sendFriendRequest'])->name('friend.send');
            Route::post('/profile/{username}/friend/accept', [FriendController::class, 'acceptFriendRequest'])->name('friend.accept');
            Route::post('/profile/{username}/friend/remove', [FriendController::class, 'removeFriend'])->name('friend.remove');

            Route::get('/profile/{username}/friends', [FriendController::class, 'friends'])->name('profile.friends');
            Route::get('/profile/{username}/followers', [ProfileController::class, 'followers'])->name('profile.followers');

            // Xem trang cá nhân bất kỳ
            Route::get('/profile/{username}', [ProfileController::class, 'show'])->name('profile.show');

            // chuyển tài khoản
            Route::post('/switch-account', [CrudUserController::class, 'switchAccount'])->name('account.switch');

            // Khoá bảo vệ trang cá nhân
            Route::get('/profile-lock', [ProfileController::class, 'profileLock'])->name('profile.lock');
            Route::post('/profile-lock/toggle', [ProfileController::class, 'toggleProfileLock'])->name('profile.lock.toggle');

            // trạng thái hoạt động
            Route::get('/activity-status', [ProfileController::class, 'activityStatus'])->name('activity.status');
            Route::post('/activity-status/toggle', [ProfileController::class, 'toggleActivityStatus'])->name('activity.status.toggle');



            // Route phụ: Nếu gõ domain.com/profile thì tự nhảy về profile của mình
            Route::get('/profile', function () {
                return redirect()->route('profile.show', ['username' => auth::user()->username]);
            })->name('profile');
        });

        // Tương tác Bài viết (Giữ lại cả 2 version chữ 's' và không có chữ 's' 
        // Route::post('/posts/{id}/like', 'toggleLike')->name('posts.like');
        // Route::get('/posts/{id}/likers', 'listLikers')->name('posts.likers');

        Route::post('/post/{id}/like', [PostController::class, 'toggleLike'])->name('post.like');
        Route::get('/post/{id}/likers', [PostController::class, 'listLikers'])->name('post.likers');
        Route::post('/post/{id}/pin', [PostController::class, 'togglePin'])->name('post.pin');
    });

    // --- BÌNH LUẬN ---
    Route::post('/posts/{id}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::post('/posts/{id}/toggle-comment', [PostController::class, 'toggleComment'])->name('post.toggleComment')->middleware('auth');
    // --- THEO DÕI ---
    Route::post('/follow/{user}', [FollowController::class, 'toggle'])->name('follow.toggle')->middleware('auth');
    Route::get('/profile/{username}/following', [FollowController::class, 'following'])->name('profile.following');
    // --- Chia sẻ ---
    Route::post('/post/{id}/share', [PostController::class, 'share'])->name('post.share')->middleware('auth');
    // --- TÌM KIẾM ---
    Route::prefix('ajax')->group(function () {
        Route::get('/users', [SearchController::class, 'searchUsers']);
        Route::get('/hashtags', [SearchController::class, 'searchHashtags']);
    });
    // --- 2. Cụm Giao diện cho người dùng (View) ---
    Route::get('/hashtag', [SearchController::class, 'searchHashtag'])->name('hashtag.search');
    Route::get('/hashtag/{name}', [SearchController::class, 'showHashtag'])->name('hashtags.show');
    //==================================================================================================
    Route::get('/ajax/posts', [PostController::class, 'searchPosts']);
    //----Lưu bài viết
    Route::post('/posts/{post}/save', [PostController::class, 'save'])
        ->name('posts.save')
        ->middleware('auth');
    Route::get('/saved', [PostController::class, 'saved'])
        ->name('posts.saved');

    // ============================== TIN NHẮN ================================
    Route::controller(MessageController::class)->group(function () {

        Route::get('/list_messages', 'index')->name('messages.index');

        Route::get('/messages/unread-count', 'unreadCount');

        Route::get('/chat-messages/{id}', 'show')->name('chat_messages');

        Route::get('/messages/conversations/{id}', 'getConversations');

        Route::get('/messages/{conversationId}', 'fetch');

        Route::get('/messages/{conversationId}/older', 'loadOlder');

        Route::get('/messages/{conversationId}/read-status', 'readStatus');

        Route::post('/messages/send', 'send');

        Route::post('/messages/recall/{id}', 'recall');

        Route::post('/messages/delete-for-me/{id}', 'deleteForMe');

        Route::post('/messages/{conversationId}/mark-read', 'markAsRead');

        Route::get('/messages/start/{username}', 'startChat')->name('messages.start');

        Route::post('/chat/group/create', 'createGroup')->name('chat.group.create');
    });
    // ============================== THÔNG BÁO ================================
    Route::prefix('notifications')
        ->name('notifications.')
        ->controller(NotificationController::class)
        ->group(function () {

            // Gọi ra /notifications (name: notifications.index)
            Route::get('/', 'index')->name('index');

            // Gọi ra /notifications/{id}/read (name: notifications.read)
            Route::get('/{id}/read', 'readSingle')->name('read');

            // Gọi ra /notifications/mark-as-read (name: notifications.markAllRead)
            Route::post('/mark-as-read', 'markAsRead')->name('markAllRead');

            // Gọi ra /notifications/{id}/unread (name: notifications.unread)
            Route::post('/{id}/unread', 'markAsUnread')->name('unread');

            // Gọi ra /notifications/{id} (name: notifications.destroy)
            Route::delete('/{id}', 'destroySingle')->name('destroy');
        });
});

// HỆ THỐNG ROUTES ĐIỀU KHIỂN HỘI NHÓM (GROUPS)
Route::middleware(['auth'])->prefix('groups')->name('groups.')->group(function () {
    Route::get('/', [GroupController::class, 'index'])->name('index');                  // Trang chủ danh sách nhóm
    Route::post('/store', [GroupController::class, 'store'])->name('store');             // Xử lý tạo nhóm mới
    Route::get('/{slug}', [GroupController::class, 'show'])->name('show');               // Xem chi tiết một nhóm
    Route::post('/{slug}/join', [GroupController::class, 'join'])->name('join');         // Bấm nút tham gia nhóm
    Route::post('/{slug}/leave', [GroupController::class, 'leave'])->name('leave');       // Bấm nút rời nhóm

    // Các tính năng quản trị nhóm dành riêng cho Admin
    Route::get('/{slug}/requests', [GroupController::class, 'manageRequests'])->name('requests'); // Trang danh sách chờ duyệt
    Route::post('/{slug}/approve/{userId}', [GroupController::class, 'approveMember'])->name('approve'); // Xử lý bấm duyệt thành viên
    // ĐÃ SỬA: Bỏ bớt chữ "groups." dư thừa ở name() để Laravel tự nối thành "groups.destroy"
    Route::delete('/{slug}/destroy', [GroupController::class, 'destroy'])->name('destroy');

    // ĐÃ SỬA: Bỏ bớt chữ "groups." dư thừa ở name() để Laravel tự nối thành "groups.kick"
    Route::delete('/{slug}/kick/{user_id}', [GroupController::class, 'kickMember'])->name('kick');
    
    Route::put('/{slug}/update', [GroupController::class, 'update'])->name('update');
});
// Blocks
Route::post('/block/{userId}', [BlockController::class, 'toggleBlock'])->name('user.block');
Route::get('/settings/blocked-users', [BlockController::class, 'index'])->name('settings.blocked');
