<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminAuthController;

Route::post('register', [AuthController::class, 'register']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('resend-otp', [AuthController::class, 'resendOtp']);
Route::post('login', [AuthController::class, 'login']);
Route::post('admin/login', [AdminAuthController::class, 'login']);

// بعد التحقق سيحصل المستخدم على token ويمكن حماية المسارات بـ auth:sanctum
Route::middleware('auth:sanctum')->group(function () {
Route::post('logout', [AuthController::class, 'logout']);
 Route::post('admin/logout', [AdminAuthController::class, 'logout']);
 Route::post('admin/register', [AdminAuthController::class, 'register']);
  Route::get('user', function (Request $request) {
        return $request->user();
    });
});
