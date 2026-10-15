<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير الحركة المالية للخزائن</title>
    <style>
        body { font-family: 'Amiri', Tahoma, sans-serif; direction: rtl; text-align: right; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .summary { display: flex; justify-content: space-around; margin-bottom: 20px; font-size: 14px; background: #f8f9fa; padding: 10px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        th { background-color: #f2f2f2; }
        .text-success { color: green; font-weight: bold; }
        .text-danger { color: red; font-weight: bold; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>{{ $company->name ?? 'منظومة المبيعات' }}</h2>
        <h3>تقرير الحركات المالية والخزائن</h3>
        <p>تاريخ التقرير: {{ date('Y-m-d H:i') }}</p>
    </div>

    <div class="summary">
        <div>إجمالي السيولة الحالية: <strong>{{ number_format($totalTreasuryBalance, 2) }} د.ل</strong></div>
        <div>إجمالي الإيداعات: <strong class="text-success">{{ number_format($totalDeposits, 2) }} د.ل</strong></div>
        <div>إجمالي السحب: <strong class="text-danger">{{ number_format($totalWithdrawals, 2) }} د.ل</strong></div>
        <div>الصافي: <strong>{{ number_format($netCashFlow, 2) }} د.ل</strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>الخزينة / الحساب</th>
                <th>نوع الحركة</th>
                <th>المبلغ</th>
                <th>المصدر</th>
                <th>التاريخ والوقت</th>
                <th>ملاحظات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $index => $trx)
                @php
                    $sourceLabels = [
                        'supplier_payment' => 'سداد دفعة لمورد',
                        'customer_payment' => 'تحصيل دفعة من زبون',
                        'sale' => 'مبيعات نقطة البيع',
                        'purchase' => 'فاتورة شراء',
                        'opening_balance' => 'رصيد أول المدة',
                        'expense' => 'مصروفات'
                    ];
                    $sourceKey = strtolower(trim($trx->source_type));
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $trx->treasury ? $trx->treasury->name : '-' }}</td>
                    <td>{{ $trx->type == 'deposit' ? 'إيداع' : 'سحب' }}</td>
                    <td class="{{ $trx->type == 'deposit' ? 'text-success' : 'text-danger' }}">
                        {{ number_format($trx->amount, 2) }} د.ل
                    </td>
                    <td>{{ $sourceLabels[$sourceKey] ?? $trx->source_type }}</td>
                    <td>{{ $trx->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $trx->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>