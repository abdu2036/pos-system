<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير جرد المخزون</title>
    <style>
        body {
            font-family: 'Amiri', Tahoma, Arial, sans-serif;
            color: #333;
            direction: rtl;
            text-align: right;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 24px;
        }
        .header p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        .info-section {
            margin-bottom: 15px;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            font-size: 13px;
            text-align: right;
        }
        table th {
            background-color: #f4f4f4;
            color: #000;
        }
        .summary-box {
            border: 1px solid #333;
            padding: 10px;
            margin-top: 20px;
            background-color: #f9f9f9;
        }
        .summary-box h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 14px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="text-align: left; margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #206bc4; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">🖨️ طباعة التقرير</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">إغلاق</button>
    </div>

    <div class="header">
        <h1>{{ $company->name ?? 'منظومة المبيعات' }}</h1>
        <p>تقرير جرد المخزون التفصيلي</p>
    </div>

    <div class="info-section">
        <strong>تاريخ التقرير:</strong> {{ date('Y-m-d H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>اسم المنتج</th>
                <th>التصنيف</th>
                <th>الباركود</th>
                <th>سعر الشراء</th>
                <th>سعر القطاعي</th>
                <th>سعر الجملة</th>
                <th>المخزون المتوفر</th>
                <th>إجمالي التكلفة</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $index => $product)
                @php
                    $qty = $product->branches->sum('pivot.quantity');
                    $totalCostItem = ($product->cost_price ?? 0) * $qty;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->barcode ?? '-' }}</td>
                    <td>{{ number_format($product->cost_price ?? 0, 2) }}</td>
                    <td>{{ number_format($product->selling_price, 2) }}</td>
                    <td>{{ number_format($product->wholesale_price ?? 0, 2) }}</td>
                    <td><strong>{{ $qty }} {{ $product->unit ?? 'قطعة' }}</strong></td>
                    <td>{{ number_format($totalCostItem, 2) }} د.ل</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <h3>ملخص إحصائيات المخزون</h3>
        <table style="border: none; margin-bottom: 0;">
            <tr style="border: none;">
                <td style="border: none;"><strong>إجمالي قطع المخزون:</strong> {{ number_format($totalQuantity) }} قطعة</td>
                <td style="border: none;"><strong>إجمالي رأس المال (الشراء):</strong> {{ number_format($totalPurchaseCost, 2) }} د.ل</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>إجمالي قيمة البيع (قطاعي):</strong> {{ number_format($totalRetailSales, 2) }} د.ل</td>
                <td style="border: none;"><strong>إجمالي قيمة البيع (جملة):</strong> {{ number_format($totalWholesaleSales, 2) }} د.ل</td>
            </tr>
        </table>
    </div>

</body>
</html>