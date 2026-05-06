<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class GoalRepository
{
    private PDO $db;
    public function __construct() { $this->db = Database::connection(); }
    public function active(): array { return $this->db->query('SELECT * FROM goals WHERE is_active=1 ORDER BY sort_order, title')->fetchAll(); }
    public function forUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT g.* FROM goals g JOIN user_goals ug ON ug.goal_id=g.id WHERE ug.user_id=? ORDER BY g.sort_order, g.title');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    public function syncUserGoals(int $userId, array $goalIds): void
    {
        $this->db->prepare('DELETE FROM user_goals WHERE user_id=?')->execute([$userId]);
        $stmt = $this->db->prepare('INSERT INTO user_goals (user_id, goal_id) VALUES (?,?)');
        foreach (array_unique(array_map('intval', $goalIds)) as $goalId) {
            if ($goalId > 0) { $stmt->execute([$userId, $goalId]); }
        }
    }
}
