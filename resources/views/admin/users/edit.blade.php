@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Edit User</h1>
                <p class="text-muted small">Update user information</p>
            </div>
            <div>
                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info text-white me-2">
                    <i class="fas fa-eye"></i> View
                </a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
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
                <i class="fas fa-user-edit"></i> Edit User: {{ $user->firstname }} {{ $user->lastname }}
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                    @csrf
                    @method('PUT')

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
                                class="form-control @error('firstname') is-invalid @enderror"
                                value="{{ old('firstname', $user->firstname) }}" required>
                            @error('firstname')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        <div class="col-md-6 mb-3">
                            <label for="lastname" class="form-label fw-bold">Last Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="lastname" id="lastname"
                                class="form-control @error('lastname') is-invalid @enderror"
                                value="{{ old('lastname', $user->lastname) }}" required>
                            @error('lastname')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label fw-bold">Email Address <span
                                    class="text-danger">*</span></label>
                            <input type="email" name="email" id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label fw-bold">Phone Number</label>
                            <input type="text" name="phone" id="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $user->phone) }}" placeholder="+1234567890">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password (Optional) -->
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-bold">Password <span
                                    class="text-muted">(Optional)</span></label>
                            <input type="password" name="password" id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Leave blank to keep current password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Only fill this field if you want to change the password</small>
                        </div>

                        <!-- Password Confirmation -->
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label fw-bold">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="form-control" placeholder="Confirm new password">
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
                                    <option value="{{ $role }}"
                                        {{ old('role', $user->role) == $role ? 'selected' : '' }}>
                                        {{ ucfirst($role) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                        {{ old('status', $user->status) == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email Verification -->
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="email_verified" id="email_verified"
                                    class="form-check-input" value="1"
                                    {{ old('email_verified', $user->email_verified_at) ? 'checked' : '' }}>
                                <label for="email_verified" class="form-check-label fw-bold">
                                    <i class="fas fa-check-circle text-success"></i> Mark email as verified
                                </label>
                                <br>
                                <small class="text-muted">
                                    Currently: @if ($user->email_verified_at)
                                        <span class="text-success">Verified</span>
                                    @else
                                        <span class="text-muted">Not Verified</span>
                                    @endif
                                </small>
                            </div>
                        </div>

                        <!-- Account Info -->
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt"></i> Created:
                                    {{ $user->created_at->format('M d, Y h:i A') }}
                                    <br>
                                    <i class="fas fa-clock"></i> Last updated:
                                    {{ $user->updated_at->format('M d, Y h:i A') }}
                                    <br>
                                    <i class="fas fa-id-badge"></i> UUID: {{ $user->uuid }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <hr>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update User
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
@endsection
