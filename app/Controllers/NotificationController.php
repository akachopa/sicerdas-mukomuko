<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class NotificationController extends Controller
{
    public function index(): void
    {
        $this->render('notifications/index', [
            'title' => __t('notif.title'),
            'notifications' => Database::select(
                "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 100",
                [Auth::id()]
            ),
        ]);
    }

    public function markAllRead(): void
    {
        Database::execute("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [Auth::id()]);
        redirect('/notifikasi');
    }

    public function open(string $id): void
    {
        $notif = Database::selectOne(
            "SELECT * FROM notifications WHERE id = ? AND user_id = ?",
            [(int) $id, Auth::id()]
        );
        if ($notif === null) {
            redirect('/notifikasi');
        }
        Database::update('notifications', ['is_read' => 1], 'id = ?', [(int) $id]);
        redirect($notif['url'] !== '' && str_starts_with($notif['url'], '/') ? $notif['url'] : '/notifikasi');
    }
}
