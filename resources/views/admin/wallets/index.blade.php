@extends('layouts.admin')

@section('title', 'Merchant Wallets')
{{-- @section('page-title', 'Merchant Wallets')
@section('page-description', 'Manage all merchant credit wallets') --}}

@section('content') <style>
        :root {
            --primary-purple: #6f42c1;
            --dark-purple: #4b2a83;
            --light-purple: #f3effb;
            --yellow: #ffc107;
            --dark-yellow: #e0a800;
            --success: #198754;
            --danger: #dc3545;
            --text-dark: #2d2a35;
            --text-muted: #77747d;
            --border-color: #e9e6ef;
        }

        /* Page */
        .wallet-page {
            color: var(--text-dark);
        }

        /* Header */
        .wallet-header {
            margin-bottom: 1.5rem;
        }

        .wallet-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: .25rem;
        }

        .wallet-subtitle {
            color: var(--text-muted);
            font-size: .875rem;
            margin-bottom: 0;
        }

        .wallet-title-icon {
            color: var(--primary-purple);
        }

        /* Main Card */
        .wallet-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(45, 42, 53, .06);
        }

        /* Search */
        .wallet-search-wrapper {
            padding: 1rem 1.25rem;
            background: #fff;
            border-bottom: 1px solid var(--border-color);
        }

        .wallet-search {
            border: 1px solid #ddd8e7;
            border-radius: .65rem;
            min-height: 44px;
        }

        .wallet-search:focus {
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 .2rem rgba(111, 66, 193, .12);
        }

        .wallet-search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 5;
        }

        .btn-purple {
            background: linear-gradient(135deg, var(--primary-purple), #8456d6);
            border: none;
            color: #fff;
            font-weight: 600;
            min-height: 44px;
            border-radius: .65rem;
            padding: .55rem 1.25rem;
            transition: all .2s ease;
            box-shadow: 0 4px 12px rgba(111, 66, 193, .18);
        }

        .btn-purple:hover {
            background: linear-gradient(135deg, var(--dark-purple), var(--primary-purple));
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(111, 66, 193, .25);
        }

        .btn-clear {
            min-height: 44px;
            border-radius: .65rem;
            font-weight: 600;
        }

        /* Table */
        .wallet-table {
            margin-bottom: 0;
        }

        .wallet-table thead th {
            background: #faf9fc;
            border-bottom: 1px solid var(--border-color);
            color: #6b6871;
            font-size: .7rem;
            font-weight: 800;
            letter-spacing: .06em;
            padding: .95rem 1.25rem;
            white-space: nowrap;
        }

        .wallet-table tbody td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
            border-bottom: 1px solid #f0edf4;
        }

        .wallet-table tbody tr {
            transition: background-color .2s ease;
        }

        .wallet-table tbody tr:hover {
            background-color: #faf8fd;
        }

        .wallet-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Merchant */
        .merchant-wrapper {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .merchant-avatar {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            border-radius: .7rem;
            background: var(--light-purple);
            color: var(--primary-purple);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: .85rem;
        }

        .merchant-name {
            font-size: .9rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: .15rem;
        }

        .merchant-email {
            font-size: .78rem;
            color: var(--text-muted);
        }

        /* Balance */
        .balance-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .45rem .75rem;
            border-radius: 50rem;
            font-size: .8rem;
            font-weight: 800;
        }

        .balance-positive {
            background: #d1e7dd;
            color: #146c43;
        }

        .balance-zero {
            background: #f8d7da;
            color: #b02a37;
        }

        .balance-icon {
            color: var(--yellow);
        }

        /* Stats */
        .wallet-stat {
            font-size: .85rem;
            font-weight: 600;
            color: #55515b;
        }

        .wallet-spent {
            color: var(--text-dark);
            font-weight: 800;
        }

        /* View button */
        .btn-view {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            color: var(--primary-purple);
            background: var(--light-purple);
            border: 1px solid #e5daf7;
            border-radius: .55rem;
            padding: .4rem .7rem;
            font-size: .78rem;
            font-weight: 700;
            text-decoration: none;
            transition: all .2s ease;
        }

        .btn-view:hover {
            background: var(--primary-purple);
            border-color: var(--primary-purple);
            color: #fff;
        }

        /* Empty state */
        .empty-state {
            padding: 4rem 1.5rem !important;
        }

        .empty-icon {
            width: 76px;
            height: 76px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: var(--light-purple);
            color: var(--primary-purple);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .empty-title {
            font-weight: 700;
            margin-bottom: .4rem;
        }

        .empty-text {
            color: var(--text-muted);
            font-size: .875rem;
            margin-bottom: 0;
        }

        /* Pagination */
        .wallet-pagination {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--border-color);
            background: #fff;
        }

        .wallet-pagination .pagination {
            margin-bottom: 0;
        }

        .wallet-pagination .page-link {
            color: var(--primary-purple);
            border-color: var(--border-color);
            border-radius: .45rem;
            margin: 0 .15rem;
        }

        .wallet-pagination .page-item.active .page-link {
            background-color: var(--primary-purple);
            border-color: var(--primary-purple);
            color: #fff;
        }

        .wallet-pagination .page-link:hover {
            background-color: var(--light-purple);
            color: var(--dark-purple);
        }

        /* Mobile */
        @media (max-width: 767.98px) {
            .wallet-search-form {
                flex-direction: column;
            }

            .wallet-search-form .btn {
                width: 100%;
            }

            .wallet-table-wrapper {
                overflow-x: auto;
            }

            .wallet-table {
                min-width: 850px;
            }
        }
    </style>
    <div class="wallet-page"> {{-- Page Header --}} <div
            class="wallet-header d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h4 class="wallet-title"> <i class="fas fa-wallet wallet-title-icon me-2"></i> Merchant Wallets </h4>
                <p class="wallet-subtitle"> Monitor merchant credit balances, purchases, usage, and spending. </p>
            </div>
        </div> {{-- Main Card --}}
        <div class="wallet-card"> {{-- Search --}} <div class="wallet-search-wrapper">
                <form method="GET" class="d-flex gap-2 wallet-search-form">
                    <div class="position-relative flex-grow-1"> <i class="fas fa-search wallet-search-icon"></i> <input
                            type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search by business name or email..." class="form-control wallet-search ps-5">
                    </div> <button type="submit" class="btn btn-purple px-4"> <i class="fas fa-search me-1"></i> Search
                    </button>
                    @if (request('search'))
                        <a href="{{ route('admin.wallets.index') }}" class="btn btn-light border btn-clear px-3"> <i
                                class="fas fa-times me-1"></i> Clear </a>
                    @endif
                </form>
            </div> {{-- Table --}} <div class="wallet-table-wrapper">
                <table class="table wallet-table align-middle">
                    <thead>
                        <tr>
                            <th>Merchant</th>
                            <th>Balance</th>
                            <th>Purchased</th>
                            <th>Used</th>
                            <th>Total Spent</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wallets as $wallet)
                            @php
                                $businessName = $wallet->merchant->business_name ?? 'N/A';
                                $email = $wallet->merchant->email ?? '';
                                $initials = collect(preg_split('/\s+/', trim($businessName)))
                                    ->filter()
                                    ->take(2)
                                    ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                                    ->implode('');
                            @endphp <tr> {{-- Merchant --}} <td>
                                    <div class="merchant-wrapper">
                                        <div class="merchant-avatar"> {{ $initials ?: 'NA' }} </div>
                                        <div>
                                            <div class="merchant-name"> {{ $businessName }} </div>
                                            @if ($email)
                                                <div class="merchant-email"> {{ $email }} </div>
                                            @endif
                                        </div>
                                    </div>
                                </td> {{-- Balance --}} <td> <span
                                        class="balance-badge {{ $wallet->credit_balance > 0 ? 'balance-positive' : 'balance-zero' }}">
                                        <i class="fas fa-coins balance-icon"></i>
                                        {{ number_format($wallet->credit_balance) }} </span> </td> {{-- Purchased --}}
                                <td> <span class="wallet-stat"> {{ number_format($wallet->total_credits_purchased) }}
                                    </span> </td> {{-- Used --}} <td> <span class="wallet-stat">
                                        {{ number_format($wallet->total_credits_used) }} </span> </td>
                                {{-- Total Spent --}} <td> <span class="wallet-spent">
                                        ₱{{ number_format($wallet->total_spent, 2) }} </span> </td> {{-- Actions --}}
                                <td class="text-end"> <a href="{{ route('admin.wallets.show', $wallet) }}"
                                        class="btn-view"> <i class="fas fa-eye"></i> View </a> </td>
                        </tr> @empty <tr>
                                <td colspan="6" class="text-center empty-state">
                                    <div class="empty-icon"> <i class="fas fa-wallet"></i> </div>
                                    <h5 class="empty-title"> No wallets found </h5>
                                    <p class="empty-text"> There are no merchant wallets matching your search. </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> {{-- Pagination --}} @if ($wallets->hasPages())
                <div class="wallet-pagination d-flex justify-content-center"> {{ $wallets->withQueryString()->links() }}
                </div>
            @endif
        </div>
</div> @endsection
