<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class DashboardRepository
{
    private PDO $db;
    public function __construct() { $this->db = Database::connection(); }

    public function stats(): array
    {
        return [
            'users' => (int)$this->db->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='user'")->fetchColumn(),
            'goals' => (int)$this->db->query('SELECT COUNT(*) FROM goals WHERE deleted_at IS NULL AND is_active=1')->fetchColumn(),
            'cities' => (int)$this->db->query('SELECT COUNT(*) FROM cities WHERE deleted_at IS NULL AND is_active=1')->fetchColumn(),
            'steps' => (int)$this->db->query('SELECT COUNT(*) FROM form_steps WHERE deleted_at IS NULL')->fetchColumn(),
            'questions' => (int)$this->db->query('SELECT COUNT(*) FROM questions WHERE deleted_at IS NULL')->fetchColumn(),
            'completed_onboardings' => (int)$this->db->query('SELECT COUNT(*) FROM user_onboarding_progress WHERE is_complete=1')->fetchColumn(),
        ];
    }
}
