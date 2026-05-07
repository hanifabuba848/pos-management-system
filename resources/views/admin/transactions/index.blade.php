@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-clock-history"></i> Business-wide Transactions</h2>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Back to Admin</a>
    </div>

    <!-- Filter / Search (Optional future expansion) -->
    <div class="row mb-3">
        <div class="col-md-12 text-end text-muted">
            <small>Showing all recorded transactions across the system</small>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Reference</th>
                            <th>Cashier</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Method</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $trx)
                        <tr>
                            <td>{{ $trx->created_at->format('M d, Y h:i A') }}</td>
                            <td><strong>{{ $trx->reference_code }}</strong></td>
                            <td>
                                @if($trx->user)
                                    <span class="badge bg-dark">{{ $trx->user->name }}</span>
                                @else
                                    <span class="badge bg-secondary">System</span>
                                @endif
                            </td>
                            <td>{{ $trx->customer ? $trx->customer->name : 'Walk-in' }}</td>
                            <td class="fw-bold">${{ number_format($trx->total, 2) }}</td>
                            <td><span class="badge bg-info text-dark">{{ $trx->payment_method }}</span></td>
                            <td>
                                <a href="{{ route('cashier.receipt', $trx->id) }}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="bi bi-printer"></i> Receipt
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No transactions found in the system.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-3 d-flex justify-content-center">
        {{ $transactions->links() }}
    </div>
</div>
@endsection
