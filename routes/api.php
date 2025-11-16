<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\AgencyController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\NotificationController;

Route::post('register', [AuthController::class, 'register']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('resend-otp', [AuthController::class, 'resendOtp']);
Route::post('login', [AuthController::class, 'login']);
Route::post('admin/login', [AdminAuthController::class, 'login']);



Route::middleware('auth:sanctum')->group(function () {
Route::post('logout', [AuthController::class, 'logout']);
 Route::post('admin/logout', [AdminAuthController::class, 'logout']);
 Route::post('admin/register', [AdminAuthController::class, 'register']);
  // إضافة شكوى
    Route::post('complaints/store', [ComplaintController::class, 'store']);

    // عرض شكاوى المستخدم حسب الحالة
    Route::get('complaints/status/{status}', [ComplaintController::class, 'listByStatus']);
   // تفاصيل شكوى
    Route::get('/complaints/details/{status}', [ComplaintController::class, 'show']);
    //get all agency
    Route::get('/agencies', [AgencyController::class, 'getAll']);
    //get complaints employee according agency
    Route::get('/employee/complaints', [ComplaintController::class, 'byEmployeeAgency']);
        // جلب شكاوى الجهة الخاصة بالموظف حسب الحالة
    Route::get('employee/complaints/status/{status}', [ComplaintController::class, 'byEmployeeAgencyAndStatus']);
          // تعديل حالة شكوى
Route::put('/employee/complaints/{id}/status', [ComplaintController::class, 'updateStatus']);
// إضافة ملاحظة
    Route::put('/employee/complaints/{id}/note', [ComplaintController::class, 'addNote']);
// عرض تفاصيل شكوى للموظف
    Route::get('/employee/complaints/{id}', [ComplaintController::class, 'showEmployeeComplaint']);
      // حجز الشكوى
    Route::post('/employee/complaints/{id}/lock', [ComplaintController::class, 'lockComplaint']);

    // فك حجز الشكوى
    Route::post('/employee/complaints/{id}/unlock', [ComplaintController::class, 'unlockComplaint']);
     // get history
    Route::get('/employee/complaints/{id}/history', [ComplaintController::class, 'getComplaintHistory']);
// get notifications
    Route::get('/user/notifications', [NotificationController::class , 'getNotifications']);
//markAsRead
    Route::post('/user/notifications/read/{id}', [NotificationController::class, 'markAsRead']);
//عرض كل الشكاوى
Route::get('/admin/complaints', [ComplaintController::class, 'index']);


    Route::get('user', function (Request $request) {
        return $request->user();
    });
});
