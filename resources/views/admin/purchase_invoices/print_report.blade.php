<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير فواتير الشراء (المشتريات)</title>
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
                <h4>{{ $branch->name ?? '  فرع  ' }}</h4>
        <p>تقرير فواتير الشراء والمشتريات</p>
        <p style="font-size: 11px; margin-top: 5px;">تاريخ الطباعة: {{ date('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>رقم الفاتورة</th>
                <th>المورد</th>
                <th>الإجمالي</th>
                <th>المدفوع</th>
                <th>المتبقي</th>
                <th>التاريخ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $index => $invoice)
            @php $remaining = $invoice->total_amount - $invoice->paid_amount; @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                <td>{{ $invoice->supplier ? $invoice->supplier->name : 'غير محدد' }}</td>
                <td>{{ number_format($invoice->total_amount, 2) }} د.ل</td>
                <td>{{ number_format($invoice->paid_amount, 2) }} د.ل</td>
                <td style="color: {{ $remaining > 0 ? 'red' : 'green' }}; font-weight: bold;">
                    {{ number_format($remaining, 2) }} د.ل
                </td>
                <td>{{ $invoice->created_at->format('Y-m-d H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <h3>الملخص المالي للمشتريات</h3>
        <p><strong>إجمالي عدد الفواتير:</strong> {{ number_format($totalInvoices) }}</p>
        <p><strong>إجمالي المشتريات:</strong> {{ number_format($totalAmount, 2) }} د.ل</p>
        <p><strong>إجمالي المدفوع:</strong> {{ number_format($totalPaid, 2) }} د.ل</p>
        <p><strong>إجمالي المتبقي (الديون):</strong> {{ number_format($totalRemaining, 2) }} د.ل</p>
    </div>

</body>
</html>