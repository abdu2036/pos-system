<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SyncController;

// مسار افتراضي لمعرفة بيانات المستخدم الحالي
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// مسار تسجيل الدخول (لا يحتاج لحماية)
Route::post('/v1/login', [AuthController::class, 'login']);

// مسارات محمية بـ Sanctum (تتطلب Token)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/sync/invoices', [SyncController::class, 'syncInvoices']);
});


Route::get('/v1/pos/initial-data', [SyncController::class, 'getInitialData']);