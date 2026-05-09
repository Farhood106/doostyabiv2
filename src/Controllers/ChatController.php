<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\ChatRepository;
use App\Repositories\RevealRepository;

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
        \flash($sent ? 'success' : 'error', $sent ? 'Message sent.' : 'Message could not be sent. Chat may be closed, blocked, or the message may be empty/too long.');
        \redirect('/chats/' . $id);
    }


    public function requestReveal(int $id): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $created = (new RevealRepository())->createRequest($id, (int)$user['id'], (int)($_POST['reveal_type_id'] ?? 0), (string)($_POST['request_message'] ?? ''));
        \flash($created ? 'success' : 'error', $created ? 'Reveal request sent.' : 'Reveal request could not be created. The chat may be closed, blocked, not mutual, or already pending.');
        \redirect('/chats/' . $id);
    }

    public function respondReveal(): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $chatId = (int)($_POST['chat_id'] ?? 0);
        $status = (string)($_POST['status'] ?? '');
        $responded = (new RevealRepository())->respond((int)($_POST['request_id'] ?? 0), (int)$user['id'], $status, (string)($_POST['response_note'] ?? ''));
        \flash($responded ? 'success' : 'error', $responded ? 'Reveal request updated.' : 'Reveal request could not be updated. Only the target can approve or reject pending requests.');
        \redirect('/chats/' . $chatId);
    }

    public function flagMessage(): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $chatId = (int)($_POST['chat_id'] ?? 0);
        $messageId = (int)($_POST['message_id'] ?? 0);
        $flagged = (new ChatRepository())->flagMessage($messageId, (int)$user['id'], (string)($_POST['reason'] ?? ''));
        \flash($flagged ? 'success' : 'error', $flagged ? 'Message flagged for admin review.' : 'Message could not be flagged.');
        \redirect('/chats/' . $chatId);
    }
}
