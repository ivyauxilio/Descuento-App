@php
    $colors = [
        'pending' => 'warning',
        'approved' => 'info',
        'active' => 'success',
        'rejected' => 'danger',
        'suspended' => 'secondary',
    ];
    $icons = [
        'pending' => 'fa-clock',
        'approved' => 'fa-check-circle',
        'active' => 'fa-check-circle',
        'rejected' => 'fa-times-circle',
        'suspended' => 'fa-pause-circle',
    ];
    $color = $colors[$status] ?? 'secondary';
    $icon = $icons[$status] ?? 'fa-circle';
@endphp

<span class="badge bg-{{ $color }}">
    <i class="fas {{ $icon }}"></i> {{ ucfirst($status) }}
</span>
