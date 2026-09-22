{{-- resources/views/admin/plans/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Subscription Plans')
@section('page-title', 'Subscription Plans')
@section('page-description', 'Manage your merchant subscription plans')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <form method="GET" class="d-flex gap-2">
            <div class="position-relative">
                <i class="fas fa-search position-absolute text-muted"
                    style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.85rem;"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control ps-4"
                    placeholder="Search plans..." style="min-width: 240px;">
            </div>
            <button type="submit" class="btn btn-light">
                <i class="fas fa-filter me-1"></i> Filter
            </button>
        </form>

        <a href="{{ route('admin.plans.create') }}" class="btn btn-purple">
            <i class="fas fa-plus me-1"></i> Add Plan
        </a>
    </div>

    <div class="row g-4">
        @forelse($plans as $plan)
            <div class="col-sm-6 col-xl-3">
                <div class="plan-card {{ $plan->is_popular ? 'popular' : '' }}"> {{-- Header --}} <div
                        class="plan-card-header">
                        @if ($plan->is_popular)
                            <span class="plan-popular-badge"> <i class="fas fa-star me-1"></i> POPULAR </span>
                        @endif
                        <h5 class="text-uppercase fw-bold mb-3"> {{ $plan->name }} </h5> @php
                            $icons = [
                                'rocket' => 'fa-rocket',
                                'chart' => 'fa-chart-line',
                                'target' => 'fa-bullseye',
                                'crown' => 'fa-crown',
                            ];
                            $icon = $icons[$plan->icon] ?? 'fa-rocket';
                        @endphp <div
                            class="plan-icon"> <i class="fas {{ $icon }}"></i> </div>
                        <div class="small opacity-75 mb-1"> Starting at </div>
                        <div class="plan-price"> ₱{{ number_format($plan->price, 0) }} </div>
                    </div> {{-- Body --}} <div class="p-4"> {{-- Base Credits --}} <div class="text-center">
                            <div class="credits-number"> {{ number_format($plan->base_credits) }} </div>
                            <div class="credits-label mt-2"> PROMO CREDITS </div>
                            @if ($plan->bonus_credits > 0)
                                <div class="mt-2"> <span class="bonus-badge"> <i class="fas fa-gift me-1"></i>
                                        +{{ number_format($plan->bonus_credits) }} FREE </span> </div>
                            @endif
                        </div>
                        <hr class="my-4"> {{-- Total Credits --}} <div class="text-center mb-4">
                            <div class="total-credits"> {{ number_format($plan->total_credits) }} </div>
                            <div class="credits-label mt-1"> TOTAL CREDITS </div>
                        </div> {{-- Cost per Credit --}} <div class="bg-purple-light rounded-3 p-3 text-center mb-4">
                            <div class="cost-label text-purple"> ₱{{ number_format($plan->cost_per_credit, 2) }} / CREDIT
                            </div>
                            <div class="plan-stars mt-2">
                                @for ($i = 0; $i < 5; $i++)
                                    <i class="fa{{ $i < $plan->star_rating ? 's' : 'r' }} fa-star"></i>
                                @endfor
                            </div>
                            @if ($plan->tagline)
                                <div class="text-purple small fw-semibold mt-2"> {{ $plan->tagline }} </div>
                            @endif
                        </div> {{-- Status --}} <div class="text-center">
                            <form method="POST" action="{{ route('admin.plans.toggle-status', $plan) }}"> @csrf
                                @method('PATCH') <button type="submit"
                                    class="status-btn {{ $plan->is_active ? 'status-active' : 'status-inactive' }}"> <i
                                        class="fas {{ $plan->is_active ? 'fa-check-circle' : 'fa-circle' }} me-1"></i>
                                    {{ $plan->is_active ? 'Active' : 'Inactive' }} </button> </form>
                        </div>
                    </div> {{-- Actions --}} <div class="plan-card-footer px-4 py-3">
                        <div class="d-flex justify-content-between align-items-center"> <a
                                href="{{ route('admin.plans.edit', $plan) }}" class="action-btn action-edit"> <i
                                    class="fas fa-pen me-1"></i> Edit </a>
                            <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}"
                                onsubmit="return confirm('Delete this plan? This cannot be undone.');"> @csrf
                                @method('DELETE') <button type="submit" class="btn btn-link p-0 action-btn action-delete">
                                    <i class="fas fa-trash me-1"></i> Delete </button> </form>
                        </div>
                    </div>
                </div>
        </div> @empty {{-- Empty State --}} <div class="col-12">
                <div class="empty-state text-center py-5 px-3">
                    <div class="empty-icon mb-4"> <i class="fas fa-crown"></i> </div>
                    <h5 class="fw-bold mb-2"> No plans yet </h5>
                    <p class="text-muted mb-4"> Create your first subscription plan to get started. </p> <a
                        href="{{ route('admin.plans.create') }}" class="btn btn-purple"> <i class="fas fa-plus me-1"></i>
                        Add Plan </a>
                </div>
            </div>
        @endforelse
    </div> {{-- Pagination --}} @if ($plans->hasPages())
        <div class="mt-5 d-flex justify-content-center"> {{ $plans->withQueryString()->links() }} </div>
    @endif
</div> @endsection
