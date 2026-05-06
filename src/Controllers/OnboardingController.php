<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\FormRepository;
use App\Repositories\GoalRepository;
use App\Repositories\LocationRepository;
use App\Services\OnboardingService;

class OnboardingController
{
    public function show(): void
    {
        $user = Auth::requireLogin();
        View::render('onboarding/form', [
            'user' => $user,
            'steps' => (new FormRepository())->activeSteps(),
            'goals' => (new GoalRepository())->active(),
            'cities' => (new LocationRepository())->activeCities(),
            'existingAnswers' => (new \App\Repositories\AnswerRepository())->existingForUser((int)$user['id']),
            'selectedGoalIds' => array_map('intval', array_column((new GoalRepository())->forUser((int)$user['id']), 'id')),
            'errors' => [],
        ]);
    }
    public function save(): void
    {
        \verify_csrf();
        $user = Auth::requireLogin();
        [$ok, $errors] = (new OnboardingService())->submit((int)$user['id'], $_POST);
        if ($ok) { \flash('success', 'Onboarding saved.'); \redirect('/onboarding'); }
        View::render('onboarding/form', [
            'user' => $user,
            'steps' => (new FormRepository())->activeSteps(),
            'goals' => (new GoalRepository())->active(),
            'cities' => (new LocationRepository())->activeCities(),
            'existingAnswers' => (new \App\Repositories\AnswerRepository())->existingForUser((int)$user['id']),
            'selectedGoalIds' => array_map('intval', $_POST['goals'] ?? []),
            'errors' => $errors,
        ]);
    }
}
