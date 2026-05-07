<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\AdminSettingsRepository;
use App\Repositories\AnswerRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\DashboardRepository;
use App\Repositories\FormRepository;
use App\Repositories\GoalRepository;
use App\Repositories\LocationRepository;
use App\Repositories\UserRepository;
use App\Services\AdminCatalogService;
use App\Services\AdminSettingsService;
use App\Repositories\MatchRepository;
use App\Services\FormBuilderService;
use App\Services\MatchService;
use App\Services\SystemHealthService;

class AdminController
{
    public function dashboard(): void
    {
        Auth::requireAdmin();
        View::render('admin/dashboard', ['stats' => (new DashboardRepository())->stats(), 'settings' => (new AdminSettingsRepository())->all()]);
    }
    public function formBuilder(): void
    {
        Auth::requirePermission('forms.manage');
        $repo = new FormRepository();
        $editQuestion = !empty($_GET['edit']) ? $repo->findQuestion((int)$_GET['edit']) : null;
        View::render('admin/form_builder', ['steps' => $repo->allSteps(), 'groups' => $repo->allGroups(), 'questions' => $repo->allQuestions(), 'types' => FormBuilderService::TYPES, 'editQuestion' => $editQuestion]);
    }
    public function saveStep(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage');
        $errors = (new AdminCatalogService())->validateTitle($_POST);
        if (!$errors) { $id = (new FormRepository())->saveStep($_POST); (new AuditLogRepository())->record((int)$admin['id'], 'saved', 'form_step', $id, ['title' => $_POST['title']]); \flash('success', 'Step saved.'); }
        \redirect('/admin/forms');
    }
    public function deleteStep(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage'); $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { (new FormRepository())->softDeleteStep($id); (new AuditLogRepository())->record((int)$admin['id'], 'soft_deleted', 'form_step', $id); \flash('success', 'Step disabled.'); }
        \redirect('/admin/forms');
    }
    public function saveGroup(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage');
        $errors = (new AdminCatalogService())->validateTitle($_POST);
        if (empty($_POST['form_step_id'])) { $errors['form_step_id'] = 'Step is required.'; }
        if (!$errors) { $id = (new FormRepository())->saveGroup($_POST); (new AuditLogRepository())->record((int)$admin['id'], 'saved', 'question_group', $id, ['title' => $_POST['title']]); \flash('success', 'Group saved.'); }
        \redirect('/admin/forms');
    }
    public function deleteGroup(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage'); $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { (new FormRepository())->softDeleteGroup($id); (new AuditLogRepository())->record((int)$admin['id'], 'soft_deleted', 'question_group', $id); \flash('success', 'Group disabled.'); }
        \redirect('/admin/forms');
    }
    public function saveQuestion(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage');
        [$ok, $errors] = (new FormBuilderService())->saveQuestion($_POST, (int)$admin['id']);
        if ($ok) { \flash('success', 'Question saved.'); \redirect('/admin/forms'); }
        $repo = new FormRepository();
        View::render('admin/form_builder', ['steps' => $repo->allSteps(), 'groups' => $repo->allGroups(), 'questions' => $repo->allQuestions(), 'types' => FormBuilderService::TYPES, 'errors' => $errors, 'editQuestion' => null]);
    }
    public function deleteQuestion(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage'); $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { (new FormRepository())->softDeleteQuestion($id); (new AuditLogRepository())->record((int)$admin['id'], 'soft_deleted', 'question', $id); \flash('success', 'Question disabled.'); }
        \redirect('/admin/forms');
    }
    public function catalogs(): void
    {
        Auth::requirePermission('forms.manage');
        $locations = new LocationRepository();
        View::render('admin/catalogs', ['goals' => (new GoalRepository())->all(), 'provinces' => $locations->allProvinces(), 'cities' => $locations->allCities()]);
    }
    public function saveGoal(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage');
        if (!(new AdminCatalogService())->validateTitle($_POST)) { $id = (new GoalRepository())->save($_POST); (new AuditLogRepository())->record((int)$admin['id'], 'saved', 'goal', $id, ['title' => $_POST['title']]); \flash('success', 'Goal saved.'); }
        \redirect('/admin/catalogs');
    }
    public function deleteGoal(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage'); $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { (new GoalRepository())->softDelete($id); (new AuditLogRepository())->record((int)$admin['id'], 'soft_deleted', 'goal', $id); \flash('success', 'Goal disabled.'); }
        \redirect('/admin/catalogs');
    }
    public function saveProvince(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage');
        if (!(new AdminCatalogService())->validateTitle($_POST, 'name')) { $id = (new LocationRepository())->saveProvince($_POST); (new AuditLogRepository())->record((int)$admin['id'], 'saved', 'province', $id, ['name' => $_POST['name']]); \flash('success', 'Province saved.'); }
        \redirect('/admin/catalogs');
    }
    public function deleteProvince(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage'); $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { (new LocationRepository())->softDeleteProvince($id); (new AuditLogRepository())->record((int)$admin['id'], 'soft_deleted', 'province', $id); \flash('success', 'Province disabled.'); }
        \redirect('/admin/catalogs');
    }
    public function saveCity(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage');
        if (!(new AdminCatalogService())->validateCity($_POST)) { $id = (new LocationRepository())->saveCity($_POST); (new AuditLogRepository())->record((int)$admin['id'], 'saved', 'city', $id, ['name' => $_POST['name']]); \flash('success', 'City saved.'); }
        \redirect('/admin/catalogs');
    }
    public function deleteCity(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage'); $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { (new LocationRepository())->softDeleteCity($id); (new AuditLogRepository())->record((int)$admin['id'], 'soft_deleted', 'city', $id); \flash('success', 'City disabled.'); }
        \redirect('/admin/catalogs');
    }
    public function users(): void
    {
        Auth::requirePermission('users.view');
        View::render('admin/users', ['users' => (new UserRepository())->allMembers()]);
    }
    public function userDetail(): void
    {
        Auth::requirePermission('users.view');
        $id = (int)($_GET['id'] ?? 0);
        $user = (new UserRepository())->find($id);
        if (!$user) { http_response_code(404); exit('User not found'); }
        View::render('admin/user_detail', ['profile' => $user, 'goals' => (new GoalRepository())->forUser($id), 'answers' => (new FormRepository())->groupedQuestionsWithAnswersForUser($id), 'progress' => (new AnswerRepository())->progressForUser($id)]);
    }


