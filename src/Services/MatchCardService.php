<?php
namespace App\Services;

class MatchCardService
{
    public function build(int $matchId, int $viewerId, int $targetId, array $score): array
    {
        return [
            'title' => 'Anonymous compatibility profile',
            'summary' => 'A privacy-first match candidate with ' . round($score['compatibility']) . '% compatibility and ' . round($score['confidence']) . '% confidence based on shared goals and matchable answers.',
            'strengths' => $score['explanation']['strengths'],
            'cautions' => $score['explanation']['cautions'],
            'label' => $score['label'],
            'payload' => [
                'match_id' => $matchId,
                'viewer_user_id' => $viewerId,
                'target_user_id' => $targetId,
                'compatibility_score' => $score['compatibility'],
                'confidence_score' => $score['confidence'],
                'privacy' => 'anonymous',
            ],
        ];
    }
}
