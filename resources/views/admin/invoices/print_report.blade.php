<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير مبيعات الفترة</title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; direction: rtl; text-align: right; padding: 20px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0 0 5px 0; font-size: 22px; }
        .header p { margin: 0; color: #666; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th, table td { border: 1px solid #ddd; padding: 8px; font-size: 12px; text-align: right; }
        table th { background-color: #f4f4f4; }
        .summary-box { border: 1px solid #333; padding: 10px; background-color: #f9f9f9; margin-top: 20px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 8px 15px; background: #206bc4; color: #fff; border: none; cursor: pointer;">🖨️ طباعة</button>
        <button onclick="window.close()" style="padding: 8px 15px; background: #6c757d; color: #fff; border: none; cursor: pointer;">إغلاق</button>
    </div>

    <div class="header">
        <h1>{{ $company->name ?? 'منظومة المبيعات' }}</h1>
        <p>تقرير سجل فواتير المبيعات</p>
        <p style="font-size: 11px; margin-top: 5px;">تاريخ الطباعة: {{ date('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>رقم الفاتورة</th>
                <th>اسم الزبون</th>
                <th>الإجمالي</th>
                <th>المدفوع</th>
                <th>طريقة الدفع</th>
                <th>التاريخ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $index => $inv)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $inv->invoice_number }}</strong></td>
                <td>{{ $inv->customer->name ?? 'زبون نقدي' }}</td>
                <td>{{ number_format($inv->total_amount, 2) }} د.ل</td>
                <td>{{ number_format($inv->paid_amount, 2) }} د.ل</td>
                <td>{{ $inv->payment_method }}</td>
                <td>{{ $inv->created_at->format('Y-m-d H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <h3>الملخص المالي للتقرير</h3>
        <p><strong>عدد الفواتير:</strong> {{ number_format($invoicesCount) }}</p>
        <p><strong>إجمالي المبيعات:</strong> {{ number_format($totalSalesSum, 2) }} د.ل</p>
        <p><strong>المبلغ المحصل:</strong> {{ number_format($totalPaidSum, 2) }} د.ل</p>
        <p><strong>إجمالي الديون (الآجل):</strong> {{ number_format($totalRemainingSum, 2) }} د.ل</p>
    </div>

</body>
</html>