<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class ReportRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function createForMatch(int $matchId, int $reporterId, string $type, string $reason, string $description): bool
    {
        $stmt = $this->db->prepare('SELECT id,user_one_id,user_two_id FROM matches WHERE id=? AND (user_one_id=? OR user_two_id=?) LIMIT 1');
        $stmt->execute([$matchId, $reporterId, $reporterId]);
        $match = $stmt->fetch();
        if (!$match) { return false; }
        $reportedId = ((int)$match['user_one_id'] === $reporterId) ? (int)$match['user_two_id'] : (int)$match['user_one_id'];
        return $this->insertReport($reporterId, $reportedId, $matchId, null, null, $type, $reason, $description);
    }

    public function createForChat(int $chatId, int $reporterId, string $type, string $reason, string $description): bool
    {
        $stmt = $this->db->prepare('SELECT c.id AS chat_id,c.match_id,m.user_one_id,m.user_two_id FROM chats c JOIN chat_participants cp ON cp.chat_id=c.id AND cp.user_id=? JOIN matches m ON m.id=c.match_id WHERE c.id=? LIMIT 1');
        $stmt->execute([$reporterId, $chatId]);
        $chat = $stmt->fetch();
        if (!$chat) { return false; }
        $reportedId = ((int)$chat['user_one_id'] === $reporterId) ? (int)$chat['user_two_id'] : (int)$chat['user_one_id'];
        return $this->insertReport($reporterId, $reportedId, (int)$chat['match_id'], $chatId, null, $type, $reason, $description);
    }

    public function createForMessage(int $messageId, int $reporterId, string $type, string $reason, string $description): bool
    {
        $stmt = $this->db->prepare('SELECT msg.id,msg.chat_id,msg.sender_user_id,c.match_id FROM messages msg JOIN chats c ON c.id=msg.chat_id JOIN chat_participants cp ON cp.chat_id=msg.chat_id AND cp.user_id=? WHERE msg.id=? AND msg.deleted_at IS NULL LIMIT 1');
        $stmt->execute([$reporterId, $messageId]);
        $message = $stmt->fetch();
        if (!$message || (int)$message['sender_user_id'] === $reporterId) { return false; }
        $this->db->prepare('INSERT INTO message_flags (message_id, reporter_user_id, reason_text) VALUES (?,?,?) ON DUPLICATE KEY UPDATE reason_text=VALUES(reason_text), created_at=CURRENT_TIMESTAMP')->execute([$messageId, $reporterId, trim($reason . ' ' . $description) ?: null]);
        $this->db->prepare("UPDATE messages SET moderation_status='flagged' WHERE id=?")->execute([$messageId]);
        return $this->insertReport($reporterId, (int)$message['sender_user_id'], (int)$message['match_id'], (int)$message['chat_id'], $messageId, $type, $reason, $description);
    }

    public function allForAdmin(array $filters = []): array
    {
        $where = [];
        $params = [];
        if (!empty($filters['status'])) { $where[] = 'r.status=?'; $params[] = $filters['status']; }
        if (!empty($filters['report_type'])) { $where[] = 'r.report_type=?'; $params[] = $filters['report_type']; }
        if (!empty($filters['priority'])) { $where[] = 'r.priority=?'; $params[] = $filters['priority']; }
        $sql = "SELECT r.*, reporter.first_name AS reporter_name, reported.first_name AS reported_name, admin.first_name AS assigned_admin_name
            FROM reports r
            JOIN users reporter ON reporter.id=r.reporter_user_id
            LEFT JOIN users reported ON reported.id=r.reported_user_id
            LEFT JOIN users admin ON admin.id=r.assigned_admin_id" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY r.created_at DESC LIMIT 200';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findForAdmin(int $reportId): ?array
    {
        $stmt = $this->db->prepare("SELECT r.*, reporter.first_name AS reporter_name, reporter.email AS reporter_email, reported.first_name AS reported_name, reported.email AS reported_email, admin.first_name AS assigned_admin_name,
                msg.body AS message_body, msg.created_at AS message_created_at, c.status AS chat_status, m.match_status, m.compatibility_score
            FROM reports r
            JOIN users reporter ON reporter.id=r.reporter_user_id
            LEFT JOIN users reported ON reported.id=r.reported_user_id
            LEFT JOIN users admin ON admin.id=r.assigned_admin_id
            LEFT JOIN messages msg ON msg.id=r.message_id
            LEFT JOIN chats c ON c.id=r.chat_id
            LEFT JOIN matches m ON m.id=r.match_id
            WHERE r.id=? LIMIT 1");
        $stmt->execute([$reportId]);
        return $stmt->fetch() ?: null;
    }

    public function updateModeration(int $reportId, int $adminId, string $status, string $priority, string $note, bool $assignToMe): bool
    {
        if (!in_array($status, ['open','reviewing','resolved','dismissed'], true)) { return false; }
        if (!in_array($priority, ['low','normal','high','urgent'], true)) { $priority = 'normal'; }
        $resolvedAt = in_array($status, ['resolved','dismissed'], true) ? date('Y-m-d H:i:s') : null;
        $assigned = $assignToMe ? $adminId : null;
        $stmt = $this->db->prepare('UPDATE reports SET status=?, priority=?, admin_resolution_note=?, assigned_admin_id=COALESCE(?, assigned_admin_id), resolved_at=? WHERE id=?');
        $stmt->execute([$status, $priority, trim($note) ?: null, $assigned, $resolvedAt, $reportId]);
        if ($resolvedAt && $this->tableExists('notifications')) {
            $report = $this->findForAdmin($reportId);
            if ($report) { $this->insertNotification((int)$report['reporter_user_id'], 'Report update', 'Your safety report has been reviewed. Thank you for helping keep Doostyabi safe.'); }
        }
        return $stmt->rowCount() > 0;
    }

    private function insertReport(int $reporterId, ?int $reportedId, ?int $matchId, ?int $chatId, ?int $messageId, string $type, string $reason, string $description): bool
    {
        $type = $this->safeChoice($type, ['match','chat','message','user','spam','harassment','safety','other'], 'other');
        $reason = $this->safeChoice($reason, ['harassment','spam','fake_profile','unsafe_behavior','privacy','inappropriate_content','other'], 'other');
        $stmt = $this->db->prepare("INSERT INTO reports (reporter_user_id, reported_user_id, match_id, chat_id, message_id, report_type, report_reason, description, status, priority) VALUES (?,?,?,?,?,?,?,?,'open','normal')");
        $stmt->execute([$reporterId, $reportedId, $matchId, $chatId, $messageId, $type, $reason, trim($description) ?: null]);
        if ($this->tableExists('notifications')) {
            $this->notifyAdmins('New safety report', 'A new safety report needs moderation review.');
        }
        return true;
    }

    private function safeChoice(string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function tableExists(string $table): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
        $stmt->execute([$table]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function notifyAdmins(string $title, string $body): void
    {
        $admins = $this->db->query("SELECT u.id FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='admin' AND u.is_active=1")->fetchAll();
        foreach ($admins as $admin) { $this->insertNotification((int)$admin['id'], $title, $body); }
    }

    private function insertNotification(int $userId, string $title, string $body): void
    {
        try {
            $this->db->prepare('INSERT INTO notifications (user_id, title, body, created_at) VALUES (?,?,?,CURRENT_TIMESTAMP)')->execute([$userId, $title, $body]);
        } catch (\Throwable $e) {
            // Optional notifications table may use a different shape on some installs; ignore safely.
        }
    }
}
