<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Administrative;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminManageAccountsController extends Controller
{
    // ✔️ 1) عرض جميع الموظفين و الإداريين
    public function index(Request $request)
    {
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

        $admin = auth()->user();
        if ($admin->role != 1) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }
    return ApiResponse::success('جميع الحسابات', $query->get(),);

        
    }

    // ✔️ 2) إنشاء حساب موظف أو Admin
    public function store(Request $request)
    {
        $admin = auth()->user();
        if ($admin->role != 1) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'user_name' => 'required|string|unique:administrative,user_name',
            'password' => 'required|string|min:6|confirmed',//
            'role' => 'required|in:1,2',  // 1 admin , 2 employee
            'id_agency' => 'nullable|exists:agencies,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Administrative::create([
            'name' => $request->name,
            'user_name' => $request->user_name,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'id_agency' => $request->id_agency,
            'status' => 1
        ]);
    return ApiResponse::success('تم إنشاء الحساب بنجاح', $user);

        
    }

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

    $admin->update($data);

    return ApiResponse::success('تم تحديث الحساب', $admin);

}


    // ✔️ 4) حذف حساب
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

        $user->delete();
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
