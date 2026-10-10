<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة شراء #{{ $purchaseInvoice->invoice_number }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        body { font-family: 'Xing', 'Segoe UI', Tahoma, sans-serif; background: #fff; color: #000; }
        .invoice-box { max-width: 800px; margin: auto; padding: 20px; border: 1px solid #eee; }
        @media print {
            .no-print { display: none !important; }
            .invoice-box { border: none; padding: 0; }
        }
    </style>
</head>
<body>

<div class="container my-4">
    <div class="no-print text-center mb-3">
        <button onclick="window.print()" class="btn btn-primary me-2">طباعة الفاتورة</button>
<a href="{{ route('purchase_invoices.index') }}" class="btn btn-secondary">الرجوع لسجل المشتريات</a>    </div>

    <div class="invoice-box">
        <!-- هيدر الفاتورة -->
        <div class="row border-bottom pb-3 mb-3">
            <div class="col-6">
                <h4>{{ $company->name ?? 'اسم الشركة/المؤسسة' }}</h4>
                <p class="mb-1">الهاتف: {{ $company->phone ?? '-' }}</p>
                <p class="mb-0">العنوان: {{ $company->address ?? '-' }}</p>
            </div>
            <div class="col-6 text-end">
                <h3>فاتورة شراء</h3>
                <p class="mb-1"><strong>رقم الفاتورة:</strong> {{ $purchaseInvoice->invoice_number }}</p>
                <p class="mb-0"><strong>التاريخ:</strong> {{ $purchaseInvoice->created_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>

        <!-- بيانات المورد والدفع -->
        <div class="row mb-3">
            <div class="col-6">
                <strong>المورد:</strong> {{ $purchaseInvoice->supplier->name ?? 'غير محدد' }}
                <br>
                 <strong>الهاتف:</strong> {{ $purchaseInvoice->supplier->phone ?? 'غير محدد' }}
            </div>
            <div class="col-6 text-end">
                <strong>طريقة الدفع:</strong> {{ $purchaseInvoice->payment_type ?? 'آجل' }}
            </div>
        </div>

        <!-- جدول الأصناف -->
        <table class="table table-bordered text-center my-3">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>سعر الشراء</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseInvoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->name ?? $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- إجمالي المبالغ -->
        <div class="row text-end mt-4">
            <div class="col-6 offset-6">
                <p class="mb-1"><strong>الإجمالي:</strong> {{ number_format($purchaseInvoice->grand_total, 2) }} د.ل</p>
                <p class="mb-1"><strong>المدفوع:</strong> {{ number_format($purchaseInvoice->paid_amount, 2) }} د.ل</p>
                <p class="mb-0"><strong>المتبقي:</strong> {{ number_format($purchaseInvoice->remaining_amount, 2) }} د.ل</p>
            </div>
        </div>
    </div>
</div>

<script>
    // تشغيل نافذة الطباعة تلقائياً عند فتح الصفحة
    window.onload = function() {
        window.print();
    }
</script>

</body>
</html>