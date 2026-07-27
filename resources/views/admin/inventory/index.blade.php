@extends('layouts.admin')

@section('title', 'Inventory Management')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Inventory Management</h1>
                <p class="text-muted small">Monitor inventory across all merchants</p>
            </div>
            <div>
                <a href="{{ route('admin.inventory.low-stock') }}" class="btn btn-warning">
                    <i class="fas fa-exclamation-triangle"></i> Low Stock Alerts
                    @if (($stats['low_stock'] ?? 0) + ($stats['out_of_stock'] ?? 0) > 0)
                        <span class="badge bg-danger ms-1">
                            {{ ($stats['low_stock'] ?? 0) + ($stats['out_of_stock'] ?? 0) }}
                        </span>
                    @endif
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-2">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Total Items</h6>
                                <h2 class="mb-0">{{ number_format($stats['total'] ?? 0) }}</h2>
                            </div>
                            <i class="fas fa-boxes fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">In Stock</h6>
                                <h2 class="mb-0">{{ number_format($stats['in_stock'] ?? 0) }}</h2>
                            </div>
                            <i class="fas fa-check-circle fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-dark-50">Low Stock</h6>
                                <h2 class="mb-0">{{ number_format($stats['low_stock'] ?? 0) }}</h2>
                            </div>
                            <i class="fas fa-exclamation-triangle fa-2x text-dark-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Out of Stock</h6>
                                <h2 class="mb-0">{{ number_format($stats['out_of_stock'] ?? 0) }}</h2>
                            </div>
                            <i class="fas fa-times-circle fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Total Value</h6>
                                <h2 class="mb-0">₱{{ number_format($stats['total_value'] ?? 0, 2) }}</h2>
                            </div>
                            <i class="fas fa-money-bill fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-secondary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Merchants</h6>
                                <h2 class="mb-0">{{ number_format($stats['merchants_with_stock'] ?? 0) }}</h2>
                            </div>
                            <i class="fas fa-store fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.inventory.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Search</label>
                        <input type="text" name="search" class="form-control"
                            placeholder="Search item, SKU, merchant..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
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
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach ($categories as $key => $value)
                                <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">
                        <i class="fas fa-boxes"></i>
                        {{ $menuItems->total() }} Item(s) Found
                    </span>
                    <span class="text-muted small">
                        Showing {{ $menuItems->firstItem() ?? 0 }} - {{ $menuItems->lastItem() ?? 0 }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Merchant</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($menuItems as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if ($item->image_url)
                                                <img src="{{ asset('storage/' . $item->image_url) }}"
                                                    alt="{{ $item->name }}"
                                                    style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px; margin-right: 10px;">
                                            @else
                                                <div class="bg-light rounded p-2 me-2">
                                                    <i class="fas fa-utensils text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <strong>{{ $item->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ Str::limit($item->description, 30) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a
                                            href="{{ route('admin.inventory.merchant', $item->merchant->merchant_id ?? '') }}">
                                            {{ $item->merchant->business_name ?? 'N/A' }}
                                        </a>
                                    </td>
                                    <td>
                                        <code class="small">{{ $item->sku }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $categories[$item->category] ?? $item->category }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>₱{{ number_format($item->price, 2) }}</strong>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $item->stock_quantity }}</span>
                                        <small class="text-muted">{{ $item->unit }}</small>
                                    </td>
                                    <td>
                                        @if ($item->stock_quantity <= 0)
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @elseif($item->stock_quantity <= $item->low_stock_threshold)
                                            <span class="badge bg-warning text-dark">
                                                Low Stock ({{ $item->stock_quantity }})
                                            </span>
                                        @else
                                            <span class="badge bg-success">In Stock</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.inventory.show', $item->menu_item_id) }}"
                                                class="btn btn-info text-white" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.merchants.show', $item->merchant->merchant_id ?? '') }}"
                                                class="btn btn-secondary" title="View Merchant">
                                                <i class="fas fa-store"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-box-open fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-0">No inventory items found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($menuItems->hasPages())
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $menuItems->firstItem() ?? 0 }} to {{ $menuItems->lastItem() ?? 0 }}
                            of {{ $menuItems->total() }} results
                        </div>
                        <div>
                            {{ $menuItems->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
