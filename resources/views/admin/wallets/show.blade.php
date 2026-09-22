{{-- resources/views/admin/wallets/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Wallet Details')
{{-- @section('page-title', $wallet->merchant->business_name ?? 'Wallet')
@section('page-description', 'Credit balance and transaction history') --}}

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

        /* Page */
        .wallet-details-page {
            color: var(--text-dark);
        }

        /* Header */
        .wallet-page-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: .25rem;
        }

        .wallet-page-subtitle {
            color: var(--text-muted);
            font-size: .875rem;
            margin-bottom: 0;
        }

        .wallet-title-icon {
            color: var(--primary-purple);
        }

        /* Cards */
        .wallet-stat-card {
            height: 100%;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.35rem;
            box-shadow: 0 4px 15px rgba(45, 42, 53, .06);
            transition: all .2s ease;
        }

        .wallet-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(45, 42, 53, .09);
        }

        .wallet-stat-card.balance-card {
            background: linear-gradient(145deg, #6f42c1, #4b2a83);
            border: none;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .balance-card::after {
            content: '';
            position: absolute;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            right: -35px;
            top: -45px;
        }

        .balance-card::before {
            content: '';
            position: absolute;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 193, 7, .10);
            right: 45px;
            bottom: -35px;
        }

        .stat-label {
            font-size: .75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: .4rem;
        }

        .balance-card .stat-label,
        .balance-card .stat-description {
            color: rgba(255, 255, 255, .7);
        }

        .stat-value {
            font-size: 1.9rem;
            line-height: 1.1;
            font-weight: 800;
            color: var(--text-dark);
        }

        .balance-card .stat-value {
            color: #fff;
        }

        .stat-description {
            color: var(--text-muted);
            font-size: .75rem;
            margin-top: .35rem;
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
            margin-bottom: 1rem;
        }

        .balance-card .stat-icon {
            background: rgba(255, 255, 255, .12);
            color: var(--yellow);
        }

        .spent-value {
            color: #198754 !important;
        }

        /* General Cards */
        .wallet-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(45, 42, 53, .06);
            overflow: hidden;
        }

        .wallet-card-header {
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
        }

        .wallet-card-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: .2rem;
        }

        .wallet-card-subtitle {
            color: var(--text-muted);
            font-size: .78rem;
            margin-bottom: 0;
        }

        /* Buttons */
        .btn-purple {
            background: linear-gradient(135deg, var(--primary-purple), #8456d6);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: .65rem;
            padding: .6rem 1rem;
            transition: all .2s ease;
            box-shadow: 0 4px 12px rgba(111, 66, 193, .18);
        }

        .btn-purple:hover {
            background: linear-gradient(135deg, var(--dark-purple), var(--primary-purple));
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(111, 66, 193, .25);
        }

        .btn-purple-light {
            background: var(--light-purple);
            border: 1px solid #e5daf7;
            color: var(--primary-purple);
            font-weight: 600;
            border-radius: .65rem;
        }

        .btn-purple-light:hover {
            background: #e7ddf7;
            border-color: #d8c8ef;
            color: var(--dark-purple);
        }

        /* Adjustment */
        .adjustment-body {
            padding: 1.25rem;
        }

        .adjustment-form {
            background: #faf9fc;
            border: 1px solid var(--border-color);
            border-radius: .8rem;
            padding: 1.25rem;
            margin-top: 1rem;
        }

        .form-label-custom {
            font-size: .8rem;
            font-weight: 700;
            color: #55515b;
            margin-bottom: .4rem;
        }

        .form-control-custom {
            border: 1px solid #ddd8e7;
            border-radius: .6rem;
            min-height: 42px;
        }

        .form-control-custom:focus {
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 .2rem rgba(111, 66, 193, .12);
        }

        .adjustment-help {
            font-size: .72rem;
            color: var(--text-muted);
            margin-top: .35rem;
        }

        /* Transactions */
        .transaction-wrapper {
            overflow-x: auto;
        }

        .transaction-table {
            margin-bottom: 0;
            min-width: 800px;
        }

        .transaction-table thead th {
            background: #faf9fc;
            border-bottom: 1px solid var(--border-color);
            color: #6b6871;
            font-size: .7rem;
            font-weight: 800;
            letter-spacing: .06em;
            padding: .95rem 1.25rem;
            white-space: nowrap;
        }

        .transaction-table tbody td {
            padding: 1rem 1.25rem;
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

        /* Transaction badges */
        .transaction-badge {
            display: inline-flex;
            align-items: center;
            padding: .4rem .7rem;
            border-radius: 50rem;
            font-size: .7rem;
            font-weight: 700;
        }

        .type-purchase {
            background: #cfe2ff;
            color: #084298;
        }

        .type-usage {
            background: #f8d7da;
            color: #b02a37;
        }

        .type-refund {
            background: #d1e7dd;
            color: #146c43;
        }

        .type-bonus {
            background: #fff3cd;
            color: #856404;
        }

        .type-adjustment {
            background: var(--light-purple);
            color: var(--primary-purple);
        }

        .type-expiry {
            background: #e9ecef;
            color: #495057;
        }

        /* Credits */
        .credits-positive {
            color: #198754;
            font-weight: 800;
        }

        .credits-negative {
            color: #dc3545;
            font-weight: 800;
        }

        .balance-after {
            font-weight: 700;
            color: #55515b;
        }

        .transaction-description {
            color: #6f6b75;
            font-size: .82rem;
        }

        .transaction-date {
            color: #77747d;
            font-size: .78rem;
            white-space: nowrap;
        }

        /* Empty state */
        .empty-transactions {
            padding: 4rem 1rem !important;
            text-align: center;
        }

        .empty-transaction-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: var(--light-purple);
            color: var(--primary-purple);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
        }

        .empty-transaction-title {
            font-weight: 700;
            margin-bottom: .35rem;
        }

        .empty-transaction-text {
            color: var(--text-muted);
            font-size: .8rem;
            margin-bottom: 0;
        }

        /* Pagination */
        .transaction-pagination {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: center;
        }

        .transaction-pagination .pagination {
            margin-bottom: 0;
        }

        .transaction-pagination .page-link {
            color: var(--primary-purple);
            border-color: var(--border-color);
            border-radius: .45rem;
            margin: 0 .15rem;
        }

        .transaction-pagination .page-item.active .page-link {
            background: var(--primary-purple);
            border-color: var(--primary-purple);
            color: #fff;
        }

        .transaction-pagination .page-link:hover {
            background: var(--light-purple);
            color: var(--dark-purple);
        }

        /* Responsive */
        @media (max-width: 767.98px) {
            .wallet-page-title {
                font-size: 1.15rem;
            }

            .adjustment-header {
                align-items: flex-start !important;
                gap: 1rem;
            }

            .adjustment-header .btn {
                width: 100%;
            }

            .transaction-table {
                min-width: 750px;
            }
        }
    </style>
    <div class="wallet-details-page"> {{-- Page Header --}} <div
            class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h4 class="wallet-page-title"> <i class="fas fa-wallet wallet-title-icon me-2"></i> Wallet Details </h4>
                <p class="wallet-page-subtitle"> {{ $wallet->merchant->business_name ?? 'Merchant' }} @if ($wallet->merchant->email ?? false)
                        <span class="mx-1">•</span> {{ $wallet->merchant->email }}
                    @endif
                </p>
            </div> <a href="{{ route('admin.wallets.index') }}" class="btn btn-light border"> <i
                    class="fas fa-arrow-left me-1"></i> Back to Wallets </a>
        </div> {{-- Stats --}} <div class="row g-4 mb-4"> {{-- Current Balance --}} <div class="col-sm-6 col-xl-3">
                <div class="wallet-stat-card balance-card">
                    <div class="stat-icon"> <i class="fas fa-coins"></i> </div>
                    <div class="stat-label"> Current Balance </div>
                    <div class="stat-value"> {{ number_format($wallet->credit_balance) }} </div>
                    <div class="stat-description"> Available credits </div>
                </div>
            </div> {{-- Total Purchased --}} <div class="col-sm-6 col-xl-3">
                <div class="wallet-stat-card">
                    <div class="stat-icon"> <i class="fas fa-cart-shopping"></i> </div>
                    <div class="stat-label"> Total Purchased </div>
                    <div class="stat-value"> {{ number_format($wallet->total_credits_purchased) }} </div>
                    <div class="stat-description"> Credits purchased </div>
                </div>
            </div> {{-- Total Used --}} <div class="col-sm-6 col-xl-3">
                <div class="wallet-stat-card">
                    <div class="stat-icon"> <i class="fas fa-chart-line"></i> </div>
                    <div class="stat-label"> Total Used </div>
                    <div class="stat-value"> {{ number_format($wallet->total_credits_used) }} </div>
                    <div class="stat-description"> Credits consumed </div>
                </div>
            </div> {{-- Total Spent --}} <div class="col-sm-6 col-xl-3">
                <div class="wallet-stat-card">
                    <div class="stat-icon"> <i class="fas fa-peso-sign"></i> </div>
                    <div class="stat-label"> Total Spent </div>
                    <div class="stat-value spent-value"> ₱{{ number_format($wallet->total_spent, 2) }} </div>
                    <div class="stat-description"> Total purchases </div>
                </div>
            </div>
        </div> {{-- Manual Adjustment --}} <div class="wallet-card mb-4" x-data="{ open: false }">
            <div class="adjustment-body">
                <div class="d-flex justify-content-between align-items-center adjustment-header">
                    <div>
                        <h5 class="wallet-card-title"> <i class="fas fa-sliders text-purple me-2"></i> Manual Adjustment
                        </h5>
                        <p class="wallet-card-subtitle"> Manually add or deduct credits from this merchant's wallet. </p>
                    </div> <button type="button" @click="open = !open" class="btn btn-purple-light"> <i class="fas"
                            :class="open ? 'fa-times' : 'fa-pen-to-square'"></i> <span class="ms-1"
                            x-text="open ? 'Cancel' : 'Adjust Credits'"> </span> </button>
                </div> {{-- Adjustment Form --}} <form x-show="open" x-transition method="POST"
                    action="{{ route('admin.wallets.adjust', $wallet) }}" class="adjustment-form"> @csrf <div
                        class="row g-3"> {{-- Amount --}} <div class="col-md-4"> <label class="form-label-custom">
                                Amount (+ / -) </label>
                            <div class="input-group"> <span class="input-group-text bg-white"> <i
                                        class="fas fa-coins text-warning"></i> </span> <input type="number" name="amount"
                                    required class="form-control form-control-custom" placeholder="50 or -20"> </div>
                            <div class="adjustment-help"> Use a positive number to add credits or a negative number to
                                deduct credits. </div>
                        </div> {{-- Reason --}} <div class="col-md-8"> <label class="form-label-custom"> Reason
                            </label> <input type="text" name="reason" required maxlength="255"
                                class="form-control form-control-custom" placeholder="e.g., Customer support credit"> </div>
                        {{-- Submit --}} <div class="col-12"> <button type="submit" class="btn btn-purple"> <i
                                    class="fas fa-check me-1"></i> Apply Adjustment </button> </div>
                    </div>
                </form>
            </div>
        </div> {{-- Transactions --}} <div class="wallet-card">
            <div class="wallet-card-header">
                <h5 class="wallet-card-title"> <i class="fas fa-clock-rotate-left text-purple me-2"></i> Transaction History
                </h5>
                <p class="wallet-card-subtitle"> Complete history of credits purchased, used, refunded, and adjusted. </p>
            </div>
            <div class="transaction-wrapper">
                <table class="table transaction-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Credits</th>
                            <th>Balance</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $txn)
                            @php
                                $typeStyles = [
                                    'purchase' => 'type-purchase',
                                    'usage' => 'type-usage',
                                    'refund' => 'type-refund',
                                    'bonus' => 'type-bonus',
                                    'adjustment' => 'type-adjustment',
                                    'expiry' => 'type-expiry',
                                ];
                                $typeIcons = [
                                    'purchase' => 'fa-cart-shopping',
                                    'usage' => 'fa-arrow-trend-down',
                                    'refund' => 'fa-rotate-left',
                                    'bonus' => 'fa-gift',
                                    'adjustment' => 'fa-sliders',
                                    'expiry' => 'fa-clock',
                                ];
                                $style = $typeStyles[$txn->type] ?? 'type-expiry';
                                $icon = $typeIcons[$txn->type] ?? 'fa-circle-info';
                            @endphp <tr> {{-- Date --}} <td> <span class="transaction-date">
                                        {{ $txn->created_at->format('M d, Y') }} <br> {{ $txn->created_at->format('H:i') }}
                                    </span> </td> {{-- Type --}} <td> <span
                                        class="transaction-badge {{ $style }}"> <i
                                            class="fas {{ $icon }} me-1"></i> {{ ucfirst($txn->type) }} </span>
                                </td> {{-- Credits --}} <td> <span
                                        class="{{ $txn->credits > 0 ? 'credits-positive' : 'credits-negative' }}">
                                        {{ $txn->credits > 0 ? '+' : '' }}{{ number_format($txn->credits) }} </span> </td>
                                {{-- Balance --}} <td> <span class="balance-after">
                                        {{ number_format($txn->balance_after) }} </span> </td> {{-- Description --}} <td>
                                    <span class="transaction-description"> {{ $txn->description ?: '—' }} </span>
                                </td>
                        </tr> @empty <tr>
                                <td colspan="5" class="empty-transactions">
                                    <div class="empty-transaction-icon"> <i class="fas fa-receipt"></i> </div>
                                    <h6 class="empty-transaction-title"> No transactions yet </h6>
                                    <p class="empty-transaction-text"> Transaction history will appear here once activity
                                        occurs. </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> {{-- Pagination --}} @if ($transactions->hasPages())
                <div class="transaction-pagination"> {{ $transactions->links() }} </div>
            @endif
        </div>
</div> @endsection
