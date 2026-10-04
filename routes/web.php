<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PosController;

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
    // مسارات التصنيفات والمنتجات
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
});

//pos system 


Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
Route::post('/pos/checkout', [PosController::class, 'store'])->name('pos.store');

// مسارات المصادقة التابعة لحزمة Breeze (تسجيل الدخول، الخروج، إلخ)
require __DIR__.'/auth.php';