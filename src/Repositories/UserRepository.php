<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class UserRepository
{
    private PDO $db;
    public function __construct() { $this->db = Database::connection(); }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function createMember(array $data): int
    {
        $roleId = $this->db->query("SELECT id FROM roles WHERE name='user' LIMIT 1")->fetchColumn();
        $stmt = $this->db->prepare('INSERT INTO users (role_id, email, password_hash, first_name, last_name, gender, birthdate) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$roleId, $data['email'], password_hash($data['password'], PASSWORD_BCRYPT), $data['first_name'], $data['last_name'] ?: null, $data['gender'] ?: null, $data['birthdate'] ?: null]);
        return (int) $this->db->lastInsertId();
    }

    public function allMembers(): array
    {
        return $this->db->query("SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE r.name='user' ORDER BY u.created_at DESC")->fetchAll();
    }
}
