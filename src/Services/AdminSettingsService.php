<?php
namespace App\Services;

use App\Repositories\AdminSettingsRepository;

class AdminSettingsService
{
    public function save(array $input, int $adminId): array
    {
        $errors = [];
        $siteName = trim($input['site_name'] ?? '');
        if ($siteName === '') { $errors['site_name'] = 'Site name is required.'; }
        $siteStatus = $input['site_status'] ?? 'active';
        if (!in_array($siteStatus, ['active', 'maintenance'], true)) { $errors['site_status'] = 'Invalid site status.'; }
        $redirect = trim($input['default_onboarding_redirect'] ?? '/onboarding');
        if ($redirect === '' || $redirect[0] !== '/') { $errors['default_onboarding_redirect'] = 'Redirect must be a local path beginning with /.'; }
        if ($errors) { return [false, $errors]; }
        (new AdminSettingsRepository())->save([
            'site_name' => $siteName,
            'site_status' => $siteStatus,
            'registration_enabled' => !empty($input['registration_enabled']) ? '1' : '0',
            'default_onboarding_redirect' => $redirect,
        ], $adminId);
        return [true, []];
    }
}
