@extends('layouts.app')

@section('content')
<div class="row g-3">
    <!-- قائمة المنتجات (اليسار) -->
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title m-0">المنتجات</h3>
                <div class="w-50">
                    <input type="text" id="search-input" class="form-control" placeholder="بحث بالاسم أو الباركود..." autofocus>
                </div>
            </div>
            <div class="card-body p-3 overflow-auto" style="max-height: 75vh;">
                <div class="row g-2" id="products-grid">
                    @foreach($products as $product)
                        @php
                            $qty =$product->branches->first()->pivot->quantity ?? 0;
                        @endphp
                        <div class="col-md-4 col-6 product-card" 
                             data-name="{{ mb_strtolower($product->name) }}" 
                             data-barcode="{{ $product->barcode }}"
                             onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->selling_price }}, {{$qty }})">
                            <div class="card card-sm border shadow-sm cursor-pointer hover-shadow" style="cursor: pointer;">
                                <div class="card-body text-center p-2">
                                    <div class="fw-bold fs-5 text-truncate">{{ $product->name }}</div>
                                    <div class="text-success fw-bold my-1">{{ number_format($product->selling_price, 2) }} د.ل</div>
                                    <span class="badge {{ $qty > 0 ? 'bg-blue-lt' : 'bg-danger-lt' }}">
                                        المتوفر: {{ $qty }} {{$product->unit }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- سلة الشراء والحساب (اليمين) -->
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title text-white">فاتورة بيع جديدة</h3>
            </div>
            <div class="card-body p-2 d-flex flex-column justify-content-between" style="min-height: 70vh;">
                <!-- جدول السلة -->
                <div class="table-responsive flex-grow-1 overflow-auto" style="max-height: 45vh;">
                    <table class="table table-vcenter table-borderless">
                        <thead>
                            <tr class="border-bottom">
                                <th>المنتج</th>
                                <th style="width: 110px;">الكمية</th>
                                <th>الإجمالي</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cart-table-body">
                            <!-- عناصر السلة -->
                        </tbody>
                    </table>
                </div>

                <!-- الإجماليات والمحاسبة -->
                <div class="border-top pt-3 bg-light p-3 rounded">
                    <div class="d-flex justify-content-between fs-3 fw-bold mb-2">
                        <span>الإجمالي الكلي:</span>
                        <span class="text-primary" id="grand-total">0.00 د.ل</span>
                    </div>

                    <div class="row g-2 my-2">
                        <div class="col-6">
                            <label class="form-label mb-1">المبلغ المدفوع</label>
                            <input type="number" id="paid-amount" class="form-control form-control-lg fw-bold" value="0" oninput="calculateChange()">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1">المتبقي (المُرجع)</label>
                            <input type="text" id="change-amount" class="form-control form-control-lg fw-bold text-danger" value="0.00" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label mb-1">طريقة الدفع</label>
                        <select id="payment-method" class="form-select">
                            <option value="cash">نقداً (Cash)</option>
                            <option value="card">بطاقة مصرفية (Card)</option>
                            <option value="transfer">تحويل بنكي</option>
                        </select>
                    </div>

                    <button class="btn btn-success btn-lg w-100 py-2 fs-3 fw-bold" onclick="processCheckout()">
                        ✓ إنهاء الفاتورة وطباعة
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];

function addToCart(id, name, price, stock) {
    if (stock <= 0) {
        alert('المنتج غير متوفر بالمخزون!');
        return;
    }

    let existingItem = cart.find(item => item.product_id === id);

    if (existingItem) {
        if (existingItem.quantity + 1 > stock) {
            alert('الكمية المطلوبة تتجاوز المتاح بالمخزون!');
            return;
        }
        existingItem.quantity++;
    } else {
        cart.push({
            product_id: id,
            name: name,
            price: price,
            quantity: 1,
            maxStock: stock
        });
    }

    updateCartUI();
}

function updateCartUI() {
    const tbody = document.getElementById('cart-table-body');
    tbody.innerHTML = '';

    let grandTotal = 0;

    cart.forEach((item, index) => {
        let subtotal = item.price * item.quantity;
        grandTotal += subtotal;

        tbody.innerHTML += `
            <tr class="border-bottom">
                <td>
                    <div class="fw-bold">${item.name}</div>
                    <small class="text-muted">${item.price.toFixed(2)} د.ل</small>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary p-1" onclick="updateQty(${index}, -1)">-</button>
                        <input type="text" class="form-control text-center px-1" value="${item.quantity}" readonly>
                        <button class="btn btn-outline-secondary p-1" onclick="updateQty(${index}, 1)">+</button>
                    </div>
                </td>
                <td class="fw-bold">${subtotal.toFixed(2)}</td>
                <td>
                    <button class="btn btn-sm btn-ghost-danger p-1" onclick="removeItem(${index})">✕</button>
                </td>
            </tr>
        `;
    });

    document.getElementById('grand-total').innerText = grandTotal.toFixed(2) + ' د.ل';
    document.getElementById('paid-amount').value = grandTotal.toFixed(2);
    calculateChange();
}

function updateQty(index, change) {
    let item = cart[index];
    let newQty = item.quantity + change;

    if (newQty <= 0) {
        cart.splice(index, 1);
    } else if (newQty > item.maxStock) {
        alert('تجاوزت الكمية المتوفرة بالمخزون!');
    } else {
        item.quantity = newQty;
    }
    updateCartUI();
}

function removeItem(index) {
    cart.splice(index, 1);
    updateCartUI();
}

function calculateChange() {
    let total = parseFloat(document.getElementById('grand-total').innerText) || 0;
    let paid = parseFloat(document.getElementById('paid-amount').value) || 0;
    let change = paid - total;
    document.getElementById('change-amount').value = change >= 0 ? change.toFixed(2) + ' د.ل' : '0.00 د.ل';
}

document.getElementById('search-input').addEventListener('input', function (e) {
    let query = e.target.value.toLowerCase().trim();
    let cards = document.querySelectorAll('.product-card');

    cards.forEach(card => {
        let name = card.getAttribute('data-name');
        let barcode = card.getAttribute('data-barcode');

        if (name.includes(query) || (barcode && barcode.includes(query))) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});

function processCheckout() {
    if (cart.length === 0) {
        alert('السلة فارغة!');
        return;
    }

    let paidAmount = parseFloat(document.getElementById('paid-amount').value);
    let paymentMethod = document.getElementById('payment-method').value;

    fetch("{{ route('pos.store') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            items: cart,
            paid_amount: paidAmount,
            payment_method: paymentMethod
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('تم إنشاء الفاتورة بنجاح: ' + data.invoice_number);
            cart = [];
            location.reload();
        } else {
            alert(data.message || 'حدث خطأ أثناء إتمام العملية.');
        }
    })
    .catch(err => alert('حدث خطأ في الاتصال بالخادم.'));
}
</script>
@endsection