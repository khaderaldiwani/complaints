<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AgencyController extends Controller
{
    public function getAll()
    {
        $agencies = Cache::remember('agencies_all', 3600, function () {
            return Agency::all();
        });
           
           return ApiResponse::success('قائمة الجهات', $agencies);
    }

    public function updateStatus(Request $request, $id)
{
    $employee = $request->user();

    if ($employee->role != 2) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح. هذا المسار للموظفين فقط.',
            'data' => null,
            'status_code' => 403,
            'timestamp' => now()->toIso8601String(),
        ], 403);
    }

    if (!$employee->id_agency) {
        return response()->json([
            'success' => false,
            'message' => 'لم يتم ربط هذا الموظف بأي جهة.',
            'data' => null,
            'status_code' => 400,
            'timestamp' => now()->toIso8601String(),
        ], 400);
    }

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

    $data = $request->validate([
        'status' => 'required|integer|in:2,3,4'
    ]);

    $complaint->update([
        'status' => $data['status']
    ]);

    // مسح الكاش عند تحديث حالة الشكوى
    Cache::forget('statistics');
    Cache::forget("user_complaints_{$complaint->user_id}");
    Cache::forget("user_complaints_{$complaint->user_id}_status_1");
    Cache::forget("user_complaints_{$complaint->user_id}_status_2");
    Cache::forget("user_complaints_{$complaint->user_id}_status_3");
    Cache::forget("user_complaints_{$complaint->user_id}_status_4");
    Cache::forget("agency_complaints_{$complaint->agency_id}");
    Cache::forget("agency_complaints_{$complaint->agency_id}_status_1");
    Cache::forget("agency_complaints_{$complaint->agency_id}_status_2");
    Cache::forget("agency_complaints_{$complaint->agency_id}_status_3");
    Cache::forget("agency_complaints_{$complaint->agency_id}_status_4");
    Cache::forget("complaint_{$complaint->id}");
    Cache::forget("complaint_{$complaint->id}_user_{$complaint->user_id}");

    return response()->json([
        'success' => true,
        'message' => 'تم تحديث حالة الشكوى بنجاح.',
        'data' => $complaint,
        'status_code' => 200,
        'timestamp' => now()->toIso8601String(),
    ], 200);
}


}
