<div class="auth-shell">
    <section class="card auth-card">
        <span class="eyebrow">ساخت حساب</span>
        <h1>شروعی آرام، خصوصی و هدفمند</h1>
        <p class="muted">برای ساخت حساب فقط اطلاعات ضروری را می‌گیریم. جزئیات سازگاری بعداً در مرحله شناخت خصوصی تکمیل می‌شود.</p>
        <?php if (!empty($errors)): ?><div class="alert error"><strong>لطفاً چند مورد را بررسی کنید.</strong><p>هنوز چیزی ذخیره نشده است؛ موارد مشخص‌شده را اصلاح کنید و دوباره تلاش کنید.</p></div><?php endif; ?>
        <form method="post" action="/register">
            <?= csrf_field() ?>
            <div class="grid two">
                <label>ایمیل <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" autocomplete="email" required placeholder="you@example.com"><span class="error-text"><?= e($errors['email'] ?? '') ?></span></label>
                <label>نام <input name="first_name" value="<?= e($old['first_name'] ?? '') ?>" autocomplete="given-name" required placeholder="نام شما"><span class="error-text"><?= e($errors['first_name'] ?? '') ?></span></label>
                <label>نام خانوادگی <input name="last_name" value="<?= e($old['last_name'] ?? '') ?>" autocomplete="family-name" placeholder="اختیاری"></label>
                <label>جنسیت <input name="gender" value="<?= e($old['gender'] ?? '') ?>" placeholder="اختیاری"></label>
                <label>تاریخ تولد <input type="date" name="birthdate" value="<?= e($old['birthdate'] ?? '') ?>"></label>
                <span class="form-note">برای زمینه حساب و سازگاری استفاده می‌شود و به‌عنوان پروفایل عمومی منتشر نمی‌شود.</span>
                <label>رمز عبور <input type="password" name="password" autocomplete="new-password" required placeholder="حداقل ۸ کاراکتر"><span class="error-text"><?= e($errors['password'] ?? '') ?></span></label>
                <label>تکرار رمز عبور <input type="password" name="password_confirm" autocomplete="new-password" required><span class="error-text"><?= e($errors['password_confirm'] ?? '') ?></span></label>
            </div>
            <button class="button large">ساخت حساب خصوصی</button>
        </form>
        <p class="auth-switch">قبلاً ثبت‌نام کرده‌اید؟ <a href="/login">وارد شوید</a></p>
    </section>
    <aside class="card auth-side"><h2>بعد از این چه می‌شود؟</h2><ol class="soft-list"><li>شناخت‌نامه راهنما را کامل می‌کنید.</li><li>کارت‌های سازگاری ناشناس دریافت می‌کنید.</li><li>علاقه، گفت‌وگو و نمایش اطلاعات فقط با رضایت پیش می‌رود.</li></ol></aside>
</div>
