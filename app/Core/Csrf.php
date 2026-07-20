<?php

declare(strict_types=1);

namespace App\Core;

class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::token() . '">';
    }

    public static function verify(): void
    {
        $token = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!hash_equals($_SESSION['_csrf'] ?? '', (string) $token)) {
            http_response_code(419);
            exit('CSRF token mismatch.');
        }
    }
}
