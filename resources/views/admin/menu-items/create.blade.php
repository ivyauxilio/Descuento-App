@extends('layouts.admin')

@section('title', 'Add Menu Item')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Add Menu Item</h1>
                <p class="text-muted small">Create a new menu item for a merchant</p>
            </div>
            <a href="{{ route('admin.menu-items.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> Please fix the following errors:
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.menu-items.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="merchant_id" class="form-label fw-bold">Merchant <span
                                    class="text-danger">*</span></label>
                            <select name="merchant_id" id="merchant_id"
                                class="form-select @error('merchant_id') is-invalid @enderror" required>
                                <option value="">Select Merchant</option>
                                @foreach ($merchants as $merchant)
                                    <option value="{{ $merchant->merchant_id }}"
                                        {{ old('merchant_id') == $merchant->merchant_id ? 'selected' : '' }}>
                                        {{ $merchant->business_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('merchant_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-bold">Item Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label for="description" class="form-label fw-bold">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label fw-bold">Price <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" name="price" id="price"
                                    class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}"
                                    required>
                            </div>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label fw-bold">Status <span
                                    class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror"
                                required>
                                <option value="">Select Status</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}"
                                        {{ old('status', 'available') == $status ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label for="image" class="form-label fw-bold">Image</label>
                            <input type="file" name="image" id="image"
                                class="form-control @error('image') is-invalid @enderror" accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Accepted formats: JPEG, PNG, JPG, GIF, WebP (Max: 2MB)</small>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Menu Item
                        </button>
                        <a href="{{ route('admin.menu-items.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
