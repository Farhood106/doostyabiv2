<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\SupportRepository;

class SupportController
{
    public function index(): void
    {
        $user = Auth::requireLogin();
        $repo = new SupportRepository();
        View::render('support/index', ['conversations' => $repo->listForUser((int)$user['id']), 'categories' => $repo->categories()]);
    }

    public function create(): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $repo = new SupportRepository();
        $id = $repo->createConversation((int)$user['id'], (string)($_POST['category'] ?? ''), trim((string)($_POST['subject'] ?? '')) ?: 'درخواست پشتیبانی', trim((string)($_POST['message'] ?? '')) ?: '');
        \flash('success', 'درخواست پشتیبانی شما ثبت شد.');
        \redirect('/support/' . $id);
    }

    public function show(int $id): void
    {
        $user = Auth::requireLogin();
        $repo = new SupportRepository();
        $admin = (($user['role_name'] ?? '') === 'admin');
        if (!$repo->canAccess($id, (int)$user['id'], $admin)) { http_response_code(403); exit('Forbidden'); }
        View::render('support/show', ['conversation' => $repo->conversation($id), 'messages' => $repo->messages($id, $admin), 'quickReplies' => $admin ? $repo->quickReplies() : []]);
    }

    public function send(int $id): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $repo = new SupportRepository();
        $admin = (($user['role_name'] ?? '') === 'admin');
        if (!$repo->canAccess($id, (int)$user['id'], $admin)) { http_response_code(403); exit('Forbidden'); }
        $internal = $admin && !empty($_POST['internal_note']);
        $repo->addMessage($id, (int)$user['id'], trim((string)($_POST['body'] ?? '')), $internal);
        \redirect('/support/' . $id);
    }
}
