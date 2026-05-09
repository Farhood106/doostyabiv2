<div class="auth-shell">
    <section class="card auth-card">
        <span class="eyebrow">خوش برگشتید</span>
        <h1>ورود به فضای خصوصی شما</h1>
        <p class="muted">شناخت‌نامه را ادامه دهید، معرفی‌های ناشناس را مرور کنید یا به گفت‌وگوی دوطرفه برگردید. اطلاعات تماس شما خودکار نمایش داده نمی‌شود.</p>
        <form method="post" action="/login">
            <?= csrf_field() ?>
            <label>ایمیل <input type="email" name="email" autocomplete="email" required placeholder="you@example.com"></label>
            <label>رمز عبور <input type="password" name="password" autocomplete="current-password" required placeholder="رمز عبور شما"></label>
            <button class="button large">ورود امن</button>
        </form>
        <p class="auth-switch">تازه وارد هستید؟ <a href="/register">یک حساب خصوصی بسازید</a></p>
    </section>
    <aside class="card auth-side"><h2>طراحی با اولویت حریم خصوصی</h2><ul class="soft-list"><li>فهرست عمومی پروفایل‌ها وجود ندارد.</li><li>کارت‌های معرفی ناشناس هستند.</li><li>گفت‌وگو و نمایش اطلاعات با رضایت دوطرفه انجام می‌شود.</li></ul></aside>
</div>
