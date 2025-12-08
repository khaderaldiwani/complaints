<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Administrative;
use App\Models\User;
use App\Models\Complaint;
use App\Models\Agency;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminDashboardController extends Controller
{
    // public function statistics(Request $request)
    // {
    //     $admin = auth()->user();
    //     if (!$admin || $admin->role != 1) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'غير مصرح لك بالوصول إلى الإحصائيات',
    //             'status_code' => 403,
    //             'timestamp' => now()->toIso8601String()
    //         ], 403);
    //     }

    //     // إجمالي الشكاوى
    //     $totalComplaints = Complaint::count();

    //     // حسب الحالات
    //     $statusStats = [
    //         'new' => Complaint::where('status', 1)->count(),
    //         'processing' => Complaint::where('status', 2)->count(),
    //         'done' => Complaint::where('status', 3)->count(),
    //         'rejected' => Complaint::where('status', 4)->count(),
    //     ];

    //     // الشكاوى حسب الجهة
    //     $agencies = Agency::withCount('complaints')->get()
    //         ->map(function ($agency) {
    //             return [
    //                 'id' => $agency->id,
    //                 'name' => $agency->name,
    //                 'complaints' => $agency->complaints_count
    //             ];
    //         });

    //     // المستخدمون
    //     $usersStats = [
    //         'citizens' => User::count(),
    //         'admins' => Administrative::where('role', 1)->count(),
    //         'employees' => Administrative::where('role', 2)->count(),
    //     ];

    //     // إحصائيات حسب الوقت
    //     $dailyComplaints = Complaint::whereDate('created_at', Carbon::today())->count();

    //     $monthlyComplaints = Complaint::whereMonth('created_at', Carbon::now()->month)
    //                                   ->whereYear('created_at', Carbon::now()->year)
    //                                   ->count();

    //     // إرجاع النتيجة
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'إحصائيات النظام',
    //         'data' => [
    //             'complaints' => [
    //                 'total' => $totalComplaints,
    //                 'by_status' => $statusStats,
    //             ],
    //             'agencies' => $agencies,
    //             'users' => $usersStats,
    //             'daily' => [
    //                 'complaints_today' => $dailyComplaints
    //             ],
    //             'monthly' => [
    //                 'complaints_this_month' => $monthlyComplaints
    //             ]
    //         ],
    //         'status_code' => 200,
    //         'timestamp' => now()->toIso8601String()
    //     ], 200);
    // }

    public function statistics(Request $request)
{
    $admin = auth()->user();
    if (!$admin || $admin->role != 1) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح لك بالوصول إلى الإحصائيات',
            'status_code' => 403,
            'timestamp' => now()->toIso8601String()
        ], 403);
    }

    $data = Cache::remember('system_statistics', 600, function () {

        return [
            'complaints' => [
                'total' => Complaint::count(),
                'by_status' => [
                    'new' => Complaint::where('status', 1)->count(),
                    'processing' => Complaint::where('status', 2)->count(),
                    'done' => Complaint::where('status', 3)->count(),
                    'rejected' => Complaint::where('status', 4)->count(),
                ]
            ],
            'agencies' => Agency::withCount('complaints')
                ->get()
                ->map(fn($a) => [
                    'id' => $a->id,
                    'name' => $a->name,
                    'complaints' => $a->complaints_count
                ]),
            'users' => [
                'citizens' => User::count(),
                'admins' => Administrative::where('role', 1)->count(),
                'employees' => Administrative::where('role', 2)->count(),
            ],
            'daily' => [
                'complaints_today' => Complaint::whereDate('created_at', now())->count()
            ],
            'monthly' => [
                'complaints_this_month' => Complaint::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)->count()
            ]
        ];
    });

    
    return ApiResponse::success('إحصائيات النظام', $data);
}

}
