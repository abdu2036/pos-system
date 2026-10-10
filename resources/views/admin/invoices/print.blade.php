<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة مبيعات - {{ $invoice->invoice_number }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #fff; color: #000; }
        .invoice-card { max-width: 800px; margin: 20px auto; border: 1px solid #ccc; padding: 25px; border-radius: 8px; }
        @media print {
            .no-print { display: none !important; }
            .invoice-card { border: none; margin: 0 auto; width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="no-print text-center my-3">
        <button onclick="window.print()" class="btn btn-primary me-2">طباعة الفاتورة</button>
        <button onclick="window.close()" class="btn btn-secondary">إغلاق</button>
    </div>

    <div class="invoice-card">
        <!-- هيدر الفاتورة -->
        <div class="row border-bottom pb-3 mb-3 align-items-center">
            <div class="col-6">
                <h3 class="fw-bold">{{ $company->name ?? 'منظومة المبيعات' }}</h3>
                <p class="mb-0 text-muted">هاتف: {{ $company->phone ?? '-' }}</p>
                <p class="mb-0 text-muted">العنوان: {{ $company->address ?? '-' }}</p>
            </div>
            <div class="col-6 text-end">
                <h4 class="fw-bold text-primary">فاتورة مبيعات</h4>
                <p class="mb-1"><strong>رقم الفاتورة:</strong> {{ $invoice->invoice_number }}</p>
                <p class="mb-0"><strong>التاريخ:</strong> {{ $invoice->created_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>

        <!-- بيانات الزبون -->
        <div class="row mb-3 bg-light p-2 rounded">
            <div class="col-6">
                <strong>الزبون:</strong> {{ $invoice->customer->name ?? 'زبون نقدي (عام)' }}
            </div>
            <div class="col-6 text-end">
                <strong>طريقة الدفع:</strong> 
                @if($invoice->payment_method == 'credit' || $invoice->payment_method == 'آجل')
                    <span class="badge bg-warning text-dark">آجل / دين</span>
                @else
                    <span class="badge bg-success">نقدي</span>
                @endif
            </div>
        </div>

        <!-- جدول المنتجات -->
        <table class="table table-bordered text-center align-middle my-3">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>سعر الوحدة</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="text-start">{{ $item->product->name ?? 'منتج' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }} د.ل</td>
                        <td>{{ number_format($item->subtotal, 2) }} د.ل</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- إجماليات الفاتورة -->
        <div class="row justify-content-end mt-3">
            <div class="col-5">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td><strong>الإجمالي الكلي:</strong></td>
                        <td class="text-end fw-bold fs-5">{{ number_format($invoice->total_amount, 2) }} د.ل</td>
                    </tr>
                    <tr>
                        <td><strong>المدفوع:</strong></td>
                        <td class="text-end text-success fw-bold">{{ number_format($invoice->paid_amount, 2) }} د.ل</td>
                    </tr>
                    <tr>
                        <td><strong>المتبقي (الدين):</strong></td>
                        <td class="text-end text-danger fw-bold">{{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }} د.ل</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="text-center text-muted mt-4 border-top pt-2">
            <small>شكراً لتعاملكم معنا!</small>
        </div>
    </div>
</div>

<script>
    window.onload = function() {
        window.print();
    }
</script>

</body>
</html>