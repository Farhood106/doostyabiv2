<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\ChatRepository;
use App\Repositories\RevealRepository;
use App\Services\MatchIntelligenceService;
use App\Repositories\ReportRepository;

class ChatController
{
    public function index(): void
    {
        $user = Auth::requireLogin();
        View::render('chats/index', ['chats' => (new ChatRepository())->listForUser((int)$user['id'])]);
    }

    public function show(int $id): void
    {
        $user = Auth::requireLogin();
        $repo = new ChatRepository();
        $chat = $repo->findForParticipant($id, (int)$user['id']);
        if (!$chat) {
            http_response_code(404);
            exit('Chat not found');
        }
        $revealRepo = new RevealRepository();
        View::render('chats/show', [
            'chat' => $chat,
            'messages' => $repo->messagesForUser($id, (int)$user['id']),
            'canSend' => $repo->canSend($id, (int)$user['id']),
            'revealTypes' => $revealRepo->activeTypes(),
            'revealRequests' => $revealRepo->requestsForChat($id, (int)$user['id']),
            'revealedSnapshots' => $revealRepo->approvedSnapshotsForChat($id, (int)$user['id']),
        ]);
    }

    public function send(int $id): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $sent = (new ChatRepository())->sendMessage($id, (int)$user['id'], (string)($_POST['body'] ?? ''));
        if ($sent) { (new MatchIntelligenceService())->calculateForUser((int)$user['id']); }
        \flash($sent ? 'success' : 'error', $sent ? 'پیام ارسال شد.' : 'پیام ارسال نشد. ممکن است گفت‌وگو بسته یا مسدود باشد، یا متن پیام خالی/بیش از حد طولانی باشد.');
        \redirect('/chats/' . $id);
    }


    public function requestReveal(int $id): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $created = (new RevealRepository())->createRequest($id, (int)$user['id'], (int)($_POST['reveal_type_id'] ?? 0), (string)($_POST['request_message'] ?? ''));
        if ($created) { (new MatchIntelligenceService())->calculateForUser((int)$user['id']); }
        \flash($created ? 'success' : 'error', $created ? 'درخواست نمایش ارسال شد.' : 'درخواست نمایش ثبت نشد. ممکن است گفت‌وگو بسته یا مسدود باشد، دوطرفه نباشد یا درخواست مشابهی در انتظار پاسخ باشد.');
        \redirect('/chats/' . $id);
    }

    public function respondReveal(): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $chatId = (int)($_POST['chat_id'] ?? 0);
        $status = (string)($_POST['status'] ?? '');
        $responded = (new RevealRepository())->respond((int)($_POST['request_id'] ?? 0), (int)$user['id'], $status, (string)($_POST['response_note'] ?? ''));
        if ($responded) { (new MatchIntelligenceService())->calculateForUser((int)$user['id']); }
        \flash($responded ? 'success' : 'error', $responded ? 'درخواست نمایش به‌روزرسانی شد.' : 'درخواست نمایش به‌روزرسانی نشد. فقط دریافت‌کننده درخواست می‌تواند درخواست‌های در انتظار را تأیید یا رد کند.');
        \redirect('/chats/' . $chatId);
    }

    public function flagMessage(): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $chatId = (int)($_POST['chat_id'] ?? 0);
        $messageId = (int)($_POST['message_id'] ?? 0);
        $flagged = (new ChatRepository())->flagMessage($messageId, (int)$user['id'], (string)($_POST['reason'] ?? ''));
        if ($flagged) { (new MatchIntelligenceService())->calculateForUser((int)$user['id']); }
        \flash($flagged ? 'success' : 'error', $flagged ? 'پیام برای بررسی مدیران علامت‌گذاری شد.' : 'پیام گزارش نشد.');
        \redirect('/chats/' . $chatId);
    }
}
