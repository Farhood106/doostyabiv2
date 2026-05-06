<?php
namespace App\Controllers;

use App\Core\View;
use App\Services\AuthService;

class AuthController
{
    public function showLogin(): void { View::render('auth/login'); }
    public function login(): void
    {
        \verify_csrf();
        if ((new AuthService())->login($_POST['email'] ?? '', $_POST['password'] ?? '')) { \redirect('/'); }
        \flash('error', 'Invalid credentials.');
        \redirect('/login');
    }
    public function showRegister(): void { View::render('auth/register'); }
    public function register(): void
    {
        \verify_csrf();
        [$ok, $errors] = (new AuthService())->register($_POST);
        if ($ok) { \redirect('/onboarding'); }
        View::render('auth/register', ['errors' => $errors, 'old' => $_POST]);
    }
    public function logout(): void
    {
        session_destroy();
        \redirect('/login');
    }
}
