<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class IntelligenceRepository
{
    private PDO $db;
    public function __construct() { $this->db = Database::connection(); }

    public function userInputs(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT u.id, u.created_at, u.updated_at, u.profile_quality_score, u.profile_quality_level, u.trust_score, u.trust_level,
                p.is_complete, p.completed_steps, p.skipped_optional_count, p.fatigue_score, p.started_at, p.completed_at, p.engagement_metadata_json, p.updated_at AS onboarding_updated_at
            FROM users u
            LEFT JOIN user_onboarding_progress p ON p.user_id=u.id
            WHERE u.id=? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch() ?: [];

        $questionStats = $this->db->query("SELECT COUNT(*) AS total_questions, SUM(is_required=1) AS required_questions, SUM(is_required=0) AS optional_questions, SUM(is_matchable=1) AS matchable_questions FROM questions WHERE is_active=1 AND deleted_at IS NULL")->fetch() ?: [];
        $answerStmt = $this->db->prepare("SELECT COUNT(DISTINCT ua.question_id) AS answered_questions,
                SUM(q.is_required=1) AS answered_required,
                SUM(q.is_matchable=1) AS answered_matchable,
                SUM(q.is_required=0) AS answered_optional,
                COUNT(DISTINCT q.answer_type) AS answer_type_count,
                COUNT(DISTINCT NULLIF(LOWER(TRIM(ua.answer_text)), '')) AS distinct_text_answer_count,
                MAX(ua.updated_at) AS last_answered_at,
                GROUP_CONCAT(CASE WHEN q.answer_type IN ('text','textarea','range') THEN LOWER(TRIM(ua.answer_text)) ELSE NULL END SEPARATOR '||') AS text_answers
            FROM user_answers ua
            JOIN questions q ON q.id=ua.question_id
            WHERE ua.user_id=? AND q.is_active=1 AND q.deleted_at IS NULL");
        $answerStmt->execute([$userId]);
        $answers = $answerStmt->fetch() ?: [];

        $goalStmt = $this->db->prepare('SELECT COUNT(*) FROM user_goals ug JOIN goals g ON g.id=ug.goal_id WHERE ug.user_id=? AND g.is_active=1 AND g.deleted_at IS NULL');
        $goalStmt->execute([$userId]);
        $goals = (int)$goalStmt->fetchColumn();

        $actionStmt = $this->db->prepare("SELECT action, COUNT(*) AS total, SUM(created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)) AS last_hour, SUM(created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)) AS last_day FROM match_actions WHERE actor_user_id=? GROUP BY action");
        $actionStmt->execute([$userId]);
        $actions = [];
        foreach ($actionStmt->fetchAll() as $row) { $actions[$row['action']] = $row; }

        $reportMade = $this->count('SELECT COUNT(*) FROM reports WHERE reporter_user_id=?', $userId);
        $reportReceived = $this->count('SELECT COUNT(*) FROM reports WHERE reported_user_id=?', $userId);
        $messageCount = $this->count('SELECT COUNT(*) FROM messages WHERE sender_user_id=? AND deleted_at IS NULL', $userId);
        $flagCount = $this->count('SELECT COUNT(*) FROM message_flags mf JOIN messages m ON m.id=mf.message_id WHERE m.sender_user_id=?', $userId);
        $revealRequested = $this->count('SELECT COUNT(*) FROM reveal_requests WHERE requester_user_id=?', $userId);
        $revealIncoming = $this->count('SELECT COUNT(*) FROM reveal_requests WHERE target_user_id=?', $userId);
        $revealApproved = $this->count("SELECT COUNT(*) FROM reveal_requests WHERE target_user_id=? AND status='approved'", $userId);
        $revealRejected = $this->count("SELECT COUNT(*) FROM reveal_requests WHERE target_user_id=? AND status='rejected'", $userId);
        $blocksMade = $this->count('SELECT COUNT(*) FROM blocks WHERE blocker_user_id=? AND deleted_at IS NULL', $userId);
        $blocksReceived = $this->count('SELECT COUNT(*) FROM blocks WHERE blocked_user_id=? AND deleted_at IS NULL', $userId);

        $messageStmt = $this->db->prepare("SELECT COUNT(*) AS total_messages, COUNT(DISTINCT LEFT(LOWER(TRIM(body)), 120)) AS distinct_message_starts, AVG(CHAR_LENGTH(TRIM(body))) AS avg_message_length FROM messages WHERE sender_user_id=? AND deleted_at IS NULL");
        $messageStmt->execute([$userId]);
        $messageStats = $messageStmt->fetch() ?: [];

        return compact('user', 'questionStats', 'answers', 'goals', 'actions', 'reportMade', 'reportReceived', 'messageCount', 'messageStats', 'flagCount', 'revealRequested', 'revealIncoming', 'revealApproved', 'revealRejected', 'blocksMade', 'blocksReceived');
    }

    private function count(string $sql, int $userId): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function saveScores(int $userId, array $profile, array $trust, array $signals): void
    {
        $stmt = $this->db->prepare('UPDATE users SET profile_quality_score=?, profile_quality_level=?, last_quality_calculated_at=NOW(), trust_score=?, trust_level=?, trust_flags_json=?, quality_flags_json=?, moderation_signals_json=? WHERE id=?');
        $stmt->execute([
            $profile['score'], $profile['level'], $trust['score'], $trust['level'],
            json_encode($trust['flags'], JSON_UNESCAPED_UNICODE),
            json_encode($profile['flags'], JSON_UNESCAPED_UNICODE),
            json_encode($signals, JSON_UNESCAPED_UNICODE),
            $userId,
        ]);
    }

    public function summaryForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT profile_quality_score, profile_quality_level, last_quality_calculated_at, trust_score, trust_level FROM users WHERE id=? LIMIT 1');
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public function overview(): array
    {
        return $this->db->query("SELECT u.id, u.first_name, u.last_name, u.email, u.created_at, u.profile_quality_score, u.profile_quality_level,
                u.trust_score, u.trust_level, u.quality_flags_json, u.trust_flags_json, u.moderation_signals_json,
                p.is_complete, p.skipped_optional_count, p.fatigue_score, p.updated_at AS onboarding_updated_at,
                (SELECT COUNT(*) FROM reports r WHERE r.reported_user_id=u.id) AS reports_received,
                (SELECT COUNT(*) FROM messages msg WHERE msg.sender_user_id=u.id AND msg.moderation_status='flagged') AS flagged_messages
            FROM users u
            JOIN roles r ON r.id=u.role_id AND r.name='user'
            LEFT JOIN user_onboarding_progress p ON p.user_id=u.id
            ORDER BY u.profile_quality_score ASC, u.trust_score ASC, u.created_at DESC
            LIMIT 200")->fetchAll();
    }
}
