@extends('layouts.admin')

@section('title', 'Merchant Menu - ' . $merchant->business_name)

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
                            <i class="fas fa-store"></i> Menu Items
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
            <div>
                <span class="badge bg-{{ $merchant->status === 'active' ? 'success' : 'secondary' }} fs-6">
                    {{ ucfirst($merchant->status) }}
                </span>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Total Items</h6>
                                <h2 class="mb-0">{{ $stats['total'] }}</h2>
                            </div>
                            <i class="fas fa-utensils fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Available</h6>
                                <h2 class="mb-0">{{ $stats['available'] }}</h2>
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
                                <h6 class="card-subtitle mb-2 text-dark-50">Unavailable</h6>
                                <h2 class="mb-0">{{ $stats['unavailable'] }}</h2>
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
                                <h6 class="card-subtitle mb-2 text-white-50">Out of Stock</h6>
                                <h2 class="mb-0">{{ $stats['out_of_stock'] }}</h2>
                            </div>
                            <i class="fas fa-times-circle fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.merchants.menu', $merchant->merchant_id) }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Search</label>
                        <input type="text" name="search" class="form-control"
                            placeholder="Search by name, description..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
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
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title mb-0">{{ $item->name }}</h5>
                                <span
                                    class="badge bg-{{ $item->status === 'available' ? 'success' : ($item->status === 'out_of_stock' ? 'danger' : 'secondary') }}">
                                    {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                </span>
                            </div>

                            @if ($item->description)
                                <p class="card-text text-muted small mt-2">
                                    {{ Str::limit($item->description, 100) }}
                                </p>
                            @endif

                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div>
                                    <span class="badge bg-info">
                                        <i class="fas fa-tag"></i>
                                        {{ $categories[$item->category] ?? $item->category }}
                                    </span>
                                </div>
                                <h5 class="text-success mb-0">₱{{ number_format($item->price, 2) }}</h5>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-0 pt-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i>
                                    {{ $item->created_at->format('M d, Y') }}
                                </small>
                                <a href="{{ route('admin.merchants.menu.show', [$merchant->merchant_id, $item->menu_item_id]) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
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
    </style>
@endsection
