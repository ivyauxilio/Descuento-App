{{-- resources/views/admin/transactions/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Transaction Details')
@section('page-title', 'Transaction Details')
@section('page-description', $transaction->transaction_uuid)

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('admin.transactions.index') }}" class="text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i> Back to Transactions
        </a>

        <div class="d-flex gap-2">
            @if (
                $transaction->type === 'purchase' &&
                    $transaction->payment_status === 'paid' &&
                    empty($transaction->metadata['refunded']))
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#refundModal">
                    <i class="fas fa-undo me-1"></i> Refund
                </button>
            @endif

            @if (empty($transaction->metadata['reversed']))
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                    data-bs-target="#reverseModal">
                    <i class="fas fa-rotate-left me-1"></i> Reverse
                </button>
            @endif

            @if ($transaction->payment_status !== 'paid')
                <form method="POST" action="{{ route('admin.transactions.destroy', $transaction->transaction_id) }}"
                    onsubmit="return confirm('Delete this transaction?')" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Status badges --}}
    <div class="mb-3 d-flex flex-wrap gap-2">
        @php
            $typeStyles = [
                'purchase' => 'badge-soft-info',
                'usage' => 'badge-soft-danger',
                'refund' => 'badge-soft-orange',
                'bonus' => 'badge-soft-warning',
                'adjustment' => 'badge-soft-primary',
            ];
        @endphp
        <span class="badge-soft {{ $typeStyles[$transaction->type] ?? 'badge-soft-secondary' }}">
            {{ strtoupper($transaction->type) }}
        </span>
        @if ($transaction->payment_status)
            <span class="badge-soft badge-soft-secondary">{{ ucfirst($transaction->payment_status) }}</span>
        @endif
        @if (!empty($transaction->metadata['refunded']))
            <span class="badge-soft badge-soft-orange">REFUNDED</span>
        @endif
        @if (!empty($transaction->metadata['reversed']))
            <span class="badge-soft badge-soft-danger">REVERSED</span>
        @endif
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Main Card --}}
            <div class="card mb-4 overflow-hidden">
                <div class="plan-card-header text-start">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-white-50 small mb-1">Credits Change</div>
                            <div class="display-4 fw-bold {{ $transaction->credits > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $transaction->credits > 0 ? '+' : '' }}{{ $transaction->credits }}
                            </div>
                            <div class="text-white-50 small mt-2">
                                Balance: {{ $transaction->balance_before }} → {{ $transaction->balance_after }}
                            </div>
                        </div>
                        @if ($transaction->amount != 0)
                            <div class="text-end">
                                <div class="text-white-50 small mb-1">Amount</div>
                                <div class="h3 fw-bold mb-0">
                                    {{ $transaction->amount > 0 ? '+' : '' }}₱{{ number_format(abs($transaction->amount), 2) }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">Transaction Info</h6>
                            <dl class="row mb-0">
                                <dt class="col-5 text-muted small">Transaction ID</dt>
                                <dd class="col-7 small font-monospace">{{ $transaction->transaction_uuid }}</dd>

                                <dt class="col-5 text-muted small">Reference #</dt>
                                <dd class="col-7 small font-monospace">{{ $transaction->reference_number ?? '—' }}</dd>

                                <dt class="col-5 text-muted small">Date</dt>
                                <dd class="col-7 small">{{ $transaction->created_at->format('M d, Y H:i:s') }}</dd>

                                <dt class="col-5 text-muted small">Currency</dt>
                                <dd class="col-7 small">{{ $transaction->currency ?? 'PHP' }}</dd>
                            </dl>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">Payment Info</h6>
                            <dl class="row mb-0">
                                <dt class="col-5 text-muted small">Method</dt>
                                <dd class="col-7 small">{{ $transaction->payment_method ?? '—' }}</dd>

                                <dt class="col-5 text-muted small">Payment Ref</dt>
                                <dd class="col-7 small font-monospace">{{ $transaction->payment_reference ?? '—' }}</dd>

                                <dt class="col-5 text-muted small">Status</dt>
                                <dd class="col-7 small">{{ ucfirst($transaction->payment_status ?? 'N/A') }}</dd>

                                <dt class="col-5 text-muted small">Plan</dt>
                                <dd class="col-7 small">{{ $transaction->plan->name ?? '—' }}</dd>
                            </dl>
                        </div>

                        @if ($transaction->description)
                            <div class="col-12">
                                <h6 class="text-muted text-uppercase small fw-bold mb-2">Description</h6>
                                <div class="bg-light rounded p-3 small">{{ $transaction->description }}</div>
                            </div>
                        @endif

                        @if ($transaction->metadata)
                            <div class="col-12">
                                <h6 class="text-muted text-uppercase small fw-bold mb-2">Metadata</h6>
                                <pre class="bg-dark text-success rounded p-3 small mb-0" style="max-height: 300px; overflow: auto;">{{ json_encode($transaction->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Merchant Card --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-store me-2 text-purple"></i> Merchant
                </div>
                <div class="card-body">
                    <div class="fw-bold">{{ $transaction->merchant->business_name ?? 'N/A' }}</div>
                    <div class="text-muted small mb-3">{{ $transaction->merchant->email ?? '' }}</div>

                    @if ($wallet)
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Current Balance</span>
                            <span class="fw-bold text-purple fs-5">{{ $wallet->credit_balance }} credits</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Before --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-history me-2 text-purple"></i> Before
                </div>
                <div class="list-group list-group-flush">
                    @forelse($before as $b)
                        <a href="{{ route('admin.transactions.show', $b->transaction_id) }}"
                            class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">{{ $b->created_at->format('M d, H:i') }}</small>
                                <small class="fw-bold {{ $b->credits > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $b->credits > 0 ? '+' : '' }}{{ $b->credits }}
                                </small>
                            </div>
                            <div class="small text-truncate">{{ $b->description }}</div>
                        </a>
                    @empty
                        <div class="list-group-item text-center text-muted small py-3">No prior transactions</div>
                    @endforelse
                </div>
            </div>

            {{-- After --}}
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clock me-2 text-purple"></i> After
                </div>
                <div class="list-group list-group-flush">
                    @forelse($after as $a)
                        <a href="{{ route('admin.transactions.show', $a->transaction_id) }}"
                            class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">{{ $a->created_at->format('M d, H:i') }}</small>
                                <small class="fw-bold {{ $a->credits > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $a->credits > 0 ? '+' : '' }}{{ $a->credits }}
                                </small>
                            </div>
                            <div class="small text-truncate">{{ $a->description }}</div>
                        </a>
                    @empty
                        <div class="list-group-item text-center text-muted small py-3">No later transactions</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Refund Modal --}}
    <div class="modal fade" id="refundModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.transactions.refund', $transaction->transaction_id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Refund Transaction</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">
                            Refund up to <strong>{{ abs($transaction->credits) }} credits</strong> to this merchant.
                        </p>
                        <div class="mb-3">
                            <label class="form-label">Credits to Refund</label>
                            <input type="number" name="refund_credits" min="1"
                                max="{{ abs($transaction->credits) }}" value="{{ abs($transaction->credits) }}" required
                                class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason *</label>
                            <textarea name="reason" rows="3" required maxlength="500" class="form-control"
                                placeholder="Reason for refund..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Confirm Refund</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reverse Modal --}}
    <div class="modal fade" id="reverseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.transactions.reverse', $transaction->transaction_id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reverse Transaction</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">
                            This will create an opposite transaction to cancel out
                            <strong>{{ $transaction->credits }} credits</strong>.
                        </p>
                        <div class="mb-3">
                            <label class="form-label">Reason *</label>
                            <textarea name="reason" rows="3" required maxlength="500" class="form-control"
                                placeholder="Reason for reversal..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Reversal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
