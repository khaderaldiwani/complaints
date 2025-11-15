<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AgencyController extends Controller
{
    public function getAll()
    {
        $agencies = Agency::all();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب جميع الجهات بنجاح.',
            'data' => $agencies,
            'status_code' => 200,
            'timestamp' => Carbon::now()->toIso8601String(),
        ], 200);
    }

    public function updateStatus(Request $request, $id)
{
    $employee = $request->user();

    // فقط الموظف يستطيع تغيير الحالات
    if ($employee->role != 2) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح. هذا المسار للموظفين فقط.',
            'data' => null,
            'status_code' => 403,
            'timestamp' => now()->toIso8601String(),
        ], 403);
    }

    // تحقق أن لديه جهة
    if (!$employee->id_agency) {
        return response()->json([
            'success' => false,
            'message' => 'لم يتم ربط هذا الموظف بأي جهة.',
            'data' => null,
            'status_code' => 400,
            'timestamp' => now()->toIso8601String(),
        ], 400);
    }

    // تحقق من أن الشكوى موجودة في جهته فقط
    $complaint = Complaint::where('id', $id)
                          ->where('agency_id', $employee->id_agency)
                          ->first();

    if (!$complaint) {
        return response()->json([
            'success' => false,
            'message' => 'الشكوى غير موجودة أو لا تنتمي لجهتك.',
            'data' => null,
            'status_code' => 404,
            'timestamp' => now()->toIso8601String(),
        ], 404);
    }

    // التحقق أن الحالة الجديدة صحيحة
    $data = $request->validate([
        'status' => 'required|integer|in:2,3,4'
    ]);

    // تحديث الحالة
    $complaint->update([
        'status' => $data['status']
    ]);

    return response()->json([
        'success' => true,
        'message' => 'تم تحديث حالة الشكوى بنجاح.',
        'data' => $complaint,
        'status_code' => 200,
        'timestamp' => now()->toIso8601String(),
    ], 200);
}


}
