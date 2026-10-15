@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- الهيدر العلوي -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="page-title fw-bold">قائمة المنتجات والمخزون</h2>
    <div>
        <a href="{{ route('products.print-inventory') }}" target="_blank" class="btn btn-outline-primary me-2">
            🖨️ طباعة تقرير الجرد
        </a>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            + إضافة منتج جديد
        </a>
    </div>
</div>

    <!-- كروت التقرير الإحصائي للمخزون والأرباح -->
    <div class="row row-cards mb-3">
        <!-- إجمالي القطع -->
        <div class="col-sm-6 col-lg-2">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي قطع المخزون</div>
                    <div class="fw-bold fs-2 text-dark">{{ number_format($totalQuantity) }} <small class="fs-6">قطعة</small></div>
                </div>
            </div>
        </div>

        <!-- إجمالي رأس المال (الشراء) -->
        <div class="col-sm-6 col-lg-2">
            <div class="card card-sm border-0 shadow-sm border-start border-primary border-3">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي رأس المال (الشراء)</div>
                    <div class="fw-bold fs-2 text-primary">{{ number_format($totalPurchaseCost, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>

        <!-- المبيعات المتوقعة (قطاعي) -->
        <div class="col-sm-6 col-lg-2">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي البيع (قطاعي)</div>
                    <div class="fw-bold fs-2 text-success">{{ number_format($totalRetailSales, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>

        <!-- الربح المتوقع (قطاعي) -->
        <div class="col-sm-6 col-lg-2">
            <div class="card card-sm border-0 shadow-sm bg-success-lt">
                <div class="card-body">
                    <div class="text-success fw-bold fs-6">الربح المتوقع (قطاعي)</div>
                    <div class="fw-bold fs-2 text-success">{{ number_format($expectedRetailProfit, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>

        <!-- المبيعات المتوقعة (جملة) -->
        <div class="col-sm-6 col-lg-2">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted fs-6">إجمالي البيع (جملة)</div>
                    <div class="fw-bold fs-2 text-info">{{ number_format($totalWholesaleSales, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>

        <!-- الربح المتوقع (جملة) -->
        <div class="col-sm-6 col-lg-2">
            <div class="card card-sm border-0 shadow-sm bg-info-lt">
                <div class="card-body">
                    <div class="text-info fw-bold fs-6">الربح المتوقع (جملة)</div>
                    <div class="fw-bold fs-2 text-info">{{ number_format($expectedWholesaleProfit, 2) }} <small class="fs-6">د.ل</small></div>
                </div>
            </div>
        </div>
    </div>

    <!-- بطاقة الجدول وشريط البحث -->
    <div class="card border-0 shadow-sm">
        <div class="card-body border-bottom py-2 bg-light">
            <div class="row align-items-center">
                <!-- شريط البحث المباشر -->
                <div class="col-md-6 col-lg-4 ms-auto">
                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7" /><line x1="21" y1="21" x2="15" y2="15" /></svg>
                        </span>
                        <input type="text" id="products-search-input" class="form-control" placeholder="ابحث باسم المنتج، الباركود، أو التصنيف...">
                    </div>
                </div>
            </div>
        </div>

        <!-- الجدول -->
        <div class="table-responsive">
            <table class="table table-vcenter table-hover card-table">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>التصنيف</th>
                        <th>الباركود</th>
                        <th>سعر الشراء</th>
                        <th>سعر القطاعي</th>
                        <th>سعر الجملة</th>
                        <th>المخزون المتوفر</th>
                        <th class="text-end">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="products-table-body">
                    @foreach($products as $product)
                        @php
                            $qty = $product->branches->sum('pivot.quantity');
                        @endphp
                        <tr class="product-row"
                            data-name="{{ mb_strtolower($product->name) }}"
                            data-barcode="{{ strtolower($product->barcode ?? '') }}"
                            data-category="{{ mb_strtolower($product->category->name ?? '') }}">
                            
                            <td class="fw-bold">{{ $product->name }}</td>
                            <td class="text-muted">{{ $product->category->name ?? '-' }}</td>
                            <td class="text-muted fs-6">{{ $product->barcode ?? '-' }}</td>
<td class="fw-bold">{{ number_format($product->cost_price ?? 0, 2) }}</td>                            <td class="fw-bold text-success">{{ number_format($product->selling_price, 2) }}</td>
                            <td class="fw-bold text-info">{{ number_format($product->wholesale_price ?? $product->selling_price, 2) }}</td>
                            <td>
                                <span class="badge {{ $qty > 0 ? 'bg-blue-lt text-blue' : 'bg-danger-lt text-danger' }} fs-6 px-2 py-1">
                                    {{ $qty }} {{ $product->unit ?? 'قطعة' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary me-1">تعديل</a>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت تأكد من حذف هذا المنتج؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- سكريبت الفلترة والبحث السريع بدون تجميد الصفحة -->
<script>
document.getElementById('products-search-input').addEventListener('input', function(e) {
    let query = e.target.value.toLowerCase().trim();
    let rows = document.querySelectorAll('#products-table-body .product-row');

    rows.forEach(row => {
        let name = row.getAttribute('data-name') || '';
        let barcode = row.getAttribute('data-barcode') || '';
        let category = row.getAttribute('data-category') || '';

        if (name.includes(query) || barcode.includes(query) || category.includes(query)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
@endsection