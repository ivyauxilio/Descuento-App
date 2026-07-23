@extends('layouts.admin')

@section('title', 'Create Merchant')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Create Merchant</h1>
                <p class="text-muted small">Add a new merchant to the system</p>
            </div>
            <a href="{{ route('admin.merchants.index') }}" class="btn btn-secondary">
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

        <div class="card">
            <div class="card-header bg-white">
                <i class="fas fa-store"></i> Merchant Information
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.merchants.store') }}">
                    @csrf

                    <div class="row">
                        <!-- Business Information -->
                        <div class="col-12">
                            <h6 class="text-muted mb-3"><i class="fas fa-info-circle"></i> Business Details</h6>
                            <hr>
                        </div>

                        <!-- Owner -->
                        <div class="col-md-6 mb-3">
                            <label for="owner_id" class="form-label fw-bold">Owner <span
                                    class="text-danger">*</span></label>
                            <select name="owner_id" id="owner_id"
                                class="form-select @error('owner_id') is-invalid @enderror" required>
                                <option value="">Select Owner</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('owner_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('owner_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label fw-bold">Email Address <span
                                    class="text-danger">*</span></label>
                            <input type="email" name="email" id="email"
                                class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                placeholder="merchant@example.com" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Business Name -->
                        <div class="col-md-6 mb-3">
                            <label for="business_name" class="form-label fw-bold">Business Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="business_name" id="business_name"
                                class="form-control @error('business_name') is-invalid @enderror"
                                value="{{ old('business_name') }}" placeholder="Enter business name" required>
                            @error('business_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Branch Name -->
                        <div class="col-md-6 mb-3">
                            <label for="branch_name" class="form-label fw-bold">Branch Name</label>
                            <input type="text" name="branch_name" id="branch_name"
                                class="form-control @error('branch_name') is-invalid @enderror"
                                value="{{ old('branch_name') }}" placeholder="e.g., Main Branch">
                            @error('branch_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category and Province -->
                        <div class="col-12 mt-3">
                            <h6 class="text-muted mb-3"><i class="fas fa-tags"></i> Classification</h6>
                            <hr>
                        </div>

                        <!-- Category -->
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label fw-bold">Category <span
                                    class="text-danger">*</span></label>
                            <select name="category_id" id="category_id"
                                class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->category_id }}"
                                        {{ old('category_id') == $category->category_id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Province -->
                        <div class="col-md-6 mb-3">
                            <label for="province_id" class="form-label fw-bold">Province <span
                                    class="text-danger">*</span></label>
                            <select name="province_id" id="province_id"
                                class="form-select @error('province_id') is-invalid @enderror" required>
                                <option value="">Select Province</option>
                                @foreach ($provinces as $province)
                                    <option value="{{ $province->province_id }}"
                                        {{ old('province_id') == $province->province_id ? 'selected' : '' }}>
                                        {{ $province->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('province_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="col-12 mt-3">
                            <h6 class="text-muted mb-3"><i class="fas fa-map-marker-alt"></i> Address Details</h6>
                            <hr>
                        </div>

                        <!-- Street Address -->
                        <div class="col-md-6 mb-3">
                            <label for="street_address" class="form-label fw-bold">Street Address <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="street_address" id="street_address"
                                class="form-control @error('street_address') is-invalid @enderror"
                                value="{{ old('street_address') }}" placeholder="123 Main St" required>
                            @error('street_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- City -->
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label fw-bold">City <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="city" id="city"
                                class="form-control @error('city') is-invalid @enderror" value="{{ old('city') }}"
                                placeholder="City name" required>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="col-12 mt-3">
                            <h6 class="text-muted mb-3"><i class="fas fa-toggle-on"></i> Status</h6>
                            <hr>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label fw-bold">Status <span
                                    class="text-danger">*</span></label>
                            <select name="status" id="status"
                                class="form-select @error('status') is-invalid @enderror" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}"
                                        {{ old('status', 'pending') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Pending: Awaiting approval | Approved: Ready for activation | Active: Fully operational
                            </small>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <hr>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Merchant
                            </button>
                            <a href="{{ route('admin.merchants.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
