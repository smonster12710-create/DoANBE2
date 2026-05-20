<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">

    {{-- TOAST THÀNH CÔNG (MÀU XANH) --}}
    @if(session('success'))
        <div class="toast align-items-center text-bg-success border-0 show shadow-lg" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" style="font-size: 15px;">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    @endif

    {{-- TOAST BÁO LỖI (MÀU ĐỎ) --}}
    @if(session('error'))
        <div class="toast align-items-center text-bg-danger border-0 show shadow-lg" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" style="font-size: 15px;">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    @endif

</div>

{{-- SCRIPT ĐỂ TOAST TỰ ĐỘNG TẮT SAU 3 GIÂY --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let toastElList = [].slice.call(document.querySelectorAll('.toast'));
        let toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, { delay: 3000 });
        });
        toastList.forEach(toast => toast.show());
    });
</script>