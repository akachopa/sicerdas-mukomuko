<?php

declare(strict_types=1);

namespace App\Core;

class Notification
{
    public static function send(int $userId, string $title, string $message, string $url = '#'): void
    {
        Database::insert('notifications', [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function sendToRole(string $roleCode, string $title, string $message, string $url = '#'): void
    {
        $users = Database::select(
            "SELECT u.id FROM users u JOIN roles r ON r.id = u.role_id WHERE r.code = ? AND u.is_active = 1",
            [$roleCode]
        );
        foreach ($users as $u) {
            self::send((int) $u['id'], $title, $message, $url);
        }
    }

    /** @return array<int, array<string, mixed>> */
    public static function unreadFor(int $userId, int $limit = 10): array
    {
        return Database::select(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY is_read ASC, created_at DESC LIMIT $limit",
            [$userId]
        );
    }

    public static function unreadCount(int $userId): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0", [$userId]);
    }
}
