@extends('layouts.admin')

@section('title', 'Menu Item Details')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Menu Item Details</h1>
                <p class="text-muted small">{{ $menuItem->name }}</p>
            </div>
            <div>
                <a href="{{ route('admin.menu-items.edit', $menuItem->menu_item_id) }}"
                    class="btn btn-warning text-white me-2">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('admin.menu-items.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="row">
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
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h3>{{ $menuItem->name }}</h3>
                        <p class="text-muted">{{ $menuItem->description ?? 'No description' }}</p>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <strong>Price:</strong>
                                <h4 class="text-success">{{ $menuItem->getFormattedPrice() }}</h4>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong>
                                <span class="badge bg-{{ $menuItem->getStatusBadgeColor() }} fs-6">
                                    {{ $menuItem->getStatusLabel() }}
                                </span>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <strong>Merchant:</strong>
                                <p>{{ $menuItem->merchant->business_name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Created:</strong>
                                <p>{{ $menuItem->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <strong>Last Updated:</strong>
                                <p>{{ $menuItem->updated_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Menu Item ID:</strong>
                                <p><code>{{ $menuItem->menu_item_id }}</code></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($menuItem->promotionsAsFree->count() > 0 || $menuItem->promotionsAsRequired->count() > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white">
                            <i class="fas fa-tags"></i> Associated Promotions
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if ($menuItem->promotionsAsFree->count() > 0)
                                    <div class="col-md-6">
                                        <h6>As Free Item:</h6>
                                        <ul>
                                            @foreach ($menuItem->promotionsAsFree as $promo)
                                                <li>{{ $promo->title }} ({{ ucfirst($promo->promo_type) }})</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if ($menuItem->promotionsAsRequired->count() > 0)
                                    <div class="col-md-6">
                                        <h6>As Required Item:</h6>
                                        <ul>
                                            @foreach ($menuItem->promotionsAsRequired as $promo)
                                                <li>{{ $promo->title }} ({{ ucfirst($promo->promo_type) }})</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
