<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\DataTable;

class AuditController extends Controller
{
    public function index(): void
    {
        $this->render('audit/index', ['title' => __t('audit.title')]);
    }

    public function data(): void
    {
        DataTable::respond(
            "SELECT a.id, a.user_name, a.action, a.module, a.record_id, a.ip_address, a.created_at",
            "FROM audit_logs a",
            [null, 'a.created_at', 'a.user_name', 'a.action', 'a.module', 'a.ip_address'],
            [],
            fn(array $row, int $no): array => [
                $no,
                e(date('d/m/Y H:i:s', strtotime($row['created_at']))),
                e($row['user_name'] ?? '-'),
                '<span class="badge text-bg-light border">' . e($row['action']) . '</span>',
                e($row['module']) . ($row['record_id'] ? ' #' . $row['record_id'] : ''),
                e($row['ip_address'] ?? '-'),
            ],
            'ORDER BY a.id DESC'
        );
    }
}
