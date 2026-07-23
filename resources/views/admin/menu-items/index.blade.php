@extends('layouts.admin')

@section('title', 'Menu Items')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Menu Items</h1>
                <p class="text-muted small">Manage all menu items for merchants</p>
            </div>
            <div>
                <a href="{{ route('admin.menu-items.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Menu Item
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.menu-items.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Search</label>
                        <input type="text" name="search" class="form-control"
                            placeholder="Search by name, description..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Merchant</label>
                        <select name="merchant_id" class="form-select">
                            <option value="">All Merchants</option>
                            @foreach ($merchants as $merchant)
                                <option value="{{ $merchant->merchant_id }}"
                                    {{ request('merchant_id') == $merchant->merchant_id ? 'selected' : '' }}>
                                    {{ $merchant->business_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
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

        <!-- Menu Items Table -->
        <div class="card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">
                        <i class="fas fa-utensils"></i>
                        {{ $menuItems->total() }} Menu Item(s) Found
                    </span>
                    <span class="text-muted small">
                        Showing {{ $menuItems->firstItem() ?? 0 }} - {{ $menuItems->lastItem() ?? 0 }}
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
                                <th>Item</th>
                                <th>Merchant</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center" width="180">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($menuItems as $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input menu-item-checkbox"
                                            value="{{ $item->menu_item_id }}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if ($item->image_url)
                                                <img src="{{ asset('storage/' . $item->image_url) }}"
                                                    alt="{{ $item->name }}"
                                                    style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; margin-right: 10px;">
                                            @else
                                                <div class="bg-secondary bg-opacity-10 rounded p-2 me-2">
                                                    <i class="fas fa-utensils text-secondary"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <strong>{{ $item->name }}</strong>
                                                @if ($item->description)
                                                    <br><small
                                                        class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $item->merchant->business_name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $item->getFormattedPrice() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $item->getStatusBadgeColor() }}">
                                            {{ $item->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $item->created_at->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm w-100">
                                            <a href="{{ route('admin.menu-items.show', $item->menu_item_id) }}"
                                                class="btn btn-info text-white" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.menu-items.edit', $item->menu_item_id) }}"
                                                class="btn btn-warning text-white" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger"
                                                onclick="deleteMenuItem('{{ $item->menu_item_id }}', '{{ addslashes($item->name) }}')"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-utensils-slash fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-0">No menu items found</p>
                                        <a href="{{ route('admin.menu-items.create') }}"
                                            class="btn btn-primary btn-sm mt-2">
                                            <i class="fas fa-plus"></i> Add your first menu item
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <x-pagination :paginator="$menuItems" />
            {{-- @if ($menuItems->hasPages())
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $menuItems->firstItem() ?? 0 }} to {{ $menuItems->lastItem() ?? 0 }}
                            of {{ $menuItems->total() }} results
                        </div>
                        <div>
                            {{ $menuItems->links() }}
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

    <form id="bulkDeleteForm" method="POST" action="{{ route('admin.menu-items.bulk-delete') }}" style="display: none;">
        @csrf
        <input type="hidden" name="ids" id="bulkDeleteIds">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('selectAll').addEventListener('change', function() {
                document.querySelectorAll('.menu-item-checkbox').forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        });

        function deleteMenuItem(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/menu-items/${id}`;
                form.submit();
            }
        }

        function confirmBulkDelete() {
            const selected = document.querySelectorAll('.menu-item-checkbox:checked');
            if (selected.length === 0) {
                alert('Please select at least one menu item to delete.');
                return;
            }

            if (confirm(`Are you sure you want to delete ${selected.length} menu item(s)?`)) {
                const ids = Array.from(selected).map(cb => cb.value);
                document.getElementById('bulkDeleteIds').value = JSON.stringify(ids);
                document.getElementById('bulkDeleteForm').submit();
            }
        }
    </script>
@endsection
