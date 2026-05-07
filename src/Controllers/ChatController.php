<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\ChatRepository;

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
        View::render('chats/show', [
            'chat' => $chat,
            'messages' => $repo->messagesForUser($id, (int)$user['id']),
            'canSend' => $repo->canSend($id, (int)$user['id']),
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
