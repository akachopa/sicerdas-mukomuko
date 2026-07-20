<?php

declare(strict_types=1);

namespace App\Core;

class Lang
{
    private static ?array $lines = null;
    private static ?string $current = null;

    public static function current(): string
    {
        if (self::$current === null) {
            self::$current = $_SESSION['lang'] ?? 'id';
            if (!in_array(self::$current, ['id', 'en'], true)) {
                self::$current = 'id';
            }
        }
        return self::$current;
    }

    public static function set(string $lang): void
    {
        if (in_array($lang, ['id', 'en'], true)) {
            $_SESSION['lang'] = $lang;
            self::$current = $lang;
            self::$lines = null;
        }
    }

    public static function get(string $key, array $replace = []): string
    {
        if (self::$lines === null) {
            $lang = self::current();
            $file = BASE_PATH . "/lang/{$lang}.php";
            self::$lines = is_file($file) ? require $file : [];
            if ($lang !== 'id') {
                $fallback = require BASE_PATH . '/lang/id.php';
                self::$lines += $fallback;
            }
        }
        $line = self::$lines[$key] ?? $key;
        foreach ($replace as $k => $v) {
            $line = str_replace(':' . $k, (string) $v, $line);
        }
        return $line;
    }
}
