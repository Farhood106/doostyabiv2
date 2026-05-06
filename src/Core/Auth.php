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

    public static function requireAdmin(): array
    {
        $user = self::requireLogin();
        if (($user['role_name'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Forbidden');
        }
        return $user;
    }
}
