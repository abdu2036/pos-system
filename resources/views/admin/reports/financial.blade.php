@extends('layouts.app')

@section('title', 'التقارير المالية الشاملة')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold">📊 لوحة التحكم والتقارير المالية الشاملة</h2>
                <p class="text-muted">نظرة عامة على الأداء المالي، المبيعات، المشتريات، والسيولة النقدية.</p>
            </div>
            <div>
                <button onclick="window.print();" class="btn btn-outline-primary">
                    <i class="fa fa-print"></i> طباعة التقرير
                </button>
            </div>
        </div>
    </div>

    <!-- كاردات الإحصائيات الرئيسية -->
    <div class="row row-cards mb-4">
        <!-- إجمالي المبيعات -->
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-primary text-white avatar">📈</span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-muted">إجمالي المبيعات</div>
                            <div class="h2 text-primary mb-0">{{ number_format($totalSalesAmount ?? 0, 2) }} د.ل</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- إجمالي المشتريات -->
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-warning text-white avatar">🛒</span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-muted">إجمالي المشتريات</div>
                            <div class="h2 text-warning mb-0">{{ number_format($totalPurchasesAmount ?? 0, 2) }} د.ل</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- صافي الأرباح -->
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-success text-white avatar">💰</span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-muted">صافي الأرباح</div>
                            <div class="h2 {{ ($netProfit ?? 0) < 0 ? 'text-danger' : 'text-success' }} mb-0">
                                {{ number_format($netProfit ?? 0, 2) }} د.ل
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- السيولة النقدية الصافية (الخزينة) -->
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-info text-white avatar">🏦</span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-muted">السيولة النقدية الصافية</div>
                            <div class="h2 {{ ($totalLiquidity ?? 0) < 0 ? 'text-danger' : 'text-info' }} mb-0">
                                {{ number_format($totalLiquidity ?? 0, 2) }} د.ل
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول تفصيلي للمخزون ورأس المال -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">تفاصيل تقييم المخزون ورأس المال</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th>البيان</th>
                                    <th>القيمة (د.ل)</th>
                                    <th>التفاصيل والملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold">إجمالي كمية المخزون</td>
                                    <td class="text-center fw-bold">{{ number_format($totalQuantity ?? 0) }} وحدة</td>
                                    <td>إجمالي قطع المنتجات المتوفرة في جميع الفروع</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">رأس المال المستثمر (تكلفة المخزون)</td>
                                    <td class="text-center text-danger fw-bold">{{ number_format($totalPurchaseCost ?? 0, 2) }} د.ل</td>
                                    <td>مجموع (الكمية × سعر التكلفة)</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">قيمة المخزون بسعر البيع (الجملة)</td>
                                    <td class="text-center text-secondary fw-bold">{{ number_format($totalWholesaleSales ?? 0, 2) }} د.ل</td>
                                    <td>الأرباح المتوقعة بالجملة: <span class="text-success">{{ number_format($expectedWholesaleProfit ?? 0, 2) }} د.ل</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">قيمة المخزون بسعر البيع (قطاعي)</td>
                                    <td class="text-center text-primary fw-bold">{{ number_format($totalRetailSales ?? 0, 2) }} د.ل</td>
                                    <td>الأرباح المتوقعة قطاعي: <span class="text-success">{{ number_format($expectedRetailProfit ?? 0, 2) }} د.ل</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection