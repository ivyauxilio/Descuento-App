@extends('layouts.admin')

@section('title', 'Payment Settings')
{{-- @section('page-title', 'Payment Gateway Settings')
@section('page-description', 'Configure payment gateways for merchant subscriptions') --}}

@section('content')
    <form method="POST" action="{{ route('admin.settings.payment.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">

                {{-- Active Gateway --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-purple-light rounded d-flex align-items-center justify-content-center"
                                style="width: 44px; height: 44px;">
                                <i class="fas fa-bolt text-purple"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Active Gateway</h5>
                                    <small class="text-muted">Select which payment gateway to use</small>
                            </div>
                        </div>

                        <select name="settings[payment.active_gateway]" class="form-select">
                            @foreach (['manual' => 'Manual', 'gcash' => 'GCash', 'stripe' => 'Stripe', 'paymongo' => 'PayMongo'] as $value => $label)
                                <option value="{{ $value }}"
                                    {{ ($settings['payment.active_gateway']['value'] ?? 'manual') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- GCash --}}
                @include('admin.settings.partials.gateway-card', [
                    'id' => 'gcash',
                    'title' => 'GCash',
                    'icon' => 'fa-mobile-screen',
                    'color' => 'info',
                    'settings' => $settings,
                ])

                {{-- Stripe --}}
                @include('admin.settings.partials.gateway-card', [
                    'id' => 'stripe',
                    'title' => 'Stripe',
                    'icon' => 'fa-credit-card',
                    'color' => 'primary',
                    'settings' => $settings,
                ])

                {{-- PayMongo --}}
                @include('admin.settings.partials.gateway-card', [
                    'id' => 'paymongo',
                    'title' => 'PayMongo',
                    'icon' => 'fa-wallet',
                    'color' => 'success',
                    'settings' => $settings,
                ])
            </div>

            <div class="col-lg-4">
                <div class="card position-sticky" style="top: 90px;">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Save Changes</h6>
                        <p class="text-muted small">Update your payment gateway configuration.</p>
                        <button type="submit" class="btn btn-purple w-100">
                            <i class="fas fa-save me-1"></i> Save Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
