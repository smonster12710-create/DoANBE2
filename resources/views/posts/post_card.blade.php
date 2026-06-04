@inject('textProcessor', 'App\Services\TextProcessorService')

<div class="grid">

    {{-- VÒNG LẶP CỦA BẠN SẼ BẮT ĐẦU TỪ ĐÂY (Ví dụ: @foreach ($posts as $post)) --}}
    <div class="card mb-4 post-clickable" data-post-id="{{ $post->id }}">
        {{-- HEADER --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                @if ($post->is_anonymous)
                    <a href="javascript:void(0)" onclick="event.stopPropagation()" class="post-user-link">
                        <div class="avatar-online-wrap">
                            <img class="avatar" src="{{ asset('img/user/user.jpg') }}" alt="Ẩn danh">
                        </div>
                    </a>

                    <div class="info ms-2">
                        <a href="javascript:void(0)" onclick="event.stopPropagation()" class="post-user-link">
                            <strong class="name d-block">Người dùng ẩn danh 🕵️</strong>
                            @if ($post->parent_id)
                                <span class="text-muted small" style="font-size: 13px;">đã chia sẻ một bài viết</span>
                            @endif
                        </a>
                        <span class="time text-muted small">{{ $post->created_at->diffForHumans() }}</span>
                    </div>

                    {{-- KIỂM TRA: NẾU LÀ BÀI CÔNG KHAI BÌNH THƯỜNG --}}
                @else
                    <a href="{{ route('profile.show', $post->user->username) }}" onclick="event.stopPropagation()"
                        class="post-user-link">
                        <div class="avatar-online-wrap">
                            <img class="avatar"
                                src="{{ $post->user->avatar_url ? asset($post->user->avatar_url) : asset('img/user/user.jpg') }}"
                                alt="avatar">
                            @include('partials.activity_dot', ['user' => $post->user])
                        </div>
                    </a>

                    <div class="info ms-2">
                        <div class="d-flex align-items-center flex-wrap" style="gap: 2px;">
                            <a href="{{ route('profile.show', $post->user->username) }}"
                                onclick="event.stopPropagation()" class="post-user-link text-decoration-none">
                                <strong class="name text-dark hover-underline"
                                    style="font-size: 14.5px;">{{ $post->user->fullname ?? ($post->user->name ?? 'Người dùng') }}</strong>
                            </a>

                            @if ($post->wall_user_id && $post->wall_user_id !== $post->user_id && $post->wallUser)
                                <span class="text-muted mx-1" style="font-size: 12px; opacity: 0.8;">➔</span>
                                <a href="{{ route('profile.show', $post->wallUser->username) }}"
                                    onclick="event.stopPropagation()" class="post-user-link text-decoration-none">
                                    <strong class="name text-dark hover-underline"
                                        style="font-size: 14.5px;">{{ $post->wallUser->fullname ?? $post->wallUser->name }}</strong>
                                </a>
                            @endif

                            @if ($post->parent_id && (!$post->wall_user_id || $post->wall_user_id === $post->user_id))
                                <span class="text-muted small ms-1" style="font-size: 13px;">đã chia sẻ một bài
                                    viết</span>
                            @endif
                        </div>
                        <span class="time text-muted small d-block"
                            style="margin-top: 1px;">{{ $post->created_at->diffForHumans() }}</span>
                    </div>

                    {{-- NÚT THEO DÕI (Chỉ hiện khi bài CÔNG KHAI và không phải bài của mình) --}}
                    @if (auth()->check() && auth()->id() !== $post->user_id)
                        <form action="{{ route('follow.toggle', $post->user_id) }}" method="POST" class="ms-5 m-0">
                            @csrf
                            @php
                                $isFollowing = auth()->user()->isFollowing($post->user_id);
                            @endphp

                            <input type="hidden" name="expected_status" value="{{ $isFollowing ? '1' : '0' }}">

                            <button type="submit"
                                class="prevent-post-modal no-post-modal btn p-0 border-0 fw-bold {{ $isFollowing ? 'btn-following' : 'btn-follow' }}"
                                style="font-size: 13px;">
                                {{ $isFollowing ? 'Đang theo dõi' : 'Theo dõi' }}
                            </button>
                        </form>
                    @endif
                @endif
                {{-- KẾT THÚC CỤM ẨN DANH / CÔNG KHAI --}}
            </div>

            @if (auth()->id() == $post->user_id)
                <div class="dropdown prevent-post-modal no-post-modal">
                    <button class="more-btn border-0 bg-transparent" type="button" data-bs-toggle="dropdown">⋯</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <button class="dropdown-item" data-bs-toggle="modal"
                                data-bs-target="#editPostModal{{ $post->id }}">Sửa bài
                                viết</button>
                        </li>

                        <li>
                            <form action="{{ route('post.toggleComment', $post->id) }}" method="POST" class="m-0">
                                @csrf
                                @php
                                    $isLocked = Str::contains($post->content, '[#LOCK_COMMENT#]');
                                @endphp
                                <button type="submit"
                                    class="dropdown-item {{ $isLocked ? 'text-success' : 'text-gray' }}">
                                    {{ $isLocked ? 'Mở lại bình luận' : 'Chặn bình luận' }}
                                </button>
                            </form>
                        </li>
                        <li>
                            <button class="dropdown-item text-danger" data-bs-toggle="modal"
                                data-bs-target="#deletePostModal{{ $post->id }}">Xóa bài viết</button>
                        </li>
                    </ul>
                </div>
            @endif
        </div>

        {{-- BODY CONTENT --}}
        <div class="card-body">
            {{-- Nội dung chữ của bài viết hiện tại --}}
            <div class="card-text text-dark mb-2">
                {!! $textProcessor->formatContent($post->content) !!}
            </div>

            {{-- TRƯỜNG HỢP 1: BÀI VIẾT CHIA SẺ --}}
            @if ($post->parent_id && $post->parent)
                <div class="original-post-block border rounded p-3 bg-light mb-3 mx-1">
                    <div class="d-flex align-items-center mb-2">
                        @if ($post->parent->is_anonymous)
                            <img src="{{ asset('img/user/user.jpg') }}"
                                style="width:30px; height:30px; border-radius:50%; object-fit:cover;">
                            <strong class="text-dark ms-2" style="font-size: 14px;">Người dùng ẩn danh 🕵️</strong>
                        @else
                            <img src="{{ $post->parent->user->avatar_url ? asset($post->parent->user->avatar_url) : asset('img/user/user.jpg') }}"
                                style="width:30px; height:30px; border-radius:50%; object-fit:cover;">
                            <strong class="text-dark ms-2" style="font-size: 14px;">
                                {{ $post->parent->user->fullname ?? 'Người dùng' }}
                            </strong>
                        @endif
                    </div>

                    <div class="text-secondary mb-2" style="font-size: 13.5px;">
                        {!! $textProcessor->formatContent($post->parent->content) !!}
                    </div>

                    @if ($post->parent->media && $post->parent->media->count())
                        @php
                            $parentMedia = $post->parent->media->values();
                            $parentCount = $parentMedia->count();
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



                        <div class="parent-fb-gallery my-2">
                            @if ($parentCount == 1)
                                <div class="single-image shadow-sm" style="max-height: 250px; overflow: hidden; border-radius: 6px;">
                                    <img src="{{ $getImageUrl($parentMedia[0]->media_url) }}" alt="parent-post-image" class="img-fluid w-100"
                                        style="object-fit: cover; max-height: 250px;" data-bs-toggle="modal"
                                        data-bs-target="#instagramModal{{ $post->parent->id }}">
                                </div>
                            @else
                                <div class="row g-1">
                                    @foreach ($parentMedia->take(4) as $index => $item)
                                        <div class="col-3 position-relative">
                                            <img src="{{ $getImageUrl($item->media_url) }}" alt="parent-post-image" class="img-fluid rounded"
                                                style="height: 80px; width: 100%; object-fit: cover;" data-bs-toggle="modal"
                                                data-bs-target="#instagramModal{{ $post->parent->id }}">
                                            @if ($index == 3 && $parentCount > 4)
                                                <div class="position-absolute top-0 start-0 w-100 h-100 rounded d-flex align-items-center justify-content-center text-white fw-bold"
                                                    style="background: rgba(0,0,0,0.5); font-size: 14px; cursor: pointer;" data-bs-toggle="modal"
                                                    data-bs-target="#instagramModal{{ $post->parent->id }}">
                                                    +{{ $parentCount - 4 }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- TRƯỜNG HỢP 2: BÀI VIẾT THƯỜNG --}}
            @else
                            @if ($post->media->count())
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
                                        <div class="single-image">
                                            <img src="{{ $getImageUrl($media[0]->media_url) }}" alt="post-image" loading="lazy"
                                                onerror="this.onerror=null; this.src='{{ $defaultImage }}';"
                                                data-bs-toggle="modal" data-bs-target="#instagramModal{{ $post->id }}">
                                        </div>
                                    @elseif ($count == 2)
                                        <div class="two-images">
                                            @foreach ($media as $item)
                                                <img src="{{ $getImageUrl($item->media_url) }}" alt="post-image" loading="lazy"
                                                    onerror="this.onerror=null;this.src='{{ $defaultImage }}';"
                                                    data-bs-toggle="modal" data-bs-target="#instagramModal{{ $post->id }}">
                                            @endforeach
                                        </div>
                                    @elseif ($count == 3)
                                        <div class="three-images">
                                            <div class="left">
                                                <img src="{{ $getImageUrl($media[0]->media_url) }}" alt="post-image"
                                                    loading="lazy" onerror="this.onerror=null;this.src='{{ $defaultImage }}';"
                                                    data-bs-toggle="modal" data-bs-target="#instagramModal{{ $post->id }}">
                                            </div>
                                            <div class="right">
                                                <img src="{{ $getImageUrl($media[1]->media_url) }}" alt="post-image"
                                                    loading="lazy" onerror="this.onerror=null;this.src='{{ $defaultImage }}';"
                                                    data-bs-toggle="modal" data-bs-target="#instagramModal{{ $post->id }}">
                                                <img src="{{ $getImageUrl($media[2]->media_url) }}" alt="post-image"
                                                    loading="lazy" onerror="this.onerror=null;this.src='{{ $defaultImage }}';"
                                                    data-bs-toggle="modal" data-bs-target="#instagramModal{{ $post->id }}">
                                            </div>
                                        </div>
                                    @else
                                        <div class="four-images">
                                            @for ($i = 0; $i < min(4, $count); $i++)
                                                <div class="img-box position-relative">
                                                    <img src="{{ $getImageUrl($media[$i]->media_url) }}" alt="post-image"
                                                        loading="lazy"
                                                        onerror="this.onerror=null;this.src='{{ $defaultImage }}';"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#instagramModal{{ $post->id }}">
                                                    @if ($i == 3 && $count > 4)
                                                        <div class="overlay-more" data-bs-toggle="modal"
                                                            data-bs-target="#instagramModal{{ $post->id }}">
                                                            +{{ $count - 4 }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endfor
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endif
        </div>

        {{-- ACTIONS --}}
        <div
            class="card-actions d-flex justify-content-between align-items-center border-top pt-3 px-1 prevent-post-modal no-post-modal">
            <div class="d-flex align-items-center gap-3">
                {{-- LIKE --}}
                <form action="{{ route('post.like', $post->id) }}" method="POST" class="m-0 like-form">
                    @csrf
                    @php
                        $userId = auth()->id();
                        $checkLike = $userId ? $post->likes->contains('user_id', $userId) : false;
                    @endphp
                    <button type="submit"
                        class="btn-action border-0 bg-transparent p-0 d-flex align-items-center btn-like-ajax {{ $checkLike ? 'text-danger' : '' }}"
                        style="gap: 5px;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="like-icon"
                            fill="{{ $checkLike ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                        <span class="fw-bold like-count-text"
                            style="font-size: 14px;">{{ $post->likes->count() }}</span>
                    </button>
                </form>

                {{-- COMMENT --}}
                <button class="btn-action border-0 bg-transparent p-0 d-flex align-items-center"
                    data-bs-toggle="modal" data-bs-target="#instagramModal{{ $post->id }}" style="gap: 5px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" width="24" height="24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48l-1.115 2.91a.45.45 0 0 0 .584.584l2.91-1.115A8.97 8.97 0 0 0 12 20.25Z" />
                    </svg>
                    <span class="fw-bold comment-count-{{ $post->id }}"
                        style="font-size: 14px;">{{ $post->comments->count() }}</span>
                </button>

                {{-- SAVE --}}
                @php
                    $isSaved = auth()->user() ? auth()->user()->savedPosts->contains($post->id) : false;
                @endphp
                <form action="{{ route('posts.save', $post->id) }}" method="POST"
                    class="no-post-modal m-0 ajax-save-form">
                    @csrf
                    <button type="submit" class="btn-action save-btn {{ $isSaved ? 'saved text-warning' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="{{ $isSaved ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 3h12a1 1 0 0 1 1 1v17l-7-5-7 5V4a1 1 0 0 1 1-1z" />
                        </svg>
                    </button>
                </form>

                {{-- PIN --}}
                @if (auth()->id() == $post->user_id && request()->routeIs('profile.show'))
                    <form action="{{ route('post.pin', $post->id) }}" method="POST"
                        class="no-post-modal m-0 ajax-pin-form">
                        @csrf
                        <button type="submit"
                            class="btn-action pin-btn {{ $post->is_pinned ? 'is-pinned text-primary' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                fill="currentColor" viewBox="0 0 640 640">
                                <path
                                    d="M288.6 76.8C344.8 20.6 436 20.6 492.2 76.8C548.4 133 548.4 224.2 492.2 280.4L328.2 444.4C293.8 478.8 238.1 478.8 203.7 444.4C169.3 410 169.3 354.3 203.7 319.9L356.5 167.3C369 154.8 389.3 154.8 401.8 167.3C414.3 179.8 414.3 200.1 401.8 212.6L249 365.3C239.6 374.7 239.6 389.9 249 399.2C258.4 408.5 273.6 408.6 282.9 399.2L446.9 235.2C478.1 204 478.1 153.3 446.9 122.1C415.7 90.9 365 90.9 333.8 122.1L169.8 286.1C116.7 339.2 116.7 425.3 169.8 478.4C222.9 531.5 309 531.5 362.1 478.4L492.3 348.3C504.8 335.8 525.1 335.8 537.6 348.3C550.1 360.8 550.1 381.1 537.6 393.6L407.4 523.6C329.3 601.7 202.7 601.7 124.6 523.6C46.5 445.5 46.5 318.9 124.6 240.8L288.6 76.8z" />
                            </svg>
                        </button>
                    </form>
                @endif
                {{-- Report --}}
                <button type="button"
                    class="btn-action border-0 bg-transparent p-0 d-flex align-items-center text-black hover-text-danger"
                    data-bs-toggle="modal" data-bs-target="#reportPostModal{{ $post->id }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                        class="bi bi-flag" viewBox="0 0 16 16">
                        <path
                            d="M14.778.085A.5.5 0 0 1 15 .5V8a.5.5 0 0 1-.314.464L14.5 8l.186.464-.003.001-.006.003-.023.009a12 12 0 0 1-.397.15c-.264.095-.631.223-1.047.35-.816.252-1.879.523-2.71.523-.847 0-1.548-.28-2.158-.525l-.028-.01C7.68 8.71 7.14 8.5 6.5 8.5c-.7 0-1.638.23-2.437.477A20 20 0 0 0 3 9.342V15.5a.5.5 0 0 1-1 0V.5a.5.5 0 0 1 1 0v.282c.226-.079.496-.17.79-.26C4.606.272 5.67 0 6.5 0c.84 0 1.524.277 2.121.519l.043.018C9.286.788 9.828 1 10.5 1c.7 0 1.638-.23 2.437-.477a20 20 0 0 0 1.349-.476l.019-.007.004-.002h.001M14 1.221c-.22.078-.48.167-.766.255-.81.252-1.872.523-2.734.523-.886 0-1.592-.286-2.203-.534l-.008-.003C7.662 1.21 7.139 1 6.5 1c-.669 0-1.606.229-2.415.478A21 21 0 0 0 3 1.845v6.433c.22-.078.48-.167.766-.255C4.576 7.77 5.638 7.5 6.5 7.5c.847 0 1.548.28 2.158.525l.028.01C9.32 8.29 9.86 8.5 10.5 8.5c.668 0 1.606-.229 2.415-.478A21 21 0 0 0 14 7.655V1.222z" />
                    </svg>
                </button>
            </div>

            {{-- SHARE --}}
            <button type="button"
                class="btn-action border-0 bg-transparent p-0 d-flex align-items-center text-primary"
                data-bs-toggle="modal" data-bs-target="#shareModal-{{ $post->id }}" style="gap: 5px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 640 640" width="24"
                    height="24" opacity="0.7">
                    <path
                        d="M371.8 82.4C359.8 87.4 352 99 352 112L352 192L240 192C142.8 192 64 270.8 64 368C64 481.3 145.5 531.9 164.2 542.1C166.7 543.5 169.5 544 172.3 544C183.2 544 192 535.1 192 524.3C192 516.8 187.7 509.9 182.2 504.8C172.8 496 160 478.4 160 448.1C160 395.1 203 352.1 256 352.1L352 352.1L352 432.1C352 445 359.8 456.7 371.8 461.7C383.8 466.7 397.5 463.9 406.7 454.8L566.7 294.8C579.2 282.3 579.2 262 566.7 249.5L406.7 89.5C397.5 80.3 383.8 77.6 371.8 82.6z" />
                </svg>
            </button>

            {{-- MODAL SHARE --}}
            @push('modals')
                <div class="modal fade" id="shareModal-{{ $post->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">Chia sẻ bài viết</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            {{-- Mẹo: Xác định ID thực sự cần chia sẻ --}}
                            @php
                                $targetPost = $post->parent_id && $post->parent ? $post->parent : $post;
                            @endphp

                            {{-- Gửi route chia sẻ với ID của bài viết đích --}}
                            <form action="{{ route('post.share', $targetPost->id) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <textarea name="content" class="form-control mb-3" rows="3" placeholder="Hãy viết gì đó về bài viết này..."></textarea>

                                    {{-- Phần preview trong modal cũng sẽ đổi thành thông tin bài gốc --}}
                                    <div class="p-3 bg-light border rounded">
                                        <div class="d-flex align-items-center mb-2">
                                            <strong class="text-dark">
                                                @if ($targetPost->is_anonymous)
                                                    Người dùng ẩn danh 🕵️
                                                @else
                                                    {{ $targetPost->fullname ?? ($targetPost->user->fullname ?? $targetPost->user->username) }}
                                                @endif
                                            </strong>
                                        </div>
                                        <p class="text-muted mb-0 style-content-preview">
                                            {{ Str::limit($targetPost->content, 120) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary shadow-none"
                                        data-bs-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-primary shadow-none">Chia sẻ ngay</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="reportPostModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">Báo cáo bài viết</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('post.report', $post->id) }}" method="POST"
                                enctype="multipart/form-data" class="report-form">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Lý do báo cáo</label>
                                        <select name="reason" class="form-select shadow-none" required>
                                            <option value="">-- Chọn lý do --</option>
                                            <option value="Spam hoặc nội dung rác">Spam hoặc nội dung rác</option>
                                            <option value="Quấy rối hoặc xúc phạm">Quấy rối hoặc xúc phạm</option>
                                            <option value="Ngôn từ kích động thù hận">Ngôn từ kích động thù hận</option>
                                            <option value="Bạo lực hoặc nhạy cảm">Bạo lực hoặc nhạy cảm</option>
                                            <option value="Lý do khác">Lý do khác</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Thêm ảnh minh chứng</label>
                                        <input type="file" name="images[]" class="form-control report-file-input"
                                            accept="image/*" multiple>
                                        <div class="report-preview d-flex flex-wrap gap-2 mt-2"></div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-danger">Gửi báo cáo</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endpush
        </div>

        {{-- LIKERS --}}
        @if ($post->likes->count() > 0)
            <div class="likers-row mt-2 px-1">
                <a href="{{ route('post.likers', $post->id) }}"
                    class="likers-link text-decoration-none small fw-bold text-muted">
                    Xem tất cả người đã thích
                </a>
            </div>
        @endif
    </div>
    {{-- VÒNG LẶP KẾT THÚC TẠI ĐÂY --}}

</div>
