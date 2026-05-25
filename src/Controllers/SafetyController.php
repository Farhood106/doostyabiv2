<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\PrivacyShieldRepository;
use App\Repositories\SafetyRepository;

class SafetyController
{
    public function index(): void
    {
        $user = Auth::requireLogin();
        View::render('safety/index', ['blocks' => (new SafetyRepository())->activeBlocksForUser((int)$user['id'])]);
    }



    public function privacyShield(): void
    {
        $user = Auth::requireLogin();
        View::render('safety/privacy_shield', ['items' => (new PrivacyShieldRepository())->listForUser((int)$user['id'])]);
    }

    public function addPrivacyShield(): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $ok = (new PrivacyShieldRepository())->add((int)$user['id'], (string)($_POST['type'] ?? ''), (string)($_POST['value'] ?? ''), trim((string)($_POST['note'] ?? '')) ?: null);
        \flash($ok ? 'success' : 'error', $ok ? 'آیتم سپر حریم خصوصی ذخیره شد.' : 'ثبت آیتم ممکن نبود.');
        \redirect('/safety/privacy-shield');
    }

    public function deletePrivacyShield(): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $ok = (new PrivacyShieldRepository())->remove((int)$user['id'], (int)($_POST['id'] ?? 0));
        \flash($ok ? 'success' : 'error', $ok ? 'آیتم حذف شد.' : 'حذف آیتم انجام نشد.');
        \redirect('/safety/privacy-shield');
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
