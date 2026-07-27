@extends('layouts.admin')

@section('title', 'Merchant Management')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Merchants</h1>
                <p class="text-muted small">Manage all merchant accounts</p>
            </div>
            <div>
                <a href="{{ route('admin.merchants.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Merchant
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.merchants.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Search</label>
                        <input type="text" name="search" class="form-control"
                            placeholder="Search by business name, email, city..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->category_id }}"
                                    {{ request('category') == $category->category_id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                    {{-- <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('admin.merchants.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div> --}}
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger w-100" id="bulkDeleteBtn" onclick="confirmBulkDelete()"
                            title="Delete selected">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Merchants Table -->
        <div class="card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">
                        <i class="fas fa-store"></i>
                        {{ $merchants->total() }} Merchant(s) Found
                    </span>
                    <span class="text-muted small">
                        Showing {{ $merchants->firstItem() ?? 0 }} - {{ $merchants->lastItem() ?? 0 }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Business</th>
                                <th>Owner</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center" width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($merchants as $merchant)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input merchant-checkbox"
                                            value="{{ $merchant->merchant_id }}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                                <i class="fas fa-store text-primary"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $merchant->business_name }}</strong>
                                                @if ($merchant->branch_name)
                                                    <br><small class="text-muted">
                                                        <i class="fas fa-building"></i> {{ $merchant->branch_name }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="fw-bold">
                                                {{ $merchant->owner->firstname ?? 'N/A' }}
                                                {{ $merchant->owner->lastname ?? '' }}
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope"></i> {{ $merchant->email }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <i class="fas fa-tag"></i> {{ $merchant->category->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <small>
                                                <i class="fas fa-map-marker-alt text-danger"></i>
                                                {{ $merchant->city }}
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                {{ $merchant->province->name ?? 'N/A' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        @include('admin.merchants.partials.status-badge', [
                                            'status' => $merchant->status,
                                        ])
                                        @if ($merchant->approved_by)
                                            <br>
                                            <small class="text-muted">
                                                Approved by: {{ $merchant->approver->firstname ?? 'N/A' }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            <i class="fas fa-calendar-alt"></i>
                                            {{ $merchant->created_at->format('M d, Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm w-100">
                                            <a href="{{ route('admin.merchants.menu', $merchant->merchant_id) }}"
                                                class="btn btn-info text-white" title="View Menu & Inventory">
                                                <i class="fas fa-utensils"></i> Menu
                                            </a>
                                            <a href="{{ route('admin.merchants.show', $merchant->merchant_id) }}"
                                                class="btn btn-info text-white" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.merchants.edit', $merchant->merchant_id) }}"
                                                class="btn btn-warning text-white" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger"
                                                onclick="deleteMerchant('{{ $merchant->merchant_id }}', '{{ addslashes($merchant->business_name) }}')"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-store-slash fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-0">No merchants found</p>
                                        <a href="{{ route('admin.merchants.create') }}"
                                            class="btn btn-primary btn-sm mt-2">
                                            <i class="fas fa-plus"></i> Create your first merchant
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <x-pagination :paginator="$merchants" />
            {{-- @if ($merchants->hasPages())
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $merchants->firstItem() ?? 0 }} to {{ $merchants->lastItem() ?? 0 }}
                            of {{ $merchants->total() }} results
                        </div>
                        <div>
                            {{ $merchants->links() }}
                        </div>
                    </div>
                </div>
            @endif --}}
        </div>
    </div>

    <!-- Hidden Forms -->
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <form id="bulkDeleteForm" method="POST" action="{{ route('admin.merchants.bulk-delete') }}"
        style="display: none;">
        @csrf
        <input type="hidden" name="ids" id="bulkDeleteIds">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select All checkbox
            document.getElementById('selectAll').addEventListener('change', function() {
                document.querySelectorAll('.merchant-checkbox').forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            // Individual checkbox - update select all state
            document.querySelectorAll('.merchant-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = document.querySelectorAll('.merchant-checkbox:checked')
                        .length ===
                        document.querySelectorAll('.merchant-checkbox').length;
                    document.getElementById('selectAll').checked = allChecked;
                });
            });
        });

        // Delete single merchant
        function deleteMerchant(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/merchants/${id}`;
                form.submit();
            }
        }

        // Bulk delete
        function confirmBulkDelete() {
            const selected = document.querySelectorAll('.merchant-checkbox:checked');
            if (selected.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select at least one merchant to delete.',
                });
                return;
            }

            if (confirm(`Are you sure you want to delete ${selected.length} merchant(s)? This action cannot be undone.`)) {
                const ids = Array.from(selected).map(cb => cb.value);
                document.getElementById('bulkDeleteIds').value = JSON.stringify(ids);
                document.getElementById('bulkDeleteForm').submit();
            }
        }
    </script>
@endsection
