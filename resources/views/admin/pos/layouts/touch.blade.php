@extends('layouts.app')

@section('content')

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .touch-btn {
        min-height: 55px;
        font-size: 1.25rem;
        font-weight: bold;
    }
    .touch-product-card {
        min-height: 140px;
        border-radius: 12px;
        transition: transform 0.1s ease;
    }
    .touch-product-card:active {
        transform: scale(0.96);
    }
    .badge-shortcut {
        background-color: #206bc4;
        color: #fff;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: bold;
        font-size: 0.9rem;
    }
</style>

<!-- شريط الاختصارات -->
<div class="card mb-2 shadow-sm border-0 bg-white">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 text-dark fs-5">
            <div class="d-flex align-items-center gap-2">
                <span class="badge-shortcut">F2</span>
                <span class="fw-semibold">البحث</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge-shortcut">F4</span>
                <span class="fw-semibold">نقدي / آجل</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge-shortcut bg-success">F8</span>
                <span class="fw-semibold">إنهاء وطباعة</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge-shortcut bg-warning text-dark">F5</span>
                <button type="button" class="btn btn-link text-decoration-none p-0 fw-bold text-dark fs-5" data-bs-toggle="modal" data-bs-target="#modal-recent-invoices">
                    سجل الفواتير 📜
                </button>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge-shortcut bg-danger">F9</span>
                <span class="fw-semibold">تفريغ السلة</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- قائمة المنتجات - شاشة اللمس (اليسار) -->
    <div class="col-md-7">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="row g-2 align-items-center w-100">
                    <div class="col-md-6">
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-search" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7" /><line x1="21" y1="21" x2="15" y2="15" /></svg>
                            </span>
                            <input type="text" id="search-input" class="form-control form-control-lg fs-3" placeholder="بحث بالاسم أو الباركود... (F2)" autofocus>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="price_type" id="price_retail" value="retail" checked onchange="changePriceType('retail')">
                            <label class="btn btn-outline-primary touch-btn d-flex align-items-center justify-content-center" for="price_retail">قطاعي</label>
                            
                            <input type="radio" class="btn-check" name="price_type" id="price_wholesale" value="wholesale" onchange="changePriceType('wholesale')">
                            <label class="btn btn-outline-primary touch-btn d-flex align-items-center justify-content-center" for="price_wholesale">جملة</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-3 overflow-auto" style="max-height: 72vh;">
                <div class="row g-3" id="products-grid">
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
                            <div class="card border border-2 shadow-sm cursor-pointer hover-shadow text-center p-3 h-100 touch-product-card bg-body-tertiary">
                                <div class="fw-bold fs-2 text-truncate mb-2">{{ $product->name }}</div>
                                
                                <div class="my-2">
                                    <span class="text-success fw-extrabold fs-2 price-display" data-retail="{{ $retailPrice }}" data-wholesale="{{ $wholesalePrice }}">
                                        {{ number_format($retailPrice, 2) }} د.ل
                                    </span>
                                </div>
                                
                                <div class="mt-auto pt-2">
                                    <span class="badge {{ $qty > 0 ? 'bg-blue text-white' : 'bg-danger text-white' }} w-100 py-2 fs-4">
                                        المتوفر: {{ $qty }}
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
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h2 class="card-title text-white m-0 fs-2">📱 فاتورة بيع (لمس)</h2>
                <span class="badge bg-white text-primary fw-bold fs-3" id="invoice-type-badge">نقدي</span>
            </div>
            
            <div class="p-3 bg-light border-bottom">
                <div class="row g-2 align-items-center">
                    <div class="col-8">
                        <select id="customer-id" class="form-select form-select-lg fs-3 fw-bold" onchange="onCustomerChange()">
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
                    <div class="col-4 text-end">
                        <button class="btn btn-primary touch-btn w-100" data-bs-toggle="modal" data-bs-target="#modal-add-customer">+ زبون</button>
                    </div>
                </div>
                <div id="customer-balance-info" class="mt-2 d-none fs-4 text-muted">
                    الرصيد السابق: <span id="customer-balance" class="fw-bold text-dark">0.00 د.ل</span>
                </div>
            </div>

            <div class="card-body p-2 d-flex flex-column justify-content-between" style="min-height: 60vh;">
                <!-- جدول السلة بلمس عريض -->
                <div class="table-responsive flex-grow-1 overflow-auto" style="max-height: 35vh;">
                    <table class="table table-vcenter table-striped">
                        <thead>
                            <tr class="border-bottom bg-white fs-4">
                                <th>المنتج</th>
                                <th style="width: 140px;" class="text-center">الكمية</th>
                                <th>الإجمالي</th>
                                <th style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="cart-table-body" class="fs-3">
                            <!-- عناصر السلة -->
                        </tbody>
                    </table>
                </div>

                <!-- الإجماليات وزر إنهاء أضخم -->
                <div class="border-top pt-2 bg-light p-3 rounded">
                    <div class="d-flex justify-content-between fs-1 fw-bold mb-2">
                        <span>الإجمالي الكلي:</span>
                        <span class="text-primary" id="grand-total">0.00 د.ل</span>
                    </div>

                    <div class="row g-2 my-2">
                        <div class="col-6">
                            <label class="form-label mb-1 fs-4 fw-bold">المدفوع</label>
                            <input type="number" step="0.01" id="paid-amount" class="form-control form-control-lg fs-2 fw-bold" value="0" oninput="calculateChange()">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1 fs-4 fw-bold" id="change-label">المُرجع</label>
                            <input type="text" id="change-amount" class="form-control form-control-lg fs-2 fw-bold text-danger" value="0.00" readonly>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12">
                            <select id="payment-method" class="form-select form-select-lg fs-3 fw-bold" onchange="onPaymentMethodChange()">
                                <option value="cash">نقداً (Cash)</option>
                                <option value="credit">آجل / على الحساب (Credit)</option>
                                <option value="card">بطاقة مصرفية (Card)</option>
                                <option value="transfer">تحويل بنكي</option>
                            </select>
                        </div>
                    </div>

                    <button class="btn btn-success btn-lg w-100 py-3 fs-1 fw-bold shadow-sm" onclick="processCheckout()">
                        ✓ إنهاء وطباعة (F8)
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
                <h5 class="modal-title text-white fs-3">📜 سجل الفواتير الأخيرة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                    <table class="table table-vcenter table-striped card-table mb-0 fs-3">
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
                                            <span class="badge {{ $inv->payment_method === 'credit' ? 'bg-warning text-dark' : 'bg-success text-white' }} fs-4">
                                                {{ $inv->payment_method === 'credit' ? 'آجل' : 'نقداً' }}
                                            </span>
                                        </td>
                                        <td class="text-muted fs-4">{{ $inv->created_at->format('Y-m-d H:i') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" class="btn btn-primary btn-md me-1 fs-4">
                                                🖨️ طباعة
                                            </a>
                                            <form action="{{ route('invoices.destroy', $inv->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت تأكد من إلغاء وحذف هذه الفاتورة؟ سيتم إرجاع الكميات للمخزون.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-md fs-4">
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
                <button type="button" class="btn btn-secondary fs-3" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal إضافة زبون جديد سريعة -->
<div class="modal fade" id="modal-add-customer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-3">إضافة زبون جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quick-customer-form">
                    <div class="mb-3">
                        <label class="form-label required fs-4">اسم الزبون</label>
                        <input type="text" id="new-customer-name" class="form-control form-control-lg fs-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-4">رقم الهاتف</label>
                        <input type="text" id="new-customer-phone" class="form-control form-control-lg fs-3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-4">العنوان</label>
                        <input type="text" id="new-customer-address" class="form-control form-control-lg fs-3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-4">الحد الائتماني (الأقصى للدين)</label>
                        <input type="number" step="0.01" id="new-customer-credit-limit" class="form-control form-control-lg fs-3" value="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary fs-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary fs-3" onclick="saveQuickCustomer()">حفظ واختيار</button>
            </div>
        </div>
    </div>
</div>

<!-- عنصر iframe مخفي مخصص للطباعة المباشرة -->
<iframe id="print-frame" style="display: none;"></iframe>

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
                    <div class="fw-bold fs-3">${item.name}</div>
                    <div class="text-muted fs-5">${item.price.toFixed(2)} د.ل</div>
                </td>
                <td class="text-center">
                    <div class="input-group input-group-lg">
                        <button class="btn btn-outline-secondary px-3 fs-2 fw-bold" onclick="updateQty(${index}, -1)">-</button>
                        <input type="text" class="form-control text-center px-1 fs-3 fw-bold" value="${item.quantity}" readonly>
                        <button class="btn btn-outline-secondary px-3 fs-2 fw-bold" onclick="updateQty(${index}, 1)">+</button>
                    </div>
                </td>
                <td class="fw-bold fs-2 text-success">${subtotal.toFixed(2)}</td>
                <td>
                    <button class="btn btn-ghost-danger btn-lg p-1 fs-2 fw-bold" onclick="removeItem(${index})">✕</button>
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

function updateQty(index, change) {
    let item = cart[index];
    let newQty = item.quantity + change;
    if (newQty <= 0) {
        cart.splice(index, 1);
    } else if (newQty > item.maxStock) {
        Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'تجاوزت الكمية المتوفرة!' });
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
        document.getElementById('change-amount').className = 'form-control form-control-lg fs-2 fw-bold text-warning';
    } else {
        let change = paid - total;
        document.getElementById('change-label').innerText = 'المُرجع';
        document.getElementById('change-amount').value = change >= 0 ? change.toFixed(2) + ' د.ل' : '0.00 د.ل';
        document.getElementById('change-amount').className = 'form-control form-control-lg fs-2 fw-bold text-danger';
    }
}

