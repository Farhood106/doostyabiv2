<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class RevealRepository
{
    private const ALLOWED_FIELDS = ['display_name', 'city', 'selected_goals_summary'];
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function activeTypes(): array
    {
        return $this->db->query('SELECT * FROM reveal_types WHERE is_active=1 ORDER BY sort_order, title')->fetchAll();
    }

    public function allTypes(): array
    {
        return $this->db->query('SELECT * FROM reveal_types ORDER BY sort_order, title')->fetchAll();
    }

    public function saveType(array $data): int
    {
        $field = (string)($data['reveal_field_key'] ?? '');
        if (!in_array($field, self::ALLOWED_FIELDS, true)) {
            return 0;
        }
        $slug = strtolower(trim((string)($data['slug'] ?? '')));
        $slug = preg_replace('/[^a-z0-9_\-]+/', '-', $slug) ?: '';
        if ($slug === '' || trim((string)($data['title'] ?? '')) === '') {
            return 0;
        }
        $privacy = in_array(($data['privacy_level'] ?? ''), ['low','medium','high'], true) ? $data['privacy_level'] : 'medium';
        if (!empty($data['id'])) {
            $stmt = $this->db->prepare('UPDATE reveal_types SET title=?, slug=?, description=?, reveal_field_key=?, privacy_level=?, requires_mutual_approval=?, is_active=?, sort_order=? WHERE id=?');
            $stmt->execute([trim((string)$data['title']), $slug, trim((string)($data['description'] ?? '')) ?: null, $field, $privacy, !empty($data['requires_mutual_approval']) ? 1 : 0, !empty($data['is_active']) ? 1 : 0, (int)($data['sort_order'] ?? 0), (int)$data['id']]);
            return (int)$data['id'];
        }
        $stmt = $this->db->prepare('INSERT INTO reveal_types (title, slug, description, reveal_field_key, privacy_level, requires_mutual_approval, is_active, sort_order) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([trim((string)$data['title']), $slug, trim((string)($data['description'] ?? '')) ?: null, $field, $privacy, !empty($data['requires_mutual_approval']) ? 1 : 0, !empty($data['is_active']) ? 1 : 0, (int)($data['sort_order'] ?? 0)]);
        return (int)$this->db->lastInsertId();
    }

    public function findChatForParticipant(int $chatId, int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT c.*, m.user_one_id, m.user_two_id, m.match_status
            FROM chats c
            JOIN chat_participants cp ON cp.chat_id=c.id AND cp.user_id=?
            JOIN matches m ON m.id=c.match_id
            WHERE c.id=? LIMIT 1");
        $stmt->execute([$userId, $chatId]);
        return $stmt->fetch() ?: null;
    }

    public function createRequest(int $chatId, int $requesterId, int $typeId, string $message): bool
    {
        $chat = $this->findChatForParticipant($chatId, $requesterId);
        if (!$chat || $chat['status'] !== 'open' || $chat['match_status'] !== 'mutual') {
            return false;
        }
        $targetId = ((int)$chat['user_one_id'] === $requesterId) ? (int)$chat['user_two_id'] : (int)$chat['user_one_id'];
        $type = $this->findActiveType($typeId);
        if (!$type) {
            return false;
        }
        $pending = $this->db->prepare("SELECT COUNT(*) FROM reveal_requests WHERE chat_id=? AND requester_user_id=? AND target_user_id=? AND reveal_type_id=? AND status='pending'");
        $pending->execute([$chatId, $requesterId, $targetId, $typeId]);
        if ((int)$pending->fetchColumn() > 0) {
            return false;
        }
        $stmt = $this->db->prepare("INSERT INTO reveal_requests (match_id, chat_id, requester_user_id, target_user_id, reveal_type_id, status, request_message, requested_at, expires_at) VALUES (?,?,?,?,?,'pending',?,CURRENT_TIMESTAMP,DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 14 DAY))");
        $stmt->execute([(int)$chat['match_id'], $chatId, $requesterId, $targetId, $typeId, trim($message) ?: null]);
        return true;
    }

    public function requestsForChat(int $chatId, int $userId): array
    {
        if (!$this->findChatForParticipant($chatId, $userId)) { return []; }
        $stmt = $this->db->prepare("SELECT rr.*, rt.title, rt.description, rt.reveal_field_key, rt.privacy_level,
                CASE WHEN rr.requester_user_id=? THEN 'outgoing' ELSE 'incoming' END AS direction
            FROM reveal_requests rr
            JOIN reveal_types rt ON rt.id=rr.reveal_type_id
            WHERE rr.chat_id=?
            ORDER BY rr.requested_at DESC, rr.id DESC");
        $stmt->execute([$userId, $chatId]);
        return $stmt->fetchAll();
    }

    public function respond(int $requestId, int $targetId, string $status, string $note): ?int
    {
        if (!in_array($status, ['approved','rejected'], true)) { return null; }
        $stmt = $this->db->prepare("SELECT rr.*, rt.reveal_field_key FROM reveal_requests rr JOIN reveal_types rt ON rt.id=rr.reveal_type_id WHERE rr.id=? AND rr.target_user_id=? AND rr.requester_user_id<>? AND rr.status='pending' AND (rr.expires_at IS NULL OR rr.expires_at > CURRENT_TIMESTAMP) LIMIT 1");
        $stmt->execute([$requestId, $targetId, $targetId]);
        $request = $stmt->fetch();
        if (!$request) { return null; }
        $this->db->prepare('UPDATE reveal_requests SET status=?, response_note=?, responded_at=CURRENT_TIMESTAMP WHERE id=?')->execute([$status, trim($note) ?: null, $requestId]);
        if ($status !== 'approved') { return $requestId; }
        $value = $this->resolveRevealValue($targetId, (string)$request['reveal_field_key']);
        $snap = $this->db->prepare("INSERT INTO match_visibility_snapshots (match_id, chat_id, reveal_request_id, viewer_user_id, subject_user_id, reveal_type_id, reveal_field_key, revealed_value, status, visible_at, expires_at) VALUES (?,?,?,?,?,?,?,?,'approved',CURRENT_TIMESTAMP,?) ON DUPLICATE KEY UPDATE revealed_value=VALUES(revealed_value), status='approved', visible_at=CURRENT_TIMESTAMP, expires_at=VALUES(expires_at)");
        $snap->execute([(int)$request['match_id'], (int)$request['chat_id'], $requestId, (int)$request['requester_user_id'], $targetId, (int)$request['reveal_type_id'], (string)$request['reveal_field_key'], $value, $request['expires_at']]);
        return $requestId;
    }

    public function approvedSnapshotsForChat(int $chatId, int $viewerId): array
    {
        $stmt = $this->db->prepare("SELECT mvs.*, rt.title, rt.description, rt.privacy_level
            FROM match_visibility_snapshots mvs
            JOIN reveal_types rt ON rt.id=mvs.reveal_type_id
            JOIN chat_participants cp ON cp.chat_id=mvs.chat_id AND cp.user_id=mvs.viewer_user_id
            WHERE mvs.chat_id=? AND mvs.viewer_user_id=? AND mvs.status='approved'
            ORDER BY mvs.visible_at DESC");
        $stmt->execute([$chatId, $viewerId]);
        return $stmt->fetchAll();
    }

    public function requestsForAdmin(array $filters): array
    {
        $where = [];
        $params = [];
        if (!empty($filters['status'])) { $where[] = 'rr.status=?'; $params[] = $filters['status']; }
        if (!empty($filters['type_id'])) { $where[] = 'rr.reveal_type_id=?'; $params[] = (int)$filters['type_id']; }
        if (!empty($filters['user_id'])) { $where[] = '(rr.requester_user_id=? OR rr.target_user_id=?)'; $params[] = (int)$filters['user_id']; $params[] = (int)$filters['user_id']; }
        $sql = "SELECT rr.*, rt.title, rt.reveal_field_key, req.first_name AS requester_name, tgt.first_name AS target_name
            FROM reveal_requests rr
            JOIN reveal_types rt ON rt.id=rr.reveal_type_id
            JOIN users req ON req.id=rr.requester_user_id
            JOIN users tgt ON tgt.id=rr.target_user_id" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY rr.requested_at DESC LIMIT 200';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function findActiveType(int $typeId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM reveal_types WHERE id=? AND is_active=1 LIMIT 1');
        $stmt->execute([$typeId]);
        $type = $stmt->fetch();
        if (!$type || !in_array($type['reveal_field_key'], self::ALLOWED_FIELDS, true)) { return null; }
        return $type;
    }

    private function resolveRevealValue(int $subjectId, string $field): string
    {
        if ($field === 'display_name') {
            $stmt = $this->db->prepare('SELECT first_name, last_name FROM users WHERE id=? LIMIT 1');
            $stmt->execute([$subjectId]);
            $user = $stmt->fetch() ?: [];
            return trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'Not provided';
        }
        if ($field === 'selected_goals_summary') {
            $stmt = $this->db->prepare('SELECT g.title FROM goals g JOIN user_goals ug ON ug.goal_id=g.id WHERE ug.user_id=? AND g.deleted_at IS NULL ORDER BY g.sort_order, g.title');
            $stmt->execute([$subjectId]);
            $goals = array_column($stmt->fetchAll(), 'title');
            return $goals ? implode(', ', $goals) : 'Not provided';
        }
        if ($field === 'city') {
            $stmt = $this->db->prepare("SELECT c.name, p.name AS province_name FROM user_answers ua JOIN questions q ON q.id=ua.question_id JOIN user_answer_cities uac ON uac.user_answer_id=ua.id JOIN cities c ON c.id=uac.city_id JOIN provinces p ON p.id=c.province_id WHERE ua.user_id=? AND q.answer_type='city_single' ORDER BY q.sort_order, ua.updated_at DESC LIMIT 1");
            $stmt->execute([$subjectId]);
            $city = $stmt->fetch();
            return $city ? ($city['name'] . ', ' . $city['province_name']) : 'Not provided';
        }
        return 'Not provided';
    }
}
