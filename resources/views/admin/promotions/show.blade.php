@extends('layouts.admin')

@section('title', 'Promotion Details')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Promotion Details</h1>
                <p class="text-muted small">{{ $promotion->title }}</p>
            </div>
            <div>
                <a href="{{ route('admin.promotions.edit', $promotion->promotion_id) }}"
                    class="btn btn-warning text-white me-2">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Main Info -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-info-circle"></i> Promotion Information</span>
                            <span
                                class="badge bg-{{ $promotion->status === 'active' ? 'success' : ($promotion->status === 'expired' ? 'danger' : 'secondary') }}">
                                {{ ucfirst($promotion->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-tag"></i> Title:</strong>
                                <p>{{ $promotion->title }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-store"></i> Merchant:</strong>
                                <p>
                                    <a href="{{ route('admin.merchants.show', $promotion->merchant->merchant_id ?? '') }}">
                                        {{ $promotion->merchant->business_name ?? 'N/A' }}
                                    </a>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-cogs"></i> Type:</strong>
                                <p><span class="badge bg-info">{{ strtoupper($promotion->promo_type) }}</span></p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-percent"></i> Value:</strong>
                                <p>
                                    @if ($promotion->promo_type === 'percentage')
                                        {{ $promotion->value }}% Off
                                    @elseif($promotion->promo_type === 'fixed')
                                        ₱{{ number_format($promotion->value, 2) }} Off
                                    @else
                                        Buy One Get One Free
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-calendar-alt"></i> Start Date:</strong>
                                <p>{{ $promotion->start_date->format('M d, Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-calendar-check"></i> End Date:</strong>
                                <p>{{ $promotion->end_date ? $promotion->end_date->format('M d, Y') : 'No Expiry' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-shopping-cart"></i> Min. Order:</strong>
                                <p>{{ $promotion->min_order_amount ? '₱' . number_format($promotion->min_order_amount, 2) : 'N/A' }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-box"></i> Min. Quantity:</strong>
                                <p>{{ $promotion->min_quantity ?: 'N/A' }}</p>
                            </div>
                        </div>

                        @if ($promotion->category || $promotion->freeMenuItem || $promotion->requiredMenuItem)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <strong><i class="fas fa-link"></i> Associated Items:</strong>
                                    @if ($promotion->category)
                                        <p><span class="badge bg-info">Category: {{ $promotion->category->name }}</span>
                                        </p>
                                    @endif
                                    @if ($promotion->freeMenuItem)
                                        <p><span class="badge bg-success">Free Item:
                                                {{ $promotion->freeMenuItem->name }}</span></p>
                                    @endif
                                    @if ($promotion->requiredMenuItem)
                                        <p><span class="badge bg-primary">Required Item:
                                                {{ $promotion->requiredMenuItem->name }}</span></p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if ($promotion->description)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <strong><i class="fas fa-align-left"></i> Description:</strong>
                                    <p>{{ $promotion->description }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-clock"></i> Created:</strong>
                                <p>{{ $promotion->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-clock"></i> Last Updated:</strong>
                                <p>{{ $promotion->updated_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <i class="fas fa-chart-bar"></i> Quick Stats
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Status:</span>
                            <span
                                class="badge bg-{{ $promotion->status === 'active' ? 'success' : ($promotion->status === 'expired' ? 'danger' : 'secondary') }}">
                                {{ ucfirst($promotion->status) }}
                            </span>
                        </div>
                        @if ($promotion->end_date)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Days Left:</span>
                                @php
                                    $daysLeft = now()->diffInDays($promotion->end_date, false);
                                @endphp
                                <span
                                    class="text-{{ $daysLeft > 7 ? 'success' : ($daysLeft > 0 ? 'warning' : 'danger') }}">
                                    {{ $daysLeft > 0 ? $daysLeft . ' days' : 'Expired' }}
                                </span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between">
                            <span>Merchant:</span>
                            <span>{{ $promotion->merchant->business_name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header bg-white">
                        <i class="fas fa-cog"></i> Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.promotions.edit', $promotion->promotion_id) }}"
                                class="btn btn-warning text-white">
                                <i class="fas fa-edit"></i> Edit Promotion
                            </a>
                            @if ($promotion->status === 'active')
                                <button class="btn btn-secondary"
                                    onclick="updateStatus('{{ $promotion->promotion_id }}', 'inactive')">
                                    <i class="fas fa-pause"></i> Deactivate
                                </button>
                            @elseif($promotion->status === 'inactive')
                                <button class="btn btn-success"
                                    onclick="updateStatus('{{ $promotion->promotion_id }}', 'active')">
                                    <i class="fas fa-play"></i> Activate
                                </button>
                            @endif
                            <button class="btn btn-danger"
                                onclick="deletePromotion('{{ $promotion->promotion_id }}', '{{ addslashes($promotion->title) }}')">
                                <i class="fas fa-trash"></i> Delete Promotion
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Form -->
    <form id="statusForm" method="POST" style="display: none;">
        @csrf
        @method('PUT')
        <input type="hidden" name="status" id="statusInput">
    </form>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function updateStatus(id, status) {
            if (confirm(`Are you sure you want to ${status} this promotion?`)) {
                const form = document.getElementById('statusForm');
                form.action = `/admin/promotions/${id}/status`;
                document.getElementById('statusInput').value = status;
                form.submit();
            }
        }

        function deletePromotion(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/promotions/${id}`;
                form.submit();
            }
        }
    </script>
@endsection
