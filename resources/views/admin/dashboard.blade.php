@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>
        <div>
            <a href="{{ route('products.index') }}" class="btn btn-outline-primary"><i class="bi bi-box"></i> Inventory</a>
            <a href="{{ route('categories.index') }}" class="btn btn-outline-success"><i class="bi bi-tags"></i> Categories</a>
            <a href="{{ route('users.index') }}" class="btn btn-outline-dark"><i class="bi bi-people"></i> Staff</a>
            <a href="{{ route('admin.transactions') }}" class="btn btn-outline-secondary"><i class="bi bi-receipt"></i> Transactions</a>
        </div>
    </div>

    <!-- Analytics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-success shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-uppercase text-white-50">Today's Revenue</h6>
                    <h2 class="mb-0">${{ number_format($todayRevenue, 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-primary shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-uppercase text-white-50">Total Revenue</h6>
                    <h2 class="mb-0">${{ number_format($totalRevenue, 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-uppercase text-white-50">Total Sales</h6>
                    <h2 class="mb-0">{{ number_format($totalTransactions) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-dark bg-warning shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-uppercase text-dark-50">Total Staff</h6>
                    <h2 class="mb-0">{{ number_format($userCount) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Low Stock Alerts -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-danger h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Low Stock Alerts</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($lowStockProducts as $product)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $product->name }}</strong><br>
                                    <small class="text-muted">Barcode: {{ $product->barcode ?? 'N/A' }}</small>
                                </div>
                                <span class="badge bg-danger rounded-pill">Only {{ $product->stock_quantity }} left</span>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted py-4">All products are sufficiently stocked!</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Recent Transactions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ref</th>
                                    <th>Cashier</th>
                                    <th>Total</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $trx)
                                    <tr>
                                        <td><strong>{{ end(explode('-', $trx->reference_code)) }}</strong></td>
                                        <td>{{ $trx->user->name ?? 'Unknown' }}</td>
                                        <td>${{ number_format($trx->total, 2) }}</td>
                                        <td class="text-muted small">{{ $trx->created_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4">No transactions recorded yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
