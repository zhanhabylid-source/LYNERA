<?php

namespace App\Services;

use App\Models\BackendAuditLog;
use Illuminate\Http\Request;

class BackendAuditLogService
{
    public function log(
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        ?string $targetLabel = null,
        array $meta = [],
        ?Request $request = null
    ): BackendAuditLog {
        $request ??= request();

        return BackendAuditLog::query()->create([
            'actor_id' => auth()->id(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'target_label' => $targetLabel,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'meta' => empty($meta) ? null : $meta,
        ]);
    }
}

