@extends('layouts.app')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .compact-table td, .compact-table th {
        padding: 4px 8px !important;
        vertical-align: middle;
    }
    .badge-shortcut {
        background-color: #206bc4;
        color: #fff;
        padding: 3px 6px;
        border-radius: 4px;
        font-weight: bold;
        font-size: 0.8rem;
    }
</style>

<!-- شريط الاختصارات -->
<div class="card mb-2 shadow-sm border-0 bg-white">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 text-dark fs-6">
            <div class="d-flex align-items-center gap-1">
                <span class="badge-shortcut">F2</span>
                <span class="fw-semibold">البحث بالباركود / الاسم</span>
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge-shortcut">F4</span>
                <span class="fw-semibold">تبديل (نقدي / آجل)</span>
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge-shortcut bg-success">F8</span>
                <span class="fw-semibold">إنهاء وطباعة</span>
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge-shortcut bg-warning text-dark">F5</span>
                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#modal-recent-invoices">
                    سجل الفواتير 📜
                </button>
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge-shortcut bg-danger">F9</span>
                <span class="fw-semibold">تفريغ السلة</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-2">
    <!-- قائمة المنتجات الجدولية (اليسار) -->
    <div class="col-md-7">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white py-2">
                <div class="row g-2 align-items-center w-100">
                    <div class="col-md-6">
                        <input type="text" id="search-input" class="form-control form-control-sm" placeholder="ابحث بالاسم أو الباركود... (F2)" autofocus>
                    </div>
                    <div class="col-md-4">
                        <div class="btn-group w-100">
                            <input type="radio" class="btn-check" name="price_type" id="price_retail" value="retail" checked onchange="changePriceType('retail')">
                            <label class="btn btn-sm btn-outline-primary py-1" for="price_retail">قطاعي</label>

                            <input type="radio" class="btn-check" name="price_type" id="price_wholesale" value="wholesale" onchange="changePriceType('wholesale')">
                            <label class="btn btn-sm btn-outline-primary py-1" for="price_wholesale">جملة</label>
                        </div>
                    </div>
                    <div class="col-md-2 text-end">
                        <span class="badge bg-blue-lt fs-6 p-1 w-100" id="products-count">عدد: {{ count($products) }}</span>
                    </div>
                </div>
            </div>

            <!-- جدول الأصناف المدمج -->
            <div class="card-body p-0 overflow-auto" style="max-height: 75vh;">
                <table class="table table-sm table-hover table-striped compact-table align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>الباركود</th>
                            <th>الاسم</th>
                            <th>المتوفر</th>
                            <th>السعر</th>
                            <th class="text-center" style="width: 70px;">إضافة</th>
                        </tr>
                    </thead>
                    <tbody id="products-table-body">
                        @foreach($products as $product)
                            @php
                                $qty =$product->branches->first()->pivot->quantity ?? 0;
                                $retailPrice =$product->selling_price;
                                $wholesalePrice = $product->wholesale_price ?? $product->selling_price;
                            @endphp
                            <tr class="product-row"
                                data-name="{{ mb_strtolower($product->name) }}"
                                data-barcode="{{ $product->barcode }}">
                                <td class="text-muted fs-6">{{ $product->barcode ?? '-' }}</td>
                                <td class="fw-bold">{{ $product->name }}</td>
                                <td>
                                    <span class="badge {{ $qty > 0 ? 'bg-blue-lt text-blue' : 'bg-danger-lt text-danger' }}">
                                        {{ $qty }} {{$product->unit }}
                                    </span>
                                </td>
                                <td class="fw-bold text-success price-display" data-retail="{{ $retailPrice }}" data-wholesale="{{ $wholesalePrice }}">
                                    {{ number_format($retailPrice, 2) }} د.ل
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary p-0 px-2 fw-bold" 
                                            onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{$retailPrice }}, {{ $wholesalePrice }}, {{$qty }})">
                                        +
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- سلة الشراء والحساب المدمجة (اليمين) -->
    <div class="col-md-5">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
                <h4 class="card-title text-white m-0 fs-4">📊 السلة المدمجة</h4>
                <span class="badge bg-white text-primary fw-bold" id="invoice-type-badge">نقدي</span>
            </div>

            <div class="p-2 bg-light border-bottom">
                <div class="row g-2 align-items-center">
                    <div class="col-9">
                        <select id="customer-id" class="form-select form-select-sm" onchange="onCustomerChange()">
                            <option value="" data-type="cash" data-balance="0">زبون نقدي (عام)</option>
                            @if(isset($customers))
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" data-type="credit" data-balance="{{ $customer->balance ?? 0 }}">
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-3 text-end">
                        <button class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#modal-add-customer">+ جديد</button>
                    </div>
                </div>
                <div id="customer-balance-info" class="mt-1 d-none fs-6 text-muted">
                    رصيد سابق: <span id="customer-balance" class="fw-bold text-dark">0.00 د.ل</span>
                </div>
            </div>

            <div class="card-body p-2 d-flex flex-column justify-content-between" style="min-height: 65vh;">
                <!-- جدول السلة -->
                <div class="table-responsive flex-grow-1 overflow-auto" style="max-height: 40vh;">
                    <table class="table table-sm table-vcenter table-borderless table-striped align-middle mb-0 compact-table">
                        <thead>
                            <tr class="border-bottom bg-white sticky-top">
                                <th>المنتج</th>
                                <th style="width: 80px;">الكمية</th>
                                <th>السعر</th>
                                <th>الإجمالي</th>
                                <th style="width: 20px;"></th>
                            </tr>
                        </thead>
                        <tbody id="cart-table-body">
                            <!-- عناصر السلة -->
                        </tbody>
                    </table>
                </div>

                <!-- الإجماليات والمحاسبة -->
                <div class="border-top pt-2 bg-light p-2 rounded">
                    <div class="d-flex justify-content-between fs-3 fw-bold mb-1">
                        <span>الإجمالي الكلي:</span>
                        <span class="text-primary" id="grand-total">0.00 د.ل</span>
                    </div>

                    <div class="row g-2 my-1">
                        <div class="col-6">
                            <label class="form-label mb-0 fs-6">المبلغ المدفوع</label>
                            <input type="number" step="0.01" id="paid-amount" class="form-control form-control-sm fw-bold" value="0" oninput="calculateChange()">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-0 fs-6" id="change-label">المُرجع (الباقي)</label>
                            <input type="text" id="change-amount" class="form-control form-control-sm fw-bold text-danger" value="0.00" readonly>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <select id="payment-method" class="form-select form-select-sm" onchange="onPaymentMethodChange()">
                                <option value="cash">نقداً (Cash)</option>
                                <option value="credit">آجل / على الحساب (Credit)</option>
                                <option value="card">بطاقة مصرفية (Card)</option>
                                <option value="transfer">تحويل بنكي</option>
                            </select>
                        </div>
                    </div>

                    <button class="btn btn-success btn-lg w-100 py-2 fs-3 fw-bold" onclick="processCheckout()">
                        ✓ إنهاء الفاتورة وطباعة (F8)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal الفواتير السابقة -->
