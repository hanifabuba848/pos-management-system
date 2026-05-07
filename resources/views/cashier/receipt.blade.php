<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $transaction->reference_code }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .receipt-container {
            max-width: 350px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 5px 0;
        }
        th.text-right, td.text-right {
            text-align: right;
        }
        .btn-print {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            background-color: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                max-width: 100%;
                padding: 0;
            }
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="text-center">
        <h2 style="margin: 0;">POS SYSTEM</h2>
        <p style="margin: 5px 0;">123 Business Road, City</p>
        <p style="margin: 5px 0;">Tel: (123) 456-7890</p>
    </div>

    <hr>
    <div>
        <p><strong>Receipt:</strong> {{ $transaction->reference_code }}</p>
        <p><strong>Date:</strong> {{ $transaction->created_at->format('Y-m-d H:i:s') }}</p>
        <p><strong>Cashier:</strong> {{ $transaction->user->name }}</p>
    </div>
    <hr>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'Unknown item' }}<br><small>${{ number_format($item->price, 2) }}</small></td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr>
    <table>
        <tr>
            <td><strong>Total</strong></td>
            <td class="text-right"><strong>${{ number_format($transaction->total, 2) }}</strong></td>
        </tr>
        <tr>
            <td>Payment ({{ $transaction->payment_method }})</td>
            <td class="text-right">${{ number_format($transaction->amount_tendered, 2) }}</td>
        </tr>
        <tr>
            <td>Change</td>
            <td class="text-right">${{ number_format($transaction->change_given, 2) }}</td>
        </tr>
    </table>

    <hr>
    <div class="text-center" style="margin-top: 20px;">
        <p>Thank you for your purchase!</p>
        <p>Please come again.</p>
    </div>

    <button onclick="window.print()" class="btn-print">Print Receipt</button>
</div>

</body>
</html>
