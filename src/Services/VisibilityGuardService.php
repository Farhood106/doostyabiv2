<?php
namespace App\Services;

use App\Repositories\PrivacyShieldRepository;
use App\Repositories\SafetyRepository;
use App\Repositories\UserRepository;

class VisibilityGuardService
{
    /**
     * Central visibility policy authority.
     * Repositories should NOT re-implement policy logic; they only fetch data.
     */
    public function evaluateVisibility(int $userA, int $userB): array
    {
        $reasons = [];
        if ($userA <= 0 || $userB <= 0 || $userA === $userB) { $reasons[] = 'stale_state'; return ['allowed' => false, 'reasons' => $reasons]; }

        $userRepo = new UserRepository();
        $a = $userRepo->find($userA);
        $b = $userRepo->find($userB);
        if (!$a || !$b || empty($a['is_active']) || empty($b['is_active'])) { $reasons[] = 'inactive_user'; }

        if ((new SafetyRepository())->isBlockedBetween($userA, $userB)) { $reasons[] = 'blocked'; }
        if ($a && $b && (new PrivacyShieldRepository())->isShieldedBetween((array)$a, (array)$b)) { $reasons[] = 'privacy_shield'; }

        return ['allowed' => empty($reasons), 'reasons' => array_values(array_unique($reasons))];
    }

    public function canUsersSeeEachOther(int $userA, int $userB): bool
    {
        return (bool)$this->evaluateVisibility($userA, $userB)['allowed'];
    }
}