    public function matches(): void
    {
        Auth::requireAdmin();
        $repo = new MatchRepository();
        $selectedMatchId = (int)($_GET['match_id'] ?? 0);
        View::render('admin/matches', [
            'matches' => $repo->allMatches(),
            'scores' => $selectedMatchId ? $repo->scoresForMatch($selectedMatchId) : [],
            'explanation' => $selectedMatchId ? $repo->explanationForMatch($selectedMatchId) : null,
            'selectedMatchId' => $selectedMatchId,
            'users' => (new UserRepository())->allMembers(),
        ]);
    }
    public function runMatching(): void
    {
        \verify_csrf();
        Auth::requireAdmin();
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId > 0) {
            $count = (new MatchService())->runForUser($userId, !empty($_POST['recalculate']), 25);
            \flash('success', 'Generated or updated ' . $count . ' match recommendations.');
        }
        \redirect('/admin/matches');
    }
    public function settings(): void
    {
        Auth::requireAdmin();
        View::render('admin/settings', ['settings' => (new AdminSettingsRepository())->all(), 'errors' => []]);
    }
    public function saveSettings(): void
    {
        \verify_csrf();
        $admin = Auth::requireAdmin();
        [$ok, $errors] = (new AdminSettingsService())->save($_POST, (int)$admin['id']);
        if ($ok) { (new AuditLogRepository())->record((int)$admin['id'], 'saved', 'admin_settings', null); \flash('success', 'Settings saved.'); \redirect('/admin/settings'); }
        View::render('admin/settings', ['settings' => $_POST, 'errors' => $errors]);
    }
    public function health(): void
    {
        Auth::requireAdmin();
        View::render('admin/health', ['report' => (new SystemHealthService())->report()]);
    }
}
