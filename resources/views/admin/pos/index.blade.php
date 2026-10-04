@extends('layouts.app')

@section('content')
<div class="row g-3">
    <!-- قائمة المنتجات (اليسار) -->
    <div class="col-md-7">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="row g-2 align-items-center w-100">
                    <div class="col-md-5">
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7" /><line x1="21" y1="21" x2="15" y2="15" /></svg>
                            </span>
                            <input type="text" id="search-input" class="form-control form-control-lg" placeholder="بحث بالاسم أو الباركود... (F2)" autofocus>
                        </div>
                    </div>
                    <!-- نوع التسعير: قطاعي / جملة -->
                    <div class="col-md-4">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="price_type" id="price_retail" value="retail" checked onchange="changePriceType('retail')">
                            <label class="btn btn-outline-primary" for="price_retail">قطاعي</label>
                            
                            <input type="radio" class="btn-check" name="price_type" id="price_wholesale" value="wholesale" onchange="changePriceType('wholesale')">
                            <label class="btn btn-outline-primary" for="price_wholesale">جملة</label>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <span class="badge bg-blue-lt fs-6 p-2 w-100" id="products-count">العدد: {{ count($products) }}</span>
                    </div>
                </div>
            </div>

            <div class="card-body p-3 overflow-auto" style="max-height: 75vh;">
                <div class="row g-2" id="products-grid">
                    @foreach($products as $product)
                        @php
                            $qty =$product->branches->first()->pivot->quantity ?? 0;
                            $retailPrice =$product->selling_price;
                            $wholesalePrice = $product->wholesale_price ?? $product->selling_price;
                        @endphp
                        <div class="col-md-4 col-6 product-card" 
                             data-name="{{ mb_strtolower($product->name) }}" 
                             data-barcode="{{ $product->barcode }}"
                             onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{$retailPrice }}, {{ $wholesalePrice }}, {{$qty }})">
                            <div class="card card-sm border shadow-sm cursor-pointer hover-shadow text-center p-2 h-100">
                                <div class="fw-bold fs-4 text-truncate mb-1">{{ $product->name }}</div>
                                
                                <div class="my-1">
                                    <span class="text-success fw-bold fs-3 price-display" data-retail="{{ $retailPrice }}" data-wholesale="{{ $wholesalePrice }}">
                                        {{ number_format($retailPrice, 2) }} د.ل
                                    </span>
                                </div>
                                
                                <div class="mt-auto">
                                    <span class="badge {{ $qty > 0 ? 'bg-blue-lt text-blue' : 'bg-danger-lt text-danger' }} w-100 py-1">
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
        <div class="card h-100 shadow-sm border-0">
            <!-- اختيار الزبون -->
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
                <h3 class="card-title text-white m-0 fs-3">فاتورة بيع</h3>
                <span class="badge bg-white text-primary fw-bold" id="invoice-type-badge">نقدي</span>
            </div>
            
            <div class="p-2 bg-light border-bottom">
                <div class="row g-2 align-items-center">
                    <div class="col-9">
                        <label class="form-label mb-1 fs-6 fw-bold">العميل / الزبون:</label>
                        <select id="customer-id" class="form-select form-select-sm" onchange="onCustomerChange()">
                            <option value="" data-type="cash" data-balance="0">زبون نقدي (عام)</option>
                            @if(isset($customers))
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" data-type="credit" data-balance="{{ $customer->balance ?? 0 }}">
                                        {{ $customer->name }} {{ $customer->phone ? '('.$customer->phone.')' : '' }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-3 text-end pt-3">
                        <button class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#modal-add-customer">+ جديد</button>
                    </div>
                </div>
                <!-- رصيد الزبون إن وجد -->
                <div id="customer-balance-info" class="mt-1 d-none fs-6 text-muted">
                    الرصيد السارق/السابق للزبون: <span id="customer-balance" class="fw-bold text-dark">0.00 د.ل</span>
                </div>
            </div>

            <div class="card-body p-2 d-flex flex-column justify-content-between" style="min-height: 65vh;">
                <!-- جدول السلة -->
                <div class="table-responsive flex-grow-1 overflow-auto" style="max-height: 38vh;">
                    <table class="table table-vcenter table-borderless table-striped">
                        <thead>
                            <tr class="border-bottom bg-white">
                                <th>المنتج</th>
                                <th style="width: 100px;">الكمية</th>
                                <th>السعر</th>
                                <th>الإجمالي</th>
                                <th style="width: 30px;"></th>
                            </tr>
                        </thead>
                        <tbody id="cart-table-body">
                            <!-- عناصر السلة -->
                        </tbody>
                    </table>
                </div>

                <!-- الإجماليات والمحاسبة -->
                <div class="border-top pt-2 bg-light p-3 rounded">
                    <div class="d-flex justify-content-between fs-2 fw-bold mb-2">
                        <span>الإجمالي الكلي:</span>
                        <span class="text-primary" id="grand-total">0.00 د.ل</span>
                    </div>

                    <div class="row g-2 my-2">
                        <div class="col-6">
                            <label class="form-label mb-1">المبلغ المدفوع</label>
                            <input type="number" step="0.01" id="paid-amount" class="form-control form-control-lg fw-bold" value="0" oninput="calculateChange()">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1" id="change-label">المُرجع (الباقي)</label>
                            <input type="text" id="change-amount" class="form-control form-control-lg fw-bold text-danger" value="0.00" readonly>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12">
                            <label class="form-label mb-1">طريقة الدفع</label>
                            <select id="payment-method" class="form-select form-select-lg" onchange="onPaymentMethodChange()">
                                <option value="cash">نقداً (Cash)</option>
                                <option value="credit">آجل / على الحساب (Credit)</option>
                                <option value="card">بطاقة مصرفية (Card)</option>
                                <option value="transfer">تحويل بنكي</option>
                            </select>
                        </div>
                    </div>

                    <button class="btn btn-success btn-lg w-100 py-2 fs-2 fw-bold" onclick="processCheckout()">
                        ✓ إنهاء الفاتورة وطباعة
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal إضافة زبون جديد سريعة -->
<div class="modal fade" id="modal-add-customer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة زبون جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quick-customer-form">
                    <div class="mb-3">
                        <label class="form-label required">اسم الزبون</label>
                        <input type="text" id="new-customer-name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" id="new-customer-phone" class="form-control">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="saveQuickCustomer()">حفظ واختيار</button>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];
