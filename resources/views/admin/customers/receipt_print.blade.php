<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إيصال قبض زبون - #{{ $transaction->id }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #fff; color: #000; }
        .receipt-card { max-width: 650px; margin: 30px auto; border: 2px solid #2c3e50; padding: 25px; border-radius: 8px; }
        .receipt-header { border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .amount-box { background: #f8f9fa; border: 2px dashed #27ae60; padding: 10px 20px; font-size: 1.5rem; font-weight: bold; color: #27ae60; border-radius: 6px; }
        @media print {
            .no-print { display: none !important; }
            .receipt-card { border: 1px solid #000; margin: 0 auto; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="no-print text-center my-3">
        <button onclick="window.print()" class="btn btn-success me-2">طباعة الواصل</button>
        <button onclick="window.close()" class="btn btn-secondary">إغلاق</button>
    </div>

    <div class="receipt-card">
        <!-- الهيدر -->
        <div class="row receipt-header align-items-center">
            <div class="col-7">
                <h4 class="fw-bold">{{ $company->name ?? 'منظومة المبيعات' }}</h4>
                <p class="mb-0 text-muted">هاتف: {{ $company->phone ?? '-' }}</p>
            </div>
            <div class="col-5 text-end">
                <h5 class="fw-bold text-success">إيصال قبض من زبون</h5>
                <small class="text-muted">التاريخ: {{ $transaction->created_at->format('Y-m-d H:i') }}</small>
            </div>
        </div>

        <!-- رقم الإيصال والمبلغ -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div><strong>رقم الإيصال:</strong> #REC-CUST-{{ sprintf('%05d', $transaction->id) }}</div>
            <div class="amount-box">
                {{ number_format($transaction->amount, 2) }} د.ل
            </div>
        </div>

        @php
            $customerName = 'غير محدد';
            if (isset($transaction->accountable)) {
                $customerName = $transaction->accountable->name ?? 'غير محدد';
            }
        @endphp

        <!-- البيانات -->
        <table class="table table-borderless fs-6">
            <tbody>
                <tr>
                    <td style="width: 30%"><strong>اسم الزبون:</strong></td>
                    <td>{{ $customerName }}</td>
                </tr>
                <tr>
                    <td><strong>طريقة الدفع:</strong></td>
                    <td>
                        @switch($transaction->payment_method)
                            @case('cash') نقداً (كاش) @break
                            @case('card') بطاقة مصرفية @break
                            @case('transfer') تحويل بنكي @break
                            @default {{ $transaction->payment_method ?? 'نقداً' }}
                        @endswitch
                    </td>
                </tr>
                <tr>
                    <td><strong>البيان / الملاحظات:</strong></td>
                    <td>{{ $transaction->notes ?? $transaction->description ?? 'سداد دفعة من الحساب' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- التوقيعات -->
        <div class="row text-center mt-5 pt-3 border-top">
            <div class="col-6">
                <p class="mb-4"><strong>توقيع الزبون / المسلم</strong></p>
                <p>.......................................</p>
            </div>
            <div class="col-6">
                <p class="mb-4"><strong>توقيع المحاسب / الخزينة</strong></p>
                <p>.......................................</p>
            </div>
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