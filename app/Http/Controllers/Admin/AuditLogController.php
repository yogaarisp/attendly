<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->input('user_id');
        $module = $request->input('module');
        $action = $request->input('action');

        $query = AuditLog::with('user');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($module) {
            $query->where('module', $module);
        }

        if ($action) {
            $query->where('action', $action);
        }

        $logs = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $users = User::orderBy('name')->get();
        $modules = AuditLog::select('module')->distinct()->pluck('module');
        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('admin.audit_logs.index', compact('logs', 'users', 'modules', 'actions', 'userId', 'module', 'action'));
    }
}
