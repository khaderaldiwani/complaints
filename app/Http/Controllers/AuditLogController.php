<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
{
    $admin = auth()->user();

    if ($admin->role != 1) {
        return ApiResponse::error('غير مصرح', 403);
    }

    $query = AuditLog::query();

    if ($request->has('action')) {
        $query->where('action', $request->action);
    }

    if ($request->has('model')) {
        $query->where('model', $request->model);
    }

    if ($request->has('admin_id')) {
        $query->where('admin_id', $request->admin_id);
    }

    if ($request->has('date_from') && $request->has('date_to')) {
        $query->whereBetween('created_at', [
            $request->date_from,
            $request->date_to
        ]);
    }

    return ApiResponse::success('سجلات العمليات', $query->orderBy('id','desc')->get());
}

}
