<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status'  => false,
                'message' => 'بيانات الدخول غير صحيحة.'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();

        // إنشاء توكن مخصص للجهاز/الكاشير
        $token = $user->createToken('pos-device-token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'تم تسجيل الدخول بنجاح.',
            'data'    => [
                'token'     => $token,
                'user_id'   => $user->id,
                'name'      => $user->name,
                'branch_id' => $user->branch_id,
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم تسجيل الخروج بنجاح.'
        ]);
    }
}