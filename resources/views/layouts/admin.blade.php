<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        #wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #2d3748;
            color: white;
            min-height: 100vh;
            padding: 20px 0;
            flex-shrink: 0;
        }

        .sidebar .brand {
            text-align: center;
            padding: 10px 0 20px;
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

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 24px;
            border-radius: 0;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .sidebar .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        .navbar-custom {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
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

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="brand">
                <h4>{{ config('app.name') }}</h4>
                <small>Admin Panel</small>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.merchants.index') }}"
                        class="nav-link {{ request()->routeIs('admin.merchants.*') ? 'active' : '' }}">
                        <i class="fas fa-box"></i> Merchants
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}"
                        class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.menu-items.index') }}"
                        class="nav-link {{ request()->routeIs('admin.menu-items.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart"></i> Menu List
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.inventory.index') }}"
                        class="nav-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                        <i class="fas fa-boxes"></i> Inventory
                        @php
                            $lowStockCount = App\Models\MenuItem::where(
                                'stock_quantity',
                                '<=',
                                DB::raw('low_stock_threshold'),
                            )
                                ->where('stock_quantity', '>', 0)
                                ->count();
                        @endphp
                        @if ($lowStockCount > 0)
                            <span class="badge bg-danger ms-1">{{ $lowStockCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.promotions.index') }}"
                        class="nav-link {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i> Promotions
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-shopping-cart"></i> Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-box"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100 mx-2"
                            style="width: calc(100% - 30px) !important;">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Top Navbar -->
            <div class="navbar-custom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">@yield('title', 'Dashboard')</h5>
                <div>
                    <span class="text-muted">
                        <i class="fas fa-user-circle"></i>
                        {{ Auth::user()->full_name ?? 'Admin' }}
                    </span>
                </div>
            </div>

            <!-- Page Content -->
            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>

</html>
