<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\AdminSettingsRepository;
use App\Repositories\MatchRepository;
use App\Services\MatchIntelligenceService;

class MatchController
{
    public function index(): void
    {
        $user = Auth::requireLogin();
        $settings = (new AdminSettingsRepository())->all();
        $repo = new MatchRepository();
        View::render('matches/index', [
            'cards' => $repo->cardsForUser((int)$user['id'], ($settings['show_low_confidence_matches'] ?? '1') === '1'),
            'quality' => (new MatchIntelligenceService())->summaryForUser((int)$user['id']),
            'readiness' => $repo->answerReadiness((int)$user['id']),
            'cardAvailability' => $repo->cardAvailabilityForUser((int)$user['id']),
            'settings' => $settings,
        ]);
    }
    public function action(): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        $action = $_POST['action'] ?? '';
        (new MatchRepository())->recordAction((int)($_POST['match_id'] ?? 0), (int)$user['id'], $action);
        \flash('success', 'انتخاب شما برای این معرفی ذخیره شد.');
        \redirect('/matches');
    }
}
