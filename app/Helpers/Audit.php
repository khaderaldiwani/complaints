<?php

namespace App\Helpers;

use App\Models\AuditLog;

class Audit
{
    public static function record($action, $oldValue, $newValue, $adminId, $model, $modelId)
    {
        AuditLog::create([
            'admin_id'  => $adminId,
            'action'    => $action,
            'model'     => $model,
            'model_id'  => $modelId,
            'old_value' => $oldValue ? json_encode($oldValue) : null,
            'new_value' => $newValue ? json_encode($newValue) : null,
            'ip'        => request()->ip(),
        ]);
    }
}
