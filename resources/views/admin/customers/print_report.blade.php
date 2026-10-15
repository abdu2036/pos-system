<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير الزبائن والديون</title>
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
        <p>تقرير أرصدة وديون الزبائن</p>
        <p style="font-size: 11px; margin-top: 5px;">تاريخ الطباعة: {{ date('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>اسم الزبون</th>
                <th>رقم الهاتف</th>
                <th>الرصيد / الدين الحالي</th>
                <th>الحد الائتماني</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $index => $customer)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $customer->name }}</strong></td>
                <td>{{ $customer->phone ?? '-' }}</td>
                <td style="color: {{ $customer->balance > 0 ? 'red' : 'green' }}; font-weight: bold;">
                    {{ number_format($customer->balance, 2) }} د.ل
                </td>
                <td>{{ number_format($customer->credit_limit, 2) }} د.ل</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <h3>الملخص المالي للزبائن</h3>
        <p><strong>إجمالي عدد الزبائن:</strong> {{ number_format($totalCustomers) }}</p>
        <p><strong>إجمالي الديون المستحقة:</strong> {{ number_format($totalDebts, 2) }} د.ل</p>
        <p><strong>إجمالي الحد الائتماني:</strong> {{ number_format($totalCreditLimit, 2) }} د.ل</p>
    </div>

</body>
</html>