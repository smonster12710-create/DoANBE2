<div class="trending-section mt-4">
    <h6 class="text-muted fw-bold mb-3">Xu hướng</h6>

    @forelse($trendingTags as $tag)
        <div class="d-flex justify-content-between align-items-center mb-2">
            <a href="{{ route('hashtags.show', $tag->name) }}" class="text-dark text-decoration-none fw-medium">
                #{{ $tag->name }}
            </a>

            <span class="text-muted small">
                {{ $tag->formatted_count }}
            </span>
        </div>
    @empty
        <div class="text-muted small">Chưa có xu hướng nào.</div>
    @endforelse
</div>
@if (session()->has('error'))
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            let errorMsg = @json(session()->pull('error'));

            // Chỉ kích hoạt Toast khi thực sự có chuỗi thông báo
            if (errorMsg) {
                // Kiểm tra xem máy đã nhận thư viện Toastr chưa, nếu chưa thì xài tạm alert để test
                if (typeof toastr !== "undefined") {
                    toastr.error(errorMsg);
                } else if (typeof Swal !== "undefined") {
                    Swal.fire({
                        icon: 'error',
                        title: 'ERROR',
                        text: errorMsg,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    alert(errorMsg); // Cứu cánh cuối cùng nếu quên nhúng thư viện UI
                }
            }
        });
    </script>
@endif