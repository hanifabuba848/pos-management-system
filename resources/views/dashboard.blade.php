@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center mt-5">
            <h1 class="display-4 border-bottom pb-3 mb-4">Welcome to POS System Dashboard!</h1>
            <p class="lead text-muted mb-5">You are logged in successfully.</p>
            
            <div class="d-flex justify-content-center gap-3">
                @if(auth()->user()->hasRole('Admin'))
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg shadow-sm">
                        <i class="bi bi-speedometer2"></i> Admin Dashboard
                    </a>
                @endif
                
                @if(auth()->user()->hasRole('Cashier'))
                    <a href="{{ route('cashier.pos') }}" class="btn btn-success btn-lg shadow-sm">
                        <i class="bi bi-cart-check"></i> Go to Cashier POS
                    </a>
                @endif

                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-lg shadow-sm">
                    <i class="bi bi-box"></i> View Products
                </a>

                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-lg shadow-sm">
                    <i class="bi bi-tags"></i> View Categories
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
