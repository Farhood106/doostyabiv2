<?php
namespace App\Services;

use App\Repositories\MatchRepository;

class MatchService
{
    public function runForUser(int $userId, bool $recalculate = false, int $limit = 25): int
    {
        $repo = new MatchRepository();
        $scorer = new MatchScoringService();
        $cards = new MatchCardService();
        $created = 0;
        $viewerGoals = $repo->goalsForUser($userId);
        $viewerCities = $repo->cityIdsForUser($userId);
        $viewerAnswers = $repo->matchableAnswers($userId);
        foreach (array_slice($repo->candidateUsers($userId, $recalculate), 0, $limit) as $candidate) {
            $targetId = (int)$candidate['id'];
            $score = $scorer->score($viewerGoals, $repo->goalsForUser($targetId), $viewerCities, $repo->cityIdsForUser($targetId), $viewerAnswers, $repo->matchableAnswers($targetId));
            if (!empty($score['rejected'])) { continue; }
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
        $repo = new MatchRepository(); $count = 0;
        foreach (array_slice($repo->activeMembers(), 0, $limit) as $user) { $count += $this->runForUser((int)$user['id'], false, 10); }
        return $count;
    }
}
