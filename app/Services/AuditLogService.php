<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log an action to the audit_logs table.
     */
    public static function log(string $action, string $module, ?int $recordId = null, ?array $metadata = null, ?int $userId = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => strtoupper($action),
            'module' => strtolower($module),
            'record_id' => $recordId,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => substr(Request::userAgent() ?? '', 0, 500),
            'metadata' => $metadata,
        ]);
    }
}
