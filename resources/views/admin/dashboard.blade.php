@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Dashboard</h1>
                <p class="text-muted small">Welcome back, {{ Auth::user()->firstname }}! Here's what's happening.</p>
            </div>
            <div>
                <span class="text-muted">{{ now()->format('l, F j, Y') }}</span>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- MAIN STATISTICS CARDS -->
        <!-- ============================================ -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Total Users</h6>
                                <h2 class="mb-0">{{ number_format($stats['total_users']) }}</h2>
                            </div>
                            <i class="fas fa-users fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Merchants</h6>
                                <h2 class="mb-0">{{ number_format($stats['total_merchants']) }}</h2>
                                <small>{{ $stats['active_merchants'] }} active</small>
                            </div>
                            <i class="fas fa-store fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Menu Items</h6>
                                <h2 class="mb-0">{{ number_format($stats['total_menu_items']) }}</h2>
                            </div>
                            <i class="fas fa-utensils fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-dark-50">Promotions</h6>
                                <h2 class="mb-0">{{ number_format($stats['total_promotions']) }}</h2>
                                <small>{{ $stats['active_promotions'] }} active</small>
                            </div>
                            <i class="fas fa-tags fa-2x text-dark-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- INVENTORY STATISTICS -->
        <!-- ============================================ -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Total Items</h6>
                                <h2 class="mb-0">{{ number_format($inventoryStats['total_items']) }}</h2>
                            </div>
                            <i class="fas fa-boxes fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">In Stock</h6>
                                <h2 class="mb-0">{{ number_format($inventoryStats['in_stock']) }}</h2>
                            </div>
                            <i class="fas fa-check-circle fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-dark-50">Low Stock</h6>
                                <h2 class="mb-0">{{ number_format($inventoryStats['low_stock']) }}</h2>
                            </div>
                            <i class="fas fa-exclamation-triangle fa-2x text-dark-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Out of Stock</h6>
                                <h2 class="mb-0">{{ number_format($inventoryStats['out_of_stock']) }}</h2>
                            </div>
                            <i class="fas fa-times-circle fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- CHARTS ROW -->
        <!-- ============================================ -->
        <div class="row g-4 mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">New Users & Merchants (Last 12 Months)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="growthChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">User Roles</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="roleChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- RECENT ACTIVITIES -->
        <!-- ============================================ -->
        <div class="row g-4">
            <!-- Recent Users -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-user-plus"></i> Recent Users</h5>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($recentUsers as $user)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $user->firstname }} {{ $user->lastname }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                    <div>
                                        <span
                                            class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'merchant' ? 'warning' : 'info') }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                        <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                        <small class="text-muted d-block text-end">
                                            {{ $user->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-3 text-muted">No users yet</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Merchants -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-store"></i> Recent Merchants</h5>
                        <a href="{{ route('admin.merchants.index') }}" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($recentMerchants as $merchant)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $merchant->business_name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $merchant->email }}
                                            @if ($merchant->branch_name)
                                                - {{ $merchant->branch_name }}
                                            @endif
                                        </small>
                                    </div>
                                    <div>
                                        <span
                                            class="badge bg-{{ $merchant->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($merchant->status) }}
                                        </span>
                                        <small class="text-muted d-block text-end">
                                            {{ $merchant->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-3 text-muted">No merchants yet</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- RECENT PROMOTIONS & TRANSACTIONS -->
        <!-- ============================================ -->
        <div class="row g-4 mt-2">
            <!-- Recent Promotions -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-tags"></i> Recent Promotions</h5>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($recentPromotions as $promotion)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $promotion->title }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $promotion->merchant->business_name ?? 'N/A' }}
                                        </small>
                                    </div>
                                    <div>
                                        <span
                                            class="badge bg-{{ $promotion->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($promotion->status) }}
                                        </span>
                                        <small class="text-muted d-block text-end">
                                            {{ $promotion->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-3 text-muted">No promotions yet</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Inventory Transactions -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-history"></i> Recent Inventory Activity</h5>
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($recentTransactions as $transaction)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $transaction->menuItem->name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <span
                                                class="badge bg-{{ $transaction->type === 'stock_in' ? 'success' : ($transaction->type === 'stock_out' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($transaction->type) }}
                                            </span>
                                            {{ $transaction->quantity }} units
                                        </small>
                                    </div>
                                    <div>
                                        <small class="text-muted">
                                            {{ $transaction->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-3 text-muted">No transactions yet</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- TOP PERFORMERS -->
        <!-- ============================================ -->
        <div class="row g-4 mt-2">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-trophy"></i> Top Merchants by Menu Items</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($topMerchants as $merchant)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $merchant->business_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $merchant->email }}</small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">
                                        {{ $merchant->menu_items_count }} items
                                    </span>
                                </div>
                            @empty
                                <div class="text-center py-3 text-muted">No data yet</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-trophy"></i> Top Merchants by Promotions</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($topPromoMerchants as $merchant)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $merchant->business_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $merchant->email }}</small>
                                    </div>
                                    <span class="badge bg-success rounded-pill">
                                        {{ $merchant->promotions_count }} promotions
                                    </span>
                                </div>
                            @empty
                                <div class="text-center py-3 text-muted">No data yet</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- CHART.JS SCRIPTS -->
    <!-- ============================================ -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ============================================
            // GROWTH CHART (Users & Merchants)
            // ============================================
            const growthCtx = document.getElementById('growthChart').getContext('2d');

            // Prepare data
            const months = @json($monthlyUsers->pluck('month_name'));
            const userData = @json($monthlyUsers->pluck('count'));
            const merchantData = @json($monthlyMerchants->pluck('count'));

            // Ensure data arrays have same length
            const maxLength = Math.max(userData.length, merchantData.length);
            while (userData.length < maxLength) userData.push(0);
            while (merchantData.length < maxLength) merchantData.push(0);

            new Chart(growthCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                            label: 'New Users',
                            data: userData,
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.3,
                            fill: true,
                            pointRadius: 3,
                            pointBackgroundColor: '#667eea'
                        },
                        {
                            label: 'New Merchants',
                            data: merchantData,
                            borderColor: '#48bb78',
                            backgroundColor: 'rgba(72, 187, 120, 0.1)',
                            tension: 0.3,
                            fill: true,
                            pointRadius: 3,
                            pointBackgroundColor: '#48bb78'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });

            // ============================================
            // ROLE CHART
            // ============================================
            const roleCtx = document.getElementById('roleChart').getContext('2d');
            const roleData = @json($roleDistribution);

            const roleColors = {
                'admin': '#e53e3e',
                'merchant': '#ed8936',
                'customer': '#4299e1'
            };

            const roleLabels = roleData.map(item => ucfirst(item.role));
            const roleCounts = roleData.map(item => item.count);
            const roleBackgroundColors = roleData.map(item => roleColors[item.role] || '#a0aec0');

            new Chart(roleCtx, {
                type: 'doughnut',
                data: {
                    labels: roleLabels,
                    datasets: [{
                        data: roleCounts,
                        backgroundColor: roleBackgroundColors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15
                            }
                        }
                    }
                }
            });
        });

        // Helper function
        function ucfirst(str) {
            if (!str) return str;
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
    </script>
@endsection
