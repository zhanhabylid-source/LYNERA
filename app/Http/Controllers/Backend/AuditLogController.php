<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BackendAuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $action = trim((string) $request->string('action', ''));
        $targetType = trim((string) $request->string('target_type', ''));

        $logs = BackendAuditLog::query()
            ->with('actor')
            ->when($action !== '', fn ($query) => $query->where('action', 'like', '%'.$action.'%'))
            ->when($targetType !== '', fn ($query) => $query->where('target_type', 'like', '%'.$targetType.'%'))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('backend.audit-logs.index', [
            'logs' => $logs,
            'action' => $action,
            'targetType' => $targetType,
        ]);
    }
}

