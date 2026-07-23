<div class="card h-100 shadow-sm hover-shadow transition">
    <div class="card-header bg-white border-0 pt-3">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <span
                    class="badge bg-{{ $promotion->status === 'active' ? 'success' : ($promotion->status === 'expired' ? 'danger' : 'secondary') }} mb-2">
                    {{ ucfirst($promotion->status) }}
                </span>
                <h5 class="card-title mb-0">{{ $promotion->title }}</h5>
            </div>
            <div>
                <span class="badge bg-info">
                    {{ strtoupper($promotion->promo_type) }}
                </span>
            </div>
        </div>
    </div>

    <div class="card-body">
        <!-- Promotion Details -->
        <div class="mb-3">
            @if ($promotion->promo_type === 'percentage')
                <div class="display-6 text-primary">
                    {{ $promotion->value }}%
                </div>
                <small class="text-muted">Off</small>
            @elseif($promotion->promo_type === 'fixed')
                <div class="display-6 text-primary">
                    ₱{{ number_format($promotion->value, 2) }}
                </div>
                <small class="text-muted">Off</small>
            @elseif($promotion->promo_type === 'bogo')
                <div class="display-6 text-primary">
                    <i class="fas fa-gift"></i> BOGO
                </div>
                <small class="text-muted">Buy One Get One</small>
            @endif
        </div>

        <!-- Conditions -->
        <div class="mb-3">
            @if ($promotion->min_order_amount)
                <div class="small text-muted">
                    <i class="fas fa-shopping-cart"></i> Min. Order:
                    ₱{{ number_format($promotion->min_order_amount, 2) }}
                </div>
            @endif
            @if ($promotion->min_quantity)
                <div class="small text-muted">
                    <i class="fas fa-box"></i> Min. Quantity: {{ $promotion->min_quantity }}
                </div>
            @endif
        </div>

        <!-- Date Range -->
        <div class="small text-muted">
            <div>
                <i class="fas fa-calendar-alt"></i>
                {{ $promotion->start_date->format('M d, Y') }}
                @if ($promotion->end_date)
                    - {{ $promotion->end_date->format('M d, Y') }}
                @else
                    - <span class="text-success">No Expiry</span>
                @endif
            </div>
            @if ($promotion->end_date)
                <div>
                    @php
                        $daysLeft = now()->diffInDays($promotion->end_date, false);
                    @endphp
                    @if ($daysLeft > 0)
                        <span class="text-{{ $daysLeft > 7 ? 'success' : 'warning' }}">
                            <i class="fas fa-hourglass-half"></i> {{ $daysLeft }} days left
                        </span>
                    @elseif($daysLeft == 0)
                        <span class="text-warning">
                            <i class="fas fa-clock"></i> Expires today
                        </span>
                    @else
                        <span class="text-danger">
                            <i class="fas fa-times"></i> Expired
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <!-- Items (if applicable) -->
        @if ($promotion->freeMenuItem || $promotion->requiredMenuItem || $promotion->category)
            <div class="mt-3 pt-3 border-top">
                @if ($promotion->freeMenuItem)
                    <div class="small">
                        <i class="fas fa-gift text-success"></i>
                        Free: <strong>{{ $promotion->freeMenuItem->name }}</strong>
                    </div>
                @endif
                @if ($promotion->requiredMenuItem)
                    <div class="small">
                        <i class="fas fa-shopping-cart text-primary"></i>
                        Required: <strong>{{ $promotion->requiredMenuItem->name }}</strong>
                    </div>
                @endif
                @if ($promotion->category)
                    <div class="small">
                        <i class="fas fa-tag text-info"></i>
                        Category: <strong>{{ $promotion->category->name }}</strong>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="card-footer bg-white border-0 pt-0">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted">
                    {{ $promotion->created_at->diffForHumans() }}
                </small>
            </div>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('merchant.promotions.show', $promotion->promotion_id) }}"
                    class="btn btn-outline-primary" title="View Details">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('merchant.promotions.edit', $promotion->promotion_id) }}"
                    class="btn btn-outline-warning" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
                <button type="button" class="btn btn-outline-danger"
                    onclick="deletePromotion('{{ $promotion->promotion_id }}', '{{ addslashes($promotion->title) }}')"
                    title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }

    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .transition {
        transition: all 0.3s ease;
    }

    .display-6 {
        font-size: 2rem;
        font-weight: 600;
    }
</style>

<!-- Delete Form -->
<form id="deletePromoForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    function deletePromotion(id, name) {
        if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
            const form = document.getElementById('deletePromoForm');
            form.action = `/merchant/promotions/${id}`;
            form.submit();
        }
    }
</script>
