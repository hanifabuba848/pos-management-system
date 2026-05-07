<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Admin']); // Only admin can access
    }

    public function dashboard()
    {
        $today = now()->startOfDay();
        
        $todayRevenue = Transaction::where('created_at', '>=', $today)->sum('total');
        $totalRevenue = Transaction::sum('total');
        $totalTransactions = Transaction::count();
        $userCount = User::count();

        // Low stock alerts (items with 10 or less stock)
        $lowStockProducts = Product::where('stock_quantity', '<=', 10)->get();

        // Recent 5 transactions
        $recentTransactions = Transaction::with('user')->orderBy('created_at', 'desc')->take(5)->get();

        return view('admin.dashboard', compact(
            'todayRevenue', 'totalRevenue', 'totalTransactions', 'userCount',
            'lowStockProducts', 'recentTransactions'
        ));
    }

    public function transactions()
    {
        $transactions = Transaction::with(['user', 'customer'])->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.transactions.index', compact('transactions'));
    }
}
