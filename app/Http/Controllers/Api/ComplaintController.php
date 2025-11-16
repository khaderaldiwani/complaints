<?php

namespace App\Http\Controllers\Api;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    $filePath = null;

if ($request->hasFile('file')) {
    $stored = $request->file('file')->store('complaints_files', 'public');
    $filePath = 'storage/' . $stored;   
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
    $complaint = Complaint::where('id', $id)
                           ->where('user_id', $request->user()->id) // منع الوصول لشكوى شخص آخر
                           ->first();

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
            'status_code'=> 400,
            'timestamp' => now()->toIso8601String(),
        ], 400);
    }

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

if ($complaint->locked_by && $complaint->locked_by != $employee->id) {
    return response()->json([
        'success' => false,
        'message' => 'لا يمكنك تعديل الشكوى لأنها محجوزة من موظف آخر.',
        'data' => null,
        'status_code' => 423,
        'timestamp' => now()->toIso8601String()
    ], 423);
}

    $data = $request->validate([
        'status' => 'required|integer|in:2,3,4'
    ]);

    if ($complaint->status == $data['status']) {
        return response()->json([
            'success' => false,
            'message' => 'الحالة الجديدة مطابقة للحالة الحالية.',
            'data' => null,
            'status_code' => 400,
            'timestamp' => now()->toIso8601String(),
        ], 400);
    }
$old_status=$complaint->status;
    
$complaint->update([
        'status' => $data['status']
    ]);

    DB::table('complaint_history')->insert([
    'action' => 'status_changed',
    'old_value' => $old_status,
    'new_value' => $data['status'],
    'administrative_id' => $employee->id,
    'complaint_id' => $complaint->id,
    'date' => now(),
    'created_at' => now(),
    'updated_at' => now()
]);
$statusText = NotificationHelper::name($data['status']);

NotificationHelper::send(
    $complaint->user_id,
    'تحديث حالة الشكوى',
    "تم تغيير حالة الشكوى رقم {$complaint->id} إلى: {$statusText}."
);

NotificationHelper::send(
    $complaint->user_id,
    'تم تحديث حالة الشكوى',
    'تم تغيير حالة الشكوى رقم ' . $complaint->id . ' إلى الحالة رقم ' . $data['status']
);


    return response()->json([
        'success' => true,
        'message' => 'تم تحديث حالة الشكوى بنجاح.',
        'data' => $complaint,
        'status_code' => 200,
        'timestamp' => now()->toIso8601String(),
    ], 200);
}
public function addNote(Request $request, $id)
{
    $employee = $request->user();

    if ($employee->role != 2) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح لك. هذا المسار خاص بالموظفين فقط.',
            'data' => null,
            'status_code' => 403,
            'timestamp' => now()->toIso8601String(),
        ], 403);
    }

    if (!$employee->id_agency) {
        return response()->json([
            'success' => false,
            'message' => 'لم يتم ربط الموظف بأي جهة.',
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
if ($complaint->locked_by && $complaint->locked_by != $employee->id) {
    return response()->json([
        'success' => false,
        'message' => 'لا يمكنك تعديل الشكوى لأنها محجوزة من موظف آخر.',
        'data' => null,
        'status_code' => 423,
        'timestamp' => now()->toIso8601String()
    ], 423);
}

    $validated = $request->validate([
        'note' => 'required|string|max:255'
    ]);

    $old_noti=$complaint->noti;
    $complaint->update([
        'noti' => $validated['note']
    ]);

    DB::table('complaint_history')->insert([
    'action' => 'note_added',
    'old_value' => $old_noti,
    'new_value' => $validated['note'],
    'administrative_id' => $employee->id,
    'complaint_id' => $complaint->id,
    'date' => now(),
    'created_at' => now(),
    'updated_at' => now()
]);


NotificationHelper::send(
    $complaint->user_id,
    'تم إضافة ملاحظة جديدة على شكواك',
    'قام الموظف بإضافة الملاحظة التالية: ' . $validated['note']
);


    return response()->json([
        'success' => true,
        'message' => 'تم إضافة الملاحظة بنجاح.',
        'data'=> $complaint,
        'status_code' => 200,
        'timestamp' => now()->toIso8601String(),
    ], 200);
}

public function showEmployeeComplaint(Request $request, $id)
{
    $employee = $request->user();

    if ($employee->role != 2) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح. هذا المسار خاص بالموظفين فقط.',
            'data' => null,
            'status_code' => 403,
            'timestamp' => now()->toIso8601String(),
        ], 403);
    }

    $complaint = Complaint::where('id', $id)
                          ->where('agency_id', $employee->id_agency)
                          ->first();

    if (!$complaint) {
        return response()->json([
            'success' => false,
            'message' => 'الشكوى غير موجودة أو لا تتبع جهتك.',
            'data' => null,
            'status_code' => 404,
            'timestamp' => now()->toIso8601String(),
        ], 404);
    }

    if ($complaint->locked_by && $complaint->locked_by != $employee->id) {

        if ($complaint->locked_at && now()->diffInMinutes($complaint->locked_at) < 10) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك عرض تفاصيل الشكوى لأنها قيد المعالجة من موظف آخر.',
                'data' => null,
                'status_code' => 423,
                'timestamp' => now()->toIso8601String(),
            ], 423);
        }
    }
    $complaint->update([
        'locked_by' => $employee->id,
        'locked_at' => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'تفاصيل الشكوى.',
        'data' => $complaint,
        'status_code' => 200,
        'timestamp' => now()->toIso8601String(),
    ], 200);
}

