@php
    $activity = $user?->activityStatusFor(auth()->user()) ?? [
        'visible' => false,
        'status' => 'hidden',
        'last_activity_at' => null,
        'label' => '',
        'short_label' => '',
    ];
@endphp

<span
    class="online-dot activity-dot {{ $activity['status'] }} {{ $activity['visible'] ? '' : 'd-none' }}"
    data-activity-user-id="{{ $user?->id }}"
    data-activity-status="{{ $activity['status'] }}"
    data-last-activity-at="{{ $activity['last_activity_at'] }}"
    data-short-label="{{ $activity['short_label'] }}"
    title="{{ $activity['label'] }}"
></span>
