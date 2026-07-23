@php
    $colors = [
        'active' => 'success',
        'inactive' => 'secondary',
        'suspended' => 'danger',
    ];
    $icons = [
        'active' => 'fa-check-circle',
        'inactive' => 'fa-pause-circle',
        'suspended' => 'fa-times-circle',
    ];
    $color = $colors[$status] ?? 'secondary';
    $icon = $icons[$status] ?? 'fa-circle';
@endphp

<span class="badge bg-{{ $color }}">
    <i class="fas {{ $icon }}"></i> {{ ucfirst($status) }}
</span>
