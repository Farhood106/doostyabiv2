<?php
namespace App\Services;

use App\Repositories\SafetyRepository;
use App\Repositories\PrivacyShieldRepository;
use App\Repositories\UserRepository;

class VisibilityGuardService
{
    public function canUsersSeeEachOther(int $userA, int $userB): bool
    {
        if ($userA <= 0 || $userB <= 0 || $userA === $userB) { return false; }
        $safety = new SafetyRepository();
        if ($safety->isBlockedBetween($userA, $userB)) { return false; }

        $userRepo = new UserRepository();
        $a = $userRepo->find($userA);
        $b = $userRepo->find($userB);
        if (!$a || !$b) { return false; }

        $shield = new PrivacyShieldRepository();
        return !$shield->isShieldedBetween((array)$a, (array)$b);
    }
}
