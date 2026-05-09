<h1>Admin settings</h1>
<section class="card narrow">
<form method="post" action="/admin/settings">
<?= csrf_field() ?>
<label>Site name <input name="site_name" value="<?= e($settings['site_name'] ?? 'Doostyabi') ?>" required></label><span class="error-text"><?= e($errors['site_name'] ?? '') ?></span>
<label>Site status <select name="site_status"><option value="active" <?= ($settings['site_status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option><option value="maintenance" <?= ($settings['site_status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option></select></label><span class="error-text"><?= e($errors['site_status'] ?? '') ?></span>
<label class="check"><input type="checkbox" name="registration_enabled" value="1" <?= ($settings['registration_enabled'] ?? '1') === '1' ? 'checked' : '' ?>> Registration enabled</label>
<label>Default onboarding redirect <input name="default_onboarding_redirect" value="<?= e($settings['default_onboarding_redirect'] ?? '/onboarding') ?>" required></label><p class="help">Use a local path such as /onboarding.</p><span class="error-text"><?= e($errors['default_onboarding_redirect'] ?? '') ?></span>
<button class="button">Save settings</button>
</form>
</section>
