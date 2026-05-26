<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\ChatRepository;
use App\Repositories\RevealRepository;
use App\Services\VisibilityGuardService;
use App\Services\MatchIntelligenceService;

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
        if (!$chat || !(new VisibilityGuardService())->canUsersSeeEachOther((int)$chat['user_one_id'], (int)$chat['user_two_id'])) {
            http_response_code(403); exit('Forbidden');
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
        $repo = new ChatRepository();
        $chat = $repo->findForParticipant($id, (int)$user['id']);
        if (!$chat || !(new VisibilityGuardService())->canUsersSeeEachOther((int)$chat['user_one_id'], (int)$chat['user_two_id'])) { \flash('error','این گفتگو در دسترس نیست.'); \redirect('/chats'); }
        $sent = $repo->sendMessage($id, (int)$user['id'], (string)($_POST['body'] ?? ''));
        if ($sent) { (new MatchIntelligenceService())->calculateForUser((int)$user['id']); }
        \flash($sent ? 'success' : 'error', $sent ? 'پیام ارسال شد.' : 'پیام ارسال نشد.');
        \redirect('/chats/' . $id);
    }

    public function requestReveal(int $id): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $repo = new ChatRepository();
        $chat = $repo->findForParticipant($id, (int)$user['id']);
        if (!$chat || !(new VisibilityGuardService())->canUsersSeeEachOther((int)$chat['user_one_id'], (int)$chat['user_two_id'])) { \flash('error','این درخواست در دسترس نیست.'); \redirect('/chats/' . $id); }
        $created = (new RevealRepository())->createRequest($id, (int)$user['id'], (int)($_POST['reveal_type_id'] ?? 0), (string)($_POST['request_message'] ?? ''));
        if ($created) { (new MatchIntelligenceService())->calculateForUser((int)$user['id']); }
        \flash($created ? 'success' : 'error', $created ? 'درخواست نمایش ارسال شد.' : 'درخواست نمایش ثبت نشد.');
        \redirect('/chats/' . $id);
    }

    public function respondReveal(): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $chatId = (int)($_POST['chat_id'] ?? 0);
        $responded = (new RevealRepository())->respond((int)($_POST['request_id'] ?? 0), (int)$user['id'], (string)($_POST['status'] ?? ''), (string)($_POST['response_note'] ?? ''));
        if ($responded) { (new MatchIntelligenceService())->calculateForUser((int)$user['id']); }
        \flash($responded ? 'success' : 'error', $responded ? 'درخواست نمایش به‌روزرسانی شد.' : 'درخواست نمایش به‌روزرسانی نشد.');
        \redirect('/chats/' . $chatId);
    }
}
