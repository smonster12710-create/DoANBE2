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