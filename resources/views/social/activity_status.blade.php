@extends('dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/activity-status.css') }}">

<div class="activity-status-page">

    <div class="activity-status-card">

        <div class="activity-status-header">
            <button type="button" class="back-btn" onclick="window.history.back()">
                ‹
            </button>

            <h1>Trạng thái hoạt động</h1>
        </div>

        @if(session('success'))
            <div class="activity-alert">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('activity.status.toggle') }}">
            @csrf

            <div class="activity-toggle-row">
                <div>
                    <h2>Hiển thị khi bạn hoạt động</h2>
                </div>

                <button
                    type="submit"
                    class="activity-switch {{ $user->show_activity_status ? 'active' : '' }}"
                    title="Bật / tắt trạng thái hoạt động"
                >
                    <span></span>
                </button>
            </div>
        </form>

        <div class="activity-content">
            <p>
                Khi đang bật, trạng thái hoạt động của bạn sẽ hiển thị với bạn bè trên ESPACE.
                Bạn bè sẽ biết khi bạn đang hoạt động thông qua chấm xanh bên dưới ảnh đại diện.
            </p>

            <p>
                Bạn chỉ có thể xem trạng thái hoạt động của bạn bè nếu bạn cũng đang bật trạng thái hoạt động của mình.
            </p>

            <p>
                Khi tắt tính năng này, người khác sẽ không thấy bạn đang hoạt động và bạn cũng không thấy trạng thái hoạt động của bạn bè.
            </p>
        </div>

    </div>

</div>
@endsection