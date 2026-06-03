@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">

@include('partials.social_topbar')

<div class="admin-page">
    <div class="admin-container">

        <div class="admin-header">
            <div>
                <div class="admin-logo">ESPACE</div>
                <h1>Quản lý báo cáo vi phạm</h1>
                <p>Xem, kiểm duyệt và xử lý các báo cáo vi phạm từ người dùng hệ thống.</p>
            </div>
        </div>

        <div class="admin-card">

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Người báo cáo</th>
                            <th>Bài viết (ID)</th>
                            <th>Lý do</th>
                            <th>Trạng thái</th>
                            <th>Ngày gửi</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($reports as $item)
                        <tr>
                            <td><strong>#{{ $item->id }}</strong></td>
                            <td>
                                {{ $item->user->fullname ?? $item->user->name ?? 'Ẩn danh' }}
                            </td>
                            <td>
                                <span style="color: #e53e3e; font-weight: 600;">
                                    #{{ $item->post_id }}
                                </span>
                            </td>
                            <td>{{ $item->reason }}</td>
                            <td>
                                @if($item->status === 'pending')
                                <span class="badge dark" style="background-color: #ecc94b; color: #744210;">
                                    {{ $item->status }}
                                </span>
                                @else
                                <span class="badge green">
                                    {{ $item->status }}
                                </span>
                                @endif
                            </td>
                            <td>
                                {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : 'Chưa rõ' }}
                            </td>
                            <td class="text-right">
                                <div class="action-row" style="justify-content: flex-end;">
                                    <a href="{{ route('admin.reports.report_show', $item->id) }}" class="small-btn edit" style="background-color: #3182ce; color: #fff; padding: 6px 16px; width: auto; height: auto; white-space: nowrap; display: inline-block;">
                                        Xem chi tiết
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="empty-row">
                                Không tìm thấy báo cáo vi phạm nào.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($reports->lastPage() > 1)
            <div class="admin-pagination-custom">

                @if ($reports->onFirstPage())
                <span class="page-btn disabled">
                    Trang trước
                </span>
                @else
                <a href="{{ $reports->previousPageUrl() }}" class="page-btn">
                    Trang trước
                </a>
                @endif

                <span class="page-info">
                    Trang {{ $reports->currentPage() }} / {{ $reports->lastPage() }}
                </span>

                @if ($reports->hasMorePages())
                <a href="{{ $reports->nextPageUrl() }}" class="page-btn">
                    Trang sau
                </a>
                @else
                <span class="page-btn disabled">
                    Trang sau
                </span>
                @endif

            </div>
            @endif

        </div>

    </div>
</div>
@endsection