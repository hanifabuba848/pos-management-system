@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <h2><i class="bi bi-person-badge"></i> Cashier Dashboard</h2>
            <div>
                <a href="{{ route('cashier.pos') }}" class="btn btn-primary btn-lg"><i class="bi bi-cart"></i> Go to POS</a>
                <a href="{{ route('cashier.transactions') }}" class="btn btn-secondary btn-lg"><i class="bi bi-clock-history"></i> Sale History</a>
            </div>
        </div>
    </div>

    <h4><i class="bi bi-box-arrow-in-right"></i> My Shift Summary (Today)</h4>
    <hr>
    
    <div class="row text-center mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Sales</h5>
                    <h2>${{ number_format($totalSales, 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Cash</h5>
                    <h2>${{ number_format($cashSales, 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5>Card</h5>
                    <h2>${{ number_format($cardSales, 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Transfer</h5>
                    <h2>${{ number_format($transferSales, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <h4><i class="bi bi-receipt"></i> Today's Transactions</h4>
    <div class="table-responsive">
        <table class="table table-striped table-hover mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Ref</th>
                    <th>Time</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Method</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $trx)
                    <tr>
                        <td>{{ $trx->reference_code }}</td>
                        <td>{{ $trx->created_at->format('H:i:s a') }}</td>
                        <td>{{ $trx->customer_id ? $trx->customer->name : 'Walk-in' }}</td>
                        <td>${{ number_format($trx->total, 2) }}</td>
                        <td>{{ $trx->payment_method }}</td>
                        <td>
                            <a href="{{ route('cashier.receipt', $trx->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Print Receipt</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No transactions today yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
