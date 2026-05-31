@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/edit-profile.css') }}">

@php
    $user = Auth::user();
@endphp
<div class="edit-profile-page">
    <div class="edit-profile-container">

        <div class="edit-header">
            {{-- Dung *_src tu model de anh local khong bi render thanh http://localhost. --}}
            <img
                src="{{ $user->cover_src }}"
                class="edit-cover clickable-image"
                alt="cover"
                onclick="document.getElementById('coverInput').click()"
            >
            <div class="edit-user">
            <img
                src="{{ $user->avatar_src }}"
                class="edit-avatar clickable-image"
                alt="avatar"
                onclick="document.getElementById('avatarInput').click()"
            >
                <div>
                    <h1>{{ $user->fullname ?? $user->name ?? 'Lam Pham' }}</h1>
                    <p>{{ '@' . ($user->username ?? 'phamlam0375') }}</p>
                </div>
            </div>
        </div>

                <form
                    class="edit-form"
                    method="POST"
                    action="{{ route('profile.update') }}"
                    enctype="multipart/form-data"
                >
                @csrf
                <input type="file" id="avatarInput" name="avatar" hidden accept="image/*">

                <input type="file" id="coverInput" name="cover" hidden accept="image/*">

            <div class="edit-card">
                <h2>Thông tin cơ bản</h2>

                <div class="form-group">
                    <label>Họ tên</label>
                    <input type="text" name="fullname" value="{{ $user->fullname ?? $user->name ?? '' }}">
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="{{ $user->username ?? '' }}">
                </div>

                <div class="form-group">
                    <label>Tiểu sử</label>
                    <textarea name="bio" rows="3">{{ $user->bio ?? '' }}</textarea>
                </div>
            </div>

            <div class="edit-card">
                <h2>Thông tin cá nhân</h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Ngày sinh</label>
                        <input type="date" name="birthday" value="{{ $user->birthday ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" value="{{ $user->phone ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ $user->email ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label>Giới tính</label>
                            <select name="gender">
                                <option value="1" {{ ($user->gender ?? '') == 1 ? 'selected' : '' }}>Nam</option>
                                <option value="2" {{ ($user->gender ?? '') == 2 ? 'selected' : '' }}>Nữ</option>
                                <option value="3" {{ ($user->gender ?? '') == 3 ? 'selected' : '' }}>Khác</option>
                            </select>
                    </div>

                    <div class="form-group full">
                        <label>Địa chỉ</label>
                        <input type="text" name="address" value="{{ $user->address ?? '' }}">
                    </div>
                </div>
            </div>

            <div class="edit-actions">
                <a href="{{ route('profile') }}" class="cancel-btn">Hủy</a>
                <button type="submit" class="save-btn">Lưu thay đổi</button>
            </div>
        </form>

    </div>
</div>
<script>
// Lấy các thẻ cần dùng trong form sửa profile.
const editForm = document.querySelector('.edit-form');
const saveButton = document.querySelector('.save-btn');
const avatarInput = document.getElementById('avatarInput');
const coverInput = document.getElementById('coverInput');

// Lưu các tiến trình nén ảnh đang chạy để khi người dùng bấm lưu quá nhanh,
// form sẽ chờ nén xong rồi mới gửi lên server.
let compressingTasks = [];
let isSubmittingAfterCompress = false;

avatarInput.addEventListener('change', function (e) {
    const task = compressSelectedImage(e.target, '.edit-avatar', 600, 'avatar');
    compressingTasks.push(task);
});

coverInput.addEventListener('change', function (e) {
    const task = compressSelectedImage(e.target, '.edit-cover', 1600, 'cover');
    compressingTasks.push(task);
});

editForm.addEventListener('submit', async function (e) {
    if (isSubmittingAfterCompress) {
        return;
    }

    e.preventDefault();
    saveButton.disabled = true;
    saveButton.textContent = 'Đang xử lý ảnh...';

    await Promise.all(compressingTasks);

    isSubmittingAfterCompress = true;
    editForm.requestSubmit();
});

async function compressSelectedImage(input, previewSelector, maxWidth, prefix) {
    const file = input.files[0];

    if (!file) {
        return;
    }

    // Hiển thị ảnh mới ngay lập tức để người dùng thấy mình đã chọn đúng ảnh.
    document.querySelector(previewSelector).src = URL.createObjectURL(file);

    // Nếu file không phải ảnh thì giữ nguyên, server sẽ xử lý/báo lỗi theo logic hiện có.
    if (!file.type.startsWith('image/')) {
        return;
    }

    try {
        const compressedFile = await compressImageFile(file, maxWidth, 0.82, prefix);
        replaceInputFile(input, compressedFile);

        // Cập nhật preview bằng ảnh đã nén thật sự sẽ được upload.
        document.querySelector(previewSelector).src = URL.createObjectURL(compressedFile);
    } catch (error) {
        console.error('Không thể nén ảnh trước khi upload:', error);
    }
}

function compressImageFile(file, maxWidth, quality, prefix) {
    return new Promise(function (resolve, reject) {
        const image = new Image();

        image.onload = function () {
            // Giới hạn dưới 1.8MB để tránh cấu hình PHP upload_max_filesize = 2MB chặn request.
            const targetSize = 1800 * 1024;
            let currentQuality = quality;
            let currentMaxWidth = maxWidth;

            const exportImage = function () {
                const ratio = Math.min(1, currentMaxWidth / image.width);
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(image.width * ratio);
                canvas.height = Math.round(image.height * ratio);

                const context = canvas.getContext('2d');
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                context.drawImage(image, 0, 0, canvas.width, canvas.height);

                // Xuất ảnh thành JPG để giảm dung lượng; nếu vẫn lớn thì tiếp tục nén mạnh hơn.
                canvas.toBlob(function (blob) {
                    if (!blob) {
                        reject(new Error('Canvas không tạo được file ảnh.'));
                        return;
                    }

                    if (blob.size > targetSize && currentQuality > 0.55) {
                        currentQuality -= 0.08;
                        exportImage();
                        return;
                    }

                    if (blob.size > targetSize && currentMaxWidth > 900) {
                        currentMaxWidth = Math.round(currentMaxWidth * 0.85);
                        exportImage();
                        return;
                    }

                    const compressedName = `${Date.now()}_${prefix}.jpg`;
                    resolve(new File([blob], compressedName, {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    }));
                }, 'image/jpeg', currentQuality);
            };

            exportImage();
        };

        image.onerror = reject;
        image.src = URL.createObjectURL(file);
    });
}

function replaceInputFile(input, file) {
    // DataTransfer cho phép thay file trong input bằng file đã nén.
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    input.files = dataTransfer.files;
}
</script>
<script>
// Script an toàn cho upload avatar/cover.
// Mục tiêu: ảnh trên 2MB vẫn được nén trước khi gửi, nhưng không để màn hình đứng mãi nếu ảnh quá lớn.
(function () {
    const form = document.querySelector('.edit-form');
    const saveButton = document.querySelector('.save-btn');
    const originalButtonText = saveButton.textContent;

    // Thay input cũ bằng input mới để bỏ các listener nén ảnh cũ có thể đang làm form chờ vô hạn.
    const avatarInput = resetFileInput('avatarInput');
    const coverInput = resetFileInput('coverInput');

    const maxOriginalImageSize = 15 * 1024 * 1024; // Cho phép ảnh gốc tối đa 15MB.
    const targetUploadSize = 1800 * 1024; // Cố gắng nén ảnh dưới 1.8MB để tránh giới hạn PHP 2MB.
    const compressTimeout = 30000; // Sau 30 giây mà chưa xử lý xong thì dừng và báo lỗi.

    avatarInput.addEventListener('change', function () {
        previewImage(avatarInput, '.edit-avatar');
    });

    coverInput.addEventListener('change', function () {
        previewImage(coverInput, '.edit-cover');
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();

        saveButton.disabled = true;
        saveButton.textContent = 'Đang xử lý ảnh...';

        try {
            if (avatarInput.files[0]) {
                await compressInputImage(avatarInput, '.edit-avatar', 600, 'avatar');
            }

            if (coverInput.files[0]) {
                await compressInputImage(coverInput, '.edit-cover', 1600, 'cover');
            }

            form.submit();
        } catch (error) {
            alert(error.message || 'Không thể xử lý ảnh. Vui lòng chọn ảnh khác.');
            saveButton.disabled = false;
            saveButton.textContent = originalButtonText;
        }
    }, true);

    function resetFileInput(id) {
        const oldInput = document.getElementById(id);
        const newInput = oldInput.cloneNode(true);
        oldInput.replaceWith(newInput);

        return newInput;
    }

    function previewImage(input, previewSelector) {
        const file = input.files[0];

        if (file) {
            document.querySelector(previewSelector).src = URL.createObjectURL(file);
        }
    }

    async function compressInputImage(input, previewSelector, maxWidth, prefix) {
        const file = input.files[0];

        if (!file) {
            return;
        }

        if (!file.type.startsWith('image/')) {
            input.value = '';
            throw new Error('File bạn chọn không phải là ảnh.');
        }

        if (file.size > maxOriginalImageSize) {
            input.value = '';
            throw new Error('Ảnh quá lớn. Vui lòng chọn ảnh dưới 15MB để hệ thống xử lý ổn định.');
        }

        const compressedFile = await withTimeout(
            compressImageFile(file, maxWidth, 0.82, prefix),
            compressTimeout,
            'Xử lý ảnh quá lâu. Vui lòng chọn ảnh nhỏ hơn hoặc ảnh có độ phân giải thấp hơn.'
        );

        replaceInputFile(input, compressedFile);
        document.querySelector(previewSelector).src = URL.createObjectURL(compressedFile);
    }

    function compressImageFile(file, maxWidth, quality, prefix) {
        return new Promise(function (resolve, reject) {
            const image = new Image();

            image.onload = function () {
                let currentQuality = quality;
                let currentMaxWidth = maxWidth;

                const exportImage = function () {
                    const ratio = Math.min(1, currentMaxWidth / image.width);
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.round(image.width * ratio);
                    canvas.height = Math.round(image.height * ratio);

                    const context = canvas.getContext('2d');
                    context.fillStyle = '#ffffff';
                    context.fillRect(0, 0, canvas.width, canvas.height);
                    context.drawImage(image, 0, 0, canvas.width, canvas.height);

                    canvas.toBlob(function (blob) {
                        if (!blob) {
                            reject(new Error('Canvas không tạo được file ảnh.'));
                            return;
                        }

                        // Nếu ảnh vẫn lớn hơn 1.8MB thì giảm chất lượng trước.
                        if (blob.size > targetUploadSize && currentQuality > 0.55) {
                            currentQuality -= 0.08;
                            exportImage();
                            return;
                        }

                        // Nếu giảm chất lượng vẫn chưa đủ thì giảm tiếp kích thước ảnh.
                        if (blob.size > targetUploadSize && currentMaxWidth > 900) {
                            currentMaxWidth = Math.round(currentMaxWidth * 0.85);
                            exportImage();
                            return;
                        }

                        resolve(new File([blob], `${Date.now()}_${prefix}.jpg`, {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        }));
                    }, 'image/jpeg', currentQuality);
                };

                exportImage();
            };

            image.onerror = function () {
                reject(new Error('Trình duyệt không đọc được ảnh này. Vui lòng chọn ảnh khác.'));
            };

            image.src = URL.createObjectURL(file);
        });
    }

    function withTimeout(promise, timeout, message) {
        return Promise.race([
            promise,
            new Promise(function (_, reject) {
                setTimeout(function () {
                    reject(new Error(message));
                }, timeout);
            })
        ]);
    }

    function replaceInputFile(input, file) {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;
    }
})();
</script>
@endsection
