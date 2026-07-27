@extends('layouts.admin')

@section('title', 'Edit Menu Item - ' . $merchant->business_name)

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.merchants.menu', $merchant->merchant_id) }}" class="btn btn-secondary me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="h3 mb-0">Edit Menu Item</h1>
                        <p class="text-muted small">{{ $merchant->business_name }} - {{ $menuItem->name }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <!-- Main Form -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST"
                            action="{{ route('admin.merchants.menu.update', [$merchant->merchant_id, $menuItem->menu_item_id]) }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Item Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $menuItem->name) }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" step="0.01" name="price" class="form-control"
                                            value="{{ old('price', $menuItem->price) }}" required>
                                    </div>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Description</label>
                                    <textarea name="description" class="form-control" rows="3">{{ old('description', $menuItem->description) }}</textarea>
                                </div>

                                {{-- <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                                    <select name="category" class="form-select" required>
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $key => $value)
                                            <option value="{{ $key }}"
                                                {{ old('category', $menuItem->category) == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div> --}}

                                {{-- <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Stock Status <span
                                            class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}"
                                                {{ old('status', $menuItem->stock_status) == $status ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div> --}}

                                <!-- Stock Status -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Stock Status <span
                                            class="text-danger">*</span></label>
                                    <select name="stock_status"
                                        class="form-select @error('stock_status') is-invalid @enderror" required>
                                        {{-- @php
                                            $stockStatuses = ['in_stock', 'low_stock', 'out_of_stock'];
                                        @endphp --}}
                                        @foreach ($statuses as $stockStatus)
                                            <option value="{{ $stockStatus }}"
                                                {{ old('stock_status', $menuItem->stock_status) == $stockStatus ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $stockStatus)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('stock_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Unit</label>
                                    <input type="text" name="unit" class="form-control"
                                        value="{{ old('unit', $menuItem->unit) }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Low Stock Threshold</label>
                                    <input type="number" name="low_stock_threshold" class="form-control"
                                        value="{{ old('low_stock_threshold', $menuItem->low_stock_threshold) }}"
                                        min="0">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Current Stock</label>
                                    <div class="form-control bg-light">{{ $menuItem->stock_quantity }}
                                        {{ $menuItem->unit }}(s)</div>
                                    <small class="text-muted">Use inventory management to change stock</small>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Menu Item
                            </button>
                            <a href="{{ route('admin.merchants.menu', $merchant->merchant_id) }}"
                                class="btn btn-secondary">
                                Cancel
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Image Management -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <i class="fas fa-image"></i> Image Management
                    </div>
                    <div class="card-body">
                        @if ($menuItem->image_url)
                            <div class="text-center mb-3">
                                <img src="{{ asset('storage/' . $menuItem->image_url) }}" alt="{{ $menuItem->name }}"
                                    class="img-fluid rounded" style="max-height: 200px; object-fit: cover;">
                                <form method="POST"
                                    action="{{ route('admin.merchants.menu.remove-image', [$merchant->merchant_id, $menuItem->menu_item_id]) }}"
                                    onsubmit="return confirm('Remove image?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm mt-2">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-image fa-4x text-muted"></i>
                                <p class="text-muted mt-2">No image</p>
                            </div>
                        @endif

                        <hr>
                        <form method="POST"
                            action="{{ route('admin.merchants.menu.update-image', [$merchant->merchant_id, $menuItem->menu_item_id]) }}"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Upload New Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*" required>
                                <small class="text-muted">JPEG, PNG, JPG, GIF, WebP (Max: 2MB)</small>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-upload"></i> Upload
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
