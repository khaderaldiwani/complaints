<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class NotificationHelper
{
    public static function send($user_id, $title, $message)
    {
        DB::table('notifications')->insert([
            'user_id' => $user_id,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
            'created_at' => now(),
        ]);
    }
    public static function name($status)
    {
        return match ($status) {
            1 => 'جديدة',
            2 => 'قيد المعالجة',
            3 => 'منجزة',
            4 => 'مرفوضة',
            default => 'غير معروفة'
        };
    }
}
