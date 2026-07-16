@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Dashboard</h1>
            <span class="text-muted">{{ now()->format('l, F j, Y') }}</span>
        </div>

        <!-- Welcome Message -->
        <div class="alert alert-info">
            <h4 class="alert-heading">Welcome, {{ Auth::user()->full_name ?? 'Admin' }}!</h4>
            <p class="mb-0">You are logged in as an administrator. This is your dashboard.</p>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Total Users</h6>
                                <h2 class="mb-0">0</h2>
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
                                <h6 class="card-subtitle mb-2 text-white-50">Total Orders</h6>
                                <h2 class="mb-0">0</h2>
                            </div>
                            <i class="fas fa-shopping-cart fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-dark-50">Pending Orders</h6>
                                <h2 class="mb-0">0</h2>
                            </div>
                            <i class="fas fa-clock fa-2x text-dark-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Total Revenue</h6>
                                <h2 class="mb-0">$0</h2>
                            </div>
                            <i class="fas fa-dollar-sign fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Row -->
        <div class="row g-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted text-center py-4">No recent activity to display.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="#" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Add User
                            </a>
                            <a href="#" class="btn btn-success">
                                <i class="fas fa-plus"></i> Create Order
                            </a>
                            <a href="#" class="btn btn-info text-white">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
