<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\CompanySetting;
use App\Models\Shift;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveShift
{
    public function handle(Request $request, Closure $next): Response
    {
        $company = CompanySetting::first();

        // إذا كان نظام الورديات مفعلاً في إعدادات الشركة
        if ($company && $company->enable_shifts) {
            // التحقق من وجود وردية مفتوحة للمستخدم الحالي
            $activeShift = Shift::where('user_id', Auth::id())
                ->where('status', 'open')
                ->first();

            if (!$activeShift) {
                return redirect()->route('shifts.create')
                    ->with('error', 'يجب عليك فتح وردية جديدة وإدخال عهدة البداية قبل الدخول إلى نقطة البيع.');
            }
        }

        return $next($request);
    }
}