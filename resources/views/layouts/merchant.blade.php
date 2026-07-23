<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Merchant Panel') - {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </style>

    @stack('styles')
</head>

<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="brand">
                <h4>{{ config('app.name') }}</h4>
                <small>Merchant Panel</small>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('merchant.dashboard') }}"
                        class="nav-link {{ request()->routeIs('merchant.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('merchant.promotions.index') }}"
                        class="nav-link {{ request()->routeIs('merchant.promotions.*') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i> Promotions
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-utensils"></i> Menu Items
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-shopping-cart"></i> Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <form method="POST" action="{{ route('logout') }}">
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
            <div class="navbar-custom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">@yield('title', 'Dashboard')</h5>
                <div>
                    <span class="text-muted">
                        <i class="fas fa-user-circle"></i>
                        {{ Auth::user()->firstname }} {{ Auth::user()->lastname }}
                    </span>
                </div>
            </div>

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
