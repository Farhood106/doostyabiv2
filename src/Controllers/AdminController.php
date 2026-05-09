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
use App\Repositories\RevealRepository;
use App\Repositories\ReportRepository;
use App\Repositories\ChatRepository;
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
        if (!$errors) { $id = (new FormRepository())->saveStep($_POST); (new AuditLogRepository())->record((int)$admin['id'], 'saved', 'form_step', $id, ['title' => $_POST['title']]); \flash('success', 'مرحله ذخیره شد.'); }
        \redirect('/admin/forms');
    }
    public function deleteStep(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage'); $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { (new FormRepository())->softDeleteStep($id); (new AuditLogRepository())->record((int)$admin['id'], 'soft_deleted', 'form_step', $id); \flash('success', 'مرحله غیرفعال شد.'); }
        \redirect('/admin/forms');
    }
    public function saveGroup(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage');
        $errors = (new AdminCatalogService())->validateTitle($_POST);
        if (empty($_POST['form_step_id'])) { $errors['form_step_id'] = 'انتخاب مرحله ضروری است.'; }
        if (!$errors) { $id = (new FormRepository())->saveGroup($_POST); (new AuditLogRepository())->record((int)$admin['id'], 'saved', 'question_group', $id, ['title' => $_POST['title']]); \flash('success', 'گروه ذخیره شد.'); }
        \redirect('/admin/forms');
    }
    public function deleteGroup(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage'); $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { (new FormRepository())->softDeleteGroup($id); (new AuditLogRepository())->record((int)$admin['id'], 'soft_deleted', 'question_group', $id); \flash('success', 'گروه غیرفعال شد.'); }
        \redirect('/admin/forms');
    }
    public function saveQuestion(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage');
        [$ok, $errors] = (new FormBuilderService())->saveQuestion($_POST, (int)$admin['id']);
        if ($ok) { \flash('success', 'پرسش ذخیره شد.'); \redirect('/admin/forms'); }
        $repo = new FormRepository();
        View::render('admin/form_builder', ['steps' => $repo->allSteps(), 'groups' => $repo->allGroups(), 'questions' => $repo->allQuestions(), 'types' => FormBuilderService::TYPES, 'errors' => $errors, 'editQuestion' => null]);
    }
    public function deleteQuestion(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage'); $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { (new FormRepository())->softDeleteQuestion($id); (new AuditLogRepository())->record((int)$admin['id'], 'soft_deleted', 'question', $id); \flash('success', 'پرسش غیرفعال شد.'); }
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
        if (!(new AdminCatalogService())->validateTitle($_POST)) { $id = (new GoalRepository())->save($_POST); (new AuditLogRepository())->record((int)$admin['id'], 'saved', 'goal', $id, ['title' => $_POST['title']]); \flash('success', 'هدف ذخیره شد.'); }
        \redirect('/admin/catalogs');
    }
    public function deleteGoal(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage'); $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { (new GoalRepository())->softDelete($id); (new AuditLogRepository())->record((int)$admin['id'], 'soft_deleted', 'goal', $id); \flash('success', 'هدف غیرفعال شد.'); }
        \redirect('/admin/catalogs');
    }
    public function saveProvince(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage');
        if (!(new AdminCatalogService())->validateTitle($_POST, 'name')) { $id = (new LocationRepository())->saveProvince($_POST); (new AuditLogRepository())->record((int)$admin['id'], 'saved', 'province', $id, ['name' => $_POST['name']]); \flash('success', 'استان ذخیره شد.'); }
        \redirect('/admin/catalogs');
    }
    public function deleteProvince(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage'); $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { (new LocationRepository())->softDeleteProvince($id); (new AuditLogRepository())->record((int)$admin['id'], 'soft_deleted', 'province', $id); \flash('success', 'استان غیرفعال شد.'); }
        \redirect('/admin/catalogs');
    }
    public function saveCity(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage');
        if (!(new AdminCatalogService())->validateCity($_POST)) { $id = (new LocationRepository())->saveCity($_POST); (new AuditLogRepository())->record((int)$admin['id'], 'saved', 'city', $id, ['name' => $_POST['name']]); \flash('success', 'شهر ذخیره شد.'); }
        \redirect('/admin/catalogs');
    }
    public function deleteCity(): void
    {
        \verify_csrf(); $admin = Auth::requirePermission('forms.manage'); $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { (new LocationRepository())->softDeleteCity($id); (new AuditLogRepository())->record((int)$admin['id'], 'soft_deleted', 'city', $id); \flash('success', 'شهر غیرفعال شد.'); }
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
            'blockedPairs' => $repo->blockedPairs(),
        ]);
    }
    public function runMatching(): void
    {
        \verify_csrf();
        Auth::requireAdmin();
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId > 0) {
            $count = (new MatchService())->runForUser($userId, !empty($_POST['recalculate']), 25);
            \flash('success', 'تعداد ' . $count . ' معرفی ایجاد یا به‌روزرسانی شد.');
        }
        \redirect('/admin/matches');
    }

    public function resetMatch(): void
    {
        \verify_csrf();
        Auth::requireAdmin();
        $matchId = (int)($_POST['match_id'] ?? 0);
        if ($matchId > 0) {
            (new MatchRepository())->resetMatch($matchId, !empty($_POST['clear_actions']));
            \flash('success', 'معرفی بازنشانی شد.');
        }

        \redirect('/admin/matches');
    }
    public function recalculateMatch(): void
    {
        \verify_csrf();
        Auth::requireAdmin();
        $repo = new MatchRepository();
        $pair = $repo->pairForMatch((int)($_POST['match_id'] ?? 0));
        if ($pair) {
            (new MatchService())->runForUser((int)$pair['user_one_id'], true, 50);
            \flash('success', 'این جفت معرفی دوباره محاسبه شد.');
        }

        \redirect('/admin/matches');
    }

    public function chats(): void
    {
        Auth::requireAdmin();
        View::render('admin/chats', ['chats' => (new ChatRepository())->allForAdmin()]);
    }

    public function chatDetail(int $id): void
    {
        Auth::requireAdmin();
        $repo = new ChatRepository();
        $chat = $repo->findForAdmin($id);
        if (!$chat) { http_response_code(404); exit('گفت‌وگو پیدا نشد'); }
        View::render('admin/chat_detail', ['chat' => $chat, 'messages' => $repo->messagesForAdmin($id)]);
    }

    public function closeChat(int $id): void
    {
        \verify_csrf();
        $admin = Auth::requireAdmin();
        $closed = (new ChatRepository())->closeByAdmin($id, (int)$admin['id'], (string)($_POST['reason'] ?? ''));
        \flash($closed ? 'success' : 'error', $closed ? 'گفت‌وگو بسته شد.' : 'پیش از بستن گفت‌وگو، دلیل کوتاهی وارد کنید.');
        \redirect('/admin/chats/' . $id);
    }


    public function reveals(): void
    {
        Auth::requireAdmin();
        $repo = new RevealRepository();
        View::render('admin/reveals', [
            'types' => $repo->allTypes(),
            'requests' => $repo->requestsForAdmin($_GET),
            'filters' => $_GET,
            'users' => (new UserRepository())->allMembers(),
        ]);
    }

    public function saveRevealType(): void
    {
        \verify_csrf();
        $admin = Auth::requireAdmin();
        $id = (new RevealRepository())->saveType($_POST);
        if ($id > 0) {
            (new AuditLogRepository())->record((int)$admin['id'], 'saved', 'reveal_type', $id, ['title' => $_POST['title'] ?? '']);
            \flash('success', 'نوع نمایش ذخیره شد.');
        } else {
            \flash('error', 'نوع نمایش ذخیره نشد. عنوان، شناسه متنی و کلید فیلد مجاز را بررسی کنید.');
        }
        \redirect('/admin/reveals');
    }


    public function moderation(): void
    {
        Auth::requireAdmin();
        View::render('admin/moderation', [
            'reports' => (new ReportRepository())->allForAdmin($_GET),
            'filters' => $_GET,
        ]);
    }

    public function reportDetail(int $id): void
    {
        Auth::requireAdmin();
        $report = (new ReportRepository())->findForAdmin($id);
        if (!$report) { http_response_code(404); exit('گزارش پیدا نشد'); }
        View::render('admin/report_detail', ['report' => $report]);
    }

    public function updateReport(int $id): void
    {
        \verify_csrf();
        $admin = Auth::requireAdmin();
        $updated = (new ReportRepository())->updateModeration($id, (int)$admin['id'], (string)($_POST['status'] ?? 'reviewing'), (string)($_POST['priority'] ?? 'normal'), (string)($_POST['admin_resolution_note'] ?? ''), !empty($_POST['assign_to_me']));
        \flash($updated ? 'success' : 'error', $updated ? 'گزارش به‌روزرسانی شد.' : 'گزارش به‌روزرسانی نشد.');
        \redirect('/admin/moderation/' . $id);
    }

    public function blockFromReport(int $id): void
    {
        \verify_csrf();
        Auth::requireAdmin();
        $report = (new ReportRepository())->findForAdmin($id);
        if ($report && !empty($report['reported_user_id'])) {
            (new MatchRepository())->blockUser((int)$report['reporter_user_id'], (int)$report['reported_user_id'], 'مسدودشده توسط رسیدگی پس از گزارش #' . $id, 'moderation_report');
            \flash('success', 'عضو گزارش‌شده برای گزارش‌دهنده مسدود شد.');
        } else {
            \flash('error', 'عضو گزارش‌شده مسدود نشد.');
        }
        \redirect('/admin/moderation/' . $id);
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
        if ($ok) { (new AuditLogRepository())->record((int)$admin['id'], 'saved', 'admin_settings', null); \flash('success', 'تنظیمات ذخیره شد.'); \redirect('/admin/settings'); }
        View::render('admin/settings', ['settings' => $_POST, 'errors' => $errors]);
    }
    public function health(): void
    {
        Auth::requireAdmin();
        View::render('admin/health', ['report' => (new SystemHealthService())->report()]);
    }
}
