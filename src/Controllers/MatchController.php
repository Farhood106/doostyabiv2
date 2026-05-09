<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\MatchRepository;

class MatchController
{
    public function index(): void
    {
        $user = Auth::requireLogin();
        View::render('matches/index', ['cards' => (new MatchRepository())->cardsForUser((int)$user['id'])]);
    }
    public function action(): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $action = $_POST['action'] ?? '';
        (new MatchRepository())->recordAction((int)($_POST['match_id'] ?? 0), (int)$user['id'], $action);
        \flash('success', 'انتخاب شما برای این معرفی ذخیره شد.');
        \redirect('/matches');
    }
}
