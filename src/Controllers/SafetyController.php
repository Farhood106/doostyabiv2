<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\SafetyRepository;

class SafetyController
{
    public function index(): void
    {
        $user = Auth::requireLogin();
        View::render('safety/index', ['blocks' => (new SafetyRepository())->activeBlocksForUser((int)$user['id'])]);
    }

    public function unblock(): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $ok = (new SafetyRepository())->unblock((int)($_POST['block_id'] ?? 0), (int)$user['id']);
        \flash($ok ? 'success' : 'error', $ok ? 'رفع مسدودی انجام شد.' : 'فعلاً امکان به‌روزرسانی مسدودی نیست.');
        \redirect('/safety');
    }
}
