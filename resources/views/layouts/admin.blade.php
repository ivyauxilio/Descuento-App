<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KlickMo Admin Panel') - {{ config('app.name') }} PH</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-purple: #6f42c1;
            --dark-purple: #4b2a83;
            --light-purple: #f3effb;
            --yellow: #ffc107;
            --dark-yellow: #e0a800;
            --success: #198754;
            --text-dark: #2d2a35;
            --text-muted: #77747d;
            --border-color: #e9e6ef;

            --sidebar-purple: #4b2a83;
            --sidebar-purple-dark: #35205f;
            --sidebar-purple-light: #6f42c1;
            --sidebar-hover: rgba(255, 255, 255, 0.09);
            --sidebar-text: #eee8fa;
            --sidebar-muted: #bdb0d5;
            --content-bg: #f7f6fa;

        }

        body {
            background: var(--content-bg);
            margin: 0;
            font-family: "Inter", "Segoe UI", sans-serif;
        }

        #wrapper {
            min-height: 100vh;
            display: flex;
        }

        /* ========================================= Sidebar ========================================= */
        .sidebar {
            width: 260px;
            min-width: 260px;
            min-height: 100vh;
            background: radial-gradient(circle at top right, rgba(255, 193, 7, 0.08), transparent 30%), linear-gradient(180deg, var(--sidebar-purple), var(--sidebar-purple-dark));
            color: #fff;
            /* position: fixed; */
            top: 0;
            left: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 20px rgba(53, 32, 95, 0.15);
            z-index: 1030;

            /* background: #2d3748; */
            flex-shrink: 0;
            padding: 20px 0;
            flex-shrink: 0;
        }

        /* ========================================= Brand ========================================= */
        .brand {
            padding: 1.5rem 1.25rem 1.35rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.10);
            position: relative;
        }

        .brand::before {
            content: "";
            position: absolute;
            left: 1.25rem;
            bottom: -1px;
            width: 42px;
            height: 3px;
            background: var(--yellow);
            border-radius: 10px;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: white;
            color: var(--sidebar-purple);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            font-weight: 800;
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.20);
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            padding: 5px;
        }

        .brand h4 {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
        }

        .brand small {
            display: block;
            margin-top: .2rem;
            color: var(--sidebar-muted);
            font-size: .7rem;
            font-weight: 500;
            letter-spacing: .03em;
        }

        .sidebar .brand {
            text-align: center;
            padding: 5px 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar .brand h4 {
            margin: 0;
            color: white;
        }

        .sidebar .brand small {
            color: rgba(255, 255, 255, 0.6);
            font-size: 12px;
        }

        /* ========================================= Navigation ========================================= */
        .sidebar-nav {
            padding: 1rem .8rem;
            overflow-y: auto;
            flex: 1;
        }

        .sidebar-section {
            padding: .65rem .75rem .4rem;
            color: rgba(255, 255, 255, .42);
            font-size: .63rem;
            font-weight: 800;
            letter-spacing: .09em;
            text-transform: uppercase;
        }

        .sidebar .nav {
            gap: .25rem;
        }

        .sidebar .nav-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: .75rem;
            min-height: 44px;
            padding: .7rem .8rem;
            border-radius: .7rem;
            color: var(--sidebar-text);
            font-size: .82rem;
            font-weight: 500;
            text-decoration: none;
            transition: background-color .2s ease, color .2s ease, transform .2s ease;
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            color: #cfc2e4;
            font-size: .9rem;
            transition: color .2s ease;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background: var(--sidebar-hover);
            transform: translateX(2px);
        }

        .sidebar .nav-link:hover i {
            color: var(--yellow);
        }

        /* ========================================= Active Navigation ========================================= */
        .sidebar .nav-link.active {
            color: var(--sidebar-purple);
            background: #fff;
            font-weight: 700;
            box-shadow: 0 5px 15px rgba(0, 0, 0, .12);
            transform: none;
        }

        .sidebar .nav-link.active i {
            color: var(--sidebar-purple);
        }

        .sidebar .nav-link.active::before {
            content: "";
            position: absolute;
            left: -12px;
            top: 8px;
            bottom: 8px;
            width: 4px;
            background: var(--yellow);
            border-radius: 0 6px 6px 0;
        }

        /* ========================================= Notification Badge ========================================= */
        .sidebar-badge {
            margin-left: auto;
            min-width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 .4rem;
            border-radius: 50rem;
            background: var(--yellow);
            color: #3d2a00;
            font-size: .62rem;
            font-weight: 800;
        }

        /* ========================================= Logout ========================================= */
        .sidebar-footer {
            padding: .85rem;
            border-top: 1px solid rgba(255, 255, 255, .10);
        }

        .logout-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: .65rem 1rem;
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: .7rem;
            background: rgba(220, 53, 69, .12);
            color: #ffb7be;
            font-size: .8rem;
            font-weight: 600;
            transition: all .2s ease;
        }

        .logout-btn:hover {
            background: #dc3545;
            border-color: #dc3545;
            color: #fff;
            transform: translateY(-1px);
        }

        .content {
            width: calc(100% - 260px);
            min-height: 100vh;
            padding: 1.5rem;
        }

        /* ========================================= Top Navbar ========================================= */
        .navbar-custom {
            min-height: 64px;
            margin-bottom: 1.5rem;
            padding: .75rem 1rem;
            background: #fff;
            border: 1px solid #e9e6ef;
            border-radius: .9rem;
            box-shadow: 0 3px 12px rgba(45, 42, 53, .05);
        }

        .navbar-page-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            color: #2d2a35;
            font-size: 1rem;
            font-weight: 700;
        }

        .navbar-page-title i {
            color: var(--sidebar-purple);
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: .65rem;
        }

        .admin-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--sidebar-purple);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            font-weight: 800;
        }

        .admin-name {
            color: #403b48;
            font-size: .8rem;
            font-weight: 700;
        }

        .admin-role {
            display: block;
            color: #9994a2;
            font-size: .65rem;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .stat-icon {
            font-size: 2rem;
            opacity: 0.3;
        }

        .stat-card .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 5px 0;
        }

        .stat-card .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
        }

        /* ========================================= Mobile Sidebar ========================================= */
        @media (max-width: 991.98px) {
            .sidebar {
                width: 230px;
                min-width: 230px;
            }

            .content {
                width: calc(100% - 230px);
                margin-left: 230px;
            }
        }

        @media (max-width: 767.98px) {
            #wrapper {
                display: block;
            }

            .sidebar {
                position: relative;
                width: 100%;
                min-width: 100%;
                min-height: auto;
                max-height: none;
            }

            .sidebar-nav {
                max-height: none;
            }

            .sidebar-footer {
                position: relative;
            }

            .content {
                width: 100%;
                margin-left: 0;
                padding: 1rem;
            }

            .navbar-custom {
                margin-bottom: 1rem;
            }
        }

        /* ========================================= Scrollbar ========================================= */
        .sidebar-nav::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .15);
            border-radius: 10px;
        }

        /* Buttons */
        .btn-purple {
            background-color: var(--klick-purple);
            border-color: var(--klick-purple);
            color: #fff;
        }

        .btn-purple:hover {
            background-color: var(--klick-purple-dark);
            border-color: var(--klick-purple-dark);
            color: #fff;
        }

        .btn-outline-purple {
            color: var(--klick-purple);
            border-color: var(--klick-purple);
        }

        .btn-outline-purple:hover {
            background-color: var(--klick-purple);
            color: #fff;
        }

        /* Page */
        .plans-page {
            color: var(--text-dark);
        }

        /* Buttons */
        .btn-purple {
            background: linear-gradient(135deg, var(--primary-purple), #8456d6);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: .65rem 1.1rem;
            border-radius: .65rem;
            box-shadow: 0 4px 12px rgba(111, 66, 193, .20);
            transition: all .2s ease;
        }

        .btn-purple:hover {
            background: linear-gradient(135deg, var(--dark-purple), var(--primary-purple));
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(111, 66, 193, .28);
        }

        /* Search */
        .plans-search {
            min-width: 280px;
            border: 1px solid var(--border-color);
            border-radius: .65rem;
            padding-top: .65rem;
            padding-bottom: .65rem;
        }

        .plans-search:focus {
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 .2rem rgba(111, 66, 193, .12);
        }

        .btn-filter {
            border: 1px solid var(--border-color);
            background: #fff;
            color: #555;
            font-weight: 600;
            border-radius: .65rem;
        }

        .btn-filter:hover {
            border-color: var(--primary-purple);
            color: var(--primary-purple);
            background: var(--light-purple);
        }

        /* Plan Card */
        .plan-card {
            height: 100%;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
            transition: all .25s ease;
            box-shadow: 0 3px 12px rgba(45, 42, 53, .06);
        }

        .plan-card:hover {
            transform: translateY(-5px);
            border-color: rgba(111, 66, 193, .35);
            box-shadow: 0 12px 30px rgba(45, 42, 53, .12);
        }

        /* Popular card */
        .plan-card.popular {
            border: 2px solid var(--yellow);
            box-shadow: 0 8px 25px rgba(255, 193, 7, .16);
        }

        .plan-card.popular:hover {
            box-shadow: 0 15px 35px rgba(255, 193, 7, .22);
        }

        /* Card Header */
        .plan-card-header {
            background: linear-gradient(145deg, #6f42c1, #58349d);
            color: #fff;
            padding: 1.5rem 1.25rem;
            text-align: center;
            position: relative;
        }

        .plan-card.popular .plan-card-header {
            background: linear-gradient(145deg, #6f42c1, #4b2a83);
        }

        .plan-card-header h5 {
            letter-spacing: .06em;
            font-size: .95rem;
        }

        /* Popular Badge */
        .plan-popular-badge {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translate(-50%, -1px);
            background: var(--yellow);
            color: #3d3000;
            font-size: .65rem;
            font-weight: 800;
            padding: .35rem .85rem;
            border-radius: 0 0 .5rem .5rem;
            letter-spacing: .08em;
        }

        .plan-card.popular .plan-card-header h5 {
            margin-top: 1rem;
        }

        /* Plan Icon */
        .plan-icon {
            width: 58px;
            height: 58px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .25);
            color: var(--yellow);
            font-size: 1.35rem;
        }

        /* Price */
        .plan-price {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -.03em;
        }

        /* Credits */
        .credits-number {
            color: var(--primary-purple);
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .credits-label {
            color: var(--text-muted);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
        }

        .total-credits {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--text-dark);
        }

        /* Bonus */
        .bonus-badge {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffe69c;
            font-size: .7rem;
            font-weight: 700;
            border-radius: 50rem;
            padding: .35rem .65rem;
        }

        /* Cost box */
        .bg-purple-light {
            background: var(--light-purple);
            border: 1px solid #e5daf7;
        }

        .text-purple {
            color: var(--primary-purple) !important;
        }

        .cost-label {
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .04em;
        }

        /* Stars */
        .plan-stars {
            color: var(--yellow);
            font-size: .8rem;
        }

        /* Status */
        .status-btn {
            border: 0;
            border-radius: 50rem;
            padding: .4rem .8rem;
            font-size: .72rem;
            font-weight: 700;
            transition: all .2s ease;
        }

        .status-active {
            background: #d1e7dd;
            color: #146c43;
        }

        .status-inactive {
            background: #e9ecef;
            color: #6c757d;
        }

        .status-active:hover {
            background: #badbcc;
        }

        .status-inactive:hover {
            background: #dee2e6;
        }

        /* Card footer */
        .plan-card-footer {
            background: #faf9fc;
            border-top: 1px solid var(--border-color);
            margin-top: auto;
        }

        .action-btn {
            font-size: .8rem;
            font-weight: 700;
            text-decoration: none;
        }

        .action-edit {
            color: var(--primary-purple);
        }

        .action-edit:hover {
            color: var(--dark-purple);
        }

        .action-delete {
            color: #dc3545;
        }

        .action-delete:hover {
            color: #bb2d3b;
        }

        /* Empty state */
        .empty-state {
            border: 1px dashed #d8d2e2;
            border-radius: 1rem;
            background: #fff;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            border-radius: 50%;
            background: var(--light-purple);
            color: var(--primary-purple);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        /* Pagination */
        .pagination .page-link {
            color: var(--primary-purple);
            border-color: var(--border-color);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-purple);
            border-color: var(--primary-purple);
            color: #fff;
        }

        .pagination .page-link:hover {
            background-color: var(--light-purple);
            color: var(--dark-purple);
        }

        /* Responsive */
        @media (max-width: 575.98px) {
            .plans-search {
                min-width: 0;
                width: 100%;
            }

            .search-wrapper {
                width: 100%;
            }

            .plans-toolbar {
                align-items: stretch !important;
            }

            .plans-toolbar>div {
                width: 100%;
            }

            .add-plan-btn {
                width: 100%;
            }
        }

        /* Table overrides */
        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #f9fafb;
            color: #6b7280;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.75rem 1rem;
        }

        .table tbody td {
            padding: 0.9rem 1rem;
            vertical-align: middle;
        }

        /* Badges */
        .badge-soft {
            padding: 0.35rem 0.65rem;
            font-weight: 600;
            border-radius: 999px;
            font-size: 0.7rem;
        }

        .badge-soft-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-soft-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-soft-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-soft-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-soft-primary {
            background: #ede9fe;
            color: #6d28d9;
        }

        .badge-soft-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .badge-soft-orange {
            background: #ffedd5;
            color: #9a3412;
        }

        /* Wallet card */
        .wallet-card {
            background: linear-gradient(135deg, var(--klick-purple) 0%, var(--klick-purple-dark) 100%);
            color: #fff;
            border-radius: 1rem;
            padding: 1.5rem;
        }

        /* General Cards */
        /* .wallet-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(45, 42, 53, .06);
            overflow: hidden;
            padding: 1.5rem;
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
        } */

        /* Utility */
        .text-purple {
            color: var(--klick-purple) !important;
        }

        .bg-purple-light {
            background-color: var(--klick-purple-light) !important;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.875rem;
            color: #374151;
        }
    </style>

    @stack('styles')
</head>

<body>
    <div id="wrapper">
        {{-- ===================================================== SIDEBAR ====================================================== --}}
        <aside class="sidebar"> {{-- Brand --}} <div class="brand">
                <div class="d-flex align-items-center gap-3">
                    <div class="brand-logo">
                        <img src="{{ asset('assets/logo/KlickCard.png') }}" alt="Brand Logo" class="h-10 w-auto">
                    </div>
                    <div>
                        <h4> {{ config('app.name') }} </h4> <small> Admin Panel </small>
                    </div>
                </div>
            </div> {{-- Navigation --}}
            <div class="sidebar-nav"> @php
                $navItems = [
                    [
                        'section' => 'Overview',
                        'items' => [['route' => 'admin.dashboard', 'icon' => 'fa-chart-line', 'label' => 'Dashboard']],
                    ],
                    [
                        'section' => 'Management',
                        'items' => [
                            ['route' => 'admin.merchants.index', 'icon' => 'fa-store', 'label' => 'Merchants'],
                            ['route' => 'admin.users.index', 'icon' => 'fa-users', 'label' => 'Users'],
                            ['route' => 'admin.menu-items.index', 'icon' => 'fa-utensils', 'label' => 'Menu List'],
                            [
                                'route' => 'admin.inventory.index',
                                'icon' => 'fa-boxes-stacked',
                                'label' => 'Inventory',
                                'stock_badge' => true,
                            ],
                            ['route' => 'admin.promotions.index', 'icon' => 'fa-tags', 'label' => 'Promotions'],
                        ],
                    ],
                    [
                        'section' => 'Credits & Billing',
                        'items' => [
                            ['route' => 'admin.plans.index', 'icon' => 'fa-crown', 'label' => 'Subscription Plans'],
                            ['route' => 'admin.wallets.index', 'icon' => 'fa-wallet', 'label' => 'Wallets'],
                            ['route' => 'admin.transactions.index', 'icon' => 'fa-receipt', 'label' => 'Transactions'],
                        ],
                    ],
                    [
                        'section' => 'Referral Program',
                        'items' => [
                            [
                                'route' => 'admin.referrals.index',
                                'icon' => 'fa-user-plus',
                                'label' => 'Referrals',
                                'referral_badge' => true, // shows count of pending approvals
                            ],
                            [
                                'route' => 'admin.referrals.transactions',
                                'icon' => 'fa-list-check',
                                'label' => 'Points Generated',
                            ],
                        ],
                    ],
                    [
                        'section' => 'Communications',
                        'items' => [
                            [
                                'route' => 'admin.broadcasts.index',
                                'icon' => 'fa-bullhorn',
                                'label' => 'Broadcast Notifications',
                                'broadcast_badge' => true,
                            ],
                        ],
                    ],
                    [
                        'section' => 'Settings',
                        'items' => [
                            [
                                'route' => 'admin.settings.payment',
                                'icon' => 'fa-credit-card',
                                'label' => 'Payment Gateways',
                            ],
                            ['route' => 'admin.settings.credits', 'icon' => 'fa-coins', 'label' => 'Credit Settings'],
                            [
                                'route' => 'admin.settings.referral',
                                'icon' => 'fa-gift',
                                'label' => 'Referral Settings',
                            ],
                        ],
                    ],
                ];
            @endphp @foreach ($navItems as $section)
                    <div class="sidebar-section"> {{ $section['section'] }} </div>
                    <ul class="nav flex-column mb-2">
                        @foreach ($section['items'] as $item)
                            @php
                                $routeName = $item['route'];

                                $activeRoutes = $item['active_routes'] ?? [$routeName];

                                $isActive = request()->routeIs($activeRoutes);

                            @endphp <li class="nav-item"> <a href="{{ route($routeName) }}"
                                    class="nav-link {{ $isActive ? 'active' : '' }}"> <i
                                        class="fas {{ $item['icon'] }}"></i> <span> {{ $item['label'] }} </span>
                                    @if (!empty($item['stock_badge']) && isset($lowStockCount))
                                        @if ($lowStockCount > 0)
                                            <span class="sidebar-badge"> {{ $lowStockCount }} </span>
                                        @endif
                                    @endif
                                    @if (!empty($item['broadcast_badge']) && isset($recentBroadcastCount) && $recentBroadcastCount > 0)
                                        <span class="sidebar-badge">{{ $recentBroadcastCount }}</span>
                                    @endif

                                    @if (!empty($item['referral_badge']) && isset($pendingReferralCount))
                                        @if ($pendingReferralCount > 0)
                                            <span class="sidebar-badge">{{ $pendingReferralCount }}</span>
                                        @endif
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div> {{-- Sidebar Footer --}} <div class="sidebar-footer">
                <form method="POST" action="{{ route('admin.logout') }}"> @csrf <button type="submit"
                        class="logout-btn"> <i class="fas fa-right-from-bracket"></i> <span> Logout </span> </button>
                </form>
            </div>
        </aside> {{-- ===================================================== MAIN CONTENT ====================================================== --}} <main class="content"> {{-- Top Navbar --}} <div
                class="navbar-custom d-flex justify-content-between align-items-center">
                <div class="navbar-page-title"> <i class="fas fa-layer-group"></i> <span> @yield('title', 'Dashboard') </span>
                </div> {{-- Admin --}} <div class="admin-profile"> @php
                    $adminName = Auth::user()->full_name ?? 'Admin';
                    $adminInitials = collect(preg_split('/\s+/', trim($adminName)))
                        ->filter()
                        ->take(2)
                        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                        ->implode('');
                @endphp <div class="admin-avatar">
                        {{ $adminInitials ?: 'A' }} </div>
                    <div class="d-none d-sm-block">
                        <div class="admin-name"> {{ $adminName }} </div> <span class="admin-role"> Administrator
                        </span>
                    </div>
                </div>
            </div> {{-- Alerts --}} @include('admin.partials.alerts') {{-- Page Content --}} @yield('content') </main>
    </div> {{-- Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> @stack('scripts') {{-- Toast --}} @include('admin.partials.toast')
</body>

</html>
