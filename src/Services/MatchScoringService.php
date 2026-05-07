<?php
namespace App\Services;

class MatchScoringService
{
    public function score(array $viewerGoals, array $targetGoals, array $viewerCities, array $targetCities, array $viewerAnswers, array $targetAnswers): array
    {
        $viewerGoalIds = array_map('intval', array_column($viewerGoals, 'id'));
        $targetGoalIds = array_map('intval', array_column($targetGoals, 'id'));
        $sharedGoalIds = array_values(array_intersect($viewerGoalIds, $targetGoalIds));
        if (!$sharedGoalIds) { return ['rejected' => true, 'reason' => 'No overlapping active goals.']; }
        $goalScore = count($sharedGoalIds) / max(1, count(array_unique(array_merge($viewerGoalIds, $targetGoalIds)))) * 100;
        $sharedGoalTitles = array_values(array_intersect(array_column($viewerGoals, 'title'), array_column($targetGoals, 'title')));

        $locationScore = 50;
        if ($viewerCities && $targetCities) { $locationScore = array_intersect($viewerCities, $targetCities) ? 100 : 25; }

        $answerScores = []; $hardRejected = false; $hardNotes = [];
        foreach ($viewerAnswers as $qid => $a) {
            if (empty($targetAnswers[$qid])) { continue; }
            $score = $this->scoreAnswer($a, $targetAnswers[$qid]);
            if (($a['match_rule'] ?? '') === 'hard_filter' && $score < 60) { $hardRejected = true; $hardNotes[] = $a['title']; }
            $answerScores[] = ['score' => $score, 'weight' => max(0.1, (float)$a['match_weight']), 'title' => $a['title']];
        }
        if ($hardRejected) { return ['rejected' => true, 'reason' => 'Hard filter mismatch: ' . implode(', ', $hardNotes)]; }
        $answerScore = $this->weightedAverage($answerScores);
        $totalMatchable = count(array_unique(array_merge(array_keys($viewerAnswers), array_keys($targetAnswers))));
        $confidence = $totalMatchable ? (count($answerScores) / $totalMatchable) * 100 : 20;
        $penalty = $confidence < 40 ? 10 : 0;
        $compatibility = max(0, min(100, ($goalScore * 0.30) + ($locationScore * 0.20) + ($answerScore * 0.40) + 10 - $penalty));

        $strong = [];
        if ($sharedGoalTitles) { $strong[] = 'Shared goals: ' . implode(', ', array_slice($sharedGoalTitles, 0, 3)); }
        if ($locationScore >= 100) { $strong[] = 'Overlapping city preferences'; }
        foreach (array_slice(array_filter($answerScores, fn($s) => $s['score'] >= 80), 0, 3) as $s) { $strong[] = 'Aligned on ' . $s['title']; }
        $cautions = [];
        if ($locationScore < 50) { $cautions[] = 'Location preferences may need discussion'; }
        if ($confidence < 50) { $cautions[] = 'Compatibility confidence is limited because some matchable answers are missing'; }
        foreach (array_slice(array_filter($answerScores, fn($s) => $s['score'] < 50), 0, 2) as $s) { $cautions[] = 'Different preferences for ' . $s['title']; }

        return [
            'rejected' => false,
            'compatibility' => round($compatibility, 2),
            'confidence' => round($confidence, 2),
            'label' => $this->label($compatibility),
            'scores' => [
                ['type' => 'goal_fit', 'value' => round($goalScore, 2), 'weight' => 0.30, 'details' => implode(', ', $sharedGoalTitles)],
                ['type' => 'location_fit', 'value' => round($locationScore, 2), 'weight' => 0.20, 'details' => $locationScore >= 100 ? 'Overlapping city answers' : 'No exact city overlap or missing city answers'],
                ['type' => 'answer_fit', 'value' => round($answerScore, 2), 'weight' => 0.40, 'details' => count($answerScores) . ' comparable matchable answers'],
                ['type' => 'boundary_fit', 'value' => 100, 'weight' => 1, 'details' => 'No hard filters rejected this candidate'],
                ['type' => 'confidence', 'value' => round($confidence, 2), 'weight' => 1, 'details' => 'Based on completed matchable answers'],
                ['type' => 'penalty', 'value' => $penalty, 'weight' => 1, 'details' => $penalty ? 'Low confidence penalty' : 'No penalty'],
            ],
            'explanation' => [
                'why' => 'This anonymous profile shares at least one active goal and has a compatibility score of ' . round($compatibility) . '%.',
                'strengths' => implode("\n", $strong ?: ['Shared relationship intention']),
                'cautions' => implode("\n", $cautions ?: ['No major caution points from available matchable answers']),
            ],
        ];
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
    private function label(float $score): string
    {
        if ($score >= 85) { return 'Excellent fit'; }
        if ($score >= 70) { return 'Strong fit'; }
        if ($score >= 55) { return 'Possible fit'; }
        return 'Low confidence fit';
    }
}
