@extends('layouts.admin')

@section('title', 'Menu Item Details')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.merchants.menu', $merchant->merchant_id) }}" class="btn btn-secondary me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="h3 mb-0">{{ $menuItem->name }}</h1>
                        <p class="text-muted small">
                            <i class="fas fa-store"></i> {{ $merchant->business_name }}
                            <span class="mx-2">|</span>
                            <i class="fas fa-tag"></i> {{ $categories[$menuItem->category] ?? $menuItem->category }}
                        </p>
                    </div>
                </div>
            </div>
            <div>
                <span
                    class="badge bg-{{ $menuItem->status === 'available' ? 'success' : ($menuItem->status === 'out_of_stock' ? 'danger' : 'secondary') }} fs-6">
                    {{ ucfirst(str_replace('_', ' ', $menuItem->status)) }}
                </span>
            </div>
        </div>

        <div class="row">
            <!-- Image -->
            <div class="col-md-4">
                @if ($menuItem->image_url)
                    <img src="{{ asset('storage/' . $menuItem->image_url) }}" alt="{{ $menuItem->name }}"
                        class="img-fluid rounded shadow">
                @else
                    <div class="bg-light rounded p-5 text-center">
                        <i class="fas fa-utensils fa-5x text-muted"></i>
                        <p class="text-muted mt-2">No image available</p>
                    </div>
                @endif
            </div>

            <!-- Details -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h3>{{ $menuItem->name }}</h3>

                        @if ($menuItem->description)
                            <p class="text-muted mt-2">{{ $menuItem->description }}</p>
                        @endif

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <strong><i class="fas fa-money-bill"></i> Price:</strong>
                                <h4 class="text-success">₱{{ number_format($menuItem->price, 2) }}</h4>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-tag"></i> Category:</strong>
                                <p><span
                                        class="badge bg-info">{{ $categories[$menuItem->category] ?? $menuItem->category }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-store"></i> Merchant:</strong>
                                <p>
                                    <a href="{{ route('admin.merchants.show', $merchant->merchant_id) }}">
                                        {{ $merchant->business_name }}
                                    </a>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-toggle-on"></i> Status:</strong>
                                <p>
                                    <span
                                        class="badge bg-{{ $menuItem->status === 'available' ? 'success' : ($menuItem->status === 'out_of_stock' ? 'danger' : 'secondary') }}">
                                        {{ ucfirst(str_replace('_', ' ', $menuItem->status)) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-calendar-alt"></i> Created:</strong>
                                <p>{{ $menuItem->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-clock"></i> Last Updated:</strong>
                                <p>{{ $menuItem->updated_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <strong><i class="fas fa-id-badge"></i> Item ID:</strong>
                                <p><code>{{ $menuItem->menu_item_id }}</code></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-3">
                    <div class="card-header bg-white">
                        <i class="fas fa-cog"></i> Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.merchants.menu', $merchant->merchant_id) }}"
                                class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Menu
                            </a>
                            <a href="{{ route('admin.merchants.show', $merchant->merchant_id) }}" class="btn btn-primary">
                                <i class="fas fa-store"></i> View Merchant
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
