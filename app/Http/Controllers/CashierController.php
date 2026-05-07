<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function dashboard()
    {
        $today = now()->startOfDay();
        $transactions = Transaction::where('user_id', auth()->id())
                            ->where('created_at', '>=', $today)
                            ->get();

        $totalSales = $transactions->sum('total');
        $cashSales = $transactions->where('payment_method', 'Cash')->sum('total');
        $cardSales = $transactions->where('payment_method', 'Card')->sum('total');
        $transferSales = $transactions->where('payment_method', 'Transfer')->sum('total');

        return view('cashier.index', compact('totalSales', 'cashSales', 'cardSales', 'transferSales', 'transactions'));
    }

    public function transactions()
    {
        $transactions = Transaction::with(['customer'])
                            ->where('user_id', auth()->id())
                            ->orderBy('created_at', 'desc')
                            ->paginate(20);
        
        return view('cashier.transactions', compact('transactions'));
    }

    public function pos()
    {
        // For a real app, you shouldn't load all products on load.
        // We will just return the view and let Javascript fetch via barcode.
        return view('cashier.pos');
    }

    public function searchBarcode(Request $request)
    {
        $product = Product::where('barcode', $request->barcode)->where('stock_quantity', '>', 0)->first();
        // Fallback to searching by ID if barcode isn't setup for testing
        if (!$product) {
            $product = Product::where('id', $request->barcode)->where('stock_quantity', '>', 0)->first();
        }

        if ($product) {
            return response()->json($product);
        }

        return response()->json(['error' => 'Product not found or out of stock'], 404);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string|in:Cash,Card,Transfer',
            'amount_tendered' => 'required_if:payment_method,Cash|numeric|min:0',
        ]);

        try {
            $transactionRecord = null;
            DB::transaction(function () use ($request, &$transactionRef, &$transactionRecord) {
                // If customer is provided, find or create
                $customerId = null;
                if (!empty($request->customer_name)) {
                    $customer = \App\Models\Customer::firstOrCreate(
                        ['phone' => $request->customer_phone],
                        ['name' => $request->customer_name, 'email' => null]
                    );
                    $customerId = $customer->id;
                }

                $transactionRef = 'TRX-' . strtoupper(uniqid());

                $transactionRecord = Transaction::create([
                    'user_id' => auth()->id(),
                    'customer_id' => $customerId,
                    'reference_code' => $transactionRef,
                    'total' => 0,
                    'total_discount' => 0,
                    'payment_method' => $request->payment_method,
                    'amount_tendered' => $request->amount_tendered ?? 0,
                    'change_given' => 0,
                ]);

                $total = 0;
                $totalDiscount = 0;

                foreach ($request->products as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item['id']);
                    $quantity = $item['quantity'];

                    if ($product->stock_quantity < $quantity) {
                        // Throw exception to trigger DB rollback and catch block
                        throw new \Exception("Insufficient stock for {$product->name}. Only {$product->stock_quantity} left.");
                    }

                    $price = $product->price;
                    $discountAmt = $item['discount'] ?? 0;
                    if ($discountAmt > $price * $quantity) {
                         throw new \Exception("Discount exceeds item subtotal for {$product->name}.");
                    }

                    TransactionItem::create([
                        'transaction_id' => $transactionRecord->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'tax_amount' => 0,
                        'discount_amount' => $discountAmt,
                    ]);

                    $product->decrement('stock_quantity', $quantity);

                    $subtotal = ($price * $quantity) - $discountAmt;
                    $total += $subtotal;
                    $totalDiscount += $discountAmt;
                }

                $change = 0;
                if ($request->payment_method === 'Cash') {
                    $change = ($request->amount_tendered ?? 0) - $total;
                    if ($change < 0) {
                        throw new \Exception("Amount tendered is less than the total cost.");
                    }
                }

                $transactionRecord->update([
                    'total' => $total,
                    'total_discount' => $totalDiscount,
                    'change_given' => $change,
                ]);
            });

            return redirect()
                ->route('cashier.pos')
                ->with('success', "Transaction $transactionRef completed successfully.")
                ->with('receipt_transaction_id', $transactionRecord->id);

        } catch (\Exception $e) {
            return redirect()
                ->route('cashier.pos')
                ->withErrors(['checkout_error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function receipt($id)
    {
        $transaction = Transaction::with(['items.product', 'user'])->findOrFail($id);
        
        return view('cashier.receipt', compact('transaction'));
    }
}
