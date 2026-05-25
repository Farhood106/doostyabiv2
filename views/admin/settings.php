<h1>تنظیمات مدیریت</h1>
<section class="card narrow">
<form method="post" action="/admin/settings">
<?= csrf_field() ?>
<label>نام سایت <input name="site_name" value="<?= e($settings['site_name'] ?? 'دوستیابی') ?>" required></label><span class="error-text"><?= e($errors['site_name'] ?? '') ?></span>
<label>وضعیت سایت <select name="site_status"><option value="active" <?= ($settings['site_status'] ?? '') === 'active' ? 'selected' : '' ?>>فعال</option><option value="maintenance" <?= ($settings['site_status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>در حال نگهداری</option></select></label><span class="error-text"><?= e($errors['site_status'] ?? '') ?></span>
<label class="check"><input type="checkbox" name="registration_enabled" value="1" <?= ($settings['registration_enabled'] ?? '1') === '1' ? 'checked' : '' ?>> ثبت‌نام فعال باشد</label>
<label>مسیر پیش‌فرض پس از ورود <input name="default_onboarding_redirect" value="<?= e($settings['default_onboarding_redirect'] ?? '/onboarding') ?>" required></label><p class="help">از مسیر داخلی مثل /onboarding استفاده کنید.</p><span class="error-text"><?= e($errors['default_onboarding_redirect'] ?? '') ?></span>
<hr>
<h2>تنظیمات معرفی و شناخت‌نامه</h2>
<label>حداقل پاسخ‌های ضروری پیش از ساخت معرفی <input type="number" min="0" max="50" name="minimum_required_answers_before_matching" value="<?= e($settings['minimum_required_answers_before_matching'] ?? '3') ?>"></label><p class="help">اگر عضو هنوز پاسخ‌های ضروری کافی ندارد، به‌جای معرفی عجولانه راهنمایی ملایم می‌بیند.</p>
<label>حداکثر پرسش اختیاری نمایش‌داده‌شده در هر مرحله <input type="number" min="0" max="20" name="max_optional_questions_per_step" value="<?= e($settings['max_optional_questions_per_step'] ?? '3') ?>"></label><p class="help">پرسش‌های اختیاری بی‌پاسخ بر اساس اهمیت مرتب می‌شوند تا شناخت‌نامه خسته‌کننده نشود.</p>
<label class="check"><input type="checkbox" name="show_low_confidence_matches" value="1" <?= ($settings['show_low_confidence_matches'] ?? '1') === '1' ? 'checked' : '' ?>> معرفی‌های کم‌اطمینان هم با توضیح ملایم نمایش داده شوند</label>
<button class="button">ذخیره تنظیمات</button>
</form>
</section>
