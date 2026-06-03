@extends('dashboard')

@section('content')
    <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
        {{-- Cover nhóm --}}
        <div
            style="background: #fff; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 20px;">
            {{-- ĐÃ SỬA: Đổi màu nền cố định thành ảnh nền động nếu Admin có upload hình --}}
            <div
                style="height: 180px; 
                                    background: {{ $group->cover ? 'url(' . asset('storage/' . $group->cover) . ') no-repeat center center / cover' : 'linear-gradient(135deg, #e51f28, #222)' }}; 
                                    padding: 20px; color: #fff; display: flex; align-items: flex-end; justify-content: space-between; position: relative;">

                {{-- Lớp phủ tối mờ để chữ luôn nổi bật khi ảnh nền quá sáng --}}
                <div style="position: absolute; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.3); z-index: 1;">
                </div>

                <div style="position: relative; z-index: 2;">
                    <h1 style="margin: 0; font-size: 24px; text-shadow: 1px 1px 3px rgba(0,0,0,0.6);">{{ $group->name }}
                    </h1>
                    <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9; text-shadow: 1px 1px 2px rgba(0,0,0,0.6);">
                        {{ ucfirst($group->privacy) }} · {{ $memberCount }} thành viên
                    </p>
                </div>
                <div style="position: relative; z-index: 2;">
                    @if (!$membership)
                        <form action="{{ route('groups.join', $group->slug) }}" method="POST">
                            @csrf
                            <button type="submit"
                                style="background: #fff; color: #111; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer;">Tham
                                gia nhóm</button>
                        </form>
                    @elseif($membership->status === 'pending')
                        <button disabled
                            style="background: #ccc; color: #666; border: none; padding: 8px 16px; border-radius: 6px;">Đang
                            chờ duyệt...</button>
                    @else
                        <div style="display: flex; gap: 10px; align-items: center;">
                            @if ($group->creator_id === auth()->id())
                                {{-- ✨ ĐÃ THÊM: Nút mở Modal chỉnh sửa thông tin nhóm --}}
                                <button type="button" data-bs-toggle="modal" data-bs-target="#editGroupModal"
                                    style="background: #0d6efd; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; font-size: 14px; cursor: pointer;">
                                    Sửa nhóm
                                </button>

                                <a href="{{ route('groups.requests', $group->slug) }}"
                                    style="background: #242526; color: #fff; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; font-size: 14px;">Duyệt
                                    thành viên</a>

                                <form action="{{ route('groups.destroy', $group->slug) }}" method="POST"
                                    onsubmit="return confirm('CẢNH BÁO: Bạn có chắc chắn muốn GIẢI TÁN nhóm này? Tất cả bài viết và thành viên sẽ bị xóa vĩnh viễn!')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#confirmDeleteGroupModal"
                                        style="background: #e51f28; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px;">
                                        Giải tán nhóm
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('groups.leave', $group->slug) }}" method="POST"
                                    onsubmit="return confirm('Bạn có chắc chắn muốn rời nhóm?')">
                                    @csrf
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#confirmLeaveGroupModal"
                                        style="background: rgba(255,255,255,0.2); color: #fff; border: 1px solid #fff; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                                        Rời nhóm
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            <div style="padding: 15px 20px; color: #65676b; font-size: 14px; background: #fafafa;">
                <strong>Mô tả:</strong> {{ $group->description ?? 'Không có mô tả.' }}
            </div>
        </div>

        {{-- Nội dung bài viết --}}
        @if ($canViewContent)
            @if ($membership && $membership->status === 'approved')
                {{-- Ô đăng bài --}}
                <div
                    style="background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); margin-bottom: 20px;">
                    <form action="{{ route('posts.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="group_id" value="{{ $group->id }}">
                        <textarea name="content" required placeholder="Viết điều gì đó vào nhóm..."
                            style="width: 100%; border: 1px solid #e4e6eb; border-radius: 8px; padding: 10px; resize: none; min-height: 60px; box-sizing: border-box;"></textarea>
                        <div style="margin-top: 10px;">
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 10px;">
                                <input type="checkbox" name="is_anonymous" value="1" {{ auth()->check() && auth()->user()->anonymous_posts ? 'checked' : '' }}>
                                <span>Đăng ẩn danh</span>
                            </label>
                            @if(auth()->check() && auth()->user()->anonymous_posts)
                                <p style="margin: 0 0 10px 0; color: #6c757d; font-size: 13px;">
                                    Chế độ ẩn danh đang bật trong cài đặt. Bỏ tích để đăng bài công khai.
                                </p>
                            @endif
                        </div>
                        <div style="display: flex; justify-content: flex-end; align-items: center; margin-top: 10px; gap: 10px;">
                            <button type="submit"
                                style="background: #e51f28; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer;">
                                Đăng bài
                            </button>
                        </div>

                        </p>
            @endif
                </form>
            </div>
        @endif

        {{-- Luồng bài viết --}}
        @if ($posts->count() > 0)
            @inject('textProcessor', 'App\Services\TextProcessorService')

            @foreach ($posts as $post)
                <div class="card mb-4 post-clickable" data-post-id="{{ $post->id }}"
                    style="background: #fff; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); margin-bottom: 20px; padding: 15px;">

                    {{-- HEADER BÀI VIẾT --}}
                    <div class="card-header d-flex justify-content-between align-items-center"
                        style="background: none; border: none; padding: 0 0 10px 0;">
                        <div class="d-flex align-items-center">
                            @php
                                $canSeeAnonymousOwner = $post->is_anonymous && auth()->check() && auth()->user()->role === 'admin';
                            @endphp

                            @if (!$post->is_anonymous || $canSeeAnonymousOwner)
                                <a href="{{ route('profile.show', $post->user->username) }}" onclick="event.stopPropagation()"
                                    class="post-user-link">
                                    <div class="avatar-online-wrap" style="position: relative; display: inline-block;">
                                        <img class="avatar"
                                            src="{{ $post->user->avatar_url ? asset($post->user->avatar_url) : asset('img/user/user.jpg') }}"
                                            alt="avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                        @if ($post->user && $post->user->canShowActivityTo(auth()->user()))
                                            <span class="online-dot"
                                                style="position: absolute; bottom: 0; right: 0; width: 10px; height: 10px; background: #31a24c; border: 2px solid #fff; border-radius: 50%;"></span>
                                        @endif
                                    </div>
                                </a>
                            @else
                                <div class="avatar-online-wrap" style="position: relative; display: inline-block;">
                                    <img class="avatar" src="{{ asset('img/user/user.jpg') }}" alt="avatar"
                                        style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                </div>
                            @endif

                            <div class="info ms-2" style="margin-left: 12px;">
                                @if (!$post->is_anonymous || $canSeeAnonymousOwner)
                                    <a href="{{ route('profile.show', $post->user->username) }}" onclick="event.stopPropagation()"
                                        class="post-user-link" style="text-decoration: none; color: inherit;">
                                        <strong class="name d-block" style="display: block; color: #050505;">
                                            {{ $post->user->fullname ?? 'Người dùng' }}
                                        </strong>
                                        @if ($post->parent_id)
                                            <span class="text-muted small" style="font-size: 13px; color: #65676b;">đã chia
                                                sẻ một bài viết</span>
                                        @endif
                                    </a>
                                @else
                                    <strong class="name d-block" style="display: block; color: #050505;">
                                        Ẩn danh
                                    </strong>
                                    @if ($post->parent_id)
                                        <span class="text-muted small" style="font-size: 13px; color: #65676b;">đã chia
                                            sẻ một bài viết</span>
                                    @endif
                                @endif
                                <span class="time text-muted small" style="font-size: 12px; color: #65676b;">
                                    {{ $post->created_at->diffForHumans() }}
                                </span>
                            </div>

                            {{-- NÚT THEO DÕI --}}
                            @if (auth()->check() && auth()->id() !== $post->user_id)
                                <form action="{{ route('follow.toggle', $post->user_id) }}" method="POST" class="ms-5 m-0"
                                    style="margin-left: 20px; display: inline-block;">
                                    @csrf
                                    @php
                                        $isFollowing = auth()->user()->isFollowing($post->user_id);
                                    @endphp
                                    <button type="submit"
                                        class="prevent-post-modal no-post-modal btn p-0 border-0 fw-bold {{ $isFollowing ? 'btn-following' : 'btn-follow' }}"
                                        style="font-size: 13px; background: none; border: none; color: #e51f28; cursor: pointer;">
                                        {{ $isFollowing ? 'Đang theo dõi' : 'Theo dõi' }}
                                    </button>
                                </form>
                            @endif

                            {{-- NÚT KICK THÀNH VIÊN TRỰC TIẾP TRÊN BÀI VIẾT --}}
                            @if ($group->creator_id === auth()->id() && $post->user_id !== auth()->id())
                                @if (in_array($post->user_id, $approvedMemberIds))
                                    <form action="{{ route('groups.kick', [$group->slug, $post->user_id]) }}" method="POST"
                                        class="ms-3 m-0 prevent-post-modal no-post-modal"
                                        onsubmit="this.querySelector('button').disabled=true; return confirm('Bạn có chắc chắn muốn trục xuất thành viên này khỏi nhóm?')"
                                        style="margin-left: 15px; display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            style="background: none; border: none; color: #e51f28; font-weight: bold; font-size: 13px; cursor: pointer; padding: 0;">
                                            Kick khỏi nhóm
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>

                        {{-- THAO TÁC BA CHẤM (CHỈ CHỦ BÀI VIẾT) --}}
                        @if (auth()->id() == $post->user_id)
                            <div class="dropdown prevent-post-modal no-post-modal">
                                <button class="more-btn border-0 bg-transparent" type="button" data-bs-toggle="dropdown"
                                    style="cursor: pointer; font-size: 18px; color: #65676b;">⋯</button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><button class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#editPostModal{{ $post->id }}">Sửa bài
                                            viết</button>
                                    </li>
                                    <li><button class="dropdown-item text-danger" data-bs-toggle="modal"
                                            data-bs-target="#deletePostModal{{ $post->id }}">Xóa bài viết</button>
                                    </li>
                                    <li>
                                        <form action="{{ route('post.toggleComment', $post->id) }}" method="POST" class="m-0">
                                            @csrf
                                            @php $isLocked = \Illuminate\Support\Str::contains($post->content, '[#LOCK_COMMENT#]'); @endphp
                                            <button type="submit"
                                                class="dropdown-item {{ $isLocked ? 'text-success' : 'text-warning' }}">
                                                {{ $isLocked ? 'Mở lại bình luận' : 'Chặn bình luận' }}
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </div>

                    {{-- NỘI DUNG CHỮ --}}
                    <div class="card-body" style="padding: 0;">
                        <div class="card-text text-dark mb-2"
                            style="font-size: 15px; line-height: 1.5; color: #050505; margin-bottom: 10px;">
                            {!! $textProcessor->formatContent($post->content) !!}
                        </div>

                        {{-- HIỂN THỊ HÌNH ẢNH / ALBUM (NẾU CÓ) --}}
                        @if (!$post->parent_id && $post->media->count())
                            @php
                                $media = $post->media->values();
                                $count = $media->count();
                                $defaultImage = asset('img/default-image.png');
                                $getImageUrl = function ($path) use ($defaultImage) {
                                    if (empty($path)) {
                                        return $defaultImage;
                                    }
                                    if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
                                        return $path;
                                    }
                                    return asset(ltrim($path, '/'));
                                };
                            @endphp

                            <div class="fb-gallery mb-3" onclick="event.stopPropagation()">
                                @if ($count == 1)
                                    <div class="single-image" style="border-radius: 8px; overflow: hidden;">
                                        <img src="{{ $getImageUrl($media[0]->media_url) }}" alt="post-image" loading="lazy"
                                            style="max-width: 100%; max-height: 400px; width: auto; object-fit: cover;"
                                            data-bs-toggle="modal" data-bs-target="#instagramModal{{ $post->id }}">
                                    </div>
                                @else
                                    <div class="row g-1">
                                        @foreach ($media->take(4) as $index => $item)
                                            <div class="col-3 position-relative">
                                                <img src="{{ $getImageUrl($item->media_url) }}" alt="post-image" class="img-fluid rounded"
                                                    style="height: 100px; width: 100%; object-fit: cover; cursor: pointer;"
                                                    data-bs-toggle="modal" data-bs-target="#instagramModal{{ $post->id }}">
                                                @if ($index == 3 && $count > 4)
                                                    <div class="position-absolute top-0 start-0 w-100 h-100 rounded d-flex align-items-center justify-content-center text-white fw-bold"
                                                        style="background: rgba(0,0,0,0.5); cursor: pointer;" data-bs-toggle="modal"
                                                        data-bs-target="#instagramModal{{ $post->id }}">
                                                        +{{ $count - 4 }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- NẾU LÀ BÀI VIẾT CHIA SẺ --}}
                        @if ($post->parent_id && $post->parent)
                            <div class="original-post-block border rounded p-3 bg-light mb-3 mx-1"
                                style="border: 1px solid #e4e6eb; padding: 12px; background: #f0f2f5; border-radius: 8px; margin-bottom: 15px;">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="{{ $post->parent->user->avatar_url ? asset($post->parent->user->avatar_url) : asset('img/user/user.jpg') }}"
                                        style="width:30px; height:30px; border-radius:50%; object-fit:cover;">
                                    <strong class="text-dark ms-2" style="font-size: 14px; margin-left: 8px;">
                                        {{ $post->parent->user->fullname ?? 'Người dùng' }}
                                    </strong>
                                </div>
                                <div class="text-secondary mb-2" style="font-size: 13.5px; color: #65676b;">
                                    {!! $textProcessor->formatContent($post->parent->content) !!}
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- CỤM NÚT TƯƠNG TÁC (LIKE, COMMENT, SHARE, SAVE) --}}
                    <div class="card-actions d-flex justify-content-between align-items-center border-top pt-3 px-1 prevent-post-modal no-post-modal"
                        style="border-top: 1px solid #e4e6eb; padding-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                        <div class="d-flex align-items-center gap-3" style="display: flex; gap: 20px; align-items: center;">
                            {{-- NÚT LIKE --}}
                            <form action="{{ route('post.like', $post->id) }}" method="POST" class="m-0 like-form"
                                style="margin: 0;">
                                @csrf
                                @php
                                    $userId = auth()->id();
                                    $checkLike = $userId ? $post->likes->contains('user_id', $userId) : false;
                                @endphp
                                <button type="submit"
                                    class="btn-action border-0 bg-transparent p-0 d-flex align-items-center btn-like-ajax {{ $checkLike ? 'text-danger' : '' }}"
                                    style="display: flex; align-items: center; gap: 5px; background: none; border: none; cursor: pointer; color: {{ $checkLike ? '#e51f28' : '#65676b' }};">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="like-icon"
                                        fill="{{ $checkLike ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" width="22" height="22">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                    </svg>
                                    <span class="fw-bold like-count-text"
                                        style="font-size: 14px;">{{ $post->likes->count() }}</span>
                                </button>
                            </form>

                            {{-- NÚT BÌNH LUẬN (MỞ MODAL) --}}
                            <button class="btn-action border-0 bg-transparent p-0 d-flex align-items-center" data-bs-toggle="modal"
                                data-bs-target="#instagramModal{{ $post->id }}"
                                style="display: flex; align-items: center; gap: 5px; background: none; border: none; cursor: pointer; color: #65676b;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" width="22" height="22">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48l-1.115 2.91a.45.45 0 0 0 .584.584l2.91-1.115A8.97 8.97 0 0 0 12 20.25Z" />
                                </svg>
                                <span class="fw-bold comment-count-{{ $post->id }}"
                                    style="font-size: 14px;">{{ $post->comments->count() }}</span>
                            </button>
                            {{-- NÚT LƯU BÀI VIẾT --}}
                            @php
                                $isSaved = auth()->user() ? auth()->user()->savedPosts->contains($post->id) : false;
                            @endphp
                            <form action="{{ route('posts.save', $post->id) }}" method="POST"
                                class="no-post-modal m-0 ajax-save-form" style="margin: 0;">
                                @csrf
                                <button type="submit" class="btn-action save-btn {{ $isSaved ? 'saved text-warning' : '' }}"
                                    style="background: none; border: none; cursor: pointer; color: {{ $isSaved ? '#ffc107' : '#65676b' }};">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                        fill="{{ $isSaved ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M6 3h12a1 1 0 0 1 1 1v17l-7-5-7 5V4a1 1 0 0 1 1-1z" />
                                    </svg>
                                </button>
                            </form>
                        </div>

                        {{-- NÚT CHIA SẺ --}}
                        <button type="button" class="btn-action border-0 bg-transparent p-0 d-flex align-items-center text-primary"
                            data-bs-toggle="modal" data-bs-target="#shareModal-{{ $post->id }}"
                            style="background: none; border: none; cursor: pointer;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 640 640" width="22" height="22"
                                opacity="0.7">
                                <path
                                    d="M371.8 82.4C359.8 87.4 352 99 352 112L352 192L240 192C142.8 192 64 270.8 64 368C64 481.3 145.5 531.9 164.2 542.1C166.7 543.5 169.5 544 172.3 544C183.2 544 192 535.1 192 524.3C192 516.8 187.7 509.9 182.2 504.8C172.8 496 160 478.4 160 448.1C160 395.1 203 352.1 256 352.1L352 352.1L352 432.1C352 445 359.8 456.7 371.8 461.7C383.8 466.7 397.5 463.9 406.7 454.8L566.7 294.8C579.2 282.3 579.2 262 566.7 249.5L406.7 89.5C397.5 80.3 383.8 77.6 371.8 82.6z" />
                            </svg>
                        </button>

                        {{-- MODAL PHỤC VỤ CHIA SẺ --}}
                        @push('modals')
                            <div class="modal fade" id="shareModal-{{ $post->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Chia sẻ bài viết</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        @php
                                            $targetPost = $post->parent_id && $post->parent ? $post->parent : $post;
                                        @endphp
                                        <form action="{{ route('post.share', $targetPost->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <textarea name="content" class="form-control mb-3" rows="3"
                                                    placeholder="Hãy viết gì đó về bài viết này..."></textarea>
                                                <div class="p-3 bg-light border rounded">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <strong class="text-dark">
                                                            {{ $targetPost->fullname ?? ($targetPost->user->fullname ?? $targetPost->user->username) }}
                                                        </strong>
                                                    </div>
                                                    <p class="text-muted mb-0 style-content-preview">
                                                        {{ \Illuminate\Support\Str::limit($targetPost->content, 120) }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary shadow-none"
                                                    data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-primary shadow-none">Chia sẻ
                                                    ngay</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endpush
                    </div>

                    {{-- LINK XEM LƯỢT THÍCH --}}
                    <div class="likers-row mt-2 px-1" style="margin-top: 8px;">
                        <a href="{{ route('post.likers', $post->id) }}"
                            class="likers-link text-decoration-none small fw-bold text-muted"
                            style="font-size: 13px; color: #65676b; text-decoration: none; display: inline-block;">
                            Xem tất cả người đã thích
                        </a>
                    </div>
                </div>

                @include('partials.post_modals', ['post' => $post])
                @include('partials.post_modals', ['post' => $post])
                @include('partials.post_modals', ['post' => $post]) {{-- Đây chính là cái bạn cần --}}
            @endforeach
        @else
            <div style="text-align: center; color: #65676b; background: #fff; padding: 30px; border-radius: 8px;">Chưa
                có bài viết nào trong nhóm này.</div>
        @endif
    </div>{{-- ✨ ĐÃ THÊM: TOÀN BỘ POPUP MODAL CHỈNH SỬA THÔNG TIN NHÓM & UPDATE COVER --}}
    @if ($group->creator_id === auth()->id())
        <div class="modal fade" id="editGroupModal" tabindex="-1" aria-labelledby="editGroupModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                    <div class="modal-header" style="border-bottom: 1px solid #e4e6eb;">
                        <h5 class="modal-title fw-bold" id="editGroupModalLabel" style="color: #050505;">Cài đặt & Sửa
                            thông tin nhóm</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="{{ route('groups.update', $group->slug) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="modal-body" style="padding: 20px;">
                            <div class="mb-3">
                                <label class="form-label fw-bold" style="font-size: 14px; color: #050505;">Tên
                                    nhóm</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $group->name) }}"
                                    required style="border-radius: 6px;">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold" style="font-size: 14px; color: #050505;">Giới thiệu / Mô
                                    tả</label>
                                <textarea name="description" class="form-control" rows="3"
                                    style="border-radius: 6px; resize: none;">{{ old('description', $group->description) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold" style="font-size: 14px; color: #050505;">Quyền riêng
                                    tư</label>
                                <select name="privacy" class="form-select" style="border-radius: 6px;">
                                    <option value="public" {{ $group->privacy === 'public' ? 'selected' : '' }}>Công khai
                                        (Public)</option>
                                    <option value="private" {{ $group->privacy === 'private' ? 'selected' : '' }}>Riêng tư
                                        (Private)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold" style="font-size: 14px; color: #050505;">Thay đổi ảnh
                                    nền (Cover)</label>
                                <input type="file" name="cover" class="form-control" accept="image/*"
                                    style="border-radius: 6px;">
                                <div style="font-size: 12px; color: #65676b; margin-top: 4px;">Hỗ trợ định dạng JPG, PNG,
                                    GIF (Tối đa 2MB).</div>

                                @if ($group->cover)
                                    <div class="mt-3">
                                        <span style="font-size: 12px; color: #65676b; display: block; margin-bottom: 5px;">Ảnh
                                            nền hiện tại:</span>
                                        <img src="{{ asset('storage/' . $group->cover) }}"
                                            style="max-height: 100px; width: 100%; object-fit: cover; border-radius: 6px; border: 1px solid #ddd;">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="modal-footer" style="border-top: 1px solid #e4e6eb;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                style="border-radius: 6px; font-weight: bold;">Hủy</button>
                            <button type="submit" class="btn btn-success"
                                style="background: #e51f28; border: none; border-radius: 6px; font-weight: bold; padding: 6px 20px;">Lưu
                                thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        </div>
    @endif
    <div class="modal fade" id="confirmDeleteGroupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận giải tán nhóm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn giải tán nhóm này không? Tất cả bài viết và dữ liệu sẽ bị xóa vĩnh viễn!
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <form id="deleteGroupForm" action="{{ route('groups.destroy', $group->slug) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Đồng ý giải tán</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmLeaveGroupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận rời nhóm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn rời khỏi nhóm này không?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>

                    {{-- Form thực hiện rời nhóm --}}
                    <form action="{{ route('groups.leave', $group->slug) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning">Đồng ý rời nhóm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection