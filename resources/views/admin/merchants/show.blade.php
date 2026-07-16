@extends('layouts.admin')

@section('title', 'Merchant Details')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Merchant Details</h1>
                <p class="text-muted small">{{ $merchant->business_name }}</p>
            </div>
            <div>
                <a href="{{ route('admin.merchants.edit', $merchant->merchant_id) }}" class="btn btn-warning text-white me-2">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('admin.merchants.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- Main Info -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <i class="fas fa-store"></i> Business Information
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-building"></i> Business Name:</strong>
                                <p>{{ $merchant->business_name }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-code-branch"></i> Branch Name:</strong>
                                <p>{{ $merchant->branch_name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-envelope"></i> Email:</strong>
                                <p>{{ $merchant->email }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-tag"></i> Category:</strong>
                                <p>{{ $merchant->category->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-map-marker-alt"></i> Address:</strong>
                                <p>{{ $merchant->street_address }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-city"></i> City:</strong>
                                <p>{{ $merchant->city }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-globe"></i> Province:</strong>
                                <p>{{ $merchant->province->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-toggle-on"></i> Status:</strong>
                                <p>@include('admin.merchants.partials.status-badge', [
                                    'status' => $merchant->status,
                                ])</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-md-4">
                <!-- Owner Info -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <i class="fas fa-user"></i> Owner Information
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-block">
                                <i class="fas fa-user-circle fa-3x text-primary"></i>
                            </div>
                        </div>
                        <strong>Name:</strong>
                        <p>{{ $merchant->owner->firstname ?? 'N/A' }} {{ $merchant->owner->lastname ?? '' }}</p>
                        <strong>Email:</strong>
                        <p>{{ $merchant->owner->email ?? 'N/A' }}</p>
                        <strong>Role:</strong>
                        <p><span class="badge bg-info">{{ ucfirst($merchant->owner->role ?? 'N/A') }}</span></p>
                        <a href="#" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-user"></i> View Owner Profile
                        </a>
                    </div>
                </div>

                <!-- Approval Info -->
                <div class="card">
                    <div class="card-header bg-white">
                        <i class="fas fa-clipboard-check"></i> Approval Details
                    </div>
                    <div class="card-body">
                        <strong>Status:</strong>
                        <p>@include('admin.merchants.partials.status-badge', ['status' => $merchant->status])</p>

                        @if ($merchant->approved_by)
                            <strong>Approved By:</strong>
                            <p>{{ $merchant->approver->firstname ?? 'N/A' }} {{ $merchant->approver->lastname ?? '' }}</p>
                            <strong>Approved At:</strong>
                            <p>{{ $merchant->updated_at->format('M d, Y h:i A') }}</p>
                        @else
                            <p class="text-muted">Not yet approved</p>
                        @endif

                        <strong>Created At:</strong>
                        <p>{{ $merchant->created_at->format('M d, Y h:i A') }}</p>
                        <strong>Last Updated:</strong>
                        <p>{{ $merchant->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <i class="fas fa-cog"></i> Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-success"
                                onclick="updateStatus('{{ $merchant->merchant_id }}', 'active')">
                                <i class="fas fa-check"></i> Activate
                            </button>
                            <button type="button" class="btn btn-warning text-white"
                                onclick="updateStatus('{{ $merchant->merchant_id }}', 'suspended')">
                                <i class="fas fa-pause"></i> Suspend
                            </button>
                            <button type="button" class="btn btn-danger"
                                onclick="deleteMerchant('{{ $merchant->merchant_id }}', '{{ addslashes($merchant->business_name) }}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Form -->
    <form id="statusForm" method="POST" action="" style="display: none;">
        @csrf
        @method('PUT')
        <input type="hidden" name="status" id="statusInput">
    </form>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" action="" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function updateStatus(id, status) {
            if (confirm(`Are you sure you want to ${status} this merchant?`)) {
                const form = document.getElementById('statusForm');
                form.action = `/admin/merchants/${id}/status`;
                document.getElementById('statusInput').value = status;
                form.submit();
            }
        }

        function deleteMerchant(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/merchants/${id}`;
                form.submit();
            }
        }
    </script>
@endsection
