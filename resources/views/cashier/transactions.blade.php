@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-clock-history"></i> My Transaction History</h3>
        <a href="{{ route('cashier.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Discount</th>
                            <th>Method</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $trx)
                        <tr>
                            <td>{{ $trx->created_at->format('M d, Y h:i a') }}</td>
                            <td><strong>{{ $trx->reference_code }}</strong></td>
                            <td>{{ $trx->customer ? $trx->customer->name : 'Walk-in' }}</td>
                            <td>${{ number_format($trx->total, 2) }}</td>
                            <td>${{ number_format($trx->total_discount, 2) }}</td>
                            <td><span class="badge bg-secondary">{{ $trx->payment_method }}</span></td>
                            <td>
                                <a href="{{ route('cashier.receipt', $trx->id) }}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="bi bi-printer"></i> Reprint
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No transactions found in history.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
