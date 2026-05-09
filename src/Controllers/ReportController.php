<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Repositories\ReportRepository;

class ReportController
{
    public function match(): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $created = (new ReportRepository())->createForMatch((int)($_POST['match_id'] ?? 0), (int)$user['id'], (string)($_POST['report_type'] ?? 'match'), (string)($_POST['report_reason'] ?? 'other'), (string)($_POST['description'] ?? ''));
        \flash($created ? 'success' : 'error', $created ? 'گزارش ثبت شد. از اینکه به امن‌ماندن فضا کمک می‌کنید سپاسگزاریم.' : 'گزارش ثبت نشد؛ لطفاً دوباره تلاش کنید.');
        \redirect('/matches');
    }

    public function chat(): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $chatId = (int)($_POST['chat_id'] ?? 0);
        $created = (new ReportRepository())->createForChat($chatId, (int)$user['id'], (string)($_POST['report_type'] ?? 'chat'), (string)($_POST['report_reason'] ?? 'other'), (string)($_POST['description'] ?? ''));
        \flash($created ? 'success' : 'error', $created ? 'گزارش گفت‌وگو ثبت شد.' : 'گزارش گفت‌وگو ثبت نشد.');
        \redirect('/chats/' . $chatId);
    }

    public function message(): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $chatId = (int)($_POST['chat_id'] ?? 0);
        $created = (new ReportRepository())->createForMessage((int)($_POST['message_id'] ?? 0), (int)$user['id'], (string)($_POST['report_type'] ?? 'message'), (string)($_POST['report_reason'] ?? 'other'), (string)($_POST['description'] ?? ''));
        \flash($created ? 'success' : 'error', $created ? 'گزارش پیام برای بررسی مدیران ثبت شد.' : 'گزارش پیام ثبت نشد.');
        \redirect('/chats/' . $chatId);
    }
}
