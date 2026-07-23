@extends('layouts.merchant')

@section('title', 'My Promotions')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">My Promotions</h1>
                <p class="text-muted small">Manage your store promotions and discounts</p>
            </div>
            <div>
                <a href="{{ route('merchant.promotions.create') }}" class="btn btn-primary">
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
                                <h2 class="mb-0">{{ $stats['total'] }}</h2>
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
                                <h2 class="mb-0">{{ $stats['active'] }}</h2>
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
                                <h2 class="mb-0">{{ $stats['inactive'] }}</h2>
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
                                <h2 class="mb-0">{{ $stats['expired'] }}</h2>
                            </div>
                            <i class="fas fa-clock fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('merchant.promotions.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by title..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Promo Type</label>
                        <select name="promo_type" class="form-select">
                            <option value="">All Types</option>
                            @foreach ($promoTypes as $type)
                                <option value="{{ $type }}" {{ request('promo_type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
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
                                    {{ ucfirst($status) }}
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

        <!-- Promotions Grid -->
        <div class="row g-4">
            @forelse($promotions as $promotion)
                <div class="col-md-6 col-lg-4">
                    @include('merchant.promotions.partials.promotion-card', ['promotion' => $promotion])
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-tags fa-3x text-muted mb-3 d-block"></i>
                            <h5>No Promotions Yet</h5>
                            <p class="text-muted">Create your first promotion to attract more customers!</p>
                            <a href="{{ route('merchant.promotions.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Promotion
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($promotions->hasPages())
            <div class="mt-4">
                {{ $promotions->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // Auto-refresh stats every 60 seconds
        setInterval(function() {
            fetch('{{ route('merchant.promotions.stats') }}')
                .then(response => response.json())
                .then(data => {
                    // Update stats cards
                    document.querySelector('.bg-primary .mb-0').textContent = data.total;
                    document.querySelector('.bg-success .mb-0').textContent = data.active;
                    document.querySelector('.bg-warning .mb-0').textContent = data.inactive;
                    document.querySelector('.bg-danger .mb-0').textContent = data.expired;
                })
                .catch(error => console.error('Error fetching stats:', error));
        }, 60000);
    </script>
@endpush
