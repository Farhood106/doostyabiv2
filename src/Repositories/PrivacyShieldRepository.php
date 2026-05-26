<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class PrivacyShieldRepository
{
    private PDO $db;
    private const ZERO_WIDTH = ["\u{200c}", "\u{200d}", "\u{200e}", "\u{200f}", "\u{feff}"];
    public function __construct() { $this->db = Database::connection(); }

    public function listForUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT id, type, note, created_at FROM privacy_shields WHERE user_id=? AND deleted_at IS NULL ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function add(int $userId, string $type, string $value, ?string $note = null): bool
    {
        [$normalized, $hash] = $this->normalizeAndHash($type, $value);
        if ($normalized === '' || $hash === '') { return false; }
        $stmt = $this->db->prepare('INSERT INTO privacy_shields (user_id,type,normalized_value,hashed_value,note) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE note=VALUES(note), deleted_at=NULL, updated_at=CURRENT_TIMESTAMP');
        return $stmt->execute([$userId, $type, $normalized, $hash, $note]);
    }

    public function remove(int $userId, int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE privacy_shields SET deleted_at=NOW() WHERE id=? AND user_id=? AND deleted_at IS NULL');
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }

    public function allForAdmin(): array
    {
        return $this->db->query("SELECT ps.id, ps.user_id, ps.type, ps.note, ps.created_at, u.first_name, u.last_name FROM privacy_shields ps JOIN users u ON u.id=ps.user_id WHERE ps.deleted_at IS NULL ORDER BY ps.created_at DESC LIMIT 500")->fetchAll();
    }

    public function isShieldedBetween(array $userA, array $userB): bool
    {
        foreach ($this->candidateHashesForUser($userB) as $hash) {
            if ($this->hasHashForUser((int)$userA['id'], $hash)) { return true; }
        }
        foreach ($this->candidateHashesForUser($userA) as $hash) {
            if ($this->hasHashForUser((int)$userB['id'], $hash)) { return true; }
        }
        return false;
    }

    public function normalizeAndHash(string $type, string $value): array
    {
        $type = in_array($type, ['phone','email','full_name','username'], true) ? $type : 'full_name';
        $value = trim($value);
        if ($type === 'email') { $normalized = strtolower($value); }
        elseif ($type === 'phone') { $normalized = $this->normalizeIranPhone($value); }
        else { $normalized = $this->normalizePersianText($value); }
        return [$normalized, $normalized !== '' ? hash('sha256', $normalized) : ''];
    }

    private function hasHashForUser(int $userId, string $hash): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM privacy_shields WHERE user_id=? AND hashed_value=? AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([$userId, $hash]);
        return (bool)$stmt->fetchColumn();
    }

    private function candidateHashesForUser(array $user): array
    {
        $values = [];
        if (!empty($user['email'])) { [, $h] = $this->normalizeAndHash('email', (string)$user['email']); if ($h) { $values[] = $h; } }
        $full = trim(((string)($user['first_name'] ?? '')) . ' ' . ((string)($user['last_name'] ?? '')));
        if ($full !== '') { [, $h] = $this->normalizeAndHash('full_name', $full); if ($h) { $values[] = $h; } }
        return array_values(array_unique($values));
    }

    private function normalizeIranPhone(string $value): string
    {
        $value = strtr($value, ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
        $digits = preg_replace('/[^0-9+]/', '', $value) ?? '';
        if (str_starts_with($digits, '0098')) { $digits = '+' . substr($digits, 2); }
        if (str_starts_with($digits, '98') && !str_starts_with($digits, '+98')) { $digits = '+' . $digits; }
        if (str_starts_with($digits, '09')) { $digits = '+98' . substr($digits, 1); }
        return preg_match('/^\+989\d{9}$/', $digits) ? $digits : '';
    }

    private function normalizePersianText(string $value): string
    {
        $value = str_replace(self::ZERO_WIDTH, ' ', $value);
        $value = strtr($value, ['ي'=>'ی','ك'=>'ک','ة'=>'ه','ۀ'=>'ه']);
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        return trim($value);
    }
}
