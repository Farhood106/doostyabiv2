<?php
namespace App\Services;

use App\Core\Database;

class SafetyConsistencyService
{
    /**
     * Stale-state philosophy:
     * if visibility breaks later, previously created entities are auto-hidden/closed.
     */
    public function repairAndReport(): array
    {
        $db = Database::connection();
        $guard = new VisibilityGuardService();
        $hiddenMatches = 0; $hiddenChats = 0; $hiddenCards = 0; $mutualInvalidated = 0;
        $reasonCounts = ['blocked' => 0, 'privacy_shield' => 0, 'inactive_user' => 0, 'stale_state' => 0];

        $pairs = $db->query('SELECT id,user_one_id,user_two_id,match_status FROM matches')->fetchAll();
        foreach ($pairs as $m) {
            $evaluation = $guard->evaluateVisibility((int)$m['user_one_id'], (int)$m['user_two_id']);
            if ($evaluation['allowed']) { continue; }
            foreach ($evaluation['reasons'] as $r) { if (isset($reasonCounts[$r])) { $reasonCounts[$r]++; } }
            $db->prepare("UPDATE matches SET match_status='blocked' WHERE id=? AND match_status<>'blocked'")->execute([(int)$m['id']]);
            if ($db->query('SELECT ROW_COUNT()')->fetchColumn() > 0) { $hiddenMatches++; if (($m['match_status'] ?? '') === 'mutual') { $mutualInvalidated++; } }
            $db->prepare("UPDATE chats SET status='closed', closed_reason=COALESCE(closed_reason,'Visibility policy enforced'), closed_at=COALESCE(closed_at,NOW()) WHERE match_id=? AND status='open'")->execute([(int)$m['id']]);
            $hiddenChats += (int)$db->query('SELECT ROW_COUNT()')->fetchColumn();
            $db->prepare("UPDATE match_cards SET hidden_until=DATE_ADD(NOW(), INTERVAL 10 YEAR), freshness_score=0 WHERE match_id=? AND (hidden_until IS NULL OR hidden_until < DATE_ADD(NOW(), INTERVAL 9 YEAR))")->execute([(int)$m['id']]);
            $hiddenCards += (int)$db->query('SELECT ROW_COUNT()')->fetchColumn();
        }

        return compact('hiddenMatches','hiddenChats','hiddenCards','mutualInvalidated','reasonCounts');
    }
}
