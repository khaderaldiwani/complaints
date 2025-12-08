<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\Audit;
use Illuminate\Http\Request;
use App\Models\Administrative;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use League\Config\Exception\ValidationException;
use Illuminate\Support\Facades\Cache;

class AdminManageAccountsController extends Controller
{
    // // ✔️ 1) عرض جميع الموظفين و الإداريين
    // public function index(Request $request)
    // {
    //     $query = Administrative::query();

    //     if ($request->has('role')) {
    //         $query->where('role', $request->role);
    //     }

    //     if ($request->has('agency_id')) {
    //         $query->where('id_agency', $request->agency_id);
    //     }

    //     if ($request->has('search')) {
    //         $query->where('name', 'LIKE', "%{$request->search}%")
    //               ->orWhere('user_name', 'LIKE', "%{$request->search}%");
    //     }

    //     $admin = auth()->user();
    //     if ($admin->role != 1) {
    //         return response()->json(['message' => 'غير مصرح'], 403);
    //     }
    // return ApiResponse::success('جميع الحسابات', $query->get(),);

        
    // }

    public function index(Request $request)
{
    $admin = auth()->user();
    if ($admin->role != 1) {
        return response()->json(['message' => 'غير مصرح'], 403);
    }

    $cacheKey = 'admins_index_' . md5(json_encode($request->all()));

    $data = Cache::remember($cacheKey, 600, function () use ($request) {
        $query = Administrative::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('agency_id')) {
            $query->where('id_agency', $request->agency_id);
        }

        if ($request->has('search')) {
            $query->where('name', 'LIKE', "%{$request->search}%")
                ->orWhere('user_name', 'LIKE', "%{$request->search}%");
        }

        return $query->get();
    });

    return ApiResponse::success('جميع الحسابات', $data);
}

    // ✔️ 2) إنشاء حساب موظف أو Admin
    public function store(Request $request)
    {
    try {
        $currentUser = $request->user();
        
        if (!$currentUser || $currentUser->role != 1) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتنفيذ هذه العملية',
                'status_code' => 403,
                'timestamp' => now()->toIso8601String()
            ], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255',       
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|integer|in:1,2',
            'id_agency' => 'nullable|exists:agencies,id'
        ]);

        $exists = Administrative::where('user_name', $data['user_name'])->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'اسم المستخدم مستخدم مسبقًا',
                'status_code' => 409,
                'timestamp' => now()->toIso8601String()
            ], 409);
        }

        $userData = [
            'name' => $data['name'],
            'user_name' => $data['user_name'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ];

        if (isset($data['id_agency']) && $data['id_agency'] !== null) {
            $userData['id_agency'] = $data['id_agency'];
        }

        $user = Administrative::create($userData);

        $roles = [
            1 => 'مدير نظام',
            2 => 'موظف'
        ];
        $roleText = $roles[$data['role']] ?? 'غير معروف';
        ////////////////////
  Audit::record(
    'create_admin_account',
    null,
    $user->toArray(),
    auth()->user()->id,
    'administrative',
    $user->id
);

Cache::forget('admin_accounts');

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المستخدم بنجاح',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'user_name' => $user->user_name,
                'role' => $roleText,
                'role_id' => $user->role,
                'agency_id' => $user->id_agency
            ],
            'status_code' => 201,
            'timestamp' => now()->toIso8601String(),
        ], 201);

    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطأ في البيانات المدخلة',
            'errors' => null, 
            'status_code' => 422,
            'timestamp' => now()->toIso8601String()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ في الخادم: ' . $e->getMessage(),
            'status_code' => 500,
            'timestamp' => now()->toIso8601String()
        ], 500);
    }
}
////
//     {
//         $admin = auth()->user();
//         if ($admin->role != 1) {
//             return response()->json(['message' => 'غير مصرح'], 403);
//         }

//         $validator = Validator::make($request->all(), [
//             'name' => 'required|string',
//             'user_name' => 'required|string|unique:administrative,user_name',
//             'password' => 'required|string|min:6|confirmed',//
//             'role' => 'required|in:1,2',  // 1 admin , 2 employee
//             'id_agency' => 'nullable|exists:agencies,id'
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['errors' => $validator->errors()], 422);
//         }

//         $user = Administrative::create([
//             'name' => $request->name,
//             'user_name' => $request->user_name,
//             'password' => Hash::make($request->password),
//             'role' => $request->role,
//             'id_agency' => $request->id_agency,
//             'status' => 1
//         ]);
// Audit::record(
//     'create_admin_account',
//     null,
//     $user->toArray(),
//     auth()->user()->id,
//     'administrative',
//     $user->id
// );

//     return ApiResponse::success('تم إنشاء الحساب بنجاح', $user);

        
//     }

    // ✔️ 3) تعديل حساب
    public function update(Request $request, $id)
{
    $admin = Administrative::find($id);

    if (!$admin) {
        return ApiResponse::error('الحساب غير موجود', 404);
    }

    $data = $request->only(['name','user_name','role','id_agency']);

    // إذا في كلمة مرور جديدة
    if ($request->has('password')) {
        $data['password'] = Hash::make($request->password);
    }

if ($request->user_name && $request->user_name !== $admin->user_name) {
    if (Administrative::where('user_name', $request->user_name)->exists()) {
        return ApiResponse::error('اسم المستخدم موجود مسبقاً', 409);
    }
}
$old = $admin->toArray();

    $admin->update($data);
Audit::record(
    'update_admin_account',
    $old,
    $admin->toArray(),
    auth()->user()->id,
    'administrative',
    $admin->id
);
//
Cache::forget('admin_accounts');
Cache::forget("admin_account_{$id}");


    return ApiResponse::success('تم تحديث الحساب', $admin);

}


  
public function destroy($id)
{
    $admin = auth()->user();
    if ($admin->role != 1) {
        return response()->json(['message' => 'غير مصرح'], 403);
    }

    $user = Administrative::find($id);
    if (!$user) {
        return response()->json(['message' => 'الحساب غير موجود'], 404);
    }

    // تسجيل البيانات القديمة قبل الحذف
    $oldData = $user->toArray();

    // حذف الحساب
    $user->delete();

    // تسجيل السجل في Audit Logs
    Audit::record(
        'delete_admin_account',
        $oldData,    //  old_value
        null,        //  new_value
        $admin->id,  //  acted_by
        'administrative',
        $id          // target_id
    );
Cache::forget('admin_accounts');
Cache::forget("admin_account_{$id}");

    return ApiResponse::success('تم حذف الحساب');
}

    // ✔️ 5) تفعيل / تعطيل حساب
    public function changeStatus(Request $request, $id)
    {
        $admin = auth()->user();
        if ($admin->role != 1) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $request->validate([
            'status' => 'required|boolean'
        ]);

        $user = Administrative::find($id);
        if (!$user) {
            return response()->json(['message' => 'الحساب غير موجود'], 404);
        }

        $user->status = $request->status;
        $user->save();
return ApiResponse::success('تم تحديث حالة الحساب', $user);

       
    }
}
