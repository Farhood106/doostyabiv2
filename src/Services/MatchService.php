<?php
namespace App\Services;

use App\Repositories\AdminSettingsRepository;
use App\Repositories\MatchRepository;
use App\Services\VisibilityGuardService;
use App\Services\SafetyConsistencyService;

class MatchService
{
    public function runForUser(int $userId, bool $recalculate = false, int $limit = 25): int
    {
        $repo = new MatchRepository();
        (new SafetyConsistencyService())->repairAndReport();
        $guard = new VisibilityGuardService();
        $scorer = new MatchScoringService();
        $cards = new MatchCardService();
        $intel = new MatchIntelligenceService();
        $created = 0;
        $settings = (new AdminSettingsRepository())->all();
        $readiness = $repo->answerReadiness($userId);
        if ((int)$readiness['required_answered'] < (int)($settings['minimum_required_answers_before_matching'] ?? 3)) { return 0; }
        $viewerQuality = $intel->calculateForUser($userId);
        $viewerGoals = $repo->goalsForUser($userId);
        $viewerCities = $repo->cityIdsForUser($userId);
        $viewerAnswers = $repo->matchableAnswers($userId);
        $coldStart = count($viewerGoals) < 2 || count($viewerAnswers) < 3 || ($viewerQuality['profile']['score'] ?? 0) < 45;
        foreach (array_slice($repo->candidateUsers($userId, $recalculate, $coldStart), 0, $limit) as $candidate) {
            $targetId = (int)$candidate['id'];
            if (!$guard->canUsersSeeEachOther($userId, $targetId)) { continue; }
            $targetQuality = $intel->calculateForUser($targetId);
            $score = $scorer->score($viewerGoals, $repo->goalsForUser($targetId), $viewerCities, $repo->cityIdsForUser($targetId), $viewerAnswers, $repo->matchableAnswers($targetId), $coldStart);
            if (!empty($score['rejected'])) { continue; }
            $freshness = min(100, max(10, ($score['compatibility'] * .55) + ($score['confidence'] * .15) + (($targetQuality['profile']['score'] ?? 50) * .20) + (($targetQuality['trust']['score'] ?? 50) * .10)));
            $score['freshness_score'] = round($freshness, 2);
            $matchId = $repo->upsertMatch($userId, $targetId, $score['compatibility'], $score['confidence']);
            $repo->replaceScores($matchId, $score['scores']);
            $repo->saveExplanation($matchId, $score['explanation']);
            $repo->saveCard($matchId, $userId, $targetId, $cards->build($matchId, $userId, $targetId, $score));
            $repo->saveCard($matchId, $targetId, $userId, $cards->build($matchId, $targetId, $userId, $score));
            $created++;
        }
        return $created;
    }

    public function runBatch(int $limit = 20): int
    {
        $repo = new MatchRepository();
        (new SafetyConsistencyService())->repairAndReport();
        $guard = new VisibilityGuardService(); $count = 0;
        foreach (array_slice($repo->activeMembers(), 0, $limit) as $user) { $count += $this->runForUser((int)$user['id'], false, 10); }
        return $count;
    }
}
