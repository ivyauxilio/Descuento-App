@extends('layouts.admin')

@section('title', 'Edit Menu Item')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Edit Menu Item</h1>
                <p class="text-muted small">{{ $menuItem->name }}</p>
            </div>
            <a href="{{ route('admin.menu-items.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
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

        <div class="row">
            <!-- Main Form -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <i class="fas fa-edit"></i> Edit: {{ $menuItem->name }}
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.menu-items.update', $menuItem->menu_item_id) }}"
                            enctype="multipart/form-data">
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
                                                {{ old('merchant_id', $menuItem->merchant_id) == $merchant->merchant_id ? 'selected' : '' }}>
                                                {{ $merchant->business_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('merchant_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Name -->
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label fw-bold">Item Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $menuItem->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="col-12 mb-3">
                                    <label for="description" class="form-label fw-bold">Description</label>
                                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                        rows="3">{{ old('description', $menuItem->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Price -->
                                <div class="col-md-4 mb-3">
                                    <label for="price" class="form-label fw-bold">Price <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" step="0.01" name="price" id="price"
                                            class="form-control @error('price') is-invalid @enderror"
                                            value="{{ old('price', $menuItem->price) }}" required>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Category -->
                                {{-- <div class="col-md-4 mb-3">
                                    <label for="category" class="form-label fw-bold">Category <span
                                            class="text-danger">*</span></label>
                                    <select name="category" id="category"
                                        class="form-select @error('category') is-invalid @enderror" required>
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $key => $value)
                                            <option value="{{ $key }}"
                                                {{ old('category', $menuItem->category) == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div> --}}

                                <!-- Status -->
                                <div class="col-md-4 mb-3">
                                    <label for="status" class="form-label fw-bold">Status <span
                                            class="text-danger">*</span></label>
                                    <select name="status" id="status"
                                        class="form-select @error('status') is-invalid @enderror" required>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}"
                                                {{ old('status', $menuItem->status) == $status ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Menu Item
                                </button>
                                <a href="{{ route('admin.menu-items.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Image Management Sidebar -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <i class="fas fa-image"></i> Image Management
                    </div>
                    <div class="card-body">
                        <!-- Current Image -->
                        @if ($menuItem->image_url)
                            <div class="text-center mb-3">
                                <img src="{{ asset('storage/' . $menuItem->image_url) }}" alt="{{ $menuItem->name }}"
                                    class="img-fluid rounded" style="max-height: 250px; object-fit: cover;">
                                <div class="mt-2">
                                    <form method="POST"
                                        action="{{ route('admin.menu-items.remove-image', $menuItem->menu_item_id) }}"
                                        onsubmit="return confirm('Are you sure you want to remove this image?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Remove Image
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-image fa-4x text-muted"></i>
                                <p class="text-muted mt-2">No image uploaded</p>
                            </div>
                        @endif

                        <!-- Upload New Image -->
                        <hr>
                        <form method="POST"
                            action="{{ route('admin.menu-items.update-image', $menuItem->menu_item_id) }}"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="image" class="form-label fw-bold">Upload New Image</label>
                                <input type="file" name="image" id="image"
                                    class="form-control @error('image') is-invalid @enderror" accept="image/*" required>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Accepted formats: JPEG, PNG, JPG, GIF, WebP (Max: 2MB)</small>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-upload"></i> Upload Image
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Preview image before upload
            document.getElementById('image').addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // You can add a preview here
                        console.log('Image loaded:', e.target.result);
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        });
    </script>
@endsection
