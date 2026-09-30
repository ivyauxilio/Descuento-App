@extends('layouts.admin')

@section('title', 'Points Generated')
{{-- @section('page-title', 'Points Generated Log')
@section('page-description', 'All wallet transactions across customers') --}}

@section('content')

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Total Transactions</span>
                    <i class="fas fa-receipt text-purple"></i>
                </div>
                <h3 class="stat-value">{{ number_format($stats['total_transactions']) }}</h3>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Total Credited</span>
                    <i class="fas fa-arrow-up text-success"></i>
                </div>
                <h3 class="stat-value text-success">₱{{ number_format($stats['total_credited'], 2) }}</h3>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Total Withdrawn</span>
                    <i class="fas fa-arrow-down text-danger"></i>
                </div>
                <h3 class="stat-value text-danger">₱{{ number_format($stats['total_withdrawn'], 2) }}</h3>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Pending Withdrawals</span>
                    <i class="fas fa-clock text-warning"></i>
                </div>
                <h3 class="stat-value text-warning">₱{{ number_format($stats['total_pending_withdrawals'], 2) }}</h3>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Wallet Transactions</span>
            <form method="GET" class="d-flex gap-2">
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all">All Types</option>
                    @foreach (['referral_reward' => 'Referral Reward', 'withdrawal' => 'Withdrawal', 'adjustment' => 'Adjustment', 'reversal' => 'Reversal'] as $val => $label)
                        <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>
                            {{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Balance</th>
                        <th>Payment</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                        <tr>
                            <td>
                                <div class="small fw-semibold">{{ $txn->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $txn->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="small fw-semibold">{{ $txn->user->firstname }} {{ $txn->user->lastname }}</div>
                                <small class="text-muted">{{ $txn->user->email }}</small>
                            </td>
                            <td>
                                @php $badge = $txn->type_badge; @endphp
                                <span class="badge-soft badge-soft-{{ $badge['color'] }}">{{ $badge['label'] }}</span>
                            </td>
                            <td>
                                <span class="fw-bold {{ $txn->amount > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $txn->amount > 0 ? '+' : '' }}₱{{ number_format(abs($txn->amount), 2) }}
                                </span>
                            </td>
                            <td class="small">₱{{ number_format($txn->balance_after, 2) }}</td>
                            <td>
                                @if ($txn->payment_status)
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'processing' => 'info',
                                            'paid' => 'success',
                                            'failed' => 'danger',
                                        ];
                                        $c = $statusColors[$txn->payment_status] ?? 'secondary';
                                    @endphp
                                    <span
                                        class="badge-soft badge-soft-{{ $c }}">{{ ucfirst($txn->payment_status) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($txn->type === 'withdrawal' && $txn->payment_status === 'pending')
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                        data-bs-target="#confirmModal{{ $txn->transaction_id }}">
                                        <i class="fas fa-check"></i> Confirm
                                    </button>
                                @endif
                            </td>
                        </tr>

                        @if ($txn->type === 'withdrawal' && $txn->payment_status === 'pending')
                            <div class="modal fade" id="confirmModal{{ $txn->transaction_id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST"
                                            action="{{ route('admin.wallet-transactions.confirm', $txn->transaction_id) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirm Withdrawal</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Customer: <strong>{{ $txn->user->firstname }}
                                                        {{ $txn->user->lastname }}</strong></p>
                                                <p>Amount: <strong
                                                        class="text-danger">₱{{ number_format(abs($txn->amount), 2) }}</strong>
                                                </p>
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select" required>
                                                        <option value="paid">Mark as Paid</option>
                                                        <option value="failed">Mark as Failed (refund wallet)</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Payment Reference</label>
                                                    <input type="text" name="payment_reference" class="form-control"
                                                        placeholder="GCash ref #">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Confirm</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No transactions found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($transactions->hasPages())
            <div class="card-footer bg-white">{{ $transactions->withQueryString()->links() }}</div>
        @endif
    </div>

@endsection
