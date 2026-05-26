<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class SafetyRepository
{
    public function isBlockedBetween(int $a, int $b): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM blocks WHERE deleted_at IS NULL AND ((blocker_user_id=? AND blocked_user_id=?) OR (blocker_user_id=? AND blocked_user_id=?)) LIMIT 1");
        $stmt->execute([$a,$b,$b,$a]);
        return (bool)$stmt->fetchColumn();
    }

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function activeBlocksForUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT b.*, u.first_name AS blocked_name FROM blocks b JOIN users u ON u.id=b.blocked_user_id WHERE b.blocker_user_id=? AND b.deleted_at IS NULL ORDER BY b.created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function unblock(int $blockId, int $userId): bool
    {
        $stmt = $this->db->prepare('UPDATE blocks SET deleted_at=CURRENT_TIMESTAMP WHERE id=? AND blocker_user_id=? AND deleted_at IS NULL');
        $stmt->execute([$blockId, $userId]);
        return $stmt->rowCount() > 0;
    }
}
