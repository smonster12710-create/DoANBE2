@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">

@include('partials.social_topbar')

<div class="admin-page">
    <div class="admin-container">

        <div class="admin-header">
            <div>
                <div class="admin-logo">ESPACE</div>
                <h1>Chi tiết báo cáo vi phạm #{{ $report->id }}</h1>
                <p>Xem thông tin chi tiết và đưa ra quyết định xử lý bài viết vi phạm.</p>
            </div>
            <a href="{{ route('admin.reports.index') }}" class="admin-btn primary" style="background-color: #718096; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                Quay lại danh sách
            </a>
        </div>

        {{-- KHU VỰC THÔNG TIN BÁO CÁO (ĐÃ THÊM LOGIC ĐỌC ẢNH BẰNG CHỨNG JSON) --}}
        <div class="admin-card" style="margin-bottom: 20px; padding: 24px;">
            <h3 style="margin-bottom: 16px; font-size: 1.2rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; color: #2d3748;">Thông tin báo cáo</h3>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <div>
                    <p style="margin-bottom: 8px;"><strong>Người báo cáo:</strong> {{ $report->user->fullname ?? $report->user->name ?? 'Ẩn danh' }}</p>
                    <p style="margin-bottom: 8px;"><strong>Lý do vi phạm:</strong> {{ $report->reason }}</p>
                </div>
                <div>
                    <p style="margin-bottom: 8px;"><strong>Ngày gửi:</strong> {{ $report->created_at ? $report->created_at->format('d/m/Y H:i') : 'Chưa rõ' }}</p>
                    <p style="margin-bottom: 8px;"><strong>Trạng thái:</strong>
                        @if($report->status === 'pending')
                        <span class="badge dark" style="background-color: #ecc94b; color: #744210; padding: 4px 8px; border-radius: 4px;">{{ $report->status }}</span>
                        @else
                        <span class="badge green" style="background-color: #48bb78; color: #fff; padding: 4px 8px; border-radius: 4px;">{{ $report->status }}</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- LOGIC ĐỌC ẢNH BẰNG CHỨNG TỪ BẢNG REPORTS --}}
            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px dashed #e2e8f0;">
                <strong style="color: #2d3748; display: block; margin-bottom: 8px;">
                    <i class="fas fa-paperclip"></i> Bằng chứng / Hình ảnh báo cáo kèm theo:
                </strong>

                @if($report->image_url)
                @php
                $images = [];
                if (str_starts_with($report->image_url, '[')) {
                $images = json_decode($report->image_url, true);
                } else {
                $images = [$report->image_url];
                }
                @endphp

                <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 8px;">
                    @if(!empty($images) && is_array($images))
                    @foreach($images as $img)
                    <div style="background: #edf2f7; padding: 6px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <img src="{{ str_starts_with($img, 'http') ? $img : asset($img) }}"
                            style="max-width: 100%; max-height: 250px; border-radius: 4px; object-fit: contain;">
                    </div>
                    @endforeach
                    @else
                    <span style="color: #718096; font-style: italic; font-size: 0.9rem;">Không thể đọc tệp hình ảnh đính kèm.</span>
                    @endif
                </div>
                @else
                <span style="color: #718096; font-style: italic; font-size: 0.9rem;">Người dùng không gửi kèm hình ảnh bằng chứng.</span>
                @endif
            </div>
        </div>

        {{-- KHU VỰC NỘI DUNG BÀI VIẾT BỊ BÁO CÁO (ĐÃ ĐƯỢC DỌN DẸP SẠCH SẼ THẺ THỪA) --}}
        <div class="admin-card" style="padding: 24px;">
            <h3 style="margin-bottom: 16px; font-size: 1.2rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; color: #2d3748;">Nội dung bài viết bị báo cáo</h3>

            @if($report->post)
            <div style="background-color: #f7fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                    <img src="{{ $report->post->user->avatar_url ? asset($report->post->user->avatar_url) : asset('img/user/user.jpg') }}" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">
                    <div>
                        <strong style="display: block; font-size: 1rem; color: #1a202c;">{{ $report->post->user->fullname ?? $report->post->user->name ?? 'Người dùng' }}</strong>
                        <span style="font-size: 0.85rem; color: #718096;">{{ '@' . ($report->post->user->username ?? 'user') }}</span>
                    </div>
                </div>

                <div style="font-size: 1rem; line-height: 1.6; color: #2d3748; white-space: pre-wrap; margin-bottom: 16px;">{{ $report->post->content }}</div>

                {{-- HIỂN THỊ ẢNH CỦA BÀI VIẾT TỪ BẢNG POSTMEDIA --}}
                @if($report->post->media && $report->post->media->count() > 0)
                <div style="margin-top: 16px; background: #edf2f7; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <p style="font-size: 0.85rem; color: #718096; text-align: left; margin-top: 0; margin-bottom: 12px;">
                        <i class="fas fa-image"></i> Hình ảnh đính kèm bài viết ({{ $report->post->media->count() }} ảnh):
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 12px; justify-content: center;">
                        @foreach($report->post->media as $mediaItem)
                        @if($mediaItem->media_type === 'photo')
                        <img src="{{ asset($mediaItem->media_url) }}" style="max-width: 100%; max-height: 400px; border-radius: 6px; object-fit: contain; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- NÚT BẤM HÀNH ĐỘNG --}}
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <form method="POST" action="{{ route('admin.reports.dismiss', $report->id) }}">
                    @csrf
                    <button type="submit" class="admin-btn primary" style="background-color: #4a5568; border: none; cursor: pointer;">
                        Bỏ qua báo cáo
                    </button>
                </form>

                <button type="button" class="admin-btn primary" style="background-color: #e53e3e; border: none; cursor: pointer;" onclick="openDeleteModal()">
                    Xóa bài viết vi phạm
                </button>
            </div>

            {{-- MODAL XÁC NHẬN XÓA --}}
            <div id="deleteConfirmModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
                <div class="admin-card" style="background-color: #fff; padding: 24px; border-radius: 8px; width: 100%; max-width: 450px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); text-align: center;">
                    <div style="color: #e53e3e; font-size: 3rem; margin-bottom: 12px;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h3 style="margin-bottom: 12px; font-size: 1.3rem; color: #1a202c;">Xác nhận xóa bài viết</h3>
                    <p style="color: #718096; margin-bottom: 24px; font-size: 0.95rem; line-height: 1.5;">Bạn có chắc chắn muốn xóa bài viết vi phạm này khỏi hệ thống không? Hành động này không thể hoàn tác.</p>

                    <div style="display: flex; gap: 12px; justify-content: center;">
                        <button type="button" class="admin-btn" style="background-color: #edf2f7; color: #4a5568; border: none; cursor: pointer; padding: 10px 20px; border-radius: 6px;" onclick="closeDeleteModal()">
                            Hủy bỏ
                        </button>
                        <form method="POST" action="{{ route('admin.reports.delete_post', $report->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn primary" style="background-color: #e53e3e; border: none; cursor: pointer; padding: 10px 20px; border-radius: 6px;">
                                Vẫn xóa bài
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @else
            <div style="color: #e53e3e; font-weight: 500; text-align: center; padding: 30px; background-color: #fff5f5; border-radius: 8px; border: 1px solid #fed7d7; margin-bottom: 24px;">
                Bài viết này đã bị xóa hoặc không còn tồn tại trên hệ thống.
            </div>
            @endif

        </div>
    </div>
</div>

<script>
    function openDeleteModal() {
        const modal = document.getElementById('deleteConfirmModal');
        modal.style.display = 'flex';
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteConfirmModal');
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('deleteConfirmModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>
@endsection