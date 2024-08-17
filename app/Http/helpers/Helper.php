<?php

namespace App\Http\helpers;


use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class Helper
{
    public static function recordAuditLog($action, $tableName, $recordId, $oldValues = null, $newValues = null)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ]);
    }

    public static function generateConfirmationNumber()
    {
        return strtoupper(Str::random(10)); // Generates a random string of 10 characters
    }
}