function onPaymentMethodChange() {
    let method = document.getElementById('payment-method').value;
    let customerSelect = document.getElementById('customer-id');
    let grandTotal = parseFloat(document.getElementById('grand-total').innerText) || 0;

    if (method === 'credit') {
        document.getElementById('invoice-type-badge').innerText = 'آجل';
        document.getElementById('invoice-type-badge').className = 'badge bg-warning text-dark fw-bold fs-3';
        document.getElementById('paid-amount').value = '0';

        if (!customerSelect.value) {
            Swal.fire({ icon: 'info', title: 'تحديد الزبون', text: 'اختر زبون مسجل لإتمام عملية البيع بالأجل!' });
        }
    } else {
        document.getElementById('invoice-type-badge').innerText = 'نقدي';
        document.getElementById('invoice-type-badge').className = 'badge bg-white text-primary fw-bold fs-3';
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

function processCheckout() {
    if (cart.length === 0) {
        Swal.fire({ icon: 'warning', title: 'السلة فارغة', text: 'يرجى إضافة منتجات إلى السلة أولاً!' });
        return;
    }

    let customerId = document.getElementById('customer-id').value;
    let paymentMethod = document.getElementById('payment-method').value;
    let paidAmount = parseFloat(document.getElementById('paid-amount').value) || 0;

    if (paymentMethod === 'credit' && !customerId) {
        Swal.fire({ icon: 'warning', title: 'زبون غير محدد', text: 'اختر زبون مسجل لإتمام البيع بالأجل!' });
        return;
    }

    fetch("{{ route('pos.store') }}", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
        body: JSON.stringify({ customer_id: customerId, items: cart, price_type: currentPriceType, paid_amount: paidAmount, payment_method: paymentMethod })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // 1. طباعة هادئة ومباشرة عبر iframe المخفي
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

            // 2. تفريغ السلة فوراً وإعادة الضبط لزبون جديد
            cart = [];
            updateCartUI();

            // 3. إعادة إحياء الفوكس على البحث لعملية البيع التالية
            let searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
            }

            // 4. إشعار سريع زاوية الشاشة (Toast)
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: 'تم حفظ وطباعة الفاتورة #' + data.invoice_number
            });

        } else {
            Swal.fire({ icon: 'warning', title: 'تعذر إتمام العملية', text: data.message });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'خطأ في الاتصال' }));
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