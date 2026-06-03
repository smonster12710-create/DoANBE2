{{-- TẦNG 0: BÙA CÀO DỮ LIỆU --}}
@php
    $activeStories = \App\Models\Story::with('user')
        ->where('expires_at', '>', now())
        ->latest()
        ->get()
        ->groupBy('user_id');
@endphp

<style>
    .story-wrapper {
        display: flex;
        gap: 15px;
        overflow-x: auto;
        padding: 15px;
        background-color: #fff;
        border-radius: 8px;
        border: 1px solid #ddd;
        margin-bottom: 20px;
    }

    .story-wrapper::-webkit-scrollbar {
        display: none;
    }

    .story-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        cursor: pointer;
        min-width: 72px;
        flex-shrink: 0;
    }

    .story-ring {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        padding: 3px;
        background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
        margin-bottom: 5px;
    }

    .story-ring img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
    }

    .story-add-btn {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        border: 2px dashed #adb5bd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #6c757d;
        background: #f8f9fa;
        margin-bottom: 5px;
        cursor: pointer;
    }

    .story-name {
        font-size: 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 70px;
        text-align: center;
        color: #1c1e21;
    }

    .text-shadow {
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8);
    }

    .carousel-item-container {
        height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #000;
    }
</style>

{{-- TẦNG 1: THANH CUỘN NGANG --}}
<div class="story-wrapper shadow-sm">

    {{-- Nút Tạo Tin Của Mình --}}
    <div class="story-item" data-bs-toggle="modal" data-bs-target="#createStoryModal">
        <div class="story-add-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path
                    d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
            </svg>
        </div>
        <span class="story-name fw-bold">Tạo tin</span>
    </div>

    {{-- Vòng Tròn Xem Tin Của Thiên Hạ --}}
    @foreach($activeStories as $userId => $userStories)
        @php
            $firstStory = $userStories->first();
        @endphp
        <div class="story-item" data-bs-toggle="modal" data-bs-target="#viewStoryModal{{ $userId }}">
            <div class="story-ring">
                <img src="{{ asset('storage/' . $firstStory->media_path) }}" alt="story preview">
            </div>
            <span class="story-name">{{ $firstStory->user->fullname ?? 'Người dùng' }}</span>
        </div>
    @endforeach

</div>


{{-- TẦNG 2: MODAL TẠO TIN (ĐÃ VÁ LẠI ID FORM) --}}
<div class="modal fade" id="createStoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tạo tin 24h</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            {{-- ĐÃ THÊM ID FORM Ở ĐÂY NÈ PRO --}}
            <form id="storyForm" action="{{ route('stories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn hình ảnh hoặc video ngắn</label>
                        <input class="form-control" type="file" name="media" accept="image/*,video/mp4,video/quicktime"
                            required>
                    </div>

                    {{-- Nơi hiển thị lỗi Real-time --}}
                    <div id="js-media-error" class="alert alert-danger py-2" style="font-size: 14px; display: none;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Thêm văn bản (Tùy chọn)</label>
                        <input type="text" id="storyContent" class="form-control" name="content"
                            placeholder="Gõ vài chữ cho xôm tụ..." maxlength="255">

                        <div class="text-right mt-1">
                            <small id="charCount" class="text-muted" style="font-size: 12px;">0/255 ký tự</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Đăng tin</button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- TẦNG 3: VÒNG LẶP MODAL XEM TIN (ĐÃ ĐUỔI SCRIPT RA NGOÀI) --}}
