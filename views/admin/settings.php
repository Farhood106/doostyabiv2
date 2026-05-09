<h1>تنظیمات مدیریت</h1>
<section class="card narrow">
<form method="post" action="/admin/settings">
<?= csrf_field() ?>
<label>نام سایت <input name="site_name" value="<?= e($settings['site_name'] ?? 'دوستیابی') ?>" required></label><span class="error-text"><?= e($errors['site_name'] ?? '') ?></span>
<label>وضعیت سایت <select name="site_status"><option value="active" <?= ($settings['site_status'] ?? '') === 'active' ? 'selected' : '' ?>>فعال</option><option value="maintenance" <?= ($settings['site_status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>در حال نگهداری</option></select></label><span class="error-text"><?= e($errors['site_status'] ?? '') ?></span>
<label class="check"><input type="checkbox" name="registration_enabled" value="1" <?= ($settings['registration_enabled'] ?? '1') === '1' ? 'checked' : '' ?>> ثبت‌نام فعال باشد</label>
<label>مسیر پیش‌فرض پس از ورود <input name="default_onboarding_redirect" value="<?= e($settings['default_onboarding_redirect'] ?? '/onboarding') ?>" required></label><p class="help">از مسیر داخلی مثل /onboarding استفاده کنید.</p><span class="error-text"><?= e($errors['default_onboarding_redirect'] ?? '') ?></span>
<button class="button">ذخیره settings</button>
</form>
</section>
