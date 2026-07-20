<?php

declare(strict_types=1);

namespace App\Core;

class Audit
{
    public static function log(
        string $action,
        string $module,
        ?int $recordId = null,
        ?array $before = null,
        ?array $after = null
    ): void {
        try {
            Database::insert('audit_logs', [
                'user_id' => Auth::id() ?: null,
                'user_name' => Auth::user()['full_name'] ?? 'system',
                'action' => $action,
                'module' => $module,
                'record_id' => $recordId,
                'data_before' => $before ? json_encode($before, JSON_UNESCAPED_UNICODE) : null,
                'data_after' => $after ? json_encode($after, JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable) {
            // Audit tidak boleh menggagalkan operasi utama
        }
    }
}
