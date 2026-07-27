@extends('layouts.admin')

@section('title', 'Add Menu Item - ' . $merchant->business_name)

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.merchants.menu', $merchant->merchant_id) }}" class="btn btn-secondary me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="h3 mb-0">Add Menu Item</h1>
                        <p class="text-muted small">{{ $merchant->business_name }}</p>
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

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.merchants.menu.store', $merchant->merchant_id) }}"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" name="price" class="form-control"
                                    value="{{ old('price') }}" required>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Stock Status <span class="text-danger">*</span></label>
                            <select name="stock_status" class="form-select @error('stock_status') is-invalid @enderror"
                                required>
                                @foreach ($statuses as $stockStatus)
                                    <option value="{{ $stockStatus }}"
                                        {{ old('stock_status', 'low_stock') == $stockStatus ? 'selected' : '' }}>
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
                            <input type="text" name="unit" class="form-control" value="{{ old('unit', 'piece') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Initial Stock</label>
                            <input type="number" name="stock_quantity" class="form-control"
                                value="{{ old('stock_quantity', 0) }}" min="0">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Low Stock Threshold</label>
                            <input type="number" name="low_stock_threshold" class="form-control"
                                value="{{ old('low_stock_threshold', 5) }}" min="0">
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Max: 2MB (JPEG, PNG, JPG, GIF, WebP)</small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Menu Item
                    </button>
                    <a href="{{ route('admin.merchants.menu', $merchant->merchant_id) }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </form>
            </div>
        </div>
    </div>
@endsection
