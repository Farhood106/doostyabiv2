<?php
namespace App\Services;

class MatchScoringService
{
    public function score(array $viewerGoals, array $targetGoals, array $viewerCities, array $targetCities, array $viewerAnswers, array $targetAnswers, bool $coldStart = false): array
    {
        $viewerGoalIds = array_map('intval', array_column($viewerGoals, 'id'));
        $targetGoalIds = array_map('intval', array_column($targetGoals, 'id'));
        $sharedGoalIds = array_values(array_intersect($viewerGoalIds, $targetGoalIds));
        if (!$sharedGoalIds && !$coldStart) { return ['rejected' => true, 'reason' => 'هدف فعال مشترکی پیدا نشد.']; }
        $goalScore = $sharedGoalIds ? count($sharedGoalIds) / max(1, count(array_unique(array_merge($viewerGoalIds, $targetGoalIds)))) * 100 : 35;
        $sharedGoalTitles = array_values(array_intersect(array_column($viewerGoals, 'title'), array_column($targetGoals, 'title')));

        $locationScore = 50;
        if ($viewerCities && $targetCities) { $locationScore = array_intersect($viewerCities, $targetCities) ? 100 : 30; }

        $answerScores = []; $hardRejected = false; $hardNotes = [];
        foreach ($viewerAnswers as $qid => $a) {
            if (empty($targetAnswers[$qid])) { continue; }
            $score = $this->scoreAnswer($a, $targetAnswers[$qid]);
            if (($a['match_rule'] ?? '') === 'hard_filter' && $score < 60 && !$coldStart) { $hardRejected = true; $hardNotes[] = $a['title']; }
            $answerScores[] = ['score' => $score, 'weight' => max(0.1, (float)$a['match_weight']), 'title' => $a['title'], 'type' => $a['answer_type']];
        }
        if ($hardRejected) { return ['rejected' => true, 'reason' => 'ناسازگاری در معیار ضروری: ' . implode('، ', $hardNotes)]; }
        $answerScore = $this->weightedAverage($answerScores);
        $totalMatchable = count(array_unique(array_merge(array_keys($viewerAnswers), array_keys($targetAnswers))));
        $confidence = $totalMatchable ? (count($answerScores) / $totalMatchable) * 100 : ($coldStart ? 25 : 20);
        if ($coldStart) { $confidence = min($confidence, 55); }
        $penalty = $confidence < 40 ? 8 : 0;
        $compatibility = max(0, min(100, ($goalScore * 0.28) + ($locationScore * 0.18) + ($answerScore * 0.38) + 12 - $penalty));

        $strong = [];
        if ($sharedGoalTitles) { $strong[] = 'در هدف‌های اصلی مثل «' . implode('، ', array_slice($sharedGoalTitles, 0, 2)) . '» هم‌پوشانی دیده می‌شود.'; }
        if ($locationScore >= 100) { $strong[] = 'ترجیحات شهری به هم نزدیک است و شروع گفت‌وگو را ساده‌تر می‌کند.'; }
        foreach (array_slice(array_filter($answerScores, fn($s) => $s['score'] >= 80), 0, 2) as $s) { $strong[] = $this->humanStrength($s); }
        if (!$strong && $coldStart) { $strong[] = 'هنوز داده‌ها کم است، اما این معرفی می‌تواند برای یک گفت‌وگوی آرام اولیه بررسی شود.'; }

        $cautions = [];
        if (!$sharedGoalTitles && $coldStart) { $cautions[] = 'هدف‌های مشترک هنوز روشن نیست؛ بهتر است با چند پرسش ساده و محترمانه شروع شود.'; }
        if ($locationScore < 50) { $cautions[] = 'ترجیحات مکانی ممکن است نیاز به گفت‌وگوی شفاف و بدون عجله داشته باشد.'; }
        if ($confidence < 50) { $cautions[] = 'اطمینان این معرفی هنوز پایین است، چون چند پاسخ مؤثر کامل نشده یا قابل مقایسه نیست.'; }
        foreach (array_slice(array_filter($answerScores, fn($s) => $s['score'] < 50), 0, 1) as $s) { $cautions[] = 'در موضوع «' . $s['title'] . '» بهتر است با آرامش بیشتر شناخت شکل بگیرد.'; }

        return [
            'rejected' => false,
            'compatibility' => round($compatibility, 2),
            'confidence' => round($confidence, 2),
            'label' => $this->label($compatibility, $confidence),
            'scores' => [
                ['type' => 'goal_fit', 'value' => round($goalScore, 2), 'weight' => 0.28, 'details' => $sharedGoalTitles ? implode('، ', $sharedGoalTitles) : 'داده هدف مشترک محدود است'],
                ['type' => 'location_fit', 'value' => round($locationScore, 2), 'weight' => 0.18, 'details' => $locationScore >= 100 ? 'پاسخ‌های شهری هم‌پوشان' : 'هم‌پوشانی دقیق شهری وجود ندارد یا پاسخ شهری کامل نیست'],
                ['type' => 'answer_fit', 'value' => round($answerScore, 2), 'weight' => 0.38, 'details' => count($answerScores) . ' پاسخ قابل مقایسه برای سازگاری'],
                ['type' => 'freshness_readiness', 'value' => $coldStart ? 45 : 70, 'weight' => 0.10, 'details' => $coldStart ? 'حالت شروع آرام با داده محدود' : 'داده کافی برای توضیح اولیه وجود دارد'],
                ['type' => 'confidence', 'value' => round($confidence, 2), 'weight' => 1, 'details' => 'بر پایه پاسخ‌های تکمیل‌شده قابل استفاده در سازگاری'],
                ['type' => 'penalty', 'value' => $penalty, 'weight' => 1, 'details' => $penalty ? 'کاهش ملایم به دلیل اطمینان پایین' : 'بدون کاهش امتیاز'],
            ],
            'explanation' => [
                'why' => $confidence < 50
                    ? 'این معرفی با داده محدود ساخته شده و بهتر است به‌عنوان شروعی آرام برای شناخت بیشتر دیده شود.'
                    : 'چند نشانه اولیه از هم‌پوشانی در هدف‌ها، ترجیحات یا سبک پاسخ‌ها دیده می‌شود؛ نتیجه قطعی نیست و فقط برای شروع گفت‌وگوی محترمانه کمک می‌کند.',
                'strengths' => implode("\n", $strong ?: ['نشانه‌های اولیه برای یک گفت‌وگوی محترمانه وجود دارد.']),
                'cautions' => implode("\n", $cautions ?: ['نکته هشدار جدی از پاسخ‌های موجود دیده نشد، اما شناخت تدریجی همچنان مهم است.']),
            ],
        ];
    }

