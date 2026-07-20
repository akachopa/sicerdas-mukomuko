<?php

declare(strict_types=1);

namespace App\Core;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    /** @return array<string, mixed>|null */
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): int
    {
        return (int) ($_SESSION['user']['id'] ?? 0);
    }

    public static function role(): string
    {
        return $_SESSION['user']['role'] ?? '';
    }

    public static function isAdmin(): bool
    {
        return in_array(self::role(), ['super_admin', 'admin_bapperida'], true);
    }

    public static function attempt(string $email, string $password): bool
    {
        $user = Database::selectOne(
            "SELECT u.*, r.code AS role, r.name AS role_name
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.email = ? AND u.is_active = 1",
            [$email]
        );
        if ($user === null || !password_verify($password, $user['password'])) {
            return false;
        }
        session_regenerate_id(true);
        unset($user['password']);
        $_SESSION['user'] = $user;
        Database::update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
        Audit::log('login', 'users', (int) $user['id'], null, null);

        // Mitra/perusahaan boleh berbahasa Inggris; internal selalu Indonesia
        if ($user['role'] !== 'mitra') {
            $_SESSION['lang'] = 'id';
        }
        return true;
    }

    public static function logout(): void
    {
        if (self::check()) {
            Audit::log('logout', 'users', self::id(), null, null);
        }
        session_destroy();
    }
}
