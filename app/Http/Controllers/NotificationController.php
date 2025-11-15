<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function getNotifications(Request $request)
    {
        $user = $request->user();

        $notifications = DB::table('notifications')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'قائمة الإشعارات',
            'data' => $notifications,
            'status_code' => 200,
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }

    public function markAsRead(Request $request, $id)
{
    DB::table('notifications')
        ->where('id', $id)
        ->where('user_id', $request->user()->id)
        ->update(['is_read' => true]);

    return response()->json([
        'success' => true,
        'message' => 'تم تحديد الإشعار كمقروء.',
        'data' => null,
        'status_code' => 200,
        'timestamp' => now()->toIso8601String()
    ], 200);
}

}
