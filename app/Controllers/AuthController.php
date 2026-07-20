<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Lang;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }
        $this->render('auth/login', ['title' => __t('auth.login')], 'layouts/guest');
    }

    public function login(): void
    {
        $email = (string) $this->input('email', '');
        $password = (string) $this->input('password', '');

        if ($email === '' || $password === '' || !Auth::attempt($email, $password)) {
            flash('danger', __t('auth.failed'));
            redirect('/login');
        }
        redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('/login');
    }

    public function setLang(string $lang): void
    {
        Lang::set($lang);
        $back = $_SERVER['HTTP_REFERER'] ?? '/';
        $host = parse_url($back, PHP_URL_HOST);
        if ($host !== null && $host !== ($_SERVER['HTTP_HOST'] ?? '')) {
            $back = '/';
        }
        redirect($back);
    }
}