public function lockComplaint(Request $request, $id)
{
    $employee = $request->user();

    if ($employee->role != 2) {
        return response()->json([
            'success' => false,
            'message' => 'هذا المسار خاص بالموظفين فقط.',
            'data' => null,
            'status_code' => 403,
            'timestamp' => now()->toIso8601String()
        ], 403);
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
            'timestamp' => now()->toIso8601String()
        ], 404);
    }

    if ($complaint->locked_by && $complaint->locked_by != $employee->id) {

        if ($complaint->locked_at && now()->diffInMinutes($complaint->locked_at) < 10) {

            return response()->json([
                'success' => false,
                'message' => 'الشكوى قيد المعالجة من موظف آخر.',
                'data' => null,
                'status_code' => 423,
                'timestamp' => now()->toIso8601String()
            ], 423);
        }
    }


    $complaint->update([
        'locked_by' => $employee->id,
        'locked_at' => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'تم حجز الشكوى للمعالجة.',
        'data' => $complaint,
        'status_code' => 200,
        'timestamp' => now()->toIso8601String()
    ], 200);
}


public function unlockComplaint(Request $request, $id)
{
    $employee = $request->user();

    $complaint = Complaint::find($id);

    if (!$complaint) {
        return response()->json([
            'success' => false,
            'message' => 'الشكوى غير موجودة.',
            'data' => null,
            'status_code' => 404,
            'timestamp' => now()->toIso8601String()
        ], 404);
    }


    if ($complaint->locked_by && $complaint->locked_by != $employee->id && $employee->role != 1) {
        return response()->json([
            'success' => false,
            'message' => 'لا يمكنك فك حجز هذه الشكوى.',
            'data' => null,
            'status_code' => 403,
            'timestamp' => now()->toIso8601String()
        ], 403);
    }

    $complaint->update([
        'locked_by' => null,
        'locked_at' => null,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'تم فك الحجز.',
        'data' => $complaint,
        'status_code' => 200,
        'timestamp' => now()->toIso8601String()
    ], 200);
}

public function getComplaintHistory(Request $request, $id)
{
    $employee = $request->user();

    if ($employee->role != 2 && $employee->role != 1) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح لك.',
            'data' => null,
            'status_code' => 403,
            'timestamp' => now()->toIso8601String()
        ], 403);
    }


    if ($employee->role == 2) {
        $complaint = Complaint::where('id', $id)
                              ->where('agency_id', $employee->id_agency)
                              ->first();

        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => 'الشكوى غير موجودة أو لا تنتمي لجهتك.',
                'data' => null,
                'status_code' => 404,
                'timestamp' => now()->toIso8601String()
            ], 404);
        }
    }

    $history = DB::table('complaint_history')
        ->where('complaint_id', $id)
        ->orderBy('date', 'desc')
        ->get();

    return response()->json([
        'success' => true,
        'message' => 'سجل الشكوى.',
        'data' => $history,
        'status_code' => 200,
        'timestamp' => now()->toIso8601String()
    ], 200);
}

public function index(Request $request)
{
    $employee = $request->user(); 

    if ($employee->role != 1) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح. هذا المسار للموظفين فقط.',
            'data' => null,
            'status_code' => 403,
            'timestamp' => now()->toIso8601String(),
        ], 403);
    }
    
    // جلب جميع الشكاوى مع معلومات المستخدم والجهة
    $complaints = Complaint::with(['user', 'agency'])
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'success' => true,
        'message' => 'تم جلب جميع الشكاوى بنجاح.',
        'data' => $complaints,
        'status_code' => 200,
        'timestamp' => now()->toIso8601String(),
    ], 200);
}


}
