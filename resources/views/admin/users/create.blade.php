@extends('layouts.admin')

@section('title', 'Create User')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Create User</h1>
                <p class="text-muted small">Add a new user to the system</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
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
                <i class="fas fa-user-plus"></i> User Information
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf

                    <div class="row">
                        <!-- Personal Information -->
                        <div class="col-12">
                            <h6 class="text-muted mb-3"><i class="fas fa-user-circle"></i> Personal Details</h6>
                            <hr>
                        </div>

                        <!-- First Name -->
                        <div class="col-md-6 mb-3">
                            <label for="firstname" class="form-label fw-bold">First Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="firstname" id="firstname"
                                class="form-control @error('firstname') is-invalid @enderror" value="{{ old('firstname') }}"
                                placeholder="Enter first name" required>
                            @error('firstname')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        <div class="col-md-6 mb-3">
                            <label for="lastname" class="form-label fw-bold">Last Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="lastname" id="lastname"
                                class="form-control @error('lastname') is-invalid @enderror" value="{{ old('lastname') }}"
                                placeholder="Enter last name" required>
                            @error('lastname')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label fw-bold">Email Address <span
                                    class="text-danger">*</span></label>
                            <input type="email" name="email" id="email"
                                class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                placeholder="user@example.com" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label fw-bold">Phone Number</label>
                            <input type="text" name="phone" id="phone"
                                class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}"
                                placeholder="+1234567890">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-bold">Password <span
                                    class="text-danger">*</span></label>
                            <input type="password" name="password" id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Minimum 8 characters" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Must be at least 8 characters</small>
                        </div>

                        <!-- Password Confirmation -->
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label fw-bold">Confirm Password <span
                                    class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="form-control" placeholder="Confirm password" required>
                        </div>

                        <!-- Role and Status -->
                        <div class="col-12 mt-3">
                            <h6 class="text-muted mb-3"><i class="fas fa-cog"></i> Account Settings</h6>
                            <hr>
                        </div>

                        <!-- Role -->
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label fw-bold">Role <span class="text-danger">*</span></label>
                            <select name="role" id="role" class="form-select @error('role') is-invalid @enderror"
                                required>
                                <option value="">Select Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                                        {{ ucfirst($role) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <span class="badge bg-danger">Admin</span> Full access
                                <span class="badge bg-warning ms-2">Merchant</span> Business access
                                <span class="badge bg-info ms-2">Customer</span> Regular user
                            </small>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label fw-bold">Status <span
                                    class="text-danger">*</span></label>
                            <select name="status" id="status"
                                class="form-select @error('status') is-invalid @enderror" required>
                                <option value="">Select Status</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}"
                                        {{ old('status', 'active') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <span class="badge bg-success">Active</span> Account is active
                                <span class="badge bg-secondary">Inactive</span> Account is disabled
                                <span class="badge bg-danger">Suspended</span> Account is suspended
                            </small>
                        </div>

                        <!-- Email Verification -->
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="email_verified" id="email_verified"
                                    class="form-check-input" value="1" {{ old('email_verified') ? 'checked' : '' }}>
                                <label for="email_verified" class="form-check-label fw-bold">
                                    <i class="fas fa-check-circle text-success"></i> Mark email as verified
                                </label>
                                <br>
                                <small class="text-muted">Skip email verification for this user</small>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <hr>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create User
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password strength indicator (optional)
            const passwordInput = document.getElementById('password');
            const strengthIndicator = document.createElement('div');
            strengthIndicator.className = 'mt-1';
            strengthIndicator.id = 'passwordStrength';
            passwordInput.parentNode.appendChild(strengthIndicator);

            passwordInput.addEventListener('input', function() {
                const value = this.value;
                let strength = '';
                let color = '';

                if (value.length === 0) {
                    strength = '';
                    color = '';
                } else if (value.length < 8) {
                    strength = 'Weak - Too short';
                    color = 'text-danger';
                } else if (value.length < 12) {
                    strength = 'Medium - Good';
                    color = 'text-warning';
                } else {
                    strength = 'Strong - Excellent';
                    color = 'text-success';
                }

                strengthIndicator.innerHTML = `<small class="${color}">${strength}</small>`;
            });
        });
    </script>
@endsection
