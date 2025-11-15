<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function store(Request $request)
{
    $data = $request->validate([
        'type'        => 'required|string',
        'address'     => 'required|string',
        'description' => 'required|string',
        'agency_id'   => 'required|exists:agencies,id',
        'file'        => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx'
    ]);

    //
    $filePath = null;

if ($request->hasFile('file')) {
    $stored = $request->file('file')->store('complaints_files', 'public');
    $filePath = 'storage/' . $stored;   // ← هنا التعديل المطلوب
}



    $complaint = Complaint::create([
        'type' => $data['type'],
        'address' => $data['address'],
        'description' => $data['description'],
        'file' => $filePath,
        'agency_id' => $data['agency_id'],
        'user_id' => $request->user()->id,
        'status' => 1,
        'noti' => null,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'تمت إضافة الشكوى بنجاح.',
        'data' => $complaint,
        'status_code' => 201,
        'timestamp' => now()->toIso8601String(),
    ], 201);
}



public function listByStatus(Request $request, $status)
{
    $complaints = Complaint::where('user_id', $request->user()->id)
                            ->where('status', $status)
                            ->get();

    return response()->json([
        'success' => true,
        'message' => 'تم جلب الشكاوى بنجاح.',
        'data' => $complaints,
        'status_code' => 200,
        'timestamp' => now()->toIso8601String(),
    ], 200);
}

public function show(Request $request, $id)
{
    // جلب الشكوى المطلوبة
    $complaint = Complaint::where('id', $id)
                           ->where('user_id', $request->user()->id) // منع الوصول لشكوى شخص آخر
                           ->first();

    // في حال لم توجد الشكوى
    if (!$complaint) {
        return response()->json([
            'success' => false,
            'message' => 'الشكوى غير موجودة.',
            'data' => null,
            'status_code' => 404,
            'timestamp' => now()->toIso8601String(),
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'تم جلب بيانات الشكوى بنجاح.',
        'data' => $complaint,
        'status_code' => 200,
        'timestamp' => now()->toIso8601String(),
    ], 200);
}

public function byEmployeeAgency(Request $request)
{
    $employee = $request->user(); // الموظف المسجل دخول

    // التحقق أن المستخدم فعلاً موظف
    if ($employee->role != 2) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح. هذا المسار للموظفين فقط.',
            'data' => null,
            'status_code' => 403,
            'timestamp' => now()->toIso8601String(),
        ], 403);
    }

    // التحقق أن لديه جهة
    if (!$employee->id_agency) {
        return response()->json([
            'success' => false,
            'message' => 'لم يتم ربط هذا الموظف بأي جهة.',
            'data' => null,
            'status_code' => 400,
            'timestamp' => now()->toIso8601String(),
        ], 400);
    }

    // جلب الشكاوى المرتبطة بجهة الموظف
    $complaints = Complaint::where('agency_id', $employee->id_agency)->get();

    return response()->json([
        'success' => true,
        'message' => 'تم جلب شكاوى الجهة بنجاح.',
        'data' => $complaints,
        'status_code' => 200,
        'timestamp' => now()->toIso8601String(),
    ], 200);
}
public function byEmployeeAgencyAndStatus(Request $request, $status)
{
    $employee = $request->user(); // الموظف الذي سجل دخول

    // مسار الموظفين فقط
    if ($employee->role != 2) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح. هذا المسار للموظفين فقط.',
            'data' => null,
            'status_code' => 403,
            'timestamp' => now()->toIso8601String(),
        ], 403);
    }

    // يجب أن يكون مرتبطًا بجهة
    if (!$employee->id_agency) {
        return response()->json([
            'success' => false,
            'message' => 'لم يتم ربط هذا الموظف بأي جهة.',
            'data' => null,
            'status_code'=> 400,
            'timestamp' => now()->toIso8601String(),
        ], 400);
    }

    // جلب الشكاوى حسب الجهة والحالة
    $complaints = Complaint::where('agency_id', $employee->id_agency)
                            ->where('status', $status)
                            ->get();

    return response()->json([
        'success' => true,
        'message' => 'تم جلب شكاوى الجهة حسب الحالة بنجاح.',
        'data' => $complaints,
        'status_code' => 200,
        'timestamp' => now()->toIso8601String(),
    ], 200);
}

}
