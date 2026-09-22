{{-- resources/views/admin/transactions/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Transactions')
@section('page-title', 'Credit Transactions')
@section('page-description', 'View all credit transactions across merchants')

@section('content') <style>
        :root {
            --primary-purple: #6f42c1;
            --dark-purple: #4b2a83;
            --light-purple: #f3effb;
            --yellow: #ffc107;
            --text-dark: #2d2a35;
            --text-muted: #77747d;
            --border-color: #e9e6ef;
        }

        /* ========================= Page ========================= */
        .transactions-page {
            color: var(--text-dark);
        }

        .page-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: .25rem;
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: .875rem;
            margin-bottom: 0;
        }

        .page-title-icon {
            color: var(--primary-purple);
        }

        /* ========================= Stats ========================= */
        .transaction-stat-card {
            height: 100%;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.25rem;
            box-shadow: 0 4px 15px rgba(45, 42, 53, .06);
            transition: all .2s ease;
        }

        .transaction-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(45, 42, 53, .09);
        }

        .transaction-stat-card.revenue {
            background: linear-gradient(145deg, #6f42c1, #4b2a83);
            border: none;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .transaction-stat-card.revenue::after {
            content: '';
            position: absolute;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            right: -40px;
            top: -45px;
        }

        .transaction-stat-card.revenue::before {
            content: '';
            position: absolute;
            width: 75px;
            height: 75px;
            border-radius: 50%;
            background: rgba(255, 193, 7, .12);
            right: 40px;
            bottom: -35px;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: .7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-purple);
            color: var(--primary-purple);
        }

        .stat-icon.success {
            background: #d1e7dd;
            color: #198754;
        }

        .stat-icon.danger {
            background: #f8d7da;
            color: #dc3545;
        }

        .revenue .stat-icon {
            background: rgba(255, 255, 255, .12);
            color: var(--yellow);
        }

        .stat-label {
            color: var(--text-muted);
            font-size: .75rem;
            font-weight: 600;
        }

        .revenue .stat-label {
            color: rgba(255, 255, 255, .7);
        }

        .stat-value {
            font-size: 1.7rem;
            font-weight: 800;
            line-height: 1.1;
            margin: .75rem 0 0;
        }

        /* ========================= General Card ========================= */
        .transaction-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(45, 42, 53, .06);
            overflow: hidden;
        }

        /* ========================= Filters ========================= */
        .filter-header {
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            background: #fff;
        }

        .filter-title {
            font-size: .95rem;
            font-weight: 700;
            margin-bottom: .2rem;
        }

        .filter-subtitle {
            color: var(--text-muted);
            font-size: .75rem;
            margin-bottom: 0;
        }

        .filter-body {
            padding: 1.25rem;
        }

        .filter-label {
            font-size: .75rem;
            font-weight: 700;
            color: #5e5a64;
            margin-bottom: .4rem;
        }

        .filter-control {
            border: 1px solid #ddd8e7;
            border-radius: .6rem;
            min-height: 42px;
            font-size: .85rem;
        }

        .filter-control:focus {
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 .2rem rgba(111, 66, 193, .12);
        }

        .search-wrapper {
            position: relative;
        }

        .search-wrapper i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 5;
            font-size: .8rem;
        }

        .search-wrapper input {
            padding-left: 2.3rem;
        }

        .btn-purple {
            background: linear-gradient(135deg, var(--primary-purple), #8456d6);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: .65rem;
            padding: .6rem 1rem;
            box-shadow: 0 4px 12px rgba(111, 66, 193, .18);
            transition: all .2s ease;
        }

        .btn-purple:hover {
            background: linear-gradient(135deg, var(--dark-purple), var(--primary-purple));
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(111, 66, 193, .25);
        }

        .btn-export {
            background: #198754;
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: .65rem;
            padding: .6rem 1rem;
            transition: all .2s ease;
        }

        .btn-export:hover {
            background: #157347;
            color: #fff;
            transform: translateY(-1px);
        }

        /* ========================= Table ========================= */
        .transaction-table-wrapper {
            overflow-x: auto;
        }

        .transaction-table {
            margin-bottom: 0;
            min-width: 1050px;
        }

        .transaction-table thead th {
            background: #faf9fc;
            border-bottom: 1px solid var(--border-color);
            color: #6b6871;
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: .95rem 1.1rem;
            white-space: nowrap;
        }

        .transaction-table tbody td {
            padding: 1rem 1.1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f0edf4;
        }

        .transaction-table tbody tr {
            transition: background-color .2s ease;
        }

        .transaction-table tbody tr:hover {
            background: #faf8fd;
        }

        .transaction-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ========================= Merchant ========================= */
        .merchant-name {
            font-size: .85rem;
            font-weight: 700;
            color: var(--text-dark);
            max-width: 200px;
        }

        .merchant-email {
            color: var(--text-muted);
            font-size: .72rem;
            max-width: 200px;
        }

        .merchant-avatar {
            width: 36px;
            height: 36px;
            border-radius: .6rem;
            background: var(--light-purple);
            color: var(--primary-purple);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            font-weight: 800;
        }

        /* ========================= Badges ========================= */
        .transaction-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .4rem .65rem;
            border-radius: 50rem;
            font-size: .68rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* Type */
        .badge-purchase {
            background: #cfe2ff;
            color: #084298;
        }

        .badge-usage {
            background: #f8d7da;
            color: #b02a37;
        }

        .badge-refund {
            background: #ffe5d0;
            color: #9a3412;
        }

        .badge-bonus {
            background: #fff3cd;
            color: #856404;
        }

        .badge-adjustment {
            background: var(--light-purple);
            color: var(--primary-purple);
        }

        .badge-expiry {
            background: #e9ecef;
            color: #495057;
        }

        /* Status */
        .status-paid {
            background: #d1e7dd;
            color: #146c43;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-failed {
            background: #f8d7da;
            color: #b02a37;
        }

        .status-refunded {
            background: #ffe5d0;
            color: #9a3412;
        }

        /* ========================= Credits / Amount ========================= */
        .credits-positive {
            color: #198754;
            font-weight: 800;
        }

        .credits-negative {
            color: #dc3545;
            font-weight: 800;
        }

        .balance-after {
            font-size: .7rem;
            color: var(--text-muted);
        }

        .amount-positive {
            color: #198754;
            font-weight: 700;
        }

        .amount-negative {
            color: #dc3545;
            font-weight: 700;
        }

        .plan-name {
            font-size: .8rem;
            font-weight: 600;
            color: #55515b;
        }

        /* ========================= View Button ========================= */
        .btn-view {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: var(--light-purple);
            border: 1px solid #e5daf7;
            color: var(--primary-purple);
            border-radius: .55rem;
            padding: .4rem .7rem;
            font-size: .75rem;
            font-weight: 700;
            text-decoration: none;
            transition: all .2s ease;
        }

        .btn-view:hover {
            background: var(--primary-purple);
            border-color: var(--primary-purple);
            color: #fff;
        }

        /* ========================= Empty State ========================= */
        .empty-state {
            padding: 4rem 1rem !important;
        }

        .empty-icon {
            width: 75px;
            height: 75px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: var(--light-purple);
            color: var(--primary-purple);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.9rem;
        }

        .empty-title {
            font-weight: 700;
            margin-bottom: .35rem;
        }

        .empty-description {
            color: var(--text-muted);
            font-size: .8rem;
            margin-bottom: 0;
        }

        /* ========================= Footer / Pagination ========================= */
        .transaction-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--border-color);
            background: #fff;
        }

        .pagination {
            margin-bottom: 0;
        }

        .pagination .page-link {
            color: var(--primary-purple);
            border-color: var(--border-color);
            border-radius: .45rem;
            margin: 0 .15rem;
        }

        .pagination .page-item.active .page-link {
            background: var(--primary-purple);
            border-color: var(--primary-purple);
            color: #fff;
        }

        .pagination .page-link:hover {
            background: var(--light-purple);
            color: var(--dark-purple);
        }

        /* ========================= Responsive ========================= */
        @media (max-width: 767.98px) {
            .page-title {
                font-size: 1.15rem;
            }

            .transaction-footer {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start !important;
            }

            .filter-actions {
                flex-direction: column;
                align-items: stretch !important;
            }

            .filter-actions .btn {
                width: 100%;
            }

            .filter-actions .ms-auto {
                margin-left: 0 !important;
            }
        }
    </style>
    <div class="transactions-page"> {{-- Page Header --}} <div
            class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h4 class="page-title"> <i class="fas fa-receipt page-title-icon me-2"></i> Transactions </h4>
                <p class="page-subtitle"> Monitor credit activity, payments, purchases, and wallet transactions. </p>
            </div>
        </div> {{-- ========================= Stats ========================= --}} <div class="row g-4 mb-4"> {{-- Total Transactions --}} <div class="col-sm-6 col-xl-3">
                <div class="transaction-stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-icon"> <i class="fas fa-receipt"></i> </div>
                    </div>
                    <div class="stat-label mt-3"> Total Transactions </div>
                    <div class="stat-value"> {{ number_format($stats['total']) }} </div>
                </div>
            </div> {{-- Credits Added --}} <div class="col-sm-6 col-xl-3">
                <div class="transaction-stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-icon success"> <i class="fas fa-arrow-trend-up"></i> </div>
                    </div>
                    <div class="stat-label mt-3"> Credits Added </div>
                    <div class="stat-value text-success"> +{{ number_format($stats['total_credits_added']) }} </div>
                </div>
            </div> {{-- Credits Used --}} <div class="col-sm-6 col-xl-3">
                <div class="transaction-stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-icon danger"> <i class="fas fa-arrow-trend-down"></i> </div>
                    </div>
                    <div class="stat-label mt-3"> Credits Used </div>
                    <div class="stat-value text-danger"> -{{ number_format($stats['total_credits_used']) }} </div>
                </div>
            </div> {{-- Revenue --}} <div class="col-sm-6 col-xl-3">
                <div class="transaction-stat-card revenue">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-icon"> <i class="fas fa-peso-sign"></i> </div>
                    </div>
                    <div class="stat-label mt-3"> Total Revenue </div>
                    <div class="stat-value"> ₱{{ number_format($stats['total_revenue'], 2) }} </div>
                </div>
            </div>
        </div> {{-- ========================= Filters ========================= --}} <div class="transaction-card mb-4">
            <div class="filter-header">
                <h5 class="filter-title"> <i class="fas fa-filter text-purple me-2"></i> Filter Transactions </h5>
                <p class="filter-subtitle"> Search and filter transactions by merchant, type, status, or date. </p>
            </div>
            <div class="filter-body">
                <form method="GET">
                    <div class="row g-3"> {{-- Search --}} <div class="col-md-6 col-lg-3"> <label class="filter-label">
                                Search </label>
                            <div class="search-wrapper"> <i class="fas fa-search"></i> <input type="text" name="search"
                                    value="{{ request('search') }}" class="form-control filter-control"
                                    placeholder="Reference, merchant..."> </div>
                        </div> {{-- Type --}} <div class="col-md-6 col-lg-2"> <label class="filter-label"> Type
                            </label> <select name="type" class="form-select filter-control">
                                <option value="all"> All Types </option>
                                @foreach (['purchase' => 'Purchase', 'usage' => 'Usage', 'refund' => 'Refund', 'bonus' => 'Bonus', 'adjustment' => 'Adjustment', 'expiry' => 'Expiry'] as $value => $label)
                                    <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>
                                        {{ $label }} </option>
                                @endforeach
                            </select> </div> {{-- Merchant --}} <div class="col-md-6 col-lg-2"> <label
                                class="filter-label"> Merchant </label> <select name="merchant_id"
                                class="form-select filter-control">
                                <option value=""> All Merchants </option>
                                @foreach ($merchants as $m)
                                    <option value="{{ $m->merchant_id }}"
                                        {{ request('merchant_id') === $m->merchant_id ? 'selected' : '' }}>
                                        {{ $m->business_name }} </option>
                                @endforeach
                            </select> </div> {{-- Status --}} <div class="col-md-6 col-lg-2"> <label
                                class="filter-label"> Status </label> <select name="payment_status"
                                class="form-select filter-control">
                                <option value="all"> All Status </option>
                                @foreach (['paid', 'pending', 'failed', 'refunded'] as $s)
                                    <option value="{{ $s }}"
                                        {{ request('payment_status') === $s ? 'selected' : '' }}> {{ ucfirst($s) }}
                                    </option>
                                @endforeach
                            </select> </div> {{-- From --}} <div class="col-md-6 col-lg-1"> <label
                                class="filter-label"> From </label> <input type="date" name="date_from"
                                value="{{ request('date_from') }}" class="form-control filter-control"> </div>
                        {{-- To --}} <div class="col-md-6 col-lg-1"> <label class="filter-label"> To </label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="form-control filter-control">
                        </div>
                    </div> {{-- Filter Actions --}} <div
                        class="filter-actions d-flex flex-wrap align-items-center gap-2 mt-4 pt-3 border-top"> <button
                            type="submit" class="btn btn-purple"> <i class="fas fa-filter me-1"></i> Apply Filters
                        </button> <a href="{{ route('admin.transactions.index') }}" class="btn btn-light border"> <i
                                class="fas fa-times me-1"></i> Clear </a> <a
                            href="{{ route('admin.transactions.export', request()->query()) }}"
                            class="btn btn-export ms-auto"> <i class="fas fa-download me-1"></i> Export CSV </a> </div>
                </form>
            </div>
        </div> {{-- ========================= Transactions Table ========================= --}} <div class="transaction-card">
            <div class="transaction-table-wrapper">
                <table class="table transaction-table align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Merchant</th>
                            <th>Type</th>
                            <th>Credits</th>
                            <th>Amount</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $t)
                            @php
                                $typeStyles = [
                                    'purchase' => 'badge-purchase',
                                    'usage' => 'badge-usage',
                                    'refund' => 'badge-refund',
                                    'bonus' => 'badge-bonus',
                                    'adjustment' => 'badge-adjustment',
                                    'expiry' => 'badge-expiry',
                                ];
                                $typeIcons = [
                                    'purchase' => 'fa-cart-shopping',
                                    'usage' => 'fa-arrow-trend-down',
                                    'refund' => 'fa-rotate-left',
                                    'bonus' => 'fa-gift',
                                    'adjustment' => 'fa-sliders',
                                    'expiry' => 'fa-clock',
                                ];
                                $statusStyles = [
                                    'paid' => 'status-paid',
                                    'pending' => 'status-pending',
                                    'failed' => 'status-failed',
                                    'refunded' => 'status-refunded',
                                ];
                                $typeStyle = $typeStyles[$t->type] ?? 'badge-expiry';
                                $typeIcon = $typeIcons[$t->type] ?? 'fa-circle-info';
                                $statusStyle = $statusStyles[$t->payment_status] ?? 'badge-expiry';
                                $merchantName = $t->merchant->business_name ?? 'N/A';
                                $initials = collect(preg_split('/\s+/', trim($merchantName)))
                                    ->filter()
                                    ->take(2)
                                    ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                                    ->implode('');
                            @endphp <tr> {{-- Date --}} <td>
                                    <div class="fw-semibold small"> {{ $t->created_at->format('M d, Y') }} </div> <small
                                        class="text-muted"> {{ $t->created_at->format('H:i') }} </small>
                                </td> {{-- Merchant --}} <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="merchant-avatar"> {{ $initials ?: 'NA' }} </div>
                                        <div>
                                            <div class="merchant-name text-truncate"> {{ $merchantName }} </div>
                                            <div class="merchant-email text-truncate"> {{ $t->merchant->email ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td> {{-- Type --}} <td> <span class="transaction-badge {{ $typeStyle }}"> <i
                                            class="fas {{ $typeIcon }}"></i> {{ ucfirst($t->type) }} </span> </td>
                                {{-- Credits --}} <td>
                                    <div class="{{ $t->credits > 0 ? 'credits-positive' : 'credits-negative' }}">
                                        {{ $t->credits > 0 ? '+' : '' }} {{ number_format($t->credits) }} </div>
                                    <div class="balance-after"> Balance: {{ number_format($t->balance_after) }} </div>
                                </td> {{-- Amount --}} <td>
                                    @if ($t->amount != 0)
                                        <span class="{{ $t->amount > 0 ? 'amount-positive' : 'amount-negative' }}">
                                            {{ $t->amount > 0 ? '+' : '' }} ₱{{ number_format(abs($t->amount), 2) }}
                                        </span>
                                    @else
                                        <span class="text-muted"> — </span>
                                    @endif
                                </td> {{-- Plan --}} <td> <span class="plan-name"> {{ $t->plan->name ?? '—' }}
                                    </span> </td> {{-- Status --}} <td>
                                    @if ($t->payment_status)
                                        <span class="transaction-badge {{ $statusStyle }}"> <i
                                                class="fas {{ $t->payment_status === 'paid' ? 'fa-check-circle' : ($t->payment_status === 'pending' ? 'fa-clock' : ($t->payment_status === 'failed' ? 'fa-circle-xmark' : 'fa-rotate-left')) }}">
                                            </i> {{ ucfirst($t->payment_status) }} </span>
                                    @else
                                        <span class="text-muted"> — </span>
                                    @endif
                                </td> {{-- Actions --}} <td class="text-end"> <a
                                        href="{{ route('admin.transactions.show', $t->transaction_id) }}"
                                        class="btn-view"> <i class="fas fa-eye"></i> View </a> </td>
                        </tr> @empty <tr>
                                <td colspan="8" class="empty-state">
                                    <div class="empty-icon"> <i class="fas fa-receipt"></i> </div>
                                    <h6 class="empty-title"> No transactions found </h6>
                                    <p class="empty-description"> Try adjusting your filters or search criteria. </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> {{-- Pagination --}} @if ($transactions->hasPages())
                <div class="transaction-footer d-flex justify-content-between align-items-center"> <small
                        class="text-muted"> Showing <strong>{{ $transactions->firstItem() }}</strong> to
                        <strong>{{ $transactions->lastItem() }}</strong> of <strong>{{ $transactions->total() }}</strong>
                        results </small> {{ $transactions->links() }} </div>
            @endif
        </div>
</div> @endsection
