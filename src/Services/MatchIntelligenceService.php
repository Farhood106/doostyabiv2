<?php
namespace App\Services;

use App\Repositories\IntelligenceRepository;

class MatchIntelligenceService
{
    public function calculateForUser(int $userId): array
    {
        $repo = new IntelligenceRepository();
        $inputs = $repo->userInputs($userId);
        $profile = $this->profileQuality($inputs);
        $trust = $this->trust($inputs, $profile);
        $signals = $this->moderationSignals($inputs);
        $repo->saveScores($userId, $profile, $trust, $signals);
        return ['profile' => $profile, 'trust' => $trust, 'signals' => $signals];
    }

    public function summaryForUser(int $userId): ?array
    {
        $repo = new IntelligenceRepository();
        $summary = $repo->summaryForUser($userId);
        if (!$summary || empty($summary['last_quality_calculated_at'])) {
            $this->calculateForUser($userId);
            $summary = $repo->summaryForUser($userId);
        }
        return $summary;
    }

    private function profileQuality(array $i): array
    {
        $q = $i['questionStats']; $a = $i['answers'];
        $required = max(1, (int)($q['required_questions'] ?? 0));
        $matchable = max(1, (int)($q['matchable_questions'] ?? 0));
        $completionRatio = min(1, ((int)($a['answered_required'] ?? 0)) / $required);
        $matchableRatio = min(1, ((int)($a['answered_matchable'] ?? 0)) / $matchable);
        $typeDiversity = min(1, ((int)($a['answer_type_count'] ?? 0)) / 5);
        $textDiversity = $this->textDiversityRatio((string)($a['text_answers'] ?? ''));
        $diversity = ($typeDiversity * .65) + ($textDiversity * .35);
        $goals = (int)$i['goals'];
        $fresh = $this->freshnessRatio((string)($a['last_answered_at'] ?? ($i['user']['updated_at'] ?? '')));
        $revealParticipation = ((int)$i['revealRequested'] + (int)$i['revealIncoming']) > 0 ? 1 : 0;
        $nonSpam = $this->nonSpamRatio($i);
        $emptyAbuse = !empty($i['user']['is_complete']) && (int)($a['answered_questions'] ?? 0) < max(2, (int)ceil($required * .6));

        $score = ($completionRatio * 30) + ($matchableRatio * 20) + ($diversity * 10) + (min(1, $goals / 3) * 15) + ($fresh * 10) + ($revealParticipation * 5) + ($nonSpam * 10);
        $flags = [];
        if ($completionRatio < .6) { $flags[] = 'incomplete_onboarding'; }
        if ($matchableRatio < .35) { $flags[] = 'few_matchable_answers'; }
        if ($goals === 0) { $flags[] = 'no_active_goals'; }
        if ($fresh < .4) { $flags[] = 'stale_profile'; }
        if ($this->hasRepetitiveText((string)($a['text_answers'] ?? ''))) { $flags[] = 'repetitive_text_answers'; $score -= 12; }
        if ($emptyAbuse) { $flags[] = 'empty_onboarding_pattern'; $score -= 15; }
        if ((float)($i['user']['fatigue_score'] ?? 0) >= 70) { $flags[] = 'high_onboarding_fatigue'; $score -= 4; }
        if ((int)$i['flagCount'] > 0 || (int)$i['reportReceived'] > 1) { $flags[] = 'moderation_history'; $score -= min(20, ((int)$i['flagCount'] * 5) + ((int)$i['reportReceived'] * 4)); }
        $score = max(0, min(100, round($score, 2)));
        return ['score' => $score, 'level' => $this->qualityLevel($score), 'flags' => $flags];
    }

    private function trust(array $i, array $profile): array
    {
        $ageDays = $this->ageDays((string)($i['user']['created_at'] ?? ''));
        $accountAge = min(20, $ageDays * 1.5);
        $onboarding = min(20, $profile['score'] * .20);
        $messageBehavior = (int)$i['flagCount'] === 0 ? 18 : max(0, 18 - ((int)$i['flagCount'] * 5));
        $reports = max(0, 15 - ((int)$i['reportReceived'] * 4) - ((int)$i['blocksReceived'] * 2) - max(0, ((int)$i['reportMade'] - 5)));
        $revealTotal = (int)$i['revealApproved'] + (int)$i['revealRejected'];
        $reveal = $revealTotal === 0 ? 8 : min(12, 6 + (((int)$i['revealApproved'] / max(1, $revealTotal)) * 6));
        $patterns = $this->patternPenalty($i) + $this->messagePatternPenalty($i);
        $score = 35 + $accountAge + $onboarding + $messageBehavior + $reports + $reveal - $patterns;
        $flags = [];
        if ($ageDays < 2) { $flags[] = 'new_account'; }
        if ((int)$i['flagCount'] > 0) { $flags[] = 'flagged_messages'; }
        if ((int)$i['reportReceived'] > 2) { $flags[] = 'multiple_reports_received'; }
        if ($patterns >= 10) { $flags[] = 'rapid_or_repetitive_actions'; }
        if ((int)$i['blocksMade'] > 5) { $flags[] = 'high_block_count'; }
        if ((int)$i['blocksReceived'] > 2) { $flags[] = 'blocks_received'; }
        if ($this->messagePatternPenalty($i) >= 8) { $flags[] = 'repetitive_messages'; }
        $score = max(0, min(100, round($score, 2)));
        return ['score' => $score, 'level' => $this->trustLevel($score), 'flags' => $flags];
    }

