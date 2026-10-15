<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PurchaseInvoiceController; 
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TreasuryController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\RoleController;
// الصفحة الرئيسية: إعادة التوجيه التلقائي إلى لوحة التحكم
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// المسارات المحمية (تتطلب تسجيل الدخول)
Route::middleware(['auth', 'verified'])->group(function () {

    // 1. لوحة التحكم الرئيسية
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. مسارات إدارة إعدادات الشركة
    Route::get('/company-settings', [CompanySettingController::class, 'index'])->name('company-settings.index');
    Route::get('/company-settings/create', [CompanySettingController::class, 'create'])->name('company-settings.create');
    Route::post('/company-settings/store', [CompanySettingController::class, 'store'])->name('company-settings.store');
    Route::get('/company-settings/edit', [CompanySettingController::class, 'edit'])->name('company-settings.edit');
    Route::post('/company-settings/update', [CompanySettingController::class, 'update'])->name('company-settings.update');

    // 3. مسارات إدارة الفروع
    Route::resource('branches', BranchController::class)->except(['']);

    // 4. مسارات الملف الشخصي للمستخدم
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

//التصنفات والمنتجات


Route::middleware(['auth'])->group(function () {
    Route::get('/products/print-inventory', [ProductController::class, 'printInventory'])->name('products.print-inventory');
    // مسارات التصنيفات والمنتجات
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);

});

Route::middleware(['auth', 'active.shift'])->group(function () {
    //pos system 
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    

Route::post('/pos/checkout', [PosController::class, 'store'])->name('pos.store');
    // أي مسارات أخرى خاصة بنقطة البيع مثل إتمام الفاتورة
});



Route::middleware(['auth'])->group(function () {
// مسارات الزبائن
Route::resource('customers', CustomerController::class);
Route::post('customers/{customer}/pay', [CustomerController::class, 'addPayment'])->name('customers.pay');
});


Route::middleware(['auth'])->group(function () {
// مسارات الموردين
Route::resource('suppliers', SupplierController::class);
Route::post('suppliers/{supplier}/pay', [SupplierController::class, 'addPayment'])->name('suppliers.pay');
Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
Route::get('/suppliers/payments/{payment}/print', [PurchaseInvoiceController::class, 'printSupplierReceipt'])
    ->name('supplier_payments.print');
Route::delete('/suppliers/transactions/{id}', [SupplierController::class, 'destroyTransaction'])->name('suppliers.transactions.destroy');

});


Route::middleware(['auth'])->group(function () {
// مسارات الفواتير
Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
Route::get('/invoices/{id}/print', [InvoiceController::class, 'print'])->name('invoices.print');
Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
});


// مسارات إدارة فواتير الشراء
Route::middleware(['auth'])->group(function () {
    Route::resource('purchase_invoices', PurchaseInvoiceController::class);
    Route::post('/purchase-invoices/quick-product', [PurchaseInvoiceController::class, 'storeQuickProduct'])->name('purchase_invoices.quick_product');
    Route::post('/purchase-invoices/{id}/settle', [PurchaseInvoiceController::class, 'settle'])->name('purchase_invoices.settle');
    Route::post('/suppliers/quick-store', [SupplierController::class, 'storeAjax'])->name('suppliers.store_ajax');
    
    // تعديل اسم مسار الطباعة ليطابق باقي المسارات (purchase_invoices.print)
    Route::get('/purchase-invoices/{purchaseInvoice}/print', [PurchaseInvoiceController::class, 'print'])->name('purchase_invoices.print');
});




// مسار عرض الفاتورة التفصيلي
Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');

// مسار طباعة الفاتورة Direct Print
Route::get('/invoices/{id}/print', [InvoiceController::class, 'print'])->name('invoices.print');

// مسار طباعة واصل السداد/القبض للزبون
Route::get('/customers/receipt/{id}/print', [CustomerController::class, 'printReceipt'])->name('customers.receipt.print');




//التقريرات المالية
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/financial-reports', [FinancialReportController::class, 'index'])->name('reports.financial');
});


Route::middleware(['auth', ])->prefix('admin')->group(function () {
    // إدارة المستخدمين
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    // إدارة الأدوار والصلاحيات
    Route::resource('roles', RoleController::class);
});

// مسارات إدارة الخزائن والحسابات
Route::middleware(['auth'])->prefix('admin')->group(function () {
    // المسارات السابقة (المستخدمين، الأدوار)...
        Route::get('treasuries/report', [TreasuryController::class, 'report'])->name('treasuries.report');

    // إدارة الخزائن
    Route::resource('treasuries', TreasuryController::class);
});


//الورديات
Route::middleware(['auth'])->prefix('admin')->group(function () {
    
    Route::get('shifts/open', [ShiftController::class, 'create'])->name('shifts.create');
    Route::post('shifts/open', [ShiftController::class, 'store'])->name('shifts.store');
    Route::get('shifts/{id}/close', [ShiftController::class, 'closeForm'])->name('shifts.close.form');
    Route::post('shifts/{id}/close', [ShiftController::class, 'close'])->name('shifts.close');

        Route::resource('shifts', ShiftController::class);
    
});

// مسارات المصادقة التابعة لحزمة Breeze (تسجيل الدخول، الخروج، إلخ) 
require __DIR__.'/auth.php';