    private function humanStrength(array $score): string
    {
        return match ($score['type']) {
            'multi_choice', 'multi_select' => 'در چند علاقه یا ترجیح، نقطه‌های مشترک دیده می‌شود.',
            'boolean', 'scale' => 'در سرعت و مرزهای راحتی، نشانه‌هایی از نزدیکی دیده می‌شود.',
            'single_choice', 'select' => 'در یکی از انتظارهای مهم، پاسخ‌ها به هم نزدیک است.',
            default => 'در موضوع «' . $score['title'] . '» هم‌سویی اولیه دیده می‌شود.',
        };
    }

    private function weightedAverage(array $scores): float
    {
        if (!$scores) { return 50.0; }
        $sum = 0; $weight = 0;
        foreach ($scores as $s) { $sum += $s['score'] * $s['weight']; $weight += $s['weight']; }
        return $weight ? $sum / $weight : 50.0;
    }

    private function scoreAnswer(array $a, array $b): float
    {
        return match ($a['answer_type']) {
            'multi_choice','multi_select' => $this->overlapScore($a['option_values'] ?? '', $b['option_values'] ?? ''),
            'single_choice','select' => (($a['option_values'] ?? '') !== '' && ($a['option_values'] === $b['option_values'])) ? 100 : 0,
            'boolean' => ((string)$a['answer_boolean'] === (string)$b['answer_boolean']) ? 100 : 0,
            'range' => $this->rangeScore($a['answer_text'] ?? '', $b['answer_text'] ?? ''),
            'scale','number' => $this->numberScore($a['answer_number'], $b['answer_number']),
            'city_single','city_multi' => $this->overlapScore($a['city_values'] ?? '', $b['city_values'] ?? ''),
            default => 50,
        };
    }

    private function overlapScore(string $a, string $b): float
    {
        $aa = array_filter(explode('|', $a)); $bb = array_filter(explode('|', $b));
        if (!$aa || !$bb) { return 0; }
        return count(array_intersect($aa, $bb)) / count(array_unique(array_merge($aa, $bb))) * 100;
    }
    private function rangeScore(string $a, string $b): float
    {
        if (!preg_match('/(\d+)\D+(\d+)/', $a, $ma) || !preg_match('/(\d+)\D+(\d+)/', $b, $mb)) { return 50; }
        $start = max((int)$ma[1], (int)$mb[1]); $end = min((int)$ma[2], (int)$mb[2]);
        return $start <= $end ? 100 : max(0, 60 - (min(abs($start-$end), 10) * 6));
    }
    private function numberScore(mixed $a, mixed $b): float
    {
        if (!is_numeric($a) || !is_numeric($b)) { return 50; }
        return max(0, 100 - min(100, abs((float)$a - (float)$b) * 10));
    }
    private function label(float $score, float $confidence): string
    {
        if ($confidence < 45) { return 'نیازمند شناخت بیشتر'; }
        if ($score >= 82) { return 'آشنایی امیدوارکننده'; }
        if ($score >= 70) { return 'احتمال گفت‌وگوی راحت'; }
        if ($score >= 55) { return 'سازگاری اولیه خوب'; }
        return 'نیازمند شناخت بیشتر';
    }
}
