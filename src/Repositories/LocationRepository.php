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
        return $this->db->query('SELECT c.*, p.name AS province_name FROM cities c JOIN provinces p ON p.id=c.province_id WHERE c.is_active=1 AND p.is_active=1 ORDER BY p.sort_order, c.sort_order, c.name')->fetchAll();
    }
}
