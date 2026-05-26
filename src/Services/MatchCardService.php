<?php
namespace App\Services;

class MatchCardService
{
    public function build(int $matchId, int $viewerId, int $targetId, array $score): array
    {
        $confidence = (float)($score['confidence'] ?? 0);
        $compatibility = (float)($score['compatibility'] ?? 0);
        $tone = $this->tone($compatibility, $confidence);
        $strengths = $this->lines((string)($score['explanation']['strengths'] ?? ''), 3, ['چند نشانه اولیه برای شروع محترمانه دیده می‌شود.']);
        $caution = $this->caution((string)($score['explanation']['cautions'] ?? ''), $confidence);

        return [
            'title' => $this->title($tone),
            'summary' => $this->summary($tone, $confidence),
            'narrative' => $this->narrative($tone, $confidence, $strengths, $caution),
            'strengths' => implode("\n", $strengths),
            'cautions' => $caution,
            'label' => $this->label($tone),
            'freshness_score' => $score['freshness_score'] ?? 50,
            'payload' => [
                'match_id' => $matchId,
                'viewer_user_id' => $viewerId,
                'target_user_id' => $targetId,
                'compatibility_score' => $compatibility,
                'confidence_score' => $confidence,
                'freshness_score' => $score['freshness_score'] ?? 50,
                'tone' => $tone,
                'privacy' => 'anonymous',
                'narrative_version' => 'human_v1',
            ],
        ];
    }

    private function tone(float $compatibility, float $confidence): string
    {
        if ($confidence < 45) { return 'exploratory'; }
        if ($compatibility >= 82 && $confidence >= 65) { return 'strong_initial_fit'; }
        if ($compatibility >= 65) { return 'hopeful'; }
        return 'cautious';
    }

    private function title(string $tone): string
    {
        return match ($tone) {
            'strong_initial_fit' => 'معرفی ناشناس با هم‌سویی اولیه خوب',
            'hopeful' => 'معرفی ناشناس با شروعی امیدوارکننده',
            'cautious' => 'معرفی ناشناس برای شناخت آرام‌تر',
            default => 'معرفی ناشناس برای بررسی آرام',
        };
    }

    private function label(string $tone): string
    {
        return match ($tone) {
            'strong_initial_fit' => 'سازگاری اولیه قابل توجه',
            'hopeful' => 'شروعی امیدوارکننده',
            'cautious' => 'نیازمند شناخت آرام‌تر',
            default => 'گفت‌وگویی که می‌تواند راحت شروع شود',
        };
    }

    private function summary(string $tone, float $confidence): string
    {
        $opening = match ($tone) {
            'strong_initial_fit' => 'چند نشانه روشن از نزدیکی در انتظارها و سبک شروع ارتباط دیده می‌شود.',
            'hopeful' => 'این معرفی می‌تواند با یک گفت‌وگوی ساده و محترمانه شروع خوبی داشته باشد.',
            'cautious' => 'این معرفی بهتر است آهسته‌تر و با چند پرسش روشن پیش برود.',
            default => 'داده‌ها هنوز کامل نیست، اما این کارت می‌تواند یک شروع آرام برای شناخت بیشتر باشد.',
        };
        return $opening . ' ' . $this->confidenceSentence($confidence);
    }


    private function narrative(string $tone, float $confidence, array $strengths, string $caution): string
    {
        return $this->summary($tone, $confidence) . "\n" . implode("\n", $strengths) . "\n" . $caution;
    }

    private function confidenceSentence(float $confidence): string
    {
        if ($confidence < 45) { return 'برای دقیق‌تر شدن معرفی‌ها، چند پاسخ بیشتر در شناخت‌نامه کمک‌کننده است.'; }
        if ($confidence < 65) { return 'اطمینان این معرفی متوسط است و بهتر است بدون عجله بررسی شود.'; }
        return 'پاسخ‌های کافی برای یک برداشت اولیه وجود دارد، اما نتیجه همچنان قطعی نیست.';
    }

    private function caution(string $raw, float $confidence): string
    {
        if ($confidence < 45) { return 'این کارت به معنی ناسازگاری نیست؛ فقط سامانه برای شناخت دقیق‌تر به پاسخ‌های بیشتری نیاز دارد.'; }
        $lines = $this->lines($raw, 1, []);
        return $lines[0] ?? 'شناخت تدریجی و گفت‌وگوی محترمانه همچنان مهم است.';
    }

    private function lines(string $text, int $limit, array $fallback): array
    {
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R+/u', $text) ?: [])));
        return array_slice($lines ?: $fallback, 0, $limit);
    }
}
