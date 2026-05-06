<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class LocationRepository
{
    private PDO $db;
    public function __construct() { $this->db = Database::connection(); }
    public function activeCities(): array
    {
        return $this->db->query('SELECT c.*, p.name AS province_name FROM cities c JOIN provinces p ON p.id=c.province_id WHERE c.is_active=1 AND p.is_active=1 AND c.deleted_at IS NULL AND p.deleted_at IS NULL ORDER BY p.sort_order, c.sort_order, c.name')->fetchAll();
    }
    public function allProvinces(): array { return $this->db->query('SELECT * FROM provinces WHERE deleted_at IS NULL ORDER BY sort_order, name')->fetchAll(); }
    public function allCities(): array
    {
        return $this->db->query('SELECT c.*, p.name AS province_name FROM cities c JOIN provinces p ON p.id=c.province_id WHERE c.deleted_at IS NULL ORDER BY p.sort_order, c.sort_order, c.name')->fetchAll();
    }
    public function validCityIds(array $cityIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $cityIds))));
        if (!$ids) { return []; }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT c.id FROM cities c JOIN provinces p ON p.id=c.province_id WHERE c.id IN ($in) AND c.is_active=1 AND p.is_active=1 AND c.deleted_at IS NULL AND p.deleted_at IS NULL");
        $stmt->execute($ids);
        return array_map('intval', array_column($stmt->fetchAll(), 'id'));
    }
    public function saveProvince(array $data): int
    {
        if (!empty($data['id'])) {
            $stmt = $this->db->prepare('UPDATE provinces SET name=?, country_code=?, sort_order=?, is_active=? WHERE id=? AND deleted_at IS NULL');
            $stmt->execute([$data['name'], strtoupper($data['country_code'] ?: 'US'), (int)$data['sort_order'], !empty($data['is_active']) ? 1 : 0, (int)$data['id']]);
            return (int)$data['id'];
        }
        $stmt = $this->db->prepare('INSERT INTO provinces (name, country_code, sort_order, is_active) VALUES (?,?,?,?)');
        $stmt->execute([$data['name'], strtoupper($data['country_code'] ?: 'US'), (int)$data['sort_order'], !empty($data['is_active']) ? 1 : 0]);
        return (int)$this->db->lastInsertId();
    }
    public function saveCity(array $data): int
    {
        if (!empty($data['id'])) {
            $stmt = $this->db->prepare('UPDATE cities SET province_id=?, name=?, sort_order=?, is_active=? WHERE id=? AND deleted_at IS NULL');
            $stmt->execute([(int)$data['province_id'], $data['name'], (int)$data['sort_order'], !empty($data['is_active']) ? 1 : 0, (int)$data['id']]);
            return (int)$data['id'];
        }
        $stmt = $this->db->prepare('INSERT INTO cities (province_id, name, sort_order, is_active) VALUES (?,?,?,?)');
        $stmt->execute([(int)$data['province_id'], $data['name'], (int)$data['sort_order'], !empty($data['is_active']) ? 1 : 0]);
        return (int)$this->db->lastInsertId();
    }
    public function softDeleteProvince(int $id): void { $this->db->prepare('UPDATE provinces SET is_active=0, deleted_at=NOW() WHERE id=?')->execute([$id]); }
    public function softDeleteCity(int $id): void { $this->db->prepare('UPDATE cities SET is_active=0, deleted_at=NOW() WHERE id=?')->execute([$id]); }
}
