<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class GoalRepository
{
    private PDO $db;
    public function __construct() { $this->db = Database::connection(); }
    public function active(): array { return $this->db->query('SELECT * FROM goals WHERE is_active=1 AND deleted_at IS NULL ORDER BY sort_order, title')->fetchAll(); }
    public function all(): array { return $this->db->query('SELECT * FROM goals WHERE deleted_at IS NULL ORDER BY sort_order, title')->fetchAll(); }
    public function forUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT g.* FROM goals g JOIN user_goals ug ON ug.goal_id=g.id WHERE ug.user_id=? AND g.deleted_at IS NULL ORDER BY g.sort_order, g.title');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    public function syncUserGoals(int $userId, array $goalIds): void
    {
        $valid = $this->validIds($goalIds);
        $this->db->prepare('DELETE FROM user_goals WHERE user_id=?')->execute([$userId]);
        $stmt = $this->db->prepare('INSERT INTO user_goals (user_id, goal_id) VALUES (?,?)');
        foreach ($valid as $goalId) { $stmt->execute([$userId, $goalId]); }
    }
    public function validIds(array $goalIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $goalIds))));
        if (!$ids) { return []; }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT id FROM goals WHERE id IN ($in) AND is_active=1 AND deleted_at IS NULL");
        $stmt->execute($ids);
        return array_map('intval', array_column($stmt->fetchAll(), 'id'));
    }
    public function save(array $data): int
    {
        if (!empty($data['id'])) {
            $stmt = $this->db->prepare('UPDATE goals SET title=?, description=?, sort_order=?, is_active=? WHERE id=? AND deleted_at IS NULL');
            $stmt->execute([$data['title'], $data['description'] ?: null, (int)$data['sort_order'], !empty($data['is_active']) ? 1 : 0, (int)$data['id']]);
            return (int)$data['id'];
        }
        $stmt = $this->db->prepare('INSERT INTO goals (title, description, sort_order, is_active) VALUES (?,?,?,?)');
        $stmt->execute([$data['title'], $data['description'] ?: null, (int)$data['sort_order'], !empty($data['is_active']) ? 1 : 0]);
        return (int)$this->db->lastInsertId();
    }
    public function softDelete(int $id): void { $this->db->prepare('UPDATE goals SET is_active=0, deleted_at=NOW() WHERE id=?')->execute([$id]); }
}