@foreach($activeStories as $userId => $userStories)
    <div class="modal fade" id="viewStoryModal{{ $userId }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark text-white" style="border-radius: 12px; overflow: hidden; border: none;">

                {{-- Thanh Gạch Ngang Chạy Trên Nóc --}}
                <div class="d-flex w-100 px-1 pt-2" style="position: absolute; top: 0; z-index: 999999; gap: 4px;">
                    @foreach($userStories as $story)
                        <div
                            style="flex-grow: 1; height: 3px; background: rgba(255,255,255,0.3); border-radius: 2px; overflow: hidden;">
                            <div class="progress-fill js-fill-{{ $userId }}-{{ $loop->index }}"
                                style="width: 0%; height: 100%; background: #fff;"></div>
                        </div>
                    @endforeach
                </div>

                {{-- Thanh Header người đăng --}}
                <div class="modal-header border-0 pb-0"
                    style="position: absolute; z-index: 99999; width: 100%; background: linear-gradient(rgba(0,0,0,0.7), transparent);">
                    <div class="d-flex align-items-center w-100 p-2">
                        <img src="{{ $userStories->first()->user->avatar_url ? asset($userStories->first()->user->avatar_url) : asset('img/user/user.jpg') }}"
                            class="rounded-circle me-2" width="40" height="40"
                            style="border: 2px solid white; object-fit: cover;">
                        <strong
                            class="text-shadow fs-6">{{ $userStories->first()->user->fullname ?? 'Người dùng' }}</strong>
                    </div>
                </div>

                <div class="modal-body p-0">
                    <div id="storyCarousel{{ $userId }}" class="carousel slide" data-bs-ride="false">
                        <div class="carousel-inner">
                            @foreach($userStories as $story)
                                <div class="carousel-item {{ $loop->index == 0 ? 'active' : '' }}">
                                    <div class="carousel-item-container">
                                        @if($story->media_type == 'video')
                                            <video src="{{ asset('storage/' . $story->media_path) }}"
                                                style="max-height: 100%; max-width: 100%; object-fit: contain;" controls autoplay
                                                muted></video>
                                        @else
                                            <img src="{{ asset('storage/' . $story->media_path) }}"
                                                style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                        @endif
                                    </div>

                                    @if($story->content)
                                        <div class="position-absolute bottom-0 w-100 p-4 text-center"
                                            style="background: linear-gradient(transparent, rgba(0,0,0,0.9)); z-index: 9;">
                                            <p class="mb-0 text-white fw-bold text-shadow">{{ $story->content }}</p>
                                            <small class="text-white-50">{{ $story->created_at->diffForHumans() }}</small>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if($userStories->count() > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#storyCarousel{{ $userId }}"
                                data-bs-slide="prev" style="z-index: 99;">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#storyCarousel{{ $userId }}"
                                data-bs-slide="next" style="z-index: 99;">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
@endforeach


{{-- TẦNG 4: KHỐI SCRIPT ĐỘC LẬP HOÀN TOÀN (NẰM NGOÀI VÒNG LẶP FOREACH) --}}
<script>
    document.addEventListener("DOMContentLoaded", function () {

        // ==========================================
        // 1. BỘ ĐẾM KÝ TỰ (REAL-TIME)
        // ==========================================
        const contentInput = document.getElementById('storyContent');
        const charCount = document.getElementById('charCount');

        if (contentInput && charCount) {
            contentInput.addEventListener('input', function () {
                const currentLength = this.value.length;
                charCount.textContent = currentLength + '/255 ký tự';

                if (currentLength >= 255) {
                    charCount.classList.remove('text-muted');
                    charCount.classList.add('text-danger', 'fw-bold');
                } else {
                    charCount.classList.remove('text-danger', 'fw-bold');
                    charCount.classList.add('text-muted');
                }
            });
        }

        // ==========================================
        // 2. MÁY SOI FILE FRONTEND & CÁI LOA PHƯỜNG
        // ==========================================
        const mediaInput = document.querySelector('input[name="media"]');
        const errorBox = document.getElementById('js-media-error');

        if (mediaInput) {
            mediaInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (!file) return;

                const maxSize = 20 * 1024 * 1024; // 20 MB
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/quicktime', 'video/x-msvideo'];
                let errorMsg = '';

                if (file.size > maxSize) {
                    errorMsg = 'Ê! File chà bá quá! Tối đa 20MB thôi nghen.';
                } else if (!allowedTypes.includes(file.type)) {
                    errorMsg = 'Lộn tiệm rồi! Chỉ nhận hình ảnh (jpg, png, webp...) hoặc video (mp4, mov).';
                }

                if (errorMsg !== '') {
                    if (errorBox) {
                        errorBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation mr-1"></i> ' + errorMsg;
                        errorBox.style.display = 'block';
                    }
                    e.target.value = ''; // Xóa luôn file sai
                } else {
                    if (errorBox) errorBox.style.display = 'none';
                }
            });
        }

        // ==========================================
        // 3. BẮN AJAX NGẦM (CHỐNG F5, HIỆN LỖI BACKEND NẾU CÓ)
        // ==========================================
        const form = document.getElementById('storyForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const submitBtn = this.querySelector('button[type="submit"]');
                const formData = new FormData(this);

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lên sóng...';
                if (errorBox) errorBox.style.display = 'none';

                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            if (response.status === 422) return response.json().then(data => { throw data; });
                            else if (response.status === 413) throw { message: 'File bự hơn 100MB, Server ói rồi!' };
                            throw { message: 'Server sập hoặc rớt mạng!' };
                        }
                        window.location.reload();
                    })
                    .catch(error => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Đăng tin';

                        let backendErrorMsg = '';
                        if (error.errors) {
                            const firstKey = Object.keys(error.errors)[0];
                            backendErrorMsg = error.errors[firstKey][0];
                        } else if (error.message) {
                            backendErrorMsg = error.message;
                        }

                        if (errorBox) {
                            errorBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation mr-1"></i> (Lỗi Server) ' + backendErrorMsg;
                            errorBox.style.display = 'block';
                        }
                    });
            });
        }

        // ==========================================
        // 4. ĐỘNG CƠ THANH TRƯỢT SỬ DỤNG INTERVAL (CHẮC CHẮN CHẠY 100%)
        // ==========================================
        let storyInterval = null;

        $('.modal[id^="viewStoryModal"]').on('shown.bs.modal', function () {
            runStoryProgress($(this));
        });

        $('.carousel[id^="storyCarousel"]').on('slid.bs.carousel', function () {
            runStoryProgress($(this).closest('.modal'));
        });

        $('.modal[id^="viewStoryModal"]').on('hidden.bs.modal', function () {
            clearInterval(storyInterval);
            $(this).find('video').each(function () { this.pause(); this.currentTime = 0; });
            $(this).find('.progress-fill').css('width', '0%');
        });

        function runStoryProgress(modal) {
            clearInterval(storyInterval);

            let carousel = modal.find('.carousel');
            let activeItem = carousel.find('.carousel-item.active');
            let activeIndex = activeItem.index();
            let totalItems = carousel.find('.carousel-item').length;
            let userId = modal.attr('id').replace('viewStoryModal', '');

            // Reset tất cả thanh trượt về trạng thái ban đầu
            modal.find('.progress-fill').each(function (idx) {
                $(this).css('transition', 'none');
                if (idx < activeIndex) {
                    $(this).css('width', '100%');
                } else {
                    $(this).css('width', '0%');
                }
            });

            let currentFill = modal.find('.js-fill-' + userId + '-' + activeIndex);
            let video = activeItem.find('video')[0];
            let duration = 5000; // Hình ảnh đứng im 5s

            let playSlide = () => {
                let elapsed = 0;
                let step = 30; // 30 mili-giây cập nhật 1 lần cho mượt

                storyInterval = setInterval(() => {
                    elapsed += step;
                    let percent = (elapsed / duration) * 100;
                    if (percent > 100) percent = 100;

                    currentFill.css('width', percent + '%');

                    if (elapsed >= duration) {
                        clearInterval(storyInterval);
                        if (activeIndex < totalItems - 1) {
                            carousel.carousel('next');
                        } else {
                            modal.modal('hide');
                        }
                    }
                }, step);
            };

            if (video) {
                video.currentTime = 0;
                let playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise.then(_ => {
                        duration = video.duration * 1000; // Lấy thời lượng thực tế của video
                        playSlide();
                    }).catch(error => {
                        duration = 5000; playSlide();
                    });
                }
            } else {
                playSlide();
            }
        }
    });
</script>