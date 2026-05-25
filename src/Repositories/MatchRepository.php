<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class MatchRepository
{
    private PDO $db;
    public function __construct() { $this->db = Database::connection(); }

    public function activeMembers(): array
    {
        return $this->db->query("SELECT u.* FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='user' AND u.is_active=1 ORDER BY u.id")->fetchAll();
    }

    public function candidateUsers(int $userId, bool $recalculate = false, bool $allowBroad = false): array
    {
        $existingSql = $recalculate ? '' : 'AND NOT EXISTS (SELECT 1 FROM matches m WHERE (m.user_one_id=LEAST(?, u.id) AND m.user_two_id=GREATEST(?, u.id)))';
        $goalJoin = $allowBroad ? '' : 'JOIN user_goals g1 ON g1.user_id=? JOIN user_goals g2 ON g2.user_id=u.id AND g2.goal_id=g1.goal_id';
        $sql = "SELECT DISTINCT u.* FROM users u
            JOIN roles r ON r.id=u.role_id AND r.name='user'
            $goalJoin
            WHERE u.is_active=1 AND u.id<>?
            AND NOT EXISTS (SELECT 1 FROM blocks b WHERE b.deleted_at IS NULL AND ((b.blocker_user_id=? AND b.blocked_user_id=u.id) OR (b.blocker_user_id=u.id AND b.blocked_user_id=?)))
            AND NOT EXISTS (SELECT 1 FROM privacy_shields ps WHERE ps.user_id=? AND ps.deleted_at IS NULL AND ps.hashed_value=SHA2(LOWER(TRIM(u.email)),256))
            AND NOT EXISTS (SELECT 1 FROM privacy_shields ps2 WHERE ps2.user_id=u.id AND ps2.deleted_at IS NULL AND ps2.hashed_value=SHA2(LOWER(TRIM((SELECT email FROM users WHERE id=? LIMIT 1))),256)) $existingSql
            ORDER BY u.profile_quality_score DESC, u.trust_score DESC, u.updated_at DESC, u.id";
        $params = $allowBroad ? [$userId, $userId, $userId, $userId, $userId] : [$userId, $userId, $userId, $userId, $userId, $userId];
        if (!$recalculate) { $params[] = $userId; $params[] = $userId; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }


    public function answerReadiness(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT
                SUM(q.is_required=1) AS required_total,
                SUM(q.is_required=1 AND ua.id IS NOT NULL) AS required_answered,
                SUM(q.is_matchable=1) AS matchable_total,
                SUM(q.is_matchable=1 AND ua.id IS NOT NULL) AS matchable_answered
            FROM questions q
            LEFT JOIN user_answers ua ON ua.question_id=q.id AND ua.user_id=?
            WHERE q.is_active=1 AND q.deleted_at IS NULL");
        $stmt->execute([$userId]);
        $row = $stmt->fetch() ?: [];
        return [
            'required_total' => (int)($row['required_total'] ?? 0),
            'required_answered' => (int)($row['required_answered'] ?? 0),
            'matchable_total' => (int)($row['matchable_total'] ?? 0),
            'matchable_answered' => (int)($row['matchable_answered'] ?? 0),
        ];
    }

    public function cardAvailabilityForUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT
                SUM(CASE WHEN m.match_status IN ('suggested','mutual') AND COALESCE(ma.action,'') NOT IN ('pass','block') AND (mc.hidden_until IS NULL OR mc.hidden_until < NOW()) THEN 1 ELSE 0 END) AS visible_cards,
                SUM(CASE WHEN m.match_status IN ('suggested','mutual') AND mc.hidden_until >= NOW() THEN 1 ELSE 0 END) AS hidden_cards,
                SUM(CASE WHEN COALESCE(ma.action,'')='pass' OR m.match_status='passed' THEN 1 ELSE 0 END) AS passed_cards,
                SUM(CASE WHEN m.match_status='mutual' THEN 1 ELSE 0 END) AS mutual_cards,
                COUNT(*) AS total_cards
            FROM match_cards mc
            JOIN matches m ON m.id=mc.match_id
            LEFT JOIN match_actions ma ON ma.match_id=mc.match_id AND ma.actor_user_id=mc.viewer_user_id
            WHERE mc.viewer_user_id=?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch() ?: [];
        return [
            'visible_cards' => (int)($row['visible_cards'] ?? 0),
            'hidden_cards' => (int)($row['hidden_cards'] ?? 0),
            'passed_cards' => (int)($row['passed_cards'] ?? 0),
            'mutual_cards' => (int)($row['mutual_cards'] ?? 0),
            'total_cards' => (int)($row['total_cards'] ?? 0),
        ];
    }

    public function goalsForUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT g.id, g.title FROM goals g JOIN user_goals ug ON ug.goal_id=g.id WHERE ug.user_id=? AND g.is_active=1 AND g.deleted_at IS NULL');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function matchableAnswers(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT q.id AS question_id, q.title, q.answer_type, q.match_weight, q.match_rule, q.privacy_level,
            ua.answer_text, ua.answer_number, ua.answer_boolean, ua.answer_date,
            GROUP_CONCAT(DISTINCT qo.value ORDER BY qo.sort_order SEPARATOR '|') AS option_values,
            GROUP_CONCAT(DISTINCT uac.city_id ORDER BY uac.city_id SEPARATOR '|') AS city_values
            FROM questions q
            JOIN user_answers ua ON ua.question_id=q.id AND ua.user_id=?
            LEFT JOIN user_answer_options uao ON uao.user_answer_id=ua.id
            LEFT JOIN question_options qo ON qo.id=uao.question_option_id AND qo.deleted_at IS NULL
            LEFT JOIN user_answer_cities uac ON uac.user_answer_id=ua.id
            WHERE q.is_active=1 AND q.is_matchable=1 AND q.deleted_at IS NULL
            GROUP BY q.id, q.title, q.answer_type, q.match_weight, q.match_rule, q.privacy_level, ua.answer_text, ua.answer_number, ua.answer_boolean, ua.answer_date");
        $stmt->execute([$userId]);
        $answers = [];
        foreach ($stmt->fetchAll() as $row) { $answers[(int)$row['question_id']] = $row; }
        return $answers;
    }

    public function cityIdsForUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT DISTINCT uac.city_id FROM user_answer_cities uac JOIN user_answers ua ON ua.id=uac.user_answer_id JOIN questions q ON q.id=ua.question_id WHERE ua.user_id=? AND q.answer_type IN ('city_single','city_multi') AND q.is_active=1 AND q.deleted_at IS NULL");
        $stmt->execute([$userId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'city_id'));
    }

    public function upsertMatch(int $userA, int $userB, float $score, float $confidence): int
    {
        $one = min($userA, $userB); $two = max($userA, $userB);
        $stmt = $this->db->prepare('INSERT INTO matches (user_one_id,user_two_id,compatibility_score,confidence_score,generated_at) VALUES (?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE compatibility_score=VALUES(compatibility_score), confidence_score=VALUES(confidence_score), generated_at=NOW(), match_status=IF(match_status="archived","suggested",match_status)');
        $stmt->execute([$one, $two, $score, $confidence]);
        $id = (int)$this->db->lastInsertId();
        if ($id === 0) { $find = $this->db->prepare('SELECT id FROM matches WHERE user_one_id=? AND user_two_id=?'); $find->execute([$one, $two]); $id = (int)$find->fetchColumn(); }
        return $id;
    }

    public function replaceScores(int $matchId, array $scores): void
    {
        $this->db->prepare('DELETE FROM match_scores WHERE match_id=?')->execute([$matchId]);
        $stmt = $this->db->prepare('INSERT INTO match_scores (match_id, score_type, score_value, weight, details) VALUES (?,?,?,?,?)');
        foreach ($scores as $score) { $stmt->execute([$matchId, $score['type'], $score['value'], $score['weight'] ?? 1, $score['details'] ?? null]); }
    }

    public function saveExplanation(int $matchId, array $explanation): void
    {
        $stmt = $this->db->prepare('INSERT INTO match_explanations (match_id, why_matched, strongest_points, caution_points) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE why_matched=VALUES(why_matched), strongest_points=VALUES(strongest_points), caution_points=VALUES(caution_points)');
        $stmt->execute([$matchId, $explanation['why'] ?? '', $explanation['strengths'] ?? '', $explanation['cautions'] ?? '']);
    }

    public function saveCard(int $matchId, int $viewerId, int $targetId, array $card): void
    {
        $stmt = $this->db->prepare('INSERT INTO match_cards (match_id, viewer_user_id, target_user_id, title, summary, narrative, strengths_text, cautions_text, compatibility_label, privacy_level, freshness_score, generated_payload_json) VALUES (?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE title=VALUES(title), summary=VALUES(summary), narrative=VALUES(narrative), strengths_text=VALUES(strengths_text), cautions_text=VALUES(cautions_text), compatibility_label=VALUES(compatibility_label), freshness_score=VALUES(freshness_score), generated_payload_json=VALUES(generated_payload_json)');
        $stmt->execute([$matchId, $viewerId, $targetId, $card['title'], $card['summary'], $card['narrative'] ?? $card['summary'], $card['strengths'], $card['cautions'], $card['label'], 'anonymous', $card['freshness_score'] ?? 50, json_encode($card['payload'], JSON_UNESCAPED_UNICODE)]);
    }

    public function cardsForUser(int $userId, bool $showLowConfidence = true): array
    {
        $this->rotateIgnoredCards($userId);
        $confidenceSql = $showLowConfidence ? '' : ' AND m.confidence_score >= 45';
        $stmt = $this->db->prepare("SELECT mc.*, m.compatibility_score, m.confidence_score, m.match_status, ma.action AS viewer_action
            FROM match_cards mc JOIN matches m ON m.id=mc.match_id
            LEFT JOIN match_actions ma ON ma.match_id=mc.match_id AND ma.actor_user_id=mc.viewer_user_id
            WHERE mc.viewer_user_id=? AND mc.privacy_level='anonymous' AND m.match_status IN ('suggested','mutual')
            AND NOT EXISTS (SELECT 1 FROM blocks b WHERE b.deleted_at IS NULL AND ((b.blocker_user_id=mc.viewer_user_id AND b.blocked_user_id=mc.target_user_id) OR (b.blocker_user_id=mc.target_user_id AND b.blocked_user_id=mc.viewer_user_id)))
            AND COALESCE(ma.action,'') NOT IN ('pass','block')
            AND (mc.hidden_until IS NULL OR mc.hidden_until < NOW())
            $confidenceSql
            ORDER BY CASE WHEN mc.last_shown_at IS NULL THEN 0 ELSE 1 END ASC, mc.freshness_score DESC, mc.shown_count ASC, COALESCE(mc.last_shown_at, '1970-01-01') ASC, m.compatibility_score DESC
            LIMIT 20");
        $stmt->execute([$userId]);
        $cards = $stmt->fetchAll();
        if ($cards) {
            $ids = array_map('intval', array_column($cards, 'id'));
            $in = implode(',', array_fill(0, count($ids), '?'));
            $this->db->prepare("UPDATE match_cards SET last_shown_at=NOW(), shown_count=shown_count+1, freshness_score=GREATEST(5, freshness_score - LEAST(12, shown_count + 1)) WHERE id IN ($in)")->execute($ids);
        }
        return $cards;
    }

    private function rotateIgnoredCards(int $userId): void
    {
        $stmt = $this->db->prepare("UPDATE match_cards mc
            JOIN matches m ON m.id=mc.match_id
            LEFT JOIN match_actions ma ON ma.match_id=mc.match_id AND ma.actor_user_id=mc.viewer_user_id
            SET mc.hidden_until=DATE_ADD(NOW(), INTERVAL 7 DAY), mc.freshness_score=GREATEST(5, mc.freshness_score-15)
            WHERE mc.viewer_user_id=? AND m.match_status='suggested' AND mc.shown_count >= 5
            AND mc.last_shown_at < DATE_SUB(NOW(), INTERVAL 2 DAY)
            AND (mc.hidden_until IS NULL OR mc.hidden_until < NOW())
            AND ma.id IS NULL");
        $stmt->execute([$userId]);
    }

    public function recordAction(int $matchId, int $actorId, string $action): void
    {
        $targetId = $this->targetForActor($matchId, $actorId);
        if (!$targetId || !in_array($action, ['interested','pass','block'], true)) { return; }
        $stmt = $this->db->prepare('INSERT INTO match_actions (match_id, actor_user_id, target_user_id, action) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE action=VALUES(action), created_at=CURRENT_TIMESTAMP');
        $stmt->execute([$matchId, $actorId, $targetId, $action]);
        $this->refreshIntelligenceQuietly($actorId);
        $this->refreshIntelligenceQuietly($targetId);
        if ($action === 'block') {
            $this->blockUser($actorId, $targetId, 'Blocked from match card', 'match_card');
            $this->db->prepare("UPDATE matches SET match_status='blocked' WHERE id=?")->execute([$matchId]);
            (new ChatRepository())->closeForBlockedMatch($matchId, 'Closed automatically because the match was blocked.');
            return;
        }
        if ($action === 'pass') { $this->db->prepare("UPDATE matches SET match_status='passed' WHERE id=? AND match_status<>'mutual'")->execute([$matchId]); $this->db->prepare('UPDATE match_cards SET hidden_until=DATE_ADD(NOW(), INTERVAL 30 DAY), freshness_score=GREATEST(5, freshness_score-20) WHERE match_id=? AND viewer_user_id=?')->execute([$matchId, $actorId]); return; }
        $check = $this->db->prepare("SELECT COUNT(*) FROM match_actions WHERE match_id=? AND action='interested'");
        $check->execute([$matchId]);
        if ((int)$check->fetchColumn() >= 2) {
            $this->db->prepare("UPDATE matches SET match_status='mutual' WHERE id=? AND match_status<>'blocked'")->execute([$matchId]);
            (new ChatRepository())->ensureForMutualMatch($matchId);
        }
    }

    public function targetForActor(int $matchId, int $actorId): ?int
    {
        $stmt = $this->db->prepare('SELECT user_one_id,user_two_id FROM matches WHERE id=? AND (user_one_id=? OR user_two_id=?)');
        $stmt->execute([$matchId, $actorId, $actorId]);
        $m = $stmt->fetch();
        if (!$m) { return null; }
        return ((int)$m['user_one_id'] === $actorId) ? (int)$m['user_two_id'] : (int)$m['user_one_id'];
    }


    public function blockUser(int $blockerId, int $blockedId, ?string $reason = null, string $source = 'member'): void
    {
        if ($blockerId === $blockedId) { return; }
        $stmt = $this->db->prepare('INSERT INTO blocks (blocker_user_id, blocked_user_id, reason_text, source) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE reason_text=VALUES(reason_text), source=VALUES(source), deleted_at=NULL');
        $stmt->execute([$blockerId, $blockedId, $reason, $source]);
        $one = min($blockerId, $blockedId); $two = max($blockerId, $blockedId);
        $stmt = $this->db->prepare("SELECT id FROM matches WHERE user_one_id=? AND user_two_id=? LIMIT 1");
        $stmt->execute([$one, $two]);
        $matchId = (int)($stmt->fetchColumn() ?: 0);
        $this->db->prepare("UPDATE matches SET match_status='blocked' WHERE user_one_id=? AND user_two_id=?")->execute([$one, $two]);
        if ($matchId > 0) { (new ChatRepository())->closeForBlockedMatch($matchId, 'Closed automatically because one participant blocked the other.'); }
        $this->refreshIntelligenceQuietly($blockerId);
        $this->refreshIntelligenceQuietly($blockedId);
    }

    private function refreshIntelligenceQuietly(int $userId): void
    {
        try { (new \App\Services\MatchIntelligenceService())->calculateForUser($userId); } catch (\Throwable $e) { }
    }

    public function blockedPairs(): array
    {
        return $this->db->query("SELECT b.*, u1.first_name AS blocker_name, u2.first_name AS blocked_name FROM blocks b JOIN users u1 ON u1.id=b.blocker_user_id JOIN users u2 ON u2.id=b.blocked_user_id WHERE b.deleted_at IS NULL ORDER BY b.created_at DESC LIMIT 200")->fetchAll();
    }

    public function resetMatch(int $matchId, bool $clearActions = false): void
    {
        $this->db->prepare("UPDATE matches SET match_status='suggested' WHERE id=? AND match_status<>'blocked'")->execute([$matchId]);
        if ($clearActions) { $this->db->prepare('DELETE FROM match_actions WHERE match_id=?')->execute([$matchId]); }
    }

    public function pairForMatch(int $matchId): ?array
    {
        $stmt = $this->db->prepare('SELECT user_one_id,user_two_id FROM matches WHERE id=?');
        $stmt->execute([$matchId]);
        return $stmt->fetch() ?: null;
    }

    public function allMatches(): array
    {
        return $this->db->query('SELECT m.*, u1.first_name AS user_one_name, u1.profile_quality_score AS user_one_quality, u1.trust_score AS user_one_trust, u2.first_name AS user_two_name, u2.profile_quality_score AS user_two_quality, u2.trust_score AS user_two_trust FROM matches m JOIN users u1 ON u1.id=m.user_one_id JOIN users u2 ON u2.id=m.user_two_id ORDER BY m.updated_at DESC LIMIT 200')->fetchAll();
    }

    public function lowConfidenceMatches(): array
    {
        return $this->db->query('SELECT m.*, u1.first_name AS user_one_name, u2.first_name AS user_two_name, COALESCE(card_stats.avg_freshness_score, 0) AS avg_freshness_score, COALESCE(card_stats.total_shown_count, 0) AS total_shown_count FROM matches m JOIN users u1 ON u1.id=m.user_one_id JOIN users u2 ON u2.id=m.user_two_id LEFT JOIN (SELECT match_id, AVG(freshness_score) AS avg_freshness_score, SUM(shown_count) AS total_shown_count FROM match_cards GROUP BY match_id) card_stats ON card_stats.match_id=m.id WHERE m.confidence_score < 50 ORDER BY m.confidence_score ASC, m.updated_at DESC LIMIT 50')->fetchAll();
    }
    public function scoresForMatch(int $matchId): array { $stmt=$this->db->prepare('SELECT * FROM match_scores WHERE match_id=? ORDER BY score_type'); $stmt->execute([$matchId]); return $stmt->fetchAll(); }
    public function explanationForMatch(int $matchId): ?array { $stmt=$this->db->prepare('SELECT * FROM match_explanations WHERE match_id=?'); $stmt->execute([$matchId]); return $stmt->fetch() ?: null; }
}
