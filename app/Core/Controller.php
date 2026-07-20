<?php

declare(strict_types=1);

namespace App\Core;

class Controller
{
    public function render(string $view, array $data = [], string $layout = 'layouts/app'): void
    {
        extract($data);
        ob_start();
        require BASE_PATH . '/app/Views/' . $view . '.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/Views/' . $layout . '.php';
    }

    public function renderPartial(string $view, array $data = []): void
    {
        extract($data);
        require BASE_PATH . '/app/Views/' . $view . '.php';
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    protected function keepOld(): void
    {
        $_SESSION['_old'] = $_POST;
    }

    protected function clearOld(): void
    {
        unset($_SESSION['_old']);
    }
}
