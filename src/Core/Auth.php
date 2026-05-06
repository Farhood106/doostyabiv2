<?php
namespace App\Core;

use App\Repositories\UserRepository;

class Auth
{
    public static function user(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        return (new UserRepository())->find((int) $_SESSION['user_id']);
    }

    public static function requireLogin(): array
    {
        $user = self::user();
        if (!$user) {
            \redirect('/login');
        }
        return $user;
    }

    public static function hasPermission(array $user, string $permission): bool
    {
        if (($user['role_name'] ?? '') !== 'admin') {
            return false;
        }
        return in_array($permission, (new UserRepository())->permissionsForUser((int)$user['id']), true);
    }

    public static function requirePermission(string $permission): array
    {
        $user = self::requireLogin();
        if (!self::hasPermission($user, $permission)) {
            http_response_code(403);
            exit('Forbidden');
        }
        return $user;
    }

    public static function requireAdmin(): array
    {
        return self::requirePermission('admin.access');
    }
}
