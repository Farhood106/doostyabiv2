<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\AnswerRepository;
use App\Repositories\FormRepository;
use App\Repositories\GoalRepository;
use App\Repositories\UserRepository;
use App\Services\FormBuilderService;

class AdminController
{
    public function dashboard(): void
    {
        Auth::requireAdmin();
        View::render('admin/dashboard', ['questions' => (new FormRepository())->allQuestions(), 'users' => (new UserRepository())->allMembers()]);
    }
    public function formBuilder(): void
    {
        Auth::requireAdmin();
        $repo = new FormRepository();
        $editQuestion = !empty($_GET['edit']) ? $repo->findQuestion((int)$_GET['edit']) : null;
        View::render('admin/form_builder', ['steps' => $repo->allSteps(), 'groups' => $repo->allGroups(), 'questions' => $repo->allQuestions(), 'types' => FormBuilderService::TYPES, 'editQuestion' => $editQuestion]);
    }
    public function saveStep(): void
    {
        \verify_csrf(); $admin = Auth::requireAdmin();
        if (trim($_POST['title'] ?? '') !== '') {
            (new FormRepository())->saveStep($_POST);
            (new \App\Repositories\AuditLogRepository())->record((int)$admin['id'], 'created', 'form_step', null, ['title' => $_POST['title']]);
        }
        \flash('success', 'Step saved.'); \redirect('/admin/forms');
    }
    public function saveGroup(): void
    {
        \verify_csrf(); $admin = Auth::requireAdmin();
        if (trim($_POST['title'] ?? '') !== '' && !empty($_POST['form_step_id'])) {
            (new FormRepository())->saveGroup($_POST);
            (new \App\Repositories\AuditLogRepository())->record((int)$admin['id'], 'created', 'question_group', null, ['title' => $_POST['title'], 'form_step_id' => $_POST['form_step_id']]);
        }
        \flash('success', 'Group saved.'); \redirect('/admin/forms');
    }
    public function saveQuestion(): void
    {
        \verify_csrf(); $admin = Auth::requireAdmin();
        [$ok, $errors] = (new FormBuilderService())->saveQuestion($_POST, (int)$admin['id']);
        if ($ok) { \flash('success', 'Question saved.'); \redirect('/admin/forms'); }
        $repo = new FormRepository();
        View::render('admin/form_builder', ['steps' => $repo->allSteps(), 'groups' => $repo->allGroups(), 'questions' => $repo->allQuestions(), 'types' => FormBuilderService::TYPES, 'errors' => $errors]);
    }
    public function deleteQuestion(): void
    {
        \verify_csrf();
        $admin = Auth::requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            (new FormRepository())->deleteQuestion($id);
            (new \App\Repositories\AuditLogRepository())->record((int)$admin['id'], 'deleted', 'question', $id);
            \flash('success', 'Question deleted.');
        }
        \redirect('/admin/forms');
    }

    public function users(): void
    {
        Auth::requireAdmin();
        View::render('admin/users', ['users' => (new UserRepository())->allMembers()]);
    }
    public function userDetail(): void
    {
        Auth::requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $user = (new UserRepository())->find($id);
        if (!$user) { http_response_code(404); exit('User not found'); }
        View::render('admin/user_detail', ['profile' => $user, 'goals' => (new GoalRepository())->forUser($id), 'answers' => (new AnswerRepository())->readableForUser($id)]);
    }
}
