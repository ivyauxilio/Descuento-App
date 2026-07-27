@extends('layouts.admin')

@section('title', $merchant->business_name . ' - Menu')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.merchants.index') }}" class="btn btn-secondary me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="h3 mb-0">{{ $merchant->business_name }}</h1>
                        <p class="text-muted small">
                            <i class="fas fa-utensils"></i> Menu Items
                            @if ($merchant->branch_name)
                                - {{ $merchant->branch_name }}
                            @endif
                            <span class="mx-2">|</span>
                            <i class="fas fa-user"></i> Owner:
                            {{ $merchant->owner->firstname ?? 'N/A' }}
                            {{ $merchant->owner->lastname ?? '' }}
                            <span class="mx-2">|</span>
                            <i class="fas fa-envelope"></i> {{ $merchant->email }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex">
                <span class="badge me-2 bg-{{ $merchant->status === 'approved' ? 'success' : 'secondary' }} fs-6">
                    {{ ucfirst($merchant->status) }}
                </span>
                <a href="{{ route('admin.merchants.menu.create', $merchant->merchant_id) }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Menu Item
                </a>
            </div>
        </div>

        <!-- Inventory Statistics -->
        <div class="row g-4 mb-4">
            <div class="col-md-2">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-white-50">Total Items</h6>
                        <h2 class="mb-0">{{ $stats['total'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-white-50">In Stock</h6>
                        <h2 class="mb-0">{{ $stats['in_stock'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-dark-50">Low Stock</h6>
                        <h2 class="mb-0">{{ $stats['low_stock'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-white-50">Out of Stock</h6>
                        <h2 class="mb-0">{{ $stats['out_of_stock'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-white-50">Total Value</h6>
                        <h2 class="mb-0">₱{{ number_format($stats['total_value'] ?? 0, 2) }}</h2>
                    </div>
                </div>
            </div>
            {{-- <div class="col-md-2">
                <div class="card bg-secondary text-white">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-white-50">Available</h6>
                        <h2 class="mb-0">{{ $stats['available'] }}</h2>
                    </div>
                </div>
            </div> --}}
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.merchants.menu', $merchant->merchant_id) }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, SKU..."
                            value="{{ request('search') }}">
                    </div>
                    {{-- <div class="col-md-3">
                        <label class="form-label fw-bold">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach ($categories as $key => $value)
                                <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div> --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Stock Status</label>
                        <select name="stock_status" class="form-select">
                            <option value="">All Status</option>
                            @foreach ($stockStatuses as $status)
                                <option value="{{ $status }}"
                                    {{ request('stock_status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.merchants.menu', $merchant->merchant_id) }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i>
                        </a>
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

        <!-- Menu Items Grid -->
        <div class="row g-4">
            @forelse($menuItems as $item)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm hover-shadow transition">
                        <!-- Image -->
                        @if ($item->image_url)
                            <img src="{{ asset('storage/' . $item->image_url) }}" alt="{{ $item->name }}"
                                class="card-img-top" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                style="height: 200px;">
                                <i class="fas fa-utensils fa-4x text-muted"></i>
                            </div>
                        @endif

                        <div class="card-body">
                            <!-- Header -->
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title mb-0">{{ $item->name }}</h5>
                                @php
                                    $badgeClass = match ($item->stock_status) {
                                        'in_stock' => 'success',
                                        'low_stock' => 'warning',
                                        'out_of_stock' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp

                                <span class="badge bg-{{ $badgeClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $item->stock_status)) }}
                                </span>
                            </div>

                            @if ($item->description)
                                <p class="card-text text-muted small mt-2">
                                    {{ Str::limit($item->description, 80) }}
                                </p>
                            @endif

                            <!-- Category & Price -->
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div>
                                    <span class="badge bg-info">
                                        <i class="fas fa-tag"></i>
                                        {{ $categories[$item->category] ?? $item->category }}
                                    </span>
                                    <span class="badge bg-secondary ms-1">
                                        <i class="fas fa-barcode"></i> {{ $item->sku }}
                                    </span>
                                </div>
                                <h5 class="text-success mb-0">₱{{ number_format($item->price, 2) }}</h5>
                            </div>

                            <!-- ============================================ -->
                            <!-- INVENTORY COUNTER WITH QUICK ACTIONS -->
                            <!-- ============================================ -->
                            <div class="mt-3 pt-3 border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted small">
                                            <i class="fas fa-boxes"></i> Stock:
                                        </span>
                                        @if ($item->stock_quantity <= 0)
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle"></i> 0 {{ $item->unit }}
                                            </span>
                                        @elseif($item->stock_quantity <= $item->low_stock_threshold)
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-exclamation-triangle"></i> {{ $item->stock_quantity }}
                                                {{ $item->unit }}
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i> {{ $item->stock_quantity }}
                                                {{ $item->unit }}
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.merchants.menu.show', [$merchant->merchant_id, $item->menu_item_id]) }}"
                                            class="btn btn-sm btn-outline-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.merchants.menu.edit', [$merchant->merchant_id, $item->menu_item_id]) }}"
                                            class="btn btn-sm btn-outline-warning" title="Edit Menu Item">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!-- Delete - Pass both parameters -->
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="deleteItem('{{ $merchant->merchant_id }}', '{{ $item->menu_item_id }}', '{{ addslashes($item->name) }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Quick Stock Actions -->
                                <div class="row g-1 mt-2">
                                    <div class="col-4">
                                        <button class="btn btn-sm btn-success w-100 quick-add-btn"
                                            data-merchant="{{ $merchant->merchant_id }}"
                                            data-id="{{ $item->menu_item_id }}" data-name="{{ $item->name }}"
                                            data-unit="{{ $item->unit }}">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </div>
                                    <div class="col-4">
                                        <button class="btn btn-sm btn-danger w-100 quick-remove-btn"
                                            data-merchant="{{ $merchant->merchant_id }}"
                                            data-id="{{ $item->menu_item_id }}" data-name="{{ $item->name }}"
                                            data-unit="{{ $item->unit }}" data-stock="{{ $item->stock_quantity }}"
                                            {{ $item->stock_quantity <= 0 ? 'disabled' : '' }}>
                                            <i class="fas fa-minus"></i> Remove
                                        </button>
                                    </div>
                                    <div class="col-4">
                                        <button class="btn btn-sm btn-outline-secondary w-100 adjust-btn"
                                            data-merchant="{{ $merchant->merchant_id }}"
                                            data-id="{{ $item->menu_item_id }}" data-name="{{ $item->name }}"
                                            data-unit="{{ $item->unit }}" data-stock="{{ $item->stock_quantity }}">
                                            <i class="fas fa-edit"></i> Set
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-0 pt-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i>
                                    {{ $item->created_at->format('M d, Y') }}
                                </small>
                                <div>
                                    <a href="{{ route('admin.inventory.transactions', $item->menu_item_id) }}"
                                        class="btn btn-sm btn-outline-secondary" title="Transaction History">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-utensils-slash fa-3x text-muted mb-3 d-block"></i>
                            <h5>No Menu Items</h5>
                            <p class="text-muted">This merchant hasn't added any menu items yet.</p>
                            <a href="{{ route('admin.merchants.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Merchants
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($menuItems->hasPages())
            <div class="mt-4">
                {{ $menuItems->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================ -->
    <!-- QUICK ADD STOCK MODAL -->
    <!-- ============================================ -->
    <div class="modal fade" id="quickAddModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <form method="POST" id="quickAddForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">Add stock to <strong id="addItemName"></strong></p>
                        <div class="mb-3">
                            <label class="form-label">Quantity to Add</label>
                            <input type="number" name="quantity" class="form-control" min="1" value="1"
                                required>
                            <small class="text-muted">Unit: <span id="addItemUnit">piece</span></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason (Optional)</label>
                            <input type="text" name="reason" class="form-control" placeholder="e.g., Restock">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- QUICK REMOVE STOCK MODAL -->
    <!-- ============================================ -->
    <div class="modal fade" id="quickRemoveModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <form method="POST" id="quickRemoveForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Remove Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">Remove stock from <strong id="removeItemName"></strong></p>
                        <div class="mb-3">
                            <label class="form-label">Quantity to Remove</label>
                            <input type="number" name="quantity" class="form-control" min="1" value="1"
                                required>
                            <small class="text-muted">Available: <span id="removeItemStock">0</span> <span
                                    id="removeItemUnit">piece</span></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason (Optional)</label>
                            <input type="text" name="reason" class="form-control" placeholder="e.g., Spoilage">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Remove Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- ADJUST STOCK MODAL -->
    <!-- ============================================ -->
    <div class="modal fade" id="adjustStockModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <form method="POST" id="adjustStockForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Adjust Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">Set stock for <strong id="adjustItemName"></strong></p>
                        <div class="mb-3">
                            <label class="form-label">New Quantity</label>
                            <input type="number" name="quantity" class="form-control" min="0" value="0"
                                required>
                            <small class="text-muted">Current: <span id="adjustItemStock">0</span> <span
                                    id="adjustItemUnit">piece</span></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason (Optional)</label>
                            <input type="text" name="reason" class="form-control"
                                placeholder="e.g., Inventory correction">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Set Quantity</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

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

        .quick-add-btn,
        .quick-remove-btn,
        .adjust-btn {
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ============================================
            // QUICK ADD STOCK
            // ============================================
            document.querySelectorAll('.quick-add-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const merchantId = this.dataset.merchant;
                    const itemId = this.dataset.id;
                    const name = this.dataset.name;
                    const unit = this.dataset.unit;

                    document.getElementById('addItemName').textContent = name;
                    document.getElementById('addItemUnit').textContent = unit;

                    const form = document.getElementById('quickAddForm');
                    form.action = `/admin/merchants/${merchantId}/menu/${itemId}/add-stock`;

                    const modal = new bootstrap.Modal(document.getElementById('quickAddModal'));
                    modal.show();
                });
            });

            // ============================================
            // QUICK REMOVE STOCK
            // ============================================
            document.querySelectorAll('.quick-remove-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const merchantId = this.dataset.merchant;
                    const itemId = this.dataset.id;
                    const name = this.dataset.name;
                    const unit = this.dataset.unit;
                    const stock = parseInt(this.dataset.stock);

                    if (stock <= 0) {
                        alert('This item is out of stock!');
                        return;
                    }

                    document.getElementById('removeItemName').textContent = name;
                    document.getElementById('removeItemStock').textContent = stock;
                    document.getElementById('removeItemUnit').textContent = unit;

                    const quantityInput = document.querySelector(
                        '#quickRemoveModal input[name="quantity"]');
                    quantityInput.max = stock;
                    quantityInput.value = 1;

                    const form = document.getElementById('quickRemoveForm');
                    form.action = `/admin/merchants/${merchantId}/menu/${itemId}/remove-stock`;

                    const modal = new bootstrap.Modal(document.getElementById('quickRemoveModal'));
                    modal.show();
                });
            });

            // ============================================
            // ADJUST STOCK
            // ============================================
            document.querySelectorAll('.adjust-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const merchantId = this.dataset.merchant;
                    const itemId = this.dataset.id;
                    const name = this.dataset.name;
                    const unit = this.dataset.unit;
                    const stock = parseInt(this.dataset.stock);

                    document.getElementById('adjustItemName').textContent = name;
                    document.getElementById('adjustItemStock').textContent = stock;
                    document.getElementById('adjustItemUnit').textContent = unit;

                    const quantityInput = document.querySelector(
                        '#adjustStockModal input[name="quantity"]');
                    quantityInput.value = stock;
                    quantityInput.min = 0;

                    const form = document.getElementById('adjustStockForm');
                    form.action = `/admin/merchants/${merchantId}/menu/${itemId}/adjust-stock`;

                    const modal = new bootstrap.Modal(document.getElementById('adjustStockModal'));
                    modal.show();
                });
            });
        });

        function deleteItem(merchantId, itemId, name) {
            if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/merchants/${merchantId}/menu/${itemId}`;
                form.submit();
            }
        }
    </script>
@endsection
