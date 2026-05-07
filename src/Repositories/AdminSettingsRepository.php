<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class AdminSettingsRepository
{
    private PDO $db;
    public function __construct() { $this->db = Database::connection(); }

    public function all(): array
    {
        $rows = $this->db->query('SELECT setting_key, setting_value FROM admin_settings')->fetchAll();
        $settings = [];
        foreach ($rows as $row) { $settings[$row['setting_key']] = $row['setting_value']; }
        return $settings + $this->defaults();
    }

    public function defaults(): array
    {
        return [
            'site_name' => 'Doostyabi',
            'site_status' => 'active',
            'registration_enabled' => '1',
            'default_onboarding_redirect' => '/onboarding',
        ];
    }

    public function save(array $settings, int $adminId): void
    {
        $stmt = $this->db->prepare('INSERT INTO admin_settings (setting_key, setting_value, updated_by) VALUES (?,?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), updated_by=VALUES(updated_by)');
        foreach ($settings as $key => $value) { $stmt->execute([$key, $value, $adminId]); }
    }
}
