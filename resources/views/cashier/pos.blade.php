@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Top Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('cashier.dashboard') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        
        <div>
            <button class="btn btn-warning" id="btn-hold-cart" onclick="holdCart()"><i class="bi bi-pause-circle"></i> Hold Cart</button>
            <button class="btn btn-info" id="btn-resume-cart" onclick="loadHeldCart()"><i class="bi bi-play-circle"></i> Resume Cart <span class="badge bg-danger" id="held-count">0</span></button>
        </div>
    </div>

    <div class="row">
        <!-- Left Side: Scanner and Cart -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-cart"></i> Current Transaction</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success d-flex justify-content-between align-items-center">
                            {{ session('success') }}
                            @if(session('receipt_transaction_id'))
                                <a href="{{ route('cashier.receipt', session('receipt_transaction_id')) }}" target="_blank" class="btn btn-sm btn-outline-success bg-white">
                                    <i class="bi bi-printer"></i> Print
                                </a>
                                <script>
                                    window.open("{{ route('cashier.receipt', session('receipt_transaction_id')) }}", '_blank', 'width=400,height=600');
                                </script>
                            @endif
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <input type="text" id="barcode_scanner" class="form-control form-control-lg border-primary" placeholder="🔍 Scan Barcode or enter Product ID..." autofocus autocomplete="off">
                    </div>

                    <form action="{{ route('cashier.checkout') }}" method="POST" id="checkout-form">
                        @csrf
                        <div class="table-responsive" style="min-height: 250px;">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th style="width: 100px;">Qty</th>
                                        <th style="width: 120px;">Discount ($)</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="cart-tbody">
                                    <!-- Populated by JS -->
                                </tbody>
                            </table>
                        </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Checkout pane -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Checkout</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 bg-light p-3 rounded text-center">
                        <small class="text-muted text-uppercase fw-bold">Grand Total</small>
                        <h2 class="mb-0 text-success" id="grand-total">$0.00</h2>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">CUSTOMER (OPTIONAL)</label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control form-control-sm mb-2" placeholder="Name (Walk-in)">
                        <input type="text" name="customer_phone" id="customer_phone" class="form-control form-control-sm" placeholder="Phone Number">
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label text-muted small fw-bold">PAYMENT METHOD</label>
                        <select name="payment_method" id="payment_method" class="form-select form-select-lg">
                            <option value="Cash">Cash</option>
                            <option value="Card">Card</option>
                            <option value="Transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <div class="mb-3" id="tender-div">
                        <label for="amount_tendered" class="form-label text-muted small fw-bold">AMOUNT TENDERED ($)</label>
                        <input type="number" step="0.01" min="0" name="amount_tendered" id="amount_tendered" class="form-control form-control-lg">
                    </div>

                    <div class="bg-dark text-white p-3 rounded mb-3" id="change-div">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Change:</span>
                            <h4 id="change-display" class="mb-0">$0.00</h4>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold" id="btn-checkout" disabled>
                        <i class="bi bi-check-circle"></i> COMPLETE SALE
                    </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cartTbody = document.getElementById('cart-tbody');
        const barcodeInput = document.getElementById('barcode_scanner');
        const grandTotalDisplay = document.getElementById('grand-total');
        const btnCheckout = document.getElementById('btn-checkout');
        const paymentMethod = document.getElementById('payment_method');
        const amountTendered = document.getElementById('amount_tendered');
        const tenderDiv = document.getElementById('tender-div');
        const changeDiv = document.getElementById('change-div');
        const changeDisplay = document.getElementById('change-display');
        const heldCountDisplay = document.getElementById('held-count');
        const customerName = document.getElementById('customer_name');
        const customerPhone = document.getElementById('customer_phone');

        let cart = [];

        // Check for held carts count immediately
        updateHeldCount();

        // Check if there is an initial product ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const initialProductId = urlParams.get('product_id');

        if (initialProductId) {
            fetchProduct(initialProductId);
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Keep focus on scanner when clicking background
        document.body.addEventListener('click', (e) => {
            if(e.target.tagName !== 'INPUT' && e.target.tagName !== 'SELECT' && e.target.tagName !== 'BUTTON') {
                barcodeInput.focus();
            }
        });

        paymentMethod.addEventListener('change', function() {
            if(this.value === 'Cash') {
                tenderDiv.style.display = 'block';
                changeDiv.style.display = 'block';
            } else {
                tenderDiv.style.display = 'none';
                changeDiv.style.display = 'none';
                amountTendered.value = '';
            }
        });

        amountTendered.addEventListener('input', calculateChange);

        barcodeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const code = this.value.trim();
                if (code) {
                    fetchProduct(code);
                }
                this.value = '';
            }
        });

        function fetchProduct(code) {
            fetch(`/cashier/barcode?barcode=${code}`)
                .then(res => {
                    if (!res.ok) throw new Error('Product not found or Out of stock');
                    return res.json();
                })
                .then(product => {
                    addToCart(product);
                })
                .catch(err => {
                    alert(err.message);
                });
        }

        function addToCart(product) {
            const existing = cart.find(i => i.id === product.id);
            if (existing) {
                if (existing.quantity >= product.stock_quantity) {
                    alert('Cannot add more. Max stock reached.');
                    return;
                }
                existing.quantity++;
            } else {
                cart.push({ ...product, quantity: 1, discount: 0 });
            }
            renderCart();
        }

        window.updateQuantity = function(id, qty) {
            const item = cart.find(i => i.id === id);
            if (item) {
                const newQty = parseInt(qty);
                if (newQty > item.stock_quantity) {
                    alert('Max stock is ' + item.stock_quantity);
                    renderCart();
                    return;
                }
                item.quantity = newQty;
                if (item.quantity <= 0) {
                    cart = cart.filter(i => i.id !== id);
                }
                renderCart();
            }
        };

        window.updateDiscount = function(id, discountVal) {
            const item = cart.find(i => i.id === id);
            if (item) {
                let disc = parseFloat(discountVal) || 0;
                let maxSubtotal = item.price * item.quantity;
                if (disc > maxSubtotal) {
                    alert("Discount cannot exceed item subtotal!");
                    disc = maxSubtotal;
                }
                item.discount = disc;
                renderCart();
            }
        }

        window.removeItem = function(id) {
            cart = cart.filter(i => i.id !== id);
            renderCart();
        }

        function renderCart() {
            cartTbody.innerHTML = '';
            let total = 0;

            cart.forEach((item, index) => {
                const grossSubtotal = item.price * item.quantity;
                const netSubtotal = grossSubtotal - (item.discount || 0);
                total += netSubtotal;

                cartTbody.innerHTML += `
                    <tr>
                        <td>
                            <strong>${item.name}</strong><br>
                            <small class="text-muted">Stock: ${item.stock_quantity}</small>
                        </td>
                        <td>$${parseFloat(item.price).toFixed(2)}</td>
                        <td>
                            <input type="hidden" name="products[${index}][id]" value="${item.id}">
                            <input type="number" name="products[${index}][quantity]" value="${item.quantity}" min="1" max="${item.stock_quantity}" class="form-control form-control-sm text-center" onchange="updateQuantity(${item.id}, this.value)">
                        </td>
                        <td>
                            <input type="number" name="products[${index}][discount]" value="${item.discount || 0}" min="0" step="0.01" class="form-control form-control-sm text-center text-danger" onchange="updateDiscount(${item.id}, this.value)">
                        </td>
                        <td class="fw-bold">$${netSubtotal.toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(${item.id})"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                `;
            });

            grandTotalDisplay.innerText = '$' + total.toFixed(2);
            btnCheckout.disabled = cart.length === 0;
            calculateChange();
        }

        function calculateChange() {
            let total = 0;
            cart.forEach(item => {
                total += ((item.price * item.quantity) - (item.discount || 0));
            });
            
            if (paymentMethod.value === 'Cash') {
                const tendered = parseFloat(amountTendered.value) || 0;
                const change = tendered - total;
                changeDisplay.innerText = '$' + Math.max(0, change).toFixed(2);
                changeDisplay.classList.remove('text-danger', 'text-success');
                if(change < 0) changeDisplay.classList.add('text-danger');
                else changeDisplay.classList.add('text-success');
            }
        }

        // --- HOLD & RESUME ---
        function getHeldCarts() {
            return JSON.parse(localStorage.getItem('holdCarts') || '[]');
        }

        function updateHeldCount() {
            heldCountDisplay.innerText = getHeldCarts().length;
        }

        window.holdCart = function() {
            if(cart.length === 0) {
                alert("Cart is empty!");
                return;
            }
            let holdName = customerName.value.trim() || 'Customer ' + new Date().toLocaleTimeString();
            let holdCarts = getHeldCarts();
            holdCarts.push({
                name: holdName,
                custPhone: customerPhone.value.trim(),
                items: cart,
                time: new Date().toISOString()
            });
            localStorage.setItem('holdCarts', JSON.stringify(holdCarts));
            
            cart = [];
            customerName.value = '';
            customerPhone.value = '';
            renderCart();
            updateHeldCount();
            barcodeInput.focus();
        };

        window.loadHeldCart = function() {
            let holdCarts = getHeldCarts();
            if(holdCarts.length === 0) {
                alert("No held carts.");
                return;
            }
            if(cart.length > 0) {
                if(!confirm("You have items in your active cart. Holding them automatically...")) return;
                window.holdCart();
                holdCarts = getHeldCarts(); // refresh after hold
            }

            // Retrieve the oldest held cart
            let loadedCart = holdCarts.shift();
            cart = loadedCart.items;
            customerName.value = loadedCart.name.startsWith('Customer ') ? '' : loadedCart.name;
            customerPhone.value = loadedCart.custPhone || '';
            
            localStorage.setItem('holdCarts', JSON.stringify(holdCarts));
            renderCart();
            updateHeldCount();
        };
    });
</script>
@endsection
