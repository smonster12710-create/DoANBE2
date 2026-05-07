<div class="grid">
    {{-- VÒNG LẶP 1: CHỈ HIỂN THỊ DANH SÁCH BÀI VIẾT --}}
    @foreach ($posts as $post)
        <div class="card mb-4">
            {{-- HEADER --}}
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img class="avatar" src="{{ $post->user->avatar_url
            ? asset($post->user->avatar_url)
            : 'https://i.pravatar.cc/40?u=' . $post->user_id }}"
                        style="width:40px; height:40px; border-radius:50%; object-fit:cover;">

                    <div class="info ms-2">
                        <strong class="name d-block">{{ $post->user->fullname ?? 'Người dùng' }}</strong>
                        <span class="time text-muted small">{{ $post->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                @if (auth()->id() == $post->user_id)
                    <div class="dropdown">
                        <button class="more-btn border-0 bg-transparent" type="button" data-bs-toggle="dropdown">
                            ⋯
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <button class="dropdown-item" data-bs-toggle="modal"
                                    data-bs-target="#editPostModal{{ $post->id }}">
                                    Sửa bài viết
                                </button>
                            </li>

                            <li>
                                <button class="dropdown-item text-danger" data-bs-toggle="modal"
                                    data-bs-target="#deletePostModal{{ $post->id }}">
                                    Xóa bài viết
                                </button>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>

            {{-- BODY CONTENT --}}
            <div class="card-body">
                <div class="card-text text-dark mb-2">
                    {!! $post->formatted_content ?? nl2br(e($post->content)) !!}
                </div>

                @if ($post->media->count())
                    <div class="card-img-container mb-3" style="cursor: pointer;" data-bs-toggle="modal"
                        data-bs-target="#instagramModal{{ $post->id }}">
                        <img class="card-img w-100 rounded" src="{{ asset($post->media->first()->media_url) }}"
                            style="max-height: 500px; object-fit: cover;">
                    </div>
                @endif

                {{-- ACTIONS (Like, Comment, Pin) - Updated SVG Style --}}
                <div class="card-actions d-flex justify-content-between align-items-center border-top pt-3 px-1">
                    <div class="d-flex align-items-center gap-3">
                        {{-- LIKE --}}
                        <form action="{{ route('post.like', $post->id) }}" method="POST" class="m-0">
                            @csrf
                            @php
                                $userId = auth()->id();
                                $checkLike = $userId ? $post->likes->contains('user_id', $userId) : false;
                            @endphp
                            <button type="submit"
                                class="btn-action border-0 bg-transparent p-0 d-flex align-items-center {{ $checkLike ? 'text-danger' : '' }}"
                                style="gap: 5px;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="{{ $checkLike ? 'currentColor' : 'none' }}"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
                                <span class="fw-bold" style="font-size: 14px;">{{ $post->likes->count() }}</span>
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
                            <span class="fw-bold" style="font-size: 14px;">{{ $post->comments->count() }}</span>
                        </button>

                        {{-- SAVE (chưa xử lý) --}}
                        <div class="btn-action">
                            🔖
                        </div>
                        {{-- Pin--}}
                        @if(auth()->id() == $post->user_id)
                            <form action="{{ route('post.pin', $post->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-action pin-btn {{ $post->is_pinned ? 'is-pinned' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                        viewBox="0 0 16 16">
                                        <path
                                            d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1z" />
                                        <path
                                            d="M8 4a.5.5 0 0 1 .5.5V6H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V7H6a.5.5 0 0 1 0-1h1.5V4.5A.5.5 0 0 1 8 4" />
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </div>
                    <div class="share-btn">🔗</div>
                </div>
                {{-- LIKERS --}}
                @if ($post->likes->count() > 0)
                    <div class="likers-row">
                        <a href="{{ route('post.likers', $post->id) }}" class="likers-link">
                            Xem tất cả người đã thích
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>