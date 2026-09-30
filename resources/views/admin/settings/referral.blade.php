@extends('layouts.admin')

@section('title', 'Referral Settings')
{{-- @section('page-title', 'Referral Program Settings')
@section('page-description', 'Configure how much customers earn for referrals') --}}

@section('content')
    <form method="POST" action="{{ route('admin.settings.referral.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">

                {{-- Enable/Disable --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold mb-1">Referral System</h6>
                                <p class="text-muted small mb-0">Enable or disable the referral program</p>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="settings[referral.enabled]" value="0">
                                <input type="checkbox" class="form-check-input" style="width: 3rem; height: 1.5rem;"
                                    name="settings[referral.enabled]" value="1"
                                    {{ $settings['referral.enabled']['value'] ?? false ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Rewards --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-gift text-purple me-2"></i>Reward Amounts
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Referrer Reward (₱)</label>
                                <input type="number" name="settings[referral.referrer_reward]" step="0.01"
                                    min="0" value="{{ $settings['referral.referrer_reward']['value'] ?? 50 }}"
                                    class="form-control">
                                <small class="text-muted">
                                    What the referrer earns when their referral activates a card
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Referee Welcome Reward (₱)</label>
                                <input type="number" name="settings[referral.referee_reward]" step="0.01" min="0"
                                    value="{{ $settings['referral.referee_reward']['value'] ?? 25 }}" class="form-control">
                                <small class="text-muted">
                                    Instant welcome credit for the new customer
                                </small>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Example:</strong>
                            Maria refers Juan → Juan gets
                            <strong>₱{{ number_format($settings['referral.referee_reward']['value'] ?? 25, 2) }}</strong>
                            instantly.
                            When Juan activates his card, Maria earns
                            <strong>₱{{ number_format($settings['referral.referrer_reward']['value'] ?? 50, 2) }}</strong>.
                        </div>
                    </div>
                </div>

                {{-- Qualification --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-check-circle text-purple me-2"></i>Qualification Rules
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Qualify Trigger</label>
                            <select name="settings[referral.qualify_on]" class="form-select">
                                <option value="card_activation"
                                    {{ ($settings['referral.qualify_on']['value'] ?? '') === 'card_activation' ? 'selected' : '' }}>
                                    Card Activation (Recommended) — after the referred customer activates their card
                                </option>
                                <option value="card_purchase"
                                    {{ ($settings['referral.qualify_on']['value'] ?? '') === 'card_purchase' ? 'selected' : '' }}>
                                    Card Purchase — immediately after payment
                                </option>
                            </select>
                        </div>

                        <div class="form-check form-switch">
                            <input type="hidden" name="settings[referral.auto_approve]" value="0">
                            <input type="checkbox" class="form-check-input" name="settings[referral.auto_approve]"
                                value="1" {{ $settings['referral.auto_approve']['value'] ?? false ? 'checked' : '' }}>
                            <label class="form-check-label">
                                <strong>Auto-Approve Rewards</strong>
                                <span class="d-block text-muted small">
                                    If ON, the referrer's ₱50 is credited immediately after activation (no admin approval
                                    needed)
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Limits --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-shield-alt text-purple me-2"></i>Limits & Security
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Max Referrals Per User</label>
                                <input type="number" name="settings[referral.max_referrals_per_user]" min="0"
                                    value="{{ $settings['referral.max_referrals_per_user']['value'] ?? 0 }}"
                                    class="form-control">
                                <small class="text-muted">0 = unlimited</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Minimum Withdrawal (₱)</label>
                                <input type="number" name="settings[referral.min_withdrawal]" step="0.01" min="0"
                                    value="{{ $settings['referral.min_withdrawal']['value'] ?? 500 }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cookie Expiry (days)</label>
                                <input type="number" name="settings[referral.cookie_expiry_days]" min="1"
                                    value="{{ $settings['referral.cookie_expiry_days']['value'] ?? 30 }}"
                                    class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card position-sticky" style="top: 90px;">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Save Settings</h6>
                        <p class="text-muted small">Apply changes to the referral program</p>
                        <button type="submit" class="btn btn-purple w-100">
                            <i class="fas fa-save me-1"></i> Save Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
