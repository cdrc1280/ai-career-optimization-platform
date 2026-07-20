<?php
namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogger {
    public function log(string $event, ?int $userId = null, ?Model $model = null, array $oldValues = [], array $newValues = [], string $ipAddress = '', string $userAgent = ''): AuditLog {
        return AuditLog::create([
            'user_id' => $userId,
            'event' => $event,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id' => $model?->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => $ipAddress ?: null,
            'user_agent' => $userAgent ?: null,
        ]);
    }

    public static function record(string $event, ?int $userId = null, ?Model $model = null): void {
        try { 
            (new self)->log($event, $userId, $model); 
        } catch (\Throwable) { 
            /* non-fatal */ 
        }
    }
}
