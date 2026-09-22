{{-- resources/views/admin/settings/credits.blade.php --}}
@extends('layouts.admin')

@section('title', 'Credit Settings')
@section('page-title', 'Credit System Settings')
@section('page-description', 'Configure credit bonuses, costs, and refunds')

@section('content')
    <form method="POST" action="{{ route('admin.settings.credits.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">

                {{-- Welcome Bonus --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-warning-subtle rounded d-flex align-items-center justify-content-center"
                                style="width: 44px; height: 44px;">
                                <i class="fas fa-gift text-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Welcome Bonus</h6>
                                <small class="text-muted">Free credits given on merchant signup</small>
                            </div>
                        </div>

                        <label class="form-label">Welcome Bonus Credits</label>
                        <input type="number" name="settings[credits.welcome_bonus]" min="0"
                            value="{{ $settings['credits.welcome_bonus']['value'] ?? 10 }}" class="form-control"
                            style="max-width: 200px;">
                        <small class="text-muted">Amount of free credits new merchants receive</small>
                    </div>
                </div>

                {{-- Voucher Costs --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-purple-light rounded d-flex align-items-center justify-content-center"
                                style="width: 44px; height: 44px;">
                                <i class="fas fa-ticket-alt text-purple"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Voucher Costs</h6>
                                <small class="text-muted">Credits required per voucher type</small>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Basic Voucher</label>
                                <input type="number" name="settings[credits.basic_cost]" min="1"
                                    value="{{ $settings['credits.basic_cost']['value'] ?? 1 }}" class="form-control">
                                <small class="text-muted">Standard discount</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Featured Voucher</label>
                                <input type="number" name="settings[credits.featured_cost]" min="1"
                                    value="{{ $settings['credits.featured_cost']['value'] ?? 2 }}" class="form-control">
                                <small class="text-muted">Highlighted placement</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Priority Voucher</label>
                                <input type="number" name="settings[credits.priority_cost]" min="1"
                                    value="{{ $settings['credits.priority_cost']['value'] ?? 5 }}" class="form-control">
                                <small class="text-muted">Top placement</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Refund Policy --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-success-subtle rounded d-flex align-items-center justify-content-center"
                                style="width: 44px; height: 44px;">
                                <i class="fas fa-undo text-success"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Refund Policy</h6>
                                <small class="text-muted">Credit refund on promotion cancellation</small>
                            </div>
                        </div>

                        <label class="form-label">Refund Percentage</label>
                        <div class="input-group" style="max-width: 200px;">
                            <input type="number" name="settings[credits.refund_percentage]" min="0" max="100"
                                value="{{ $settings['credits.refund_percentage']['value'] ?? 100 }}" class="form-control">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Credits refunded when a promotion is cancelled or expires unused</small>
                    </div>
                </div>

                {{-- Expiry --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-danger-subtle rounded d-flex align-items-center justify-content-center"
                                style="width: 44px; height: 44px;">
                                <i class="fas fa-clock text-danger"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Credit Expiry</h6>
                                <small class="text-muted">How long credits remain valid</small>
                            </div>
                        </div>

                        <label class="form-label">Expiry (Days)</label>
                        <input type="number" name="settings[credits.expire_days]" min="0"
                            value="{{ $settings['credits.expire_days']['value'] ?? 365 }}" class="form-control"
                            style="max-width: 200px;">
                        <small class="text-muted">Set to 0 for never expire</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card position-sticky" style="top: 90px;">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Save Changes</h6>
                        <p class="text-muted small">Apply the new credit configuration.</p>
                        <button type="submit" class="btn btn-purple w-100">
                            <i class="fas fa-save me-1"></i> Save Credit Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
