<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminManageAccountsController;
use App\Http\Controllers\AdminUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\AgencyController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Jobs\SendFcmNotificationJob;

Route::post('register', [AuthController::class, 'register']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('resend-otp', [AuthController::class, 'resendOtp']);
//Route::post('login', [AuthController::class, 'login']);
//Route::post('admin/login', [AdminAuthController::class, 'login']);

Route::withoutMiddleware('throttle:api')->middleware('throttle:login')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('admin/login', [AdminAuthController::class, 'login']);
});


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
    Route::get('/user/notifications', [NotificationController::class, 'getNotifications']);
    //markAsRead
    Route::post('/user/notifications/read/{id}', [NotificationController::class, 'markAsRead']);
    //عرض كل الشكاوى
    Route::get('/admin/complaints', [ComplaintController::class, 'index']);

//دارة الاداريين
    Route::get('/admin/accounts', [AdminManageAccountsController::class, 'index']);
    Route::post('/admin/accounts', [AdminManageAccountsController::class, 'store']);
    Route::put('/admin/accounts/{id}', [AdminManageAccountsController::class, 'update']);
    Route::delete('/admin/accounts/{id}', [AdminManageAccountsController::class, 'destroy']);
    Route::post('/admin/accounts/{id}/status', [AdminManageAccountsController::class, 'changeStatus']);
// إدارة المواطنين
    Route::get('admin/users', [AdminUserController::class, 'index']);        
    Route::post('admin/users/{id}/disable', [AdminUserController::class, 'disable']);
    Route::post('admin/users/{id}/enable', [AdminUserController::class, 'enable']);
    Route::delete('admin/users/{id}', [AdminUserController::class, 'delete']);
    //سجل العمليات
    Route::get('admin/audit-logs', [AuditLogController::class , 'index']);
//الاحصائيات
Route::get('admin/statistics', [AdminDashboardController::class, 'statistics']);
//pdf
Route::get('/admin/reports/complaints', [ReportController::class, 'complaintsReport']);   
Route::get('user', function (Request $request) {
        return $request->user();
    });
});



Route::post('/test-fcm', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'topic' => 'required|string',
        'title' => 'required|string',
        'body'  => 'required|string',
    ]);

    $response = \App\Helpers\FcmV1::sendToTopic(
        $request->topic,
        $request->title,
        $request->body
    );

    return response()->json([
        'success' => true,
        'message' => 'تم إرسال الإشعار بنجاح.',
        'fcm_response' => json_decode($response, true),
    ]);
});

// Dispatch the FCM job to the queue (asynchronous)
Route::post('/test-fcm-queue', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'topic' => 'required|string',
        'title' => 'required|string',
        'body'  => 'required|string',
    ]);

    dispatch(new \App\Jobs\SendFcmNotificationJob(
        $request->topic,
        $request->title,
        $request->body
    ));

    return response()->json([
        'success' => true,
        'message' => 'تم جدولة الإشعار في الـ queue. شغّل `php artisan queue:work` لمعالجته.',
    ]);
});

// Synchronous test: send immediately and return FCM response
Route::post('/test-fcm-sync', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'topic' => 'required|string',
        'title' => 'required|string',
        'body'  => 'required|string',
    ]);

    $response = \App\Helpers\FcmV1::sendToTopic(
        $request->topic,
        $request->title,
        $request->body
    );

    return response()->json([
        'success' => true,
        'message' => 'تم إرسال الإشعار (مباشر).',
        'fcm_response' => json_decode($response, true),
    ]);
});

// Convenience: test sending to a user-specific topic (e.g., 'user_123') - queued
Route::post('/test-fcm-user/{id}/queue', function (\Illuminate\Http\Request $request, $id) {
    $request->validate([
        'title' => 'required|string',
        'body'  => 'required|string',
    ]);

    $topic = $id;

    dispatch(new \App\Jobs\SendFcmNotificationJob(
        $topic,
        $request->title,
        $request->body
    ));

    return response()->json([
        'success' => true,
        'message' => "تم جدولة الإشعار للمستخدم $id في الـ queue.",
        'topic' => $topic,
    ]);
});

// Convenience: test sending to a user-specific topic (synchronous)
Route::post('/test-fcm-user/{id}/sync', function (\Illuminate\Http\Request $request, $id) {
    $request->validate([
        'title' => 'required|string',
        'body'  => 'required|string',
    ]);

    $topic =  $id;

    $response = \App\Helpers\FcmV1::sendToTopic(
        $topic,
        $request->title,
        $request->body
    );

    return response()->json([
        'success' => true,
        'message' => "تم إرسال الإشعار مباشرة للمستخدم $id.",
        'topic' => $topic,
        'fcm_response' => json_decode($response, true),
    ]);
});

