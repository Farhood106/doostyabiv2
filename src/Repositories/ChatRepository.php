<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class ChatRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function ensureForMutualMatch(int $matchId): ?int
    {
        $stmt = $this->db->prepare("SELECT id, user_one_id, user_two_id, match_status FROM matches WHERE id=? LIMIT 1");
        $stmt->execute([$matchId]);
        $match = $stmt->fetch();
        if (!$match || $match['match_status'] !== 'mutual') {
            return null;
        }

        $existing = $this->db->prepare('SELECT id FROM chats WHERE match_id=? LIMIT 1');
        $existing->execute([$matchId]);
        $chatId = (int)($existing->fetchColumn() ?: 0);
        if ($chatId > 0) {
            return $chatId;
        }

        $this->db->beginTransaction();
        try {
            $this->db->prepare("INSERT INTO chats (match_id, status) VALUES (?, 'open')")->execute([$matchId]);
            $chatId = (int)$this->db->lastInsertId();
            $participant = $this->db->prepare('INSERT INTO chat_participants (chat_id, user_id) VALUES (?, ?)');
            $participant->execute([$chatId, (int)$match['user_one_id']]);
            $participant->execute([$chatId, (int)$match['user_two_id']]);
            $this->db->commit();
            return $chatId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $existing->execute([$matchId]);
            $chatId = (int)($existing->fetchColumn() ?: 0);
            return $chatId ?: null;
        }
    }

    public function listForUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT c.*, m.match_status, mc.title AS anonymous_title, mc.compatibility_label,
                (SELECT body FROM messages WHERE chat_id=c.id AND deleted_at IS NULL ORDER BY created_at DESC, id DESC LIMIT 1) AS last_message,
                (SELECT created_at FROM messages WHERE chat_id=c.id AND deleted_at IS NULL ORDER BY created_at DESC, id DESC LIMIT 1) AS last_message_at
            FROM chats c
            JOIN chat_participants cp ON cp.chat_id=c.id AND cp.user_id=?
            JOIN matches m ON m.id=c.match_id
            LEFT JOIN match_cards mc ON mc.match_id=c.match_id AND mc.viewer_user_id=? AND mc.privacy_level='anonymous'
            ORDER BY COALESCE(last_message_at, c.updated_at) DESC");
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll();
    }

    public function findForParticipant(int $chatId, int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT c.*, m.match_status, mc.title AS anonymous_title, mc.summary AS anonymous_summary, mc.compatibility_label
            FROM chats c
            JOIN chat_participants cp ON cp.chat_id=c.id AND cp.user_id=?
            JOIN matches m ON m.id=c.match_id
            LEFT JOIN match_cards mc ON mc.match_id=c.match_id AND mc.viewer_user_id=? AND mc.privacy_level='anonymous'
            WHERE c.id=? LIMIT 1");
        $stmt->execute([$userId, $userId, $chatId]);
        $chat = $stmt->fetch();
        return $chat ?: null;
    }

    public function messagesForUser(int $chatId, int $userId): array
    {
        $stmt = $this->db->prepare("SELECT msg.*, CASE WHEN msg.sender_user_id=? THEN 1 ELSE 0 END AS sent_by_me,
                EXISTS(SELECT 1 FROM message_flags mf WHERE mf.message_id=msg.id AND mf.reporter_user_id=?) AS flagged_by_me
            FROM messages msg
            JOIN chat_participants cp ON cp.chat_id=msg.chat_id AND cp.user_id=?
            WHERE msg.chat_id=? AND msg.deleted_at IS NULL
            ORDER BY msg.created_at ASC, msg.id ASC");
        $stmt->execute([$userId, $userId, $userId, $chatId]);
        return $stmt->fetchAll();
    }

    public function sendMessage(int $chatId, int $senderId, string $body): bool
    {
        $body = trim($body);
        if ($body === '' || strlen($body) > 2000) {
            return false;
        }
        if (!$this->canSend($chatId, $senderId)) {
            return false;
        }
        $stmt = $this->db->prepare('INSERT INTO messages (chat_id, sender_user_id, body) VALUES (?, ?, ?)');
        $stmt->execute([$chatId, $senderId, $body]);
        $this->db->prepare('UPDATE chats SET updated_at=CURRENT_TIMESTAMP WHERE id=?')->execute([$chatId]);
        return true;
    }

    public function canSend(int $chatId, int $userId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM chats c
            JOIN chat_participants cp ON cp.chat_id=c.id AND cp.user_id=?
            JOIN matches m ON m.id=c.match_id
            WHERE c.id=? AND c.status='open' AND m.match_status='mutual'");
        $stmt->execute([$userId, $chatId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function flagMessage(int $messageId, int $reporterId, string $reason): bool
    {
        $reason = trim($reason);
        $stmt = $this->db->prepare('SELECT msg.id FROM messages msg JOIN chat_participants cp ON cp.chat_id=msg.chat_id AND cp.user_id=? WHERE msg.id=? AND msg.sender_user_id<>? LIMIT 1');
        $stmt->execute([$reporterId, $messageId, $reporterId]);
        if (!$stmt->fetch()) {
            return false;
        }
        $insert = $this->db->prepare('INSERT INTO message_flags (message_id, reporter_user_id, reason_text) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE reason_text=VALUES(reason_text), created_at=CURRENT_TIMESTAMP');
        $insert->execute([$messageId, $reporterId, $reason !== '' ? $reason : null]);
        $this->db->prepare("UPDATE messages SET moderation_status='flagged' WHERE id=?")->execute([$messageId]);
        return true;
    }

    public function allForAdmin(): array
    {
        return $this->db->query("SELECT c.*, m.match_status, u1.first_name AS user_one_name, u2.first_name AS user_two_name,
                (SELECT COUNT(*) FROM messages WHERE chat_id=c.id AND deleted_at IS NULL) AS message_count,
                (SELECT COUNT(*) FROM message_flags mf JOIN messages msg ON msg.id=mf.message_id WHERE msg.chat_id=c.id) AS flag_count
            FROM chats c
            JOIN matches m ON m.id=c.match_id
            JOIN users u1 ON u1.id=m.user_one_id
            JOIN users u2 ON u2.id=m.user_two_id
            ORDER BY c.updated_at DESC LIMIT 200")->fetchAll();
    }

    public function findForAdmin(int $chatId): ?array
    {
        $stmt = $this->db->prepare("SELECT c.*, m.match_status, u1.first_name AS user_one_name, u2.first_name AS user_two_name
            FROM chats c
            JOIN matches m ON m.id=c.match_id
            JOIN users u1 ON u1.id=m.user_one_id
            JOIN users u2 ON u2.id=m.user_two_id
            WHERE c.id=? LIMIT 1");
        $stmt->execute([$chatId]);
        return $stmt->fetch() ?: null;
    }

    public function messagesForAdmin(int $chatId): array
    {
        $stmt = $this->db->prepare("SELECT msg.*, u.first_name AS sender_name,
                (SELECT COUNT(*) FROM message_flags WHERE message_id=msg.id) AS flag_count,
                (SELECT GROUP_CONCAT(reason_text SEPARATOR '; ') FROM message_flags WHERE message_id=msg.id) AS flag_reasons
            FROM messages msg
            JOIN users u ON u.id=msg.sender_user_id
            WHERE msg.chat_id=? AND msg.deleted_at IS NULL
            ORDER BY msg.created_at ASC, msg.id ASC");
        $stmt->execute([$chatId]);
        return $stmt->fetchAll();
    }

    public function closeByAdmin(int $chatId, int $adminId, string $reason): bool
    {
        $reason = trim($reason);
        if ($reason === '') {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE chats SET status='closed', closed_reason=?, closed_by_admin_user_id=?, closed_at=CURRENT_TIMESTAMP WHERE id=?");
        $stmt->execute([$reason, $adminId, $chatId]);
        return $stmt->rowCount() > 0;
    }

    public function closeForBlockedMatch(int $matchId, string $reason): void
    {
        $this->db->prepare("UPDATE chats SET status='closed', closed_reason=COALESCE(closed_reason, ?), closed_at=COALESCE(closed_at, CURRENT_TIMESTAMP) WHERE match_id=? AND status='open'")->execute([$reason, $matchId]);
    }
}
