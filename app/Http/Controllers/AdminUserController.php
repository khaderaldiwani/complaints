<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    // عرض كل المواطنين (مع البحث)
    public function index(Request $request)
    {
        $admin = auth()->user();
        if ($admin->role != 1) {
            return ApiResponse::error('غير مصرح', 403);
        }

        $query = User::query();

        // بحث بالاسم أو الإيميل
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%$search%")
                  ->orWhere('email', 'LIKE', "%$search%");
        }

        // المستخدمين المقفلين فقط
        if ($request->has('locked') && $request->locked == 1) {
            $query->whereNotNull('locked_until')
                  ->where('locked_until', '>', now());
        }

        return ApiResponse::success('جميع المواطنين', $query->get());
    }


    // تعطيل مستخدم
    public function disable($id)
    {
        $admin = auth()->user();
        if ($admin->role != 1) {
            return ApiResponse::error('غير مصرح', 403);
        }

        $user = User::find($id);

        if (!$user) {
            return ApiResponse::error('المستخدم غير موجود', 404);
        }

        $user->status = 0;
        $user->save();

        return ApiResponse::success('تم تعطيل المستخدم', $user);
    }

    // تفعيل مستخدم
    public function enable($id)
    {
        $admin = auth()->user();
        if ($admin->role != 1) {
            return ApiResponse::error('غير مصرح', 403);
        }

        $user = User::find($id);

        if (!$user) {
            return ApiResponse::error('المستخدم غير موجود', 404);
        }

        $user->status = 1;
        $user->locked_until = null;
        $user->failed_attempts = 0;
        $user->save();

        return ApiResponse::success('تم تفعيل المستخدم', $user);
    }


    // حذف مستخدم
    public function delete($id)
    {
        $admin = auth()->user();
        if ($admin->role != 1) {
            return ApiResponse::error('غير مصرح', 403);
        }

        $user = User::find($id);

        if (!$user) {
            return ApiResponse::error('المستخدم غير موجود', 404);
        }

        $user->delete();

        return ApiResponse::success('تم حذف المستخدم', null);
    }
}
