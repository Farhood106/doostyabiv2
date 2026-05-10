<?php
namespace App\Services;

use App\Repositories\AdminSettingsRepository;

class AdminSettingsService
{
    public function save(array $input, int $adminId): array
    {
        $errors = [];
        $siteName = trim($input['site_name'] ?? '');
        if ($siteName === '') { $errors['site_name'] = 'نام سایت ضروری است.'; }
        $siteStatus = $input['site_status'] ?? 'active';
        if (!in_array($siteStatus, ['active', 'maintenance'], true)) { $errors['site_status'] = 'وضعیت سایت معتبر نیست.'; }
        $redirect = trim($input['default_onboarding_redirect'] ?? '/onboarding');
        if ($redirect === '' || $redirect[0] !== '/') { $errors['default_onboarding_redirect'] = 'مسیر انتقال باید داخلی باشد و با / شروع شود.'; }
        $minimumRequiredAnswers = max(0, min(50, (int)($input['minimum_required_answers_before_matching'] ?? 3)));
        $maxOptionalPerStep = max(0, min(20, (int)($input['max_optional_questions_per_step'] ?? 3)));
        if ($errors) { return [false, $errors]; }
        (new AdminSettingsRepository())->save([
            'site_name' => $siteName,
            'site_status' => $siteStatus,
            'registration_enabled' => !empty($input['registration_enabled']) ? '1' : '0',
            'default_onboarding_redirect' => $redirect,
            'minimum_required_answers_before_matching' => (string)$minimumRequiredAnswers,
            'max_optional_questions_per_step' => (string)$maxOptionalPerStep,
            'show_low_confidence_matches' => !empty($input['show_low_confidence_matches']) ? '1' : '0',
        ], $adminId);
        return [true, []];
    }
}
