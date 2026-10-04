<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>معاينة لوحة التحكم - Tabler RTL</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="layout-fluid">
    <div class="page">
        
        <!-- الشريط الجانبي التجريبي -->
        <aside class="navbar navbar-vertical navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <h1 class="navbar-brand navbar-brand-autodark my-2">
                    <a href="#">منظومة المبيعات</a>
                </h1>
                <div class="collapse navbar-collapse show" id="sidebar-menu">
                    <ul class="navbar-nav pt-lg-3">
                        <li class="nav-item active">
                            <a class="nav-link" href="#">
                                <span class="nav-link-title">الرئيسية (اللوحة)</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <span class="nav-link-title">نقطة البيع (POS)</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <span class="nav-link-title">إدارة المخزون والمنتجات</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <span class="nav-link-title">حسابات العملاء والديون</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <span class="nav-link-title">التقارير المالية</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>

        <!-- المحتوى الرئيسي -->
        <div class="page-wrapper">
            
            <!-- الشريط العلوي -->
            <header class="navbar navbar-expand-md navbar-light d-none d-lg-flex">
                <div class="container-xl">
                    <div class="navbar-nav flex-row order-md-last ms-auto">
                        <div class="nav-item">
                            <span class="badge bg-green text-green-fg p-2">الفرع الرئيسي - طرابلس</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- محتوى اللوحة التجريبية -->
            <div class="page-body">
                <div class="container-xl">
                    <div class="row row-cards mb-4">
                        <!-- كارت إجمالي المبيعات -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-muted">مبيعات اليوم</div>
                                            <div class="h2 m-0 font-weight-bold">12,450 د.ل</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- كارت عدد الفواتير -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-muted">عدد الفواتير</div>
                                            <div class="h2 m-0 font-weight-bold">48 فاتورة</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- كارت تنبيه المخزون -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-sm bg-danger-lt">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-danger">تنبيهات نواقص المخزون</div>
                                            <div class="h2 m-0 font-weight-bold text-danger">5 أصناف</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- كارت النقدية بالصندوق -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-muted">النقدية بالصندوق</div>
                                            <div class="h2 m-0 font-weight-bold">8,900 د.ل</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- جدول تجريبي لآخر الفواتير -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">آخر العمليات المنجزة</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter text-nowrap datatable">
                                <thead>
                                    <tr>
                                        <th>رقم الفاتورة</th>
                                        <th>نوع البيع</th>
                                        <th>الزبون / العميل</th>
                                        <th>المبلغ الإجمالي</th>
                                        <th>حالة الدفع</th>
                                        <th>التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>#INV-10024</td>
                                        <td>جملة</td>
                                        <td>شركة الأفق للمقاولات</td>
                                        <td>4,200 د.ل</td>
                                        <td><span class="badge bg-warning text-warning-fg">آجل (جزئي)</span></td>
                                        <td>اليوم 10:30 ص</td>
                                    </tr>
                                    <tr>
                                        <td>#INV-10023</td>
                                        <td>قطاعي</td>
                                        <td>زبون عابر</td>
                                        <td>185 د.ل</td>
                                        <td><span class="badge bg-success text-success-fg">نقدي</span></td>
                                        <td>اليوم 10:15 ص</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</body>
</html>