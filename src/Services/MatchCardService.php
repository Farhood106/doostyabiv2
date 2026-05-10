<?php
namespace App\Services;

class MatchCardService
{
    public function build(int $matchId, int $viewerId, int $targetId, array $score): array
    {
        $confidence = (float)($score['confidence'] ?? 0);
        $compatibility = (float)($score['compatibility'] ?? 0);
        $summary = $confidence < 50
            ? 'این معرفی با اطمینان محدود ساخته شده است. اگر مایل بودید، آن را فقط به‌عنوان شروعی آرام برای شناخت بیشتر ببینید.'
            : 'چند نشانه اولیه از هم‌پوشانی دیده می‌شود. این کارت قطعی یا قضاوت‌گر نیست و فقط برای تصمیم‌گیری آرام‌تر کمک می‌کند.';
        return [
            'title' => 'معرفی ناشناس و خصوصی',
            'summary' => $summary,
            'strengths' => $score['explanation']['strengths'],
            'cautions' => $score['explanation']['cautions'],
            'label' => $score['label'],
            'freshness_score' => $score['freshness_score'] ?? 50,
            'payload' => [
                'match_id' => $matchId,
                'viewer_user_id' => $viewerId,
                'target_user_id' => $targetId,
                'compatibility_score' => $compatibility,
                'confidence_score' => $confidence,
                'freshness_score' => $score['freshness_score'] ?? 50,
                'privacy' => 'anonymous',
            ],
        ];
    }
}
