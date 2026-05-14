<div class="info-box">
    <div class="box-title"><span>●</span> Giới thiệu</div>

    <div class="info-list info-list-vertical">
        <div class="info-row">
            <div class="info-icon">▦</div>
            <div>
                <div class="info-label">Ngày sinh</div>
                <div class="info-value">{{ $user->birthday ?? 'Chưa có' }}</div>
            </div>
        </div>

        <div class="info-row">
            <div class="info-icon">☎</div>
            <div>
                <div class="info-label">Số điện thoại</div>
                <div class="info-value">{{ $user->phone ?? 'Chưa có' }}</div>
            </div>
        </div>

        <div class="info-row">
            <div class="info-icon">✉</div>
            <div>
                <div class="info-label">Email</div>
                <div class="info-value">{{ $user->email ?? 'Chưa có' }}</div>
            </div>
        </div>

        <div class="info-row">
            <div class="info-icon">⚥</div>
            <div>
                <div class="info-label">Giới tính</div>
                <div class="info-value">
                    @if($user->gender == 1)
                    Nam
                    @elseif($user->gender == 2)
                    Nữ
                    @elseif($user->gender == 3)
                    Khác
                    @else
                    Chưa có
                    @endif
                </div>
            </div>
        </div>

        <div class="info-row">
            <div class="info-icon">⌖</div>
            <div>
                <div class="info-label">Địa chỉ</div>
                <div class="info-value">{{ $user->address ?? 'Chưa có' }}</div>
            </div>
        </div>
    </div>
</div>