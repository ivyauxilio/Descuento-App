@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">User Details</h1>
                <p class="text-muted small">{{ $user->email }}</p>
            </div>
            <div>
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning text-white me-2">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Main Info -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <i class="fas fa-user-circle"></i> Personal Information
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-user"></i> Full Name:</strong>
                                <p>{{ $user->firstname }} {{ $user->lastname }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-envelope"></i> Email:</strong>
                                <p>{{ $user->email }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-phone"></i> Phone:</strong>
                                <p>{{ $user->phone ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-id-badge"></i> UUID:</strong>
                                <p><code>{{ $user->uuid }}</code></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-tag"></i> Role:</strong>
                                <p>
                                    <span
                                        class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'merchant' ? 'warning' : 'info') }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-toggle-on"></i> Status:</strong>
                                <p>
                                    @include('admin.users.partials.status-badge', [
                                        'status' => $user->status,
                                    ])
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-check-circle"></i> Email Verified:</strong>
                                <p>
                                    @if ($user->email_verified_at)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Verified at
                                            {{ $user->email_verified_at->format('M d, Y h:i A') }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times"></i> Not Verified
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-calendar-alt"></i> Account Created:</strong>
                                <p>{{ $user->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-clock"></i> Last Updated:</strong>
                                <p>{{ $user->updated_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-trash"></i> Deleted At:</strong>
                                <p>{{ $user->deleted_at ? $user->deleted_at->format('M d, Y h:i A') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-md-4">
                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <i class="fas fa-chart-bar"></i> Quick Stats
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Member Since:</span>
                            <strong>{{ $user->created_at->format('M Y') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Last Update:</span>
                            <strong>{{ $user->updated_at->diffForHumans() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Account Status:</span>
                            @include('admin.users.partials.status-badge', ['status' => $user->status])
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header bg-white">
                        <i class="fas fa-cog"></i> Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning text-white">
                                <i class="fas fa-edit"></i> Edit User
                            </a>
                            <button type="button" class="btn btn-info text-white"
                                onclick="toggleVerification('{{ $user->id }}')">
                                <i class="fas fa-envelope"></i>
                                {{ $user->email_verified_at ? 'Unverify Email' : 'Verify Email' }}
                            </button>
                            @if ($user->id !== auth()->id())
                                <button type="button" class="btn btn-danger"
                                    onclick="deleteUser('{{ $user->id }}', '{{ addslashes($user->firstname . ' ' . $user->lastname) }}')">
                                    <i class="fas fa-trash"></i> Delete User
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Forms -->
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <form id="verifyForm" method="POST" action="{{ route('admin.users.toggle-verification', $user->id) }}"
        style="display: none;">
        @csrf
        @method('PUT')
    </form>

    <script>
        function deleteUser(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/users/${id}`;
                form.submit();
            }
        }

        function toggleVerification(id) {
            if (confirm('Are you sure you want to toggle email verification status?')) {
                document.getElementById('verifyForm').submit();
            }
        }
    </script>
@endsection
