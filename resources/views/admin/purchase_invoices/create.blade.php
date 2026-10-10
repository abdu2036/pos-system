@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">إضافة فاتورة شراء جديدة</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('purchase_invoices.index') }}" class="btn btn-outline-secondary">
                    رجوع لسجل المشتريات
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('purchase_invoices.store') }}" method="POST" id="purchaseInvoiceForm">
        @csrf
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
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
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
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>نقداً (كاش)</option>
                                <option value="credit" {{ old('payment_method') == 'credit' ? 'selected' : '' }}>آجل (على الحساب)</option>
                                <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>تحويل مصرفي</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="card bg-light p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">إجمالي الفاتورة:</span>
                                <span class="h3 mb-0 text-primary" id="grand-total">0.00</span>
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
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary" id="submitFormBtn">حفظ فاتورة الشراء وتحديث المخزون والأسعار</button>
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

<div class="col-md-6 mb-3">
    <label class="form-label">التصنيف <span class="text-danger">*</span></label>
    <div class="input-group">
        <select name="category_id" id="quick_category_id" class="form-select" required>
            <option value="">-- اختر التصنيف --</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
        <button type="button" class="btn btn-outline-secondary" id="btnQuickAddCategory" title="إضافة تصنيف جديد">
            <i class="fas fa-plus">+</i>
        </button>
    </div>
</div>

<!-- حقل مخفي يتفعل عند الضغط على زر إضافة تصنيف جديد -->
<div class="col-md-12 mb-3 d-none" id="quickCategoryInputContainer">
    <label class="form-label text-primary">اسم التصنيف الجديد</label>
    <div class="input-group">
        <input type="text" id="new_category_name" class="form-control" placeholder="أدخل اسم التصنيف الجديد">
        <button type="button" class="btn btn-success" id="btnSaveQuickCategory">حفظ التصنيف</button>
        <button type="button" class="btn btn-light" id="btnCancelQuickCategory">إلغاء</button>
    </div>
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
                selling_price: {{ $product->selling_price ?? 0 }},
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
                    purchasePriceInput.value = productsData[productId].cost_price;
                    sellingPriceInput.value = productsData[productId].selling_price;
                    wholesalePriceInput.value = productsData[productId].wholesale_price;
                } else {
                    purchasePriceInput.value = '';
                    sellingPriceInput.value = '';
                    wholesalePriceInput.value = '';
                }
                calculateTotals();
            });

            qtyInput.addEventListener('input', calculateTotals);
            purchasePriceInput.addEventListener('input', calculateTotals);
        }

        document.querySelectorAll('#items-table tbody tr.item-row').forEach(bindRowEvents);

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

        // ==========================================
        // حفظ المورد السريع عبر AJAX
        // ==========================================
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
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    saveSupplierBtn.disabled = false;
                    saveSupplierBtn.textContent = 'حفظ المورد واختياره';

                    if (data.success || data.supplier) {
                        const supplier = data.supplier || data;
                        const supplierSelect = document.getElementById('supplier_select');

                        // إضافة المورد المضاف حديثاً للقائمة المنسدلة واختياره
                        const newOption = new Option(supplier.name, supplier.id, true, true);
                        supplierSelect.add(newOption);

                        document.getElementById('quickSupplierForm').reset();

                        const modalElement = document.getElementById('quickSupplierModal');
                        if (window.jQuery && $(modalElement).modal) {$(modalElement).modal('hide');
                        } else if (typeof bootstrap !== 'undefined') {
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
                    console.error('Error:', error);
                });
            });
        }

        // ==========================================
        // حفظ صنف جديد عبر Modal
        // ==========================================
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
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
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
                        if (window.jQuery && $(modalElement).modal) {$(modalElement).modal('hide');
                        } else if (typeof bootstrap !== 'undefined') {
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
                    console.error('Error:', error);
                });
            });
        }
    });
document.addEventListener('DOMContentLoaded', function () {
    const btnQuickAddCategory = document.getElementById('btnQuickAddCategory');
    const quickCategoryContainer = document.getElementById('quickCategoryInputContainer');
    const btnCancelQuickCategory = document.getElementById('btnCancelQuickCategory');
    const btnSaveQuickCategory = document.getElementById('btnSaveQuickCategory');
    const categorySelect = document.getElementById('quick_category_id');

    // إظهار حقل إضافة تصنيف جديد
    if (btnQuickAddCategory) {
        btnQuickAddCategory.addEventListener('click', function () {
            quickCategoryContainer.classList.remove('d-none');
        });
    }

    // إخفاء حقل الإضافة
    if (btnCancelQuickCategory) {
        btnCancelQuickCategory.addEventListener('click', function () {
            quickCategoryContainer.classList.add('d-none');
            document.getElementById('new_category_name').value = '';
        });
    }

    // حفظ التصنيف الجديد عبر AJAX
    if (btnSaveQuickCategory) {
        btnSaveQuickCategory.addEventListener('click', function () {
            const categoryName = document.getElementById('new_category_name').value.trim();

            if (!categoryName) {
                alert('يرجى إدخال اسم التصنيف!');
                return;
            }

            fetch("{{ route('categories.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({ name: categoryName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success || data.id) {
                    // إضافة التصنيف الجديد للقائمة المنسدلة وتحديده تلقائياً
                    const newOption = new Option(data.category ? data.category.name : categoryName, data.category ? data.category.id : data.id, true, true);
                    categorySelect.add(newOption);
                    
                    // إعادة تعيين وإخفاء الحقل
                    document.getElementById('new_category_name').value = '';
                    quickCategoryContainer.classList.add('d-none');
                } else {
                    alert(data.message || 'حدث خطأ أثناء إضافة التصنيف');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء حفظ التصنيف.');
            });
        });
    }
});


</script>
@endsection