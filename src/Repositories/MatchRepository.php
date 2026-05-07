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

    public function candidateUsers(int $userId, bool $recalculate = false): array
    {
        $existingSql = $recalculate ? '' : 'AND NOT EXISTS (SELECT 1 FROM matches m WHERE (m.user_one_id=LEAST(?, u.id) AND m.user_two_id=GREATEST(?, u.id)))';
        $sql = "SELECT DISTINCT u.* FROM users u
            JOIN roles r ON r.id=u.role_id AND r.name='user'
            JOIN user_goals g1 ON g1.user_id=?
            JOIN user_goals g2 ON g2.user_id=u.id AND g2.goal_id=g1.goal_id
            WHERE u.is_active=1 AND u.id<>? $existingSql
            ORDER BY u.id";
        $params = [$userId, $userId];
        if (!$recalculate) { $params[] = $userId; $params[] = $userId; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
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
        $stmt = $this->db->prepare('INSERT INTO match_cards (match_id, viewer_user_id, target_user_id, title, summary, strengths_text, cautions_text, compatibility_label, privacy_level, generated_payload_json) VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE title=VALUES(title), summary=VALUES(summary), strengths_text=VALUES(strengths_text), cautions_text=VALUES(cautions_text), compatibility_label=VALUES(compatibility_label), generated_payload_json=VALUES(generated_payload_json)');
        $stmt->execute([$matchId, $viewerId, $targetId, $card['title'], $card['summary'], $card['strengths'], $card['cautions'], $card['label'], 'anonymous', json_encode($card['payload'])]);
    }

    public function cardsForUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT mc.*, m.compatibility_score, m.confidence_score, m.match_status, ma.action AS viewer_action
            FROM match_cards mc JOIN matches m ON m.id=mc.match_id
            LEFT JOIN match_actions ma ON ma.match_id=mc.match_id AND ma.actor_user_id=mc.viewer_user_id
            WHERE mc.viewer_user_id=? AND mc.privacy_level='anonymous' AND m.match_status IN ('suggested','mutual')
            ORDER BY m.compatibility_score DESC, mc.updated_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function recordAction(int $matchId, int $actorId, string $action): void
    {
        $targetId = $this->targetForActor($matchId, $actorId);
        if (!$targetId || !in_array($action, ['interested','pass'], true)) { return; }
        $stmt = $this->db->prepare('INSERT INTO match_actions (match_id, actor_user_id, target_user_id, action) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE action=VALUES(action), created_at=CURRENT_TIMESTAMP');
        $stmt->execute([$matchId, $actorId, $targetId, $action]);
        if ($action === 'pass') { $this->db->prepare("UPDATE matches SET match_status='passed' WHERE id=?")->execute([$matchId]); return; }
        $check = $this->db->prepare("SELECT COUNT(*) FROM match_actions WHERE match_id=? AND action='interested'");
        $check->execute([$matchId]);
        if ((int)$check->fetchColumn() >= 2) { $this->db->prepare("UPDATE matches SET match_status='mutual' WHERE id=?")->execute([$matchId]); }
    }

    public function targetForActor(int $matchId, int $actorId): ?int
    {
        $stmt = $this->db->prepare('SELECT user_one_id,user_two_id FROM matches WHERE id=? AND (user_one_id=? OR user_two_id=?)');
        $stmt->execute([$matchId, $actorId, $actorId]);
        $m = $stmt->fetch();
        if (!$m) { return null; }
        return ((int)$m['user_one_id'] === $actorId) ? (int)$m['user_two_id'] : (int)$m['user_one_id'];
    }

    public function allMatches(): array
    {
        return $this->db->query('SELECT m.*, u1.first_name AS user_one_name, u2.first_name AS user_two_name FROM matches m JOIN users u1 ON u1.id=m.user_one_id JOIN users u2 ON u2.id=m.user_two_id ORDER BY m.updated_at DESC LIMIT 200')->fetchAll();
    }
    public function scoresForMatch(int $matchId): array { $stmt=$this->db->prepare('SELECT * FROM match_scores WHERE match_id=? ORDER BY score_type'); $stmt->execute([$matchId]); return $stmt->fetchAll(); }
    public function explanationForMatch(int $matchId): ?array { $stmt=$this->db->prepare('SELECT * FROM match_explanations WHERE match_id=?'); $stmt->execute([$matchId]); return $stmt->fetch() ?: null; }
}
