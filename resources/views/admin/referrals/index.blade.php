@extends('layouts.admin')

@section('title', 'Referrals')
{{-- @section('page-title', 'Referral Program')
@section('page-description', 'Manage customer referrals and rewards') --}}

@section('content')

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Total Referrals</span>
                    <i class="fas fa-users text-purple"></i>
                </div>
                <h3 class="stat-value">{{ number_format($stats['total']) }}</h3>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Pending Review</span>
                    <i class="fas fa-clock text-warning"></i>
                </div>
                <h3 class="stat-value text-warning">{{ number_format($stats['pending'] + $stats['qualified']) }}</h3>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Approved</span>
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <h3 class="stat-value text-success">{{ number_format($stats['approved']) }}</h3>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Total Rewards Paid</span>
                    <i class="fas fa-peso-sign text-warning"></i>
                </div>
                <h3 class="stat-value text-danger">₱{{ number_format($stats['total_rewards_paid'], 2) }}</h3>
                <small class="text-success">₱{{ number_format($stats['total_pending_rewards'], 2) }} pending</small>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute text-muted"
                            style="left: 12px; top: 50%; transform: translateY(-50%);"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control ps-4"
                            placeholder="Search referrer or referee...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="all">All Status</option>
                        @foreach (['pending' => 'Pending', 'qualified' => 'Qualified', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $val => $label)
                            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>
                                {{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-purple w-100"><i class="fas fa-filter"></i></button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk Actions --}}
    <div id="bulk-bar" class="alert alert-warning d-none d-flex align-items-center justify-content-between">
        <span><strong id="bulk-count">0</strong> referrals selected</span>
        <form method="POST" action="{{ route('admin.referrals.bulk-approve') }}" id="bulk-form">
            @csrf
            <div id="bulk-ids"></div>
            <button type="submit" class="btn btn-success btn-sm"
                onclick="return confirm('Approve selected referrals and credit wallets?')">
                <i class="fas fa-check me-1"></i> Approve Selected
            </button>
        </form>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="select-all" class="form-check-input">
                        </th>
                        <th>Date</th>
                        <th>Referrer</th>
                        <th>Referee</th>
                        <th>Reward</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referrals as $referral)
                        <tr>
                            <td>
                                @if (in_array($referral->status, ['pending', 'qualified']))
                                    <input type="checkbox" class="row-checkbox form-check-input"
                                        value="{{ $referral->referral_id }}">
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold small">{{ $referral->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $referral->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold small">{{ $referral->referrer->firstname }}
                                    {{ $referral->referrer->lastname }}</div>
                                <small class="text-muted">{{ $referral->referrer->email }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold small">{{ $referral->referee->firstname }}
                                    {{ $referral->referee->lastname }}</div>
                                <small class="text-muted">{{ $referral->referee->email }}</small>
                            </td>
                            <td>
                                <span class="fw-bold text-success">₱{{ number_format($referral->reward_amount, 2) }}</span>
                            </td>
                            <td>
                                @php $badge = $referral->status_badge; @endphp
                                <span
                                    class="badge-soft badge-soft-{{ $badge['color'] === 'info' ? 'info' : $badge['color'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.referrals.show', $referral->referral_id) }}"
                                    class="btn btn-sm btn-light">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-users text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-0">No referrals found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($referrals->hasPages())
            <div class="card-footer bg-white">
                {{ $referrals->withQueryString()->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const selectAll = document.getElementById('select-all');
                const checkboxes = document.querySelectorAll('.row-checkbox');
                const bulkBar = document.getElementById('bulk-bar');
                const bulkCount = document.getElementById('bulk-count');
                const bulkIds = document.getElementById('bulk-ids');

                function updateBulk() {
                    const checked = Array.from(checkboxes).filter(c => c.checked);
                    if (checked.length === 0) {
                        bulkBar.classList.add('d-none');
                    } else {
                        bulkBar.classList.remove('d-none');
                        bulkCount.textContent = checked.length;
                        bulkIds.innerHTML = '';
                        checked.forEach(c => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'ids[]';
                            input.value = c.value;
                            bulkIds.appendChild(input);
                        });
                    }
                }

                if (selectAll) {
                    selectAll.addEventListener('change', function() {
                        checkboxes.forEach(c => c.checked = selectAll.checked);
                        updateBulk();
                    });
                }
                checkboxes.forEach(c => c.addEventListener('change', updateBulk));
            });
        </script>
    @endpush

@endsection
