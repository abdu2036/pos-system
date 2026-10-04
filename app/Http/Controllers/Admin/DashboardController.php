<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CompanySetting;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // جلب البيانات الأساسية والإحصائيات
        $company = CompanySetting::first();
        $totalBranches = Branch::count();
        $activeBranches = Branch::where('is_active', true)->count();
        $totalUsers = User::count();

        // جلب أحدث الفروع المضافة
        $recentBranches = Branch::withCount('users')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'company',
            'totalBranches',
            'activeBranches',
            'totalUsers',
            'recentBranches'
        ));
    }
}