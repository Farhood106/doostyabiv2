<div class="auth-shell">
    <section class="card auth-card">
        <span class="eyebrow">Create account</span>
        <h1>Start with privacy and intention</h1>
        <p class="muted">We ask for only what is needed to set up your account. Matching details are handled later in private onboarding.</p>
        <?php if (!empty($errors)): ?><div class="alert error"><strong>Please check the highlighted fields.</strong><p>Nothing was saved yet; update the items below and try again.</p></div><?php endif; ?>
        <form method="post" action="/register">
            <?= csrf_field() ?>
            <div class="grid two">
                <label>Email <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" autocomplete="email" required placeholder="you@example.com"><span class="error-text"><?= e($errors['email'] ?? '') ?></span></label>
                <label>First name <input name="first_name" value="<?= e($old['first_name'] ?? '') ?>" autocomplete="given-name" required placeholder="Your first name"><span class="error-text"><?= e($errors['first_name'] ?? '') ?></span></label>
                <label>Last name <input name="last_name" value="<?= e($old['last_name'] ?? '') ?>" autocomplete="family-name" placeholder="Optional"></label>
                <label>Gender <input name="gender" value="<?= e($old['gender'] ?? '') ?>" placeholder="Optional"></label>
                <label>Birthdate <input type="date" name="birthdate" value="<?= e($old['birthdate'] ?? '') ?>"></label>
                <span class="form-note">Used for account context and compatibility. It is not published as a public profile.</span>
                <label>Password <input type="password" name="password" autocomplete="new-password" required placeholder="At least 8 characters"><span class="error-text"><?= e($errors['password'] ?? '') ?></span></label>
                <label>Confirm password <input type="password" name="password_confirm" autocomplete="new-password" required><span class="error-text"><?= e($errors['password_confirm'] ?? '') ?></span></label>
            </div>
            <button class="button large">Create private account</button>
        </form>
        <p class="auth-switch">Already registered? <a href="/login">Log in</a></p>
    </section>
    <aside class="card auth-side"><h2>What happens next?</h2><ol class="soft-list"><li>Complete guided onboarding.</li><li>Receive anonymous compatibility cards.</li><li>Choose interest, chat, and reveals only by consent.</li></ol></aside>
</div>