    private function moderationSignals(array $i): array
    {
        return [
            'reports_made' => (int)$i['reportMade'],
            'reports_received' => (int)$i['reportReceived'],
            'flagged_messages' => (int)$i['flagCount'],
            'messages_sent' => (int)$i['messageCount'],
            'distinct_message_starts' => (int)($i['messageStats']['distinct_message_starts'] ?? 0),
            'avg_message_length' => round((float)($i['messageStats']['avg_message_length'] ?? 0), 2),
            'passes_last_day' => (int)($i['actions']['pass']['last_day'] ?? 0),
            'interests_last_day' => (int)($i['actions']['interested']['last_day'] ?? 0),
            'blocks_total' => (int)$i['blocksMade'],
            'blocks_received' => (int)$i['blocksReceived'],
            'onboarding_skipped_optional' => (int)($i['user']['skipped_optional_count'] ?? 0),
            'onboarding_fatigue_score' => round((float)($i['user']['fatigue_score'] ?? 0), 2),
            'reveal_requests' => (int)$i['revealRequested'],
            'reveal_approved_given' => (int)$i['revealApproved'],
            'reveal_rejected_given' => (int)$i['revealRejected'],
        ];
    }

    private function freshnessRatio(string $date): float
    {
        $days = $this->ageDays($date);
        if ($days <= 7) { return 1; }
        if ($days <= 30) { return .75; }
        if ($days <= 90) { return .45; }
        return .15;
    }

    private function ageDays(string $date): int
    {
        if ($date === '') { return 0; }
        $ts = strtotime($date);
        return $ts ? max(0, (int)floor((time() - $ts) / 86400)) : 0;
    }

    private function nonSpamRatio(array $i): float
    {
        $penalty = min(.8, ((int)$i['flagCount'] * .2) + ((int)$i['reportReceived'] * .12) + ($this->patternPenalty($i) / 100));
        return max(.1, 1 - $penalty);
    }

    private function patternPenalty(array $i): float
    {
        $rapid = (int)($i['actions']['pass']['last_hour'] ?? 0) + (int)($i['actions']['interested']['last_hour'] ?? 0) + ((int)($i['actions']['block']['last_hour'] ?? 0) * 2);
        $dayPasses = (int)($i['actions']['pass']['last_day'] ?? 0);
        $sameDayActions = $dayPasses + (int)($i['actions']['interested']['last_day'] ?? 0);
        return min(30, max(0, ($rapid - 8) * 2) + max(0, ($dayPasses - 20) * .7) + max(0, ($sameDayActions - 35) * .3));
    }


    private function messagePatternPenalty(array $i): float
    {
        $messages = (int)($i['messageStats']['total_messages'] ?? 0);
        if ($messages < 5) { return 0; }
        $distinct = max(1, (int)($i['messageStats']['distinct_message_starts'] ?? 0));
        $repeatRatio = 1 - min(1, $distinct / max(1, $messages));
        $shortPenalty = ((float)($i['messageStats']['avg_message_length'] ?? 0) > 0 && (float)$i['messageStats']['avg_message_length'] < 8) ? 4 : 0;
        return min(14, ($repeatRatio * 12) + $shortPenalty);
    }

    private function textDiversityRatio(string $textAnswers): float
    {
        $parts = array_values(array_filter(array_map('trim', explode('||', $textAnswers))));
        if (!$parts) { return .35; }
        $long = array_values(array_filter($parts, fn($p) => strlen($p) >= 8));
        if (!$long) { return .45; }
        return min(1, count(array_unique($long)) / max(1, count($long)));
    }

    private function hasRepetitiveText(string $textAnswers): bool
    {
        $parts = array_values(array_filter(array_map('trim', explode('||', $textAnswers))));
        if (count($parts) < 2) { return false; }
        $long = array_filter($parts, fn($p) => strlen($p) >= 12);
        return count($long) >= 2 && count(array_unique($long)) <= max(1, (int)floor(count($long) / 2));
    }

    private function qualityLevel(float $score): string
    {
        if ($score >= 80) { return 'strong'; }
        if ($score >= 60) { return 'growing'; }
        if ($score >= 35) { return 'limited'; }
        return 'not_ready';
    }

    private function trustLevel(float $score): string
    {
        if ($score >= 80) { return 'steady'; }
        if ($score >= 60) { return 'normal'; }
        if ($score >= 40) { return 'watch'; }
        return 'low';
    }
}
