@extends('layouts.admin')

@section('title', 'Promotions Management')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Promotions Management</h1>
                <p class="text-muted small">View and manage all merchant promotions</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.promotions.export') }}" class="btn btn-success">
                    <i class="fas fa-file-export"></i> Export CSV
                </a>
                <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Promotion
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Total Promotions</h6>
                                <h2 class="mb-0" id="totalPromos">{{ $stats['total'] }}</h2>
                            </div>
                            <i class="fas fa-tags fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Active</h6>
                                <h2 class="mb-0" id="activePromos">{{ $stats['active'] }}</h2>
                            </div>
                            <i class="fas fa-check-circle fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-dark-50">Inactive</h6>
                                <h2 class="mb-0" id="inactivePromos">{{ $stats['inactive'] }}</h2>
                            </div>
                            <i class="fas fa-pause-circle fa-2x text-dark-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Expired</h6>
                                <h2 class="mb-0" id="expiredPromos">{{ $stats['expired'] }}</h2>
                            </div>
                            <i class="fas fa-clock fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Type Distribution -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <i class="fas fa-chart-pie"></i> Promotion Type Distribution
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="p-3 border rounded">
                                    <h5 class="text-primary">{{ $stats['by_type']['percentage'] }}</h5>
                                    <small class="text-muted">Percentage</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 border rounded">
                                    <h5 class="text-success">{{ $stats['by_type']['fixed'] }}</h5>
                                    <small class="text-muted">Fixed</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 border rounded">
                                    <h5 class="text-warning">{{ $stats['by_type']['bogo'] }}</h5>
                                    <small class="text-muted">BOGO</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <i class="fas fa-store"></i> Top Merchants by Promotions
                    </div>
                    <div class="card-body">
                        @forelse($stats['by_merchant'] as $item)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ $item->merchant->business_name ?? 'Unknown' }}</span>
                                <span class="badge bg-primary rounded-pill">{{ $item->total }}</span>
                            </div>
                        @empty
                            <p class="text-muted text-center">No promotions yet</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.promotions.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by title, merchant..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Merchant</label>
                        <select name="merchant_id" class="form-select">
                            <option value="">All Merchants</option>
                            @foreach ($merchants as $merchant)
                                <option value="{{ $merchant->merchant_id }}"
                                    {{ request('merchant_id') == $merchant->merchant_id ? 'selected' : '' }}>
                                    {{ $merchant->business_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Type</label>
                        <select name="promo_type" class="form-select">
                            <option value="">All Types</option>
                            @foreach ($promoTypes as $type)
                                <option value="{{ $type }}"
                                    {{ request('promo_type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Date Range</label>
                        <select name="date_range" class="form-select">
                            <option value="">All</option>
                            <option value="active" {{ request('date_range') == 'active' ? 'selected' : '' }}>Active Now
                            </option>
                            <option value="upcoming" {{ request('date_range') == 'upcoming' ? 'selected' : '' }}>Upcoming
                            </option>
                            <option value="expired" {{ request('date_range') == 'expired' ? 'selected' : '' }}>Expired
                            </option>
                            <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Promotions Table -->
        <div class="card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">
                        <i class="fas fa-tags"></i>
                        {{ $promotions->total() }} Promotion(s) Found
                    </span>
                    <span class="text-muted small">
                        Showing {{ $promotions->firstItem() ?? 0 }} - {{ $promotions->lastItem() ?? 0 }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Title</th>
                                <th>Merchant</th>
                                <th>Type</th>
                                <th>Value</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th class="text-center" width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($promotions as $promotion)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input promo-checkbox"
                                            value="{{ $promotion->promotion_id }}">
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $promotion->title }}</strong>
                                            @if ($promotion->description)
                                                <br><small
                                                    class="text-muted">{{ Str::limit($promotion->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        {{ $promotion->merchant->business_name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ strtoupper($promotion->promo_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($promotion->promo_type === 'percentage')
                                            <strong>{{ $promotion->value }}%</strong>
                                        @elseif($promotion->promo_type === 'fixed')
                                            <strong>₱{{ number_format($promotion->value, 2) }}</strong>
                                        @else
                                            <strong>BOGO</strong>
                                        @endif
                                        @if ($promotion->min_order_amount)
                                            <br><small class="text-muted">Min:
                                                ₱{{ number_format($promotion->min_order_amount, 2) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            <i class="fas fa-calendar-alt"></i>
                                            {{ $promotion->start_date->format('M d, Y') }}
                                            @if ($promotion->end_date)
                                                - {{ $promotion->end_date->format('M d, Y') }}
                                            @else
                                                <span class="text-success">No Expiry</span>
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $promotion->status === 'active' ? 'success' : ($promotion->status === 'expired' ? 'danger' : 'secondary') }}">
                                            {{ ucfirst($promotion->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm w-100">
                                            <a href="{{ route('admin.promotions.show', $promotion->promotion_id) }}"
                                                class="btn btn-info text-white" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.promotions.edit', $promotion->promotion_id) }}"
                                                class="btn btn-warning text-white" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger"
                                                onclick="deletePromotion('{{ $promotion->promotion_id }}', '{{ addslashes($promotion->title) }}')"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-tags fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-0">No promotions found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <x-pagination :paginator="$promotions" />
            {{-- @if ($promotions->hasPages())
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $promotions->firstItem() ?? 0 }} to {{ $promotions->lastItem() ?? 0 }}
                            of {{ $promotions->total() }} results
                        </div>
                        <div>
                            {{ $promotions->links() }}
                        </div>
                    </div>
                </div>
            @endif --}}
        </div>
    </div>

    <!-- Hidden Forms -->
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <form id="bulkDeleteForm" method="POST" action="{{ route('admin.promotions.bulk-delete') }}"
        style="display: none;">
        @csrf
        <input type="hidden" name="ids" id="bulkDeleteIds">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select All
            document.getElementById('selectAll').addEventListener('change', function() {
                document.querySelectorAll('.promo-checkbox').forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            // Auto-refresh stats every 60 seconds
            setInterval(function() {
                fetch('{{ route('admin.promotions.stats') }}')
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('totalPromos').textContent = data.total;
                        document.getElementById('activePromos').textContent = data.active;
                        document.getElementById('inactivePromos').textContent = data.inactive;
                        document.getElementById('expiredPromos').textContent = data.expired;
                    })
                    .catch(error => console.error('Error fetching stats:', error));
            }, 60000);
        });

        function deletePromotion(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/promotions/${id}`;
                form.submit();
            }
        }

        function confirmBulkDelete() {
            const selected = document.querySelectorAll('.promo-checkbox:checked');
            if (selected.length === 0) {
                alert('Please select at least one promotion to delete.');
                return;
            }

            if (confirm(`Are you sure you want to delete ${selected.length} promotion(s)?`)) {
                const ids = Array.from(selected).map(cb => cb.value);
                document.getElementById('bulkDeleteIds').value = JSON.stringify(ids);
                document.getElementById('bulkDeleteForm').submit();
            }
        }
    </script>
@endsection
