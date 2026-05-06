<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;

class HomeController
{
    public function index(): void
    {
        $user = Auth::user();
        if (!$user) { View::render('home'); return; }
        if ($user['role_name'] === 'admin') { \redirect('/admin'); }
        \redirect('/onboarding');
    }
}
