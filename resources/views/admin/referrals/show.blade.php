@extends('layouts.admin')

@section('title', 'Referral Details')
{{-- @section('page-title', 'Referral Details')
@section('page-description', $referral->referral_uuid) --}}

@section('content')

    <div class="d-flex justify-content-between mb-4">
        <a href="{{ route('admin.referrals.index') }}" class="text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>

        @if (in_array($referral->status, ['pending', 'qualified']))
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                    <i class="fas fa-check me-1"></i> Approve & Credit ₱{{ number_format($referral->reward_amount, 2) }}
                </button>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fas fa-times me-1"></i> Reject
                </button>
            </div>
        @endif
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">Referral Information</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Referral ID</dt>
                        <dd class="col-sm-8 font-monospace small">{{ $referral->referral_uuid }}</dd>

                        <dt class="col-sm-4 text-muted">Referral Code</dt>
                        <dd class="col-sm-8"><code>{{ $referral->referral_code }}</code></dd>

                        <dt class="col-sm-4 text-muted">Status</dt>
                        <dd class="col-sm-8">
                            @php $badge = $referral->status_badge; @endphp
                            <span class="badge-soft badge-soft-{{ $badge['color'] === 'info' ? 'info' : $badge['color'] }}">
                                {{ $badge['label'] }}
                            </span>
                        </dd>

                        <dt class="col-sm-4 text-muted">Reward Amount</dt>
                        <dd class="col-sm-8"><strong
                                class="text-success fs-5">₱{{ number_format($referral->reward_amount, 2) }}</strong></dd>

                        <dt class="col-sm-4 text-muted">Created</dt>
                        <dd class="col-sm-8">{{ $referral->created_at->format('M d, Y H:i:s') }}</dd>

                        @if ($referral->purchase_reference)
                            <dt class="col-sm-4 text-muted">Purchase Ref</dt>
                            <dd class="col-sm-8 font-monospace">{{ $referral->purchase_reference }}</dd>

                            <dt class="col-sm-4 text-muted">Purchase Amount</dt>
                            <dd class="col-sm-8">₱{{ number_format($referral->purchase_amount, 2) }}</dd>

                            <dt class="col-sm-4 text-muted">Verified At</dt>
                            <dd class="col-sm-8">{{ $referral->purchase_verified_at?->format('M d, Y H:i') ?? '—' }}</dd>
                        @endif

                        @if ($referral->approved_at)
                            <dt class="col-sm-4 text-muted">Approved</dt>
                            <dd class="col-sm-8">
                                {{ $referral->approved_at->format('M d, Y H:i') }}
                                @if ($referral->approvedBy)
                                    <small class="text-muted">by {{ $referral->approvedBy->firstname }}</small>
                                @endif
                            </dd>
                        @endif

                        @if ($referral->rejection_reason)
                            <dt class="col-sm-4 text-muted">Rejection Reason</dt>
                            <dd class="col-sm-8 text-danger">{{ $referral->rejection_reason }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><i class="fas fa-user-check text-success me-2"></i>Referrer</div>
                        <div class="card-body">
                            <div class="fw-bold">{{ $referral->referrer->firstname }} {{ $referral->referrer->lastname }}
                            </div>
                            <div class="text-muted small">{{ $referral->referrer->email }}</div>
                            <div class="text-muted small">Code: <code>{{ $referral->referrer->referral_code }}</code></div>
                            <hr>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Total Referrals</span>
                                <strong>{{ $referral->referrer->total_referrals }}</strong>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Lifetime Earnings</span>
                                <strong
                                    class="text-success">₱{{ number_format($referral->referrer->total_referral_earnings, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><i class="fas fa-user-plus text-primary me-2"></i>Referee</div>
                        <div class="card-body">
                            <div class="fw-bold">{{ $referral->referee->firstname }} {{ $referral->referee->lastname }}
                            </div>
                            <div class="text-muted small">{{ $referral->referee->email }}</div>
                            <div class="text-muted small">Joined: {{ $referral->referee->created_at->format('M d, Y') }}
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Account Status</span>
                                <strong>{{ ucfirst($referral->referee->status) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">Fraud Info</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">IP Address</dt>
                        <dd class="col-7 font-monospace">{{ $referral->ip_address ?? '—' }}</dd>

                        <dt class="col-5 text-muted">Device</dt>
                        <dd class="col-7 text-truncate">{{ $referral->device_fingerprint ?? '—' }}</dd>

                        <dt class="col-5 text-muted">User Agent</dt>
                        <dd class="col-7 small text-truncate" title="{{ $referral->user_agent }}">
                            {{ Str::limit($referral->user_agent, 40) ?? '—' }}
                        </dd>
                    </dl>
                </div>
            </div>

            @if ($referral->walletTransaction)
                <div class="card">
                    <div class="card-header">Wallet Credit</div>
                    <div class="card-body small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Amount</span>
                            <strong
                                class="text-success">+₱{{ number_format($referral->walletTransaction->amount, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Balance After</span>
                            <strong>₱{{ number_format($referral->walletTransaction->balance_after, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Date</span>
                            <span>{{ $referral->walletTransaction->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.referrals.approve', $referral->referral_id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Referral</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>This will credit <strong
                                class="text-success">₱{{ number_format($referral->reward_amount, 2) }}</strong> to
                            <strong>{{ $referral->referrer->firstname }}'s</strong> wallet.
                        </p>
                        <p class="text-muted small mb-0">The reward will be immediately available for withdrawal.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> Approve & Credit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.referrals.reject', $referral->referral_id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Referral</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Reason *</label>
                        <textarea name="reason" rows="3" class="form-control" required
                            placeholder="e.g., Duplicate account, fraud detected..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
