<?php
namespace App\Repositories;

use App\Core\Database;

class AuditLogRepository
{
    public function record(?int $adminUserId, string $action, string $entityType, ?int $entityId, array $metadata = []): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO audit_logs (admin_user_id, action, entity_type, entity_id, metadata, ip_address) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$adminUserId, $action, $entityType, $entityId, json_encode($metadata), $_SERVER['REMOTE_ADDR'] ?? null]);
    }
}
