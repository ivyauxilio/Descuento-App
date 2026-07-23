@extends('layouts.admin')

@section('title', 'Edit Promotion')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Edit Promotion</h1>
                <p class="text-muted small">{{ $promotion->title }}</p>
            </div>
            <div>
                <a href="{{ route('admin.promotions.show', $promotion->promotion_id) }}" class="btn btn-info text-white me-2">
                    <i class="fas fa-eye"></i> View
                </a>
                <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> Please fix the following errors:
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-header bg-white">
                <i class="fas fa-edit"></i> Edit Promotion: {{ $promotion->title }}
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.promotions.update', $promotion->promotion_id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Merchant -->
                        <div class="col-md-6 mb-3">
                            <label for="merchant_id" class="form-label fw-bold">Merchant <span
                                    class="text-danger">*</span></label>
                            <select name="merchant_id" id="merchant_id"
                                class="form-select @error('merchant_id') is-invalid @enderror" required>
                                <option value="">Select Merchant</option>
                                @foreach ($merchants as $merchant)
                                    <option value="{{ $merchant->merchant_id }}"
                                        {{ old('merchant_id', $promotion->merchant_id) == $merchant->merchant_id ? 'selected' : '' }}>
                                        {{ $merchant->business_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('merchant_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Title -->
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label fw-bold">Title <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="title" id="title"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $promotion->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Promo Type -->
                        <div class="col-md-6 mb-3">
                            <label for="promo_type" class="form-label fw-bold">Promotion Type <span
                                    class="text-danger">*</span></label>
                            <select name="promo_type" id="promo_type"
                                class="form-select @error('promo_type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                @foreach ($promoTypes as $type)
                                    <option value="{{ $type }}"
                                        {{ old('promo_type', $promotion->promo_type) == $type ? 'selected' : '' }}>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('promo_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Value -->
                        <div class="col-md-6 mb-3">
                            <label for="value" class="form-label fw-bold">Value <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="value" id="value"
                                class="form-control @error('value') is-invalid @enderror"
                                value="{{ old('value', $promotion->value) }}" required>
                            @error('value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">For percentage: e.g., 20 | For fixed: e.g., 100 | For BOGO: 0</small>
                        </div>

                        <!-- Min Order Amount -->
                        <div class="col-md-6 mb-3">
                            <label for="min_order_amount" class="form-label fw-bold">Minimum Order Amount</label>
                            <input type="number" step="0.01" name="min_order_amount" id="min_order_amount"
                                class="form-control @error('min_order_amount') is-invalid @enderror"
                                value="{{ old('min_order_amount', $promotion->min_order_amount) }}" placeholder="Optional">
                            @error('min_order_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Min Quantity -->
                        <div class="col-md-6 mb-3">
                            <label for="min_quantity" class="form-label fw-bold">Minimum Quantity</label>
                            <input type="number" name="min_quantity" id="min_quantity"
                                class="form-control @error('min_quantity') is-invalid @enderror"
                                value="{{ old('min_quantity', $promotion->min_quantity) }}" placeholder="Optional">
                            @error('min_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Start Date -->
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label fw-bold">Start Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date"
                                class="form-control @error('start_date') is-invalid @enderror"
                                value="{{ old('start_date', $promotion->start_date->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- End Date -->
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label fw-bold">End Date</label>
                            <input type="date" name="end_date" id="end_date"
                                class="form-control @error('end_date') is-invalid @enderror"
                                value="{{ old('end_date', $promotion->end_date ? $promotion->end_date->format('Y-m-d') : '') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave blank for no expiry</small>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label fw-bold">Status <span
                                    class="text-danger">*</span></label>
                            <select name="status" id="status"
                                class="form-select @error('status') is-invalid @enderror" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}"
                                        {{ old('status', $promotion->status) == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label fw-bold">Category</label>
                            <select name="category_id" id="category_id"
                                class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->category_id }}"
                                        {{ old('category_id', $promotion->category_id) == $category->category_id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Free Menu Item -->
                        <div class="col-md-6 mb-3">
                            <label for="free_menu_item_id" class="form-label fw-bold">Free Menu Item</label>
                            <select name="free_menu_item_id" id="free_menu_item_id"
                                class="form-select @error('free_menu_item_id') is-invalid @enderror">
                                <option value="">Select Free Item</option>
                                @foreach ($menuItems as $item)
                                    <option value="{{ $item->menu_item_id }}"
                                        {{ old('free_menu_item_id', $promotion->free_menu_item_id) == $item->menu_item_id ? 'selected' : '' }}>
                                        {{ $item->name }} ({{ $item->merchant->business_name ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('free_menu_item_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">For BOGO promotions - the free item</small>
                        </div>

                        <!-- Required Menu Item -->
                        <div class="col-md-6 mb-3">
                            <label for="required_menu_item_id" class="form-label fw-bold">Required Menu Item</label>
                            <select name="required_menu_item_id" id="required_menu_item_id"
                                class="form-select @error('required_menu_item_id') is-invalid @enderror">
                                <option value="">Select Required Item</option>
                                @foreach ($menuItems as $item)
                                    <option value="{{ $item->menu_item_id }}"
                                        {{ old('required_menu_item_id', $promotion->required_menu_item_id) == $item->menu_item_id ? 'selected' : '' }}>
                                        {{ $item->name }} ({{ $item->merchant->business_name ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('required_menu_item_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">For BOGO promotions - the required item to buy</small>
                        </div>

                        <!-- Timestamps -->
                        <div class="col-12 mt-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt"></i> Created:
                                    {{ $promotion->created_at->format('M d, Y h:i A') }}
                                    <br>
                                    <i class="fas fa-clock"></i> Last updated:
                                    {{ $promotion->updated_at->format('M d, Y h:i A') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Promotion
                        </button>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update value label based on type
            document.getElementById('promo_type').addEventListener('change', function() {
                const valueLabel = document.querySelector('label[for="value"]');
                const valueInput = document.getElementById('value');

                if (this.value === 'percentage') {
                    valueLabel.textContent = 'Value (Percentage) *';
                    valueInput.placeholder = 'e.g., 20';
                } else if (this.value === 'fixed') {
                    valueLabel.textContent = 'Value (Amount) *';
                    valueInput.placeholder = 'e.g., 100';
                } else if (this.value === 'bogo') {
                    valueLabel.textContent = 'Value (BOGO) *';
                    valueInput.placeholder = '0';
                } else {
                    valueLabel.textContent = 'Value *';
                    valueInput.placeholder = '0';
                }
            });
        });
    </script>
@endsection
