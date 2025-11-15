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