let currentPriceType = 'retail'; // retail OR wholesale

function changePriceType(type) {
    currentPriceType = type;
    
    // تحديث أسعار المعروضة في بطاقات المنتجات
    document.querySelectorAll('.price-display').forEach(el => {
        let price = type === 'wholesale' ? el.getAttribute('data-wholesale') : el.getAttribute('data-retail');
        el.innerText = parseFloat(price).toFixed(2) + ' د.ل';
    });

    // تحديث أسعار المنتجات في السلة
    cart.forEach(item => {
        item.price = type === 'wholesale' ? item.wholesalePrice : item.retailPrice;
    });

    updateCartUI();
}

function addToCart(id, name, retailPrice, wholesalePrice, stock) {
    if (stock <= 0) {
        alert('المنتج غير متوفر بالمخزون!');
        return;
    }

    let price = currentPriceType === 'wholesale' ? wholesalePrice : retailPrice;
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
            retailPrice: retailPrice,
            wholesalePrice: wholesalePrice,
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
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary p-1" onclick="updateQty(${index}, -1)">-</button>
                        <input type="text" class="form-control text-center px-1" value="${item.quantity}" readonly>
                        <button class="btn btn-outline-secondary p-1" onclick="updateQty(${index}, 1)">+</button>
                    </div>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm px-1 text-center" value="${item.price}" onchange="updateItemPrice(${index}, this.value)">
                </td>
                <td class="fw-bold">${subtotal.toFixed(2)}</td>
                <td>
                    <button class="btn btn-sm btn-ghost-danger p-1" onclick="removeItem(${index})">✕</button>
                </td>
            </tr>
        `;
    });

    document.getElementById('grand-total').innerText = grandTotal.toFixed(2) + ' د.ل';
    
    // ضبط التلقائي للمبلغ المدفوع حسب طريقة الدفع
    let paymentMethod = document.getElementById('payment-method').value;
    if (paymentMethod !== 'credit') {
        document.getElementById('paid-amount').value = grandTotal.toFixed(2);
    }
    
    calculateChange();
}

function updateItemPrice(index, newPrice) {
    let price = parseFloat(newPrice);
    if (!isNaN(price) && price >= 0) {
        cart[index].price = price;
        updateCartUI();
    }
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
    let paymentMethod = document.getElementById('payment-method').value;

    if (paymentMethod === 'credit') {
        let remaining = total - paid;
        document.getElementById('change-label').innerText = 'المتبقي كدين (آجل)';
        document.getElementById('change-amount').value = remaining >= 0 ? remaining.toFixed(2) + ' د.ل' : '0.00 د.ل';
        document.getElementById('change-amount').className = 'form-control form-control-lg fw-bold text-warning';
    } else {
        let change = paid - total;
        document.getElementById('change-label').innerText = 'المُرجع (الباقي)';
        document.getElementById('change-amount').value = change >= 0 ? change.toFixed(2) + ' د.ل' : '0.00 د.ل';
        document.getElementById('change-amount').className = 'form-control form-control-lg fw-bold text-danger';
    }
}

function onPaymentMethodChange() {
    let method = document.getElementById('payment-method').value;
    let customerSelect = document.getElementById('customer-id');

    if (method === 'credit') {
        document.getElementById('invoice-type-badge').innerText = 'آجل';
        document.getElementById('invoice-type-badge').className = 'badge bg-warning text-dark fw-bold';
        if (!customerSelect.value) {
            alert('تنبيه: يجب اختيار زبون مسجل لإتمام عملية البيع بالأجل!');
        }
    } else {
        document.getElementById('invoice-type-badge').innerText = 'نقدي';
        document.getElementById('invoice-type-badge').className = 'badge bg-white text-primary fw-bold';
    }
    calculateChange();
}

function onCustomerChange() {
    let customerSelect = document.getElementById('customer-id');
    let selectedOption = customerSelect.options[customerSelect.selectedIndex];
    let balance = selectedOption.getAttribute('data-balance');
    let infoDiv = document.getElementById('customer-balance-info');

    if (customerSelect.value) {
        infoDiv.classList.remove('d-none');
        document.getElementById('customer-balance').innerText = parseFloat(balance).toFixed(2) + ' د.ل';
    } else {
        infoDiv.classList.add('d-none');
    }
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

function saveQuickCustomer() {
    let name = document.getElementById('new-customer-name').value;
    let phone = document.getElementById('new-customer-phone').value;

    if (!name) {
        alert('يرجى إدخال اسم الزبون');
        return;
    }

    fetch("{{ route('customers.store') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ name: name, phone: phone })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            let select = document.getElementById('customer-id');
            let option = document.createElement('option');
            option.value = data.customer.id;
            option.text = data.customer.name + (data.customer.phone ? ' (' + data.customer.phone + ')' : '');
            option.setAttribute('data-balance', '0');
            option.selected = true;
            select.appendChild(option);
            
            // اغلاق الـ Modal
            var modalEl = document.getElementById('modal-add-customer');
            var modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            onCustomerChange();
        } else {
            alert(data.message || 'خطأ أثناء إضافة الزبون');
        }
    })
    .catch(err => alert('حدث خطأ أثناء التواصل مع السيرفر'));
}

function processCheckout() {
    if (cart.length === 0) {
        alert('السلة فارغة!');
        return;
    }

    let customerId = document.getElementById('customer-id').value;
    let paymentMethod = document.getElementById('payment-method').value;
    let paidAmount = parseFloat(document.getElementById('paid-amount').value) || 0;

    if (paymentMethod === 'credit' && !customerId) {
        alert('لا يمكن إتمام البيع بالأجل لزبون عام! يرجى تحديد أو إضافة زبون أولاً.');
        return;
    }

    fetch("{{ route('pos.store') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            customer_id: customerId,
            items: cart,
            price_type: currentPriceType,
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