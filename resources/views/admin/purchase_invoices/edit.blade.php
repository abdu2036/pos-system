@extends('layouts.app')

@section('content')
<div class="container-xl">
    @if ($errors->any())
    <div class="alert alert-danger mb-3">
        <h4 class="alert-title">تنبيه: يوجد أخطاء يرجى تصحيحها</h4>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">تعديل فاتورة شراء رقم: {{ $purchaseInvoice->id }}</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('purchase_invoices.index') }}" class="btn btn-outline-secondary">
                    رجوع لسجل المشتريات
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('purchase_invoices.update', $purchaseInvoice->id) }}" method="POST" id="purchaseInvoiceForm">
        @csrf
        @method('PUT')
        <div class="row row-cards">
            <!-- بيانات الفاتورة والمورد -->
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">بيانات الفاتورة</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label required mb-0">المورد</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#quickSupplierModal">
                                    + مورد جديد
                                </button>
                            </div>
                            <select name="supplier_id" id="supplier_select" class="form-select @error('supplier_id') is-invalid @enderror" required>
                                <option value="">-- اختر المورد --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchaseInvoice->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">طريقة الدفع</label>
                            <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                <option value="cash" {{ old('payment_method', $purchaseInvoice->payment_method) == 'cash' ? 'selected' : '' }}>نقداً (كاش)</option>
                                <option value="credit" {{ old('payment_method', $purchaseInvoice->payment_method) == 'credit' ? 'selected' : '' }}>آجل (على الحساب)</option>
                                <option value="transfer" {{ old('payment_method', $purchaseInvoice->payment_method) == 'transfer' ? 'selected' : '' }}>تحويل مصرفي</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="أي ملاحظات إضافية...">{{ old('notes', $purchaseInvoice->notes) }}</textarea>
                        </div>

                        <div class="card bg-light p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">إجمالي الفاتورة:</span>
                                <span class="h3 mb-0 text-primary" id="grand-total">{{ number_format($purchaseInvoice->total_amount ?? 0, 2, '.', '') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- جدول الأصناف المشتراة -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">عناصر الفاتورة (الأصناف)</h3>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#quickProductModal">
                                + صنف جديد غير مسجل
                            </button>
                            <button type="button" class="btn btn-sm btn-success" id="add-item-btn">
                                + إضافة سطر صنف
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-vcenter" id="items-table">
                                <thead>
                                    <tr>
                                        <th style="width: 25%">المنتج</th>
                                        <th style="width: 12%">الكمية</th>
                                        <th style="width: 15%">سعر الشراء</th>
                                        <th style="width: 15%">سعر البيع</th>
                                        <th style="width: 15%">سعر الجملة</th>
                                        <th style="width: 13%">الإجمالي</th>
                                        <th style="width: 5%">حذف</th>
                                    </tr>
                                </thead>
                                <tbody>
    @php
        $items = old('items', $purchaseInvoice->items ?? $purchaseInvoice->details ?? []);
    @endphp

    @forelse($items as $index => $item)
        @php
            $productId = is_array($item) ? ($item['product_id'] ?? '') : $item->product_id;
            
            // جلب المنتج المرتبط لمعرفة أسعاره الافتراضية إذا لم تكن موجودة في الفاتورة
            $productObj = $products->firstWhere('id', $productId);

            $quantity = is_array($item) ? ($item['quantity'] ?? 1) : $item->quantity;
            $purchasePrice = is_array($item) ? ($item['purchase_price'] ?? 0) : ($item->purchase_price ?? $item->unit_price ?? $productObj?->purchase_price ?? $productObj?->cost_price ?? 0);
            
            // جلب سعر البيع (مع مراعاة المسميات المختلفة مثل sale_price)
            $sellingPrice = is_array($item) 
                ? ($item['selling_price'] ?? $item['sale_price'] ?? null) 
                : ($item->selling_price ?? $item->sale_price ?? $productObj?->selling_price ?? $productObj?->sale_price ?? 0);

            // جلب سعر الجملة
            $wholesalePrice = is_array($item) 
                ? ($item['wholesale_price'] ?? null) 
                : ($item->wholesale_price ?? $productObj?->wholesale_price ?? 0);

            $subtotal = $quantity * $purchasePrice;
        @endphp
        <tr class="item-row">
            <td>
                <select name="items[{{ $index }}][product_id]" class="form-select product-select" required>
                    <option value="">-- اختر المنتج --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ $productId == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity-input" min="1" value="{{ $quantity }}" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[{{ $index }}][purchase_price]" class="form-control purchase-price-input" placeholder="0.00" value="{{ number_format($purchasePrice, 2, '.', '') }}" min="0" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[{{ $index }}][selling_price]" class="form-control selling-price-input" placeholder="0.00" value="{{ $sellingPrice ? number_format($sellingPrice, 2, '.', '') : '' }}" min="0">
            </td>
            <td>
                <input type="number" step="0.01" name="items[{{ $index }}][wholesale_price]" class="form-control wholesale-price-input" placeholder="0.00" value="{{ $wholesalePrice ? number_format($wholesalePrice, 2, '.', '') : '' }}" min="0">
            </td>
            <td>
                <input type="text" class="form-control row-subtotal" readonly value="{{ number_format($subtotal, 2, '.', '') }}">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn">&times;</button>
            </td>
        </tr>
    @empty
        <tr class="item-row">
            <td>
                <select name="items[0][product_id]" class="form-select product-select" required>
                    <option value="">-- اختر المنتج --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="items[0][quantity]" class="form-control quantity-input" min="1" value="1" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[0][purchase_price]" class="form-control purchase-price-input" placeholder="0.00" min="0" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[0][selling_price]" class="form-control selling-price-input" placeholder="0.00" min="0">
            </td>
            <td>
                <input type="number" step="0.01" name="items[0][wholesale_price]" class="form-control wholesale-price-input" placeholder="0.00" min="0">
            </td>
            <td>
                <input type="text" class="form-control row-subtotal" readonly value="0.00">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn">&times;</button>
            </td>
        </tr>
    @endforelse
</tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary" id="submitFormBtn">تحديث فاتورة الشراء وتعديل المخزون والأسعار</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal إضافة مورد جديد -->
<div class="modal fade" id="quickSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة مورد جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="quickSupplierError" class="alert alert-danger d-none" role="alert"></div>

                <form id="quickSupplierForm">
                    <div class="mb-3">
                        <label class="form-label required">اسم المورد / الشركة</label>
                        <input type="text" id="quick_supplier_name" name="name" class="form-control" required placeholder="مثال: شركة الأمل لتوريد الأدوات">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" id="quick_supplier_phone" name="phone" class="form-control" placeholder="09XXXXXXX">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">العنوان / الملاحظات</label>
                        <input type="text" id="quick_supplier_address" name="address" class="form-control" placeholder="مثال: طرابلس - سوق الجمعة">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="saveQuickSupplierBtn">حفظ المورد واختياره</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal إضافة صنف جديد -->
<div class="modal fade" id="quickProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة صنف جديد للمنظومة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="quickProductError" class="alert alert-danger d-none" role="alert"></div>

                <form id="quickProductForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">اسم الصنف / المنتج</label>
                            <input type="text" id="quick_product_name" name="name" class="form-control" required placeholder="مثال: لمبة 50 وات">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">الباركود (Barcode)</label>
                            <input type="text" id="quick_barcode" name="barcode" class="form-control" placeholder="654123">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">التصنيف</label>
                            <select id="quick_category_id" name="category_id" class="form-select" required>
                                <option value="">-- اختر التصنيف --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">الوحدة</label>
                            <select id="quick_unit" name="unit" class="form-select" required>
                                <option value="قطعة" selected>قطعة</option>
                                <option value="كيلو">كيلو</option>
                                <option value="متر">متر</option>
                                <option value="صندوق">صندوق</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required">سعر الشراء</label>
                            <input type="number" step="0.01" id="quick_cost_price" name="cost_price" class="form-control" required value="0.00" min="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required">سعر البيع (قطاعي)</label>
                            <input type="number" step="0.01" id="quick_selling_price" name="selling_price" class="form-control" required value="0.00" min="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">سعر البيع (جملة)</label>
                            <input type="number" step="0.01" id="quick_wholesale_price" name="wholesale_price" class="form-control" placeholder="اختياري" min="0">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="saveQuickProductBtn">حفظ وإضافة للفاتورة</button>
            </div>
        </div>
    </div>
</div>

<script>
const productsData = {
        @foreach($products as $product)
            "{{ $product->id }}": {
                cost_price: {{ $product->purchase_price ?? $product->cost_price ?? 0 }},
                selling_price: {{ $product->selling_price ?? $product->sale_price ?? 0 }},
                wholesale_price: {{ $product->wholesale_price ?? 0 }}
            },
        @endforeach
    };

    document.addEventListener('DOMContentLoaded', function () {
        const addBtn = document.getElementById('add-item-btn');
        const tableBody = document.querySelector('#items-table tbody');
        const grandTotalElem = document.getElementById('grand-total');

        let productsListOptions = `@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach`;

        function calculateTotals() {
            let total = 0;
            document.querySelectorAll('#items-table tbody tr.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const price = parseFloat(row.querySelector('.purchase-price-input').value) || 0;
                const subtotal = qty * price;
                
                row.querySelector('.row-subtotal').value = subtotal.toFixed(2);
                total += subtotal;
            });
            grandTotalElem.textContent = total.toFixed(2);
        }

        function reindexRows() {
            document.querySelectorAll('#items-table tbody tr.item-row').forEach((row, index) => {
                row.querySelector('.product-select').name = `items[${index}][product_id]`;
                row.querySelector('.quantity-input').name = `items[${index}][quantity]`;
                row.querySelector('.purchase-price-input').name = `items[${index}][purchase_price]`;
                row.querySelector('.selling-price-input').name = `items[${index}][selling_price]`;
                row.querySelector('.wholesale-price-input').name = `items[${index}][wholesale_price]`;
            });
        }

function bindRowEvents(row) {
        const productSelect = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.quantity-input');
        const purchasePriceInput = row.querySelector('.purchase-price-input');
        const sellingPriceInput = row.querySelector('.selling-price-input');
        const wholesalePriceInput = row.querySelector('.wholesale-price-input');

        productSelect.addEventListener('change', function () {
            const productId = this.value;
            if (productId && productsData[productId]) {
                // ملء الأسعار تلقائياً بقيم المنتج الأصلية
                purchasePriceInput.value = parseFloat(productsData[productId].cost_price).toFixed(2);
                sellingPriceInput.value = parseFloat(productsData[productId].selling_price).toFixed(2);
                wholesalePriceInput.value = parseFloat(productsData[productId].wholesale_price).toFixed(2);
            }
            calculateTotals();
        });

        qtyInput.addEventListener('input', calculateTotals);
        purchasePriceInput.addEventListener('input', calculateTotals);
    }

        document.querySelectorAll('#items-table tbody tr.item-row').forEach(bindRowEvents);
        calculateTotals(); // حساب الإجمالي الابتدائي عند التحميل

        if (addBtn) {
            addBtn.addEventListener('click', function () {
                const newRow = document.createElement('tr');
                newRow.className = 'item-row';
                newRow.innerHTML = `
                    <td>
                        <select class="form-select product-select" required>
                            <option value="">-- اختر المنتج --</option>
                            ${productsListOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control quantity-input" min="1" value="1" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control purchase-price-input" placeholder="0.00" min="0" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control selling-price-input" placeholder="0.00" min="0">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control wholesale-price-input" placeholder="0.00" min="0">
                    </td>
                    <td>
                        <input type="text" class="form-control row-subtotal" readonly value="0.00">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn">&times;</button>
                    </td>
                `;
                tableBody.appendChild(newRow);
                bindRowEvents(newRow);
                reindexRows();
            });
        }

        if (tableBody) {
            tableBody.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-row-btn')) {
                    const rows = tableBody.querySelectorAll('tr.item-row');
                    if (rows.length > 1) {
                        e.target.closest('tr').remove();
                        reindexRows();
                        calculateTotals();
                    } else {
                        alert('يجب أن تحتوي الفاتورة على صنف واحد على الأقل.');
                    }
                }
            });
        }

        // AJAX المورد السريع
        const saveSupplierBtn = document.getElementById('saveQuickSupplierBtn');
        if (saveSupplierBtn) {
            saveSupplierBtn.addEventListener('click', function () {
                const nameInput = document.getElementById('quick_supplier_name');
                const phoneInput = document.getElementById('quick_supplier_phone');
                const addressInput = document.getElementById('quick_supplier_address');
                const errorDiv = document.getElementById('quickSupplierError');

                if (errorDiv) errorDiv.classList.add('d-none');
                const name = nameInput ? nameInput.value.trim() : '';

                if (!name) {
                    if (errorDiv) {
                        errorDiv.textContent = 'يرجى إدخال اسم المورد';
                        errorDiv.classList.remove('d-none');
                    }
                    return;
                }

                saveSupplierBtn.disabled = true;
                saveSupplierBtn.textContent = 'جاري الحفظ...';

                fetch("{{ route('suppliers.store_ajax') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: name,
                        phone: phoneInput ? phoneInput.value : '',
                        address: addressInput ? addressInput.value : ''
                    })
                })
                .then(response => {
                    if (!response.ok) return response.json().then(err => { throw err; });
                    return response.json();
                })
                .then(data => {
                    saveSupplierBtn.disabled = false;
                    saveSupplierBtn.textContent = 'حفظ المورد واختياره';

                    if (data.success || data.supplier) {
                        const supplier = data.supplier || data;
                        const supplierSelect = document.getElementById('supplier_select');
                        const newOption = new Option(supplier.name, supplier.id, true, true);
                        supplierSelect.add(newOption);

                        document.getElementById('quickSupplierForm').reset();
                        const modalElement = document.getElementById('quickSupplierModal');
                        if (window.jQuery && $(modalElement).modal) {$(modalElement).modal('hide'); }
                        else if (typeof bootstrap !== 'undefined') {
                            const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                            modal.hide();
                        }
                    } else {
                        if (errorDiv) {
                            errorDiv.textContent = data.message || 'حدث خطأ أثناء الحفظ';
                            errorDiv.classList.remove('d-none');
                        }
                    }
                })
                .catch(error => {
                    saveSupplierBtn.disabled = false;
                    saveSupplierBtn.textContent = 'حفظ المورد واختياره';
                    if (errorDiv) {
                        errorDiv.textContent = error.message || 'حدث خطأ أثناء الاتصال بالخادم.';
                        errorDiv.classList.remove('d-none');
                    }
                });
            });
        }

        // AJAX الصنف السريع
        const saveBtn = document.getElementById('saveQuickProductBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                const nameInput = document.getElementById('quick_product_name');
                const categorySelect = document.getElementById('quick_category_id');
                const barcodeInput = document.getElementById('quick_barcode');
                const unitSelect = document.getElementById('quick_unit');
                const costPriceInput = document.getElementById('quick_cost_price');
                const sellingPriceInput = document.getElementById('quick_selling_price');
                const wholesalePriceInput = document.getElementById('quick_wholesale_price');
                const errorDiv = document.getElementById('quickProductError');

                if (errorDiv) errorDiv.classList.add('d-none');

                const name = nameInput ? nameInput.value.trim() : '';
                const categoryId = categorySelect ? categorySelect.value : '';
                const costPrice = costPriceInput ? costPriceInput.value : 0;
                const sellingPrice = sellingPriceInput ? sellingPriceInput.value : 0;
                const wholesalePrice = wholesalePriceInput ? wholesalePriceInput.value : 0;

                if (!name || !categoryId) {
                    if (errorDiv) {
                        errorDiv.textContent = 'يرجى تعبئة الحقول المطلوبة (اسم الصنف والتصنيف)';
                        errorDiv.classList.remove('d-none');
                    }
                    return;
                }

                saveBtn.disabled = true;
                saveBtn.textContent = 'جاري الحفظ...';

                fetch("{{ route('purchase_invoices.quick_product') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: name,
                        category_id: categoryId,
                        barcode: barcodeInput ? barcodeInput.value : '',
                        unit: unitSelect ? unitSelect.value : 'قطعة',
                        cost_price: costPrice,
                        selling_price: sellingPrice,
                        wholesale_price: wholesalePrice
                    })
                })
                .then(response => {
                    if (!response.ok) return response.json().then(err => { throw err; });
                    return response.json();
                })
                .then(data => {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'حفظ وإضافة للفاتورة';

                    if (data.success) {
                        const newOption = `<option value="${data.product.id}">${data.product.name}</option>`;
                        productsListOptions += newOption;
                        productsData[data.product.id] = {
                            cost_price: data.product.cost_price || costPrice,
                            selling_price: data.product.selling_price || sellingPrice,
                            wholesale_price: data.product.wholesale_price || wholesalePrice
                        };

                        document.querySelectorAll('.product-select').forEach(select => {
                            select.insertAdjacentHTML('beforeend', newOption);
                        });

                        const lastRow = tableBody.querySelector('tr:last-child');
                        if (lastRow) {
                            const lastSelect = lastRow.querySelector('.product-select');
                            if (lastSelect) {
                                lastSelect.value = data.product.id;
                                lastSelect.dispatchEvent(new Event('change'));
                            }
                        }

                        document.getElementById('quickProductForm').reset();
                        const modalElement = document.getElementById('quickProductModal');
                        if (window.jQuery && $(modalElement).modal) {$(modalElement).modal('hide'); }
                        else if (typeof bootstrap !== 'undefined') {
                            const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                            modal.hide();
                        }
                    } else {
                        if (errorDiv) {
                            errorDiv.textContent = data.message || 'حدث خطأ أثناء الحفظ';
                            errorDiv.classList.remove('d-none');
                        }
                    }
                })
                .catch(error => {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'حفظ وإضافة للفاتورة';
                    if (errorDiv) {
                        errorDiv.textContent = error.message || 'حدث خطأ في السيرفر أو البيانات.';
                        errorDiv.classList.remove('d-none');
                    }
                });
            });
        }
    });
</script>
@endsection