<div class="modal fade" id="modal-recent-invoices" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title text-white">📜 سجل الفواتير الأخيرة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                    <table class="table table-vcenter table-striped card-table mb-0 compact-table">
                        <thead>
                            <tr>
                                <th>رقم الفاتورة</th>
                                <th>الزبون</th>
                                <th>الإجمالي</th>
                                <th>طريقة الدفع</th>
                                <th>التاريخ</th>
                                <th class="text-center">العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($recentInvoices) && count($recentInvoices) > 0)
                                @foreach($recentInvoices as $inv)
                                    <tr>
                                        <td class="fw-bold text-primary">{{ $inv->invoice_number }}</td>
                                        <td>{{ $inv->customer->name ?? 'زبون نقدي' }}</td>
                                        <td class="fw-bold">{{ number_format($inv->total_amount, 2) }} د.ل</td>
                                        <td>
                                            <span class="badge {{ $inv->payment_method === 'credit' ? 'bg-warning text-dark' : 'bg-success text-white' }}">
                                                {{ $inv->payment_method === 'credit' ? 'آجل' : 'نقداً' }}
                                            </span>
                                        </td>
                                        <td class="text-muted fs-6">{{ $inv->created_at->format('Y-m-d H:i') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" class="btn btn-sm btn-primary me-1">
                                                🖨️ طباعة
                                            </a>
                                            <form action="{{ route('invoices.destroy', $inv->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت تأكد من إلغاء وحذف هذه الفاتورة؟ سيتم إرجاع الكميات للمخزون.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    إلغاء
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">لا توجد فواتير حديثة حتى الآن.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
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
                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <input type="text" id="new-customer-address" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الحد الائتماني (الحد الأقصى للدين)</label>
                        <input type="number" step="0.01" id="new-customer-credit-limit" class="form-control" value="0">
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
<!-- استدعاء مكتبة Dexie.js للتعامل مع IndexedDB -->
<script src="https://unpkg.com/dexie/dist/dexie.js"></script>

<script>
    // تعريف متغير db ليصبح عاماً (Global) ومتاحاً لجميع الدوال مثل processCheckout
    const db = new Dexie('PosOfflineDB');
    
    db.version(1).stores({
        invoices: '++id, uuid, invoice_number, total_amount, payment_method, is_synced, created_at',
        products: '++id, product_id, name, price, stock',
        customers: '++id, customer_id, name, phone'
    });

    // فتح قاعدة البيانات للتأكد من ظهورها في المتصفح
    db.open().then(() => {
        console.log("تم تفعيل IndexedDB بنجاح وأصبحت جاهزة للاستخدام!");
    }).catch(err => {
        console.error("خطأ أثناء فتح قاعدة البيانات المحلية:", err);
    });
</script>
<script>
let cart = [];
let currentPriceType = 'retail';

function changePriceType(type) {
    currentPriceType = type;
    document.querySelectorAll('.price-display').forEach(el => {
        let price = type === 'wholesale' ? el.getAttribute('data-wholesale') : el.getAttribute('data-retail');
        el.innerText = parseFloat(price).toFixed(2) + ' د.ل';
    });
    cart.forEach(item => {
        item.price = type === 'wholesale' ? item.wholesalePrice : item.retailPrice;
    });
    updateCartUI();
}

function addToCart(id, name, retailPrice, wholesalePrice, stock) {
    if (stock <= 0) {
        Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'المنتج غير متوفر بالمخزون!' });
        return;
    }
    let price = currentPriceType === 'wholesale' ? wholesalePrice : retailPrice;
    let existingItem = cart.find(item => item.product_id === id);

    if (existingItem) {
        if (existingItem.quantity + 1 > stock) {
            Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'الكمية تتجاوز المتاح بالمخزون!' });
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
                    <div class="fw-bold text-truncate" style="max-width: 100px;">${item.name}</div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary p-0 px-1" onclick="updateQty(${index}, -1)">-</button>
                        <input type="text" class="form-control text-center p-0" value="${item.quantity}" readonly>
                        <button class="btn btn-outline-secondary p-0 px-1" onclick="updateQty(${index}, 1)">+</button>
                    </div>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm p-0 text-center" value="${item.price}" onchange="updateItemPrice(${index}, this.value)">
                </td>
                <td class="fw-bold fs-6">${subtotal.toFixed(2)}</td>
                <td>
                    <button class="btn btn-sm btn-ghost-danger p-0" onclick="removeItem(${index})">✕</button>
                </td>
            </tr>
        `;
    });

    document.getElementById('grand-total').innerText = grandTotal.toFixed(2) + ' د.ل';
    let paymentMethod = document.getElementById('payment-method').value;
    if (paymentMethod === 'credit') {
        document.getElementById('paid-amount').value = '0';
    } else {
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
        Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'تجاوزت الكمية المتوفرة بالمخزون!' });
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
        document.getElementById('change-label').innerText = 'المتبقي (آجل)';
        document.getElementById('change-amount').value = remaining >= 0 ? remaining.toFixed(2) + ' د.ل' : '0.00 د.ل';
        document.getElementById('change-amount').className = 'form-control form-control-sm fw-bold text-warning';
    } else {
        let change = paid - total;
        document.getElementById('change-label').innerText = 'المُرجع';
        document.getElementById('change-amount').value = change >= 0 ? change.toFixed(2) + ' د.ل' : '0.00 د.ل';
        document.getElementById('change-amount').className = 'form-control form-control-sm fw-bold text-danger';
    }
}

function onPaymentMethodChange() {
    let method = document.getElementById('payment-method').value;
    let customerSelect = document.getElementById('customer-id');
    let grandTotal = parseFloat(document.getElementById('grand-total').innerText) || 0;

    if (method === 'credit') {
        document.getElementById('invoice-type-badge').innerText = 'آجل';
        document.getElementById('invoice-type-badge').className = 'badge bg-warning text-dark fw-bold';
        document.getElementById('paid-amount').value = '0';

        if (!customerSelect.value) {
            Swal.fire({ icon: 'info', title: 'تحديد الزبون', text: 'تنبيه: اختر زبون مسجل لإتمام البيع بالأجل!' });
        }
    } else {
        document.getElementById('invoice-type-badge').innerText = 'نقدي';
        document.getElementById('invoice-type-badge').className = 'badge bg-white text-primary fw-bold';
        document.getElementById('paid-amount').value = grandTotal.toFixed(2);
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
    let rows = document.querySelectorAll('.product-row');

    rows.forEach(row => {
        let name = row.getAttribute('data-name');
        let barcode = row.getAttribute('data-barcode');
        if (name.includes(query) || (barcode && barcode.includes(query))) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

function saveQuickCustomer() {
    let name = document.getElementById('new-customer-name').value;
    let phone = document.getElementById('new-customer-phone').value;
    let address = document.getElementById('new-customer-address').value;
    let creditLimit = document.getElementById('new-customer-credit-limit').value || 0;

    if (!name) {
        Swal.fire({ icon: 'warning', title: 'حقل مطلوب', text: 'يرجى إدخال اسم الزبون' });
        return;
    }

    fetch("{{ route('customers.store') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ name: name, phone: phone, address: address, credit_limit: creditLimit })
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'حدث خطأ في السيرفر');
        return data;
    })
    .then(data => {
        if (data.success || data.customer) {
            let customer = data.customer;
            let select = document.getElementById('customer-id');
            let option = document.createElement('option');
            option.value = customer.id;
            option.text = customer.name;
            option.setAttribute('data-balance', customer.balance || '0');
            option.selected = true;
            select.appendChild(option);

            document.getElementById('quick-customer-form').reset();
            var modalEl = document.getElementById('modal-add-customer');
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            if (typeof onCustomerChange === 'function') onCustomerChange();
            Swal.fire({ icon: 'success', title: 'تم الإضافة', timer: 1500, showConfirmButton: false });
        }
    })
    .catch(err => Swal.fire({ icon: 'error', title: 'خطأ', text: err.message }));
}

async function processCheckout() {
    if (cart.length === 0) {
        Swal.fire({ icon: 'warning', title: 'السلة فارغة', text: 'يرجى إضافة منتجات إلى السلة أولاً!' });
        return;
    }

    let customerId = document.getElementById('customer-id').value;
    let paymentMethod = document.getElementById('payment-method').value;
    let paidAmount = parseFloat(document.getElementById('paid-amount').value) || 0;
    let grandTotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    let changeAmount = paidAmount - grandTotal;

    if (paymentMethod === 'credit' && !customerId) {
        Swal.fire({ icon: 'warning', title: 'زبون غير محدد', text: 'اختر زبون مسجل لإتمام البيع بالأجل!' });
        return;
    }

    // تجهيز هيكل بيانات الفاتورة الموحد
    let invoiceData = {
        uuid: crypto.randomUUID(),
        invoice_number: 'INV-' + Math.floor(100000 + Math.random() * 900000),
        customer_id: customerId ? parseInt(customerId) : null,
        total_amount: grandTotal,
        paid_amount: paidAmount,
        change_amount: changeAmount >= 0 ? changeAmount : 0,
        payment_method: paymentMethod,
        items: cart.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            price: item.price
        }))
    };

    // 1. التحقق من حالة الاتصال بالإنترنت
    if (navigator.onLine) {
        try {
            let res = await fetch("{{ route('pos.store') }}", {
                method: "POST",
                headers: { 
                    "Content-Type": "application/json", 
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}" 
                },
                body: JSON.stringify({ 
                    customer_id: customerId, 
                    items: cart, 
                    price_type: currentPriceType, 
                    paid_amount: paidAmount, 
                    payment_method: paymentMethod 
                })
            });
            let data = await res.json();

            if (data.success) {
                // طباعة هادئة ومباشرة عبر iframe المخفي
                let printFrame = document.getElementById('print-frame');
                printFrame.src = '/invoices/' + data.invoice_id + '/print';

                printFrame.onload = function() {
                    try {
                        printFrame.contentWindow.focus();
                        printFrame.contentWindow.print();
                    } catch (e) {
                        console.error("خطأ في الطباعة المباشرة:", e);
                    }
                };

                resetCartAndUI('تم حفظ وطباعة الفاتورة #' + data.invoice_number, 'success');
                return;
            }
        } catch (e) {
            console.warn("فشل الاتصال المباشر بالسيرفر، سيتم الحفظ محلياً...", e);
        }
    }

    // 2. حالة الانقطاع (أوفلاين) -> الحفظ في IndexedDB محلياً
    try {
        // تأكد من استيراد db أو استخدامها إذا كانت معرفة عالمياً، أو حفظها عبر Dexie مباشرة:
        await db.invoices.add({
            uuid: invoiceData.uuid,
            invoice_number: invoiceData.invoice_number,
            customer_id: invoiceData.customer_id,
            total_amount: invoiceData.total_amount,
            paid_amount: invoiceData.paid_amount,
            change_amount: invoiceData.change_amount,
            payment_method: invoiceData.payment_method,
            items: invoiceData.items,
            is_synced: false, // مؤشر أنها معلقة بانتظار عودة النت
            created_at: new Date().toISOString()
        });

        resetCartAndUI('⚠️ أوفلاين! تم حفظ الفاتورة محلياً وسيتم مزامنتها تلقائياً لاحقاً.', 'warning');

    } catch (err) {
        Swal.fire({ icon: 'error', title: 'خطأ في التخزين المحلي', text: err.message });
    }
}

// دالة مساعدة لتفريغ السلة وإعادة تهيئة الفوكس للبحث
function resetCartAndUI(message, iconType) {
    cart = [];
    updateCartUI();

    let searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
    }

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });
    Toast.fire({ icon: iconType, title: message });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'F2') { e.preventDefault(); document.getElementById('search-input').focus(); }
    if (e.key === 'F4') { e.preventDefault(); let m = document.getElementById('payment-method'); m.value = (m.value === 'cash') ? 'credit' : 'cash'; onPaymentMethodChange(); }
    if (e.key === 'F5') { e.preventDefault(); new bootstrap.Modal(document.getElementById('modal-recent-invoices')).show(); }
    if (e.key === 'F8') { e.preventDefault(); processCheckout(); }
    if (e.key === 'F9') { e.preventDefault(); if (cart.length > 0 && confirm('تفريغ السلة؟')) { cart = []; updateCartUI(); } }
});
</script>
@endsection