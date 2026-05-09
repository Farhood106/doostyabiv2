<section class="hero safety-hero card">
    <div><span class="eyebrow">مرکز امنیت</span><h1>حریم خصوصی و آرامش شما اولویت دارد.</h1><p class="lead">در این صفحه می‌بینید ناشناس‌بودن چگونه حفظ می‌شود، نمایش اطلاعات با رضایت چطور کار می‌کند و مسدودسازی‌ها را چگونه مدیریت کنید.</p></div>
</section>
<section class="grid three feature-grid">
    <article class="card feature-card"><span class="feature-icon">ب</span><h2>مسدودسازی</h2><p>مسدودسازی معرفی را پنهان و گفت‌وگوی مرتبط را غیرفعال می‌کند. اگر نظرتان عوض شد، پایین همین صفحه می‌توانید رفع مسدودی کنید.</p></article>
    <article class="card feature-card"><span class="feature-icon">گ</span><h2>گزارش</h2><p>می‌توانید معرفی، گفت‌وگو یا پیام را برای بررسی مدیران گزارش کنید؛ بدون اینکه اطلاعات خصوصی بیشتری برای طرف مقابل آشکار شود.</p></article>
    <article class="card feature-card"><span class="feature-icon">✓</span><h2>نمایش با رضایت</h2><p>جزئیات محدودی مثل نام نمایشی، شهر یا خلاصه هدف‌ها فقط پس از تأیید شما نمایش داده می‌شود.</p></article>
</section>
<section class="card promise-card"><h2>یادآوری‌های حریم خصوصی</h2><ul class="soft-list"><li>پروفایل عمومی یا فهرست قابل جست‌وجوی اعضا وجود ندارد.</li><li>پیش از علاقه دوطرفه، کارت‌های معرفی ناشناس هستند.</li><li>ایمیل، تلفن، شبکه اجتماعی یا نشانی به‌صورت خودکار نمایش داده نمی‌شود.</li><li>مدیران گزارش‌ها را برای حفظ امنیت و رسیدگی منصفانه بررسی می‌کنند.</li></ul></section>
<section class="card table-wrap">
    <div class="section-title"><div><h2>مسدودی‌های فعال شما</h2><p class="muted">عضو مسدودشده تا وقتی مسدودی فعال باشد نمی‌تواند گفت‌وگوی مرتبط را ادامه دهد.</p></div><span class="pill"><?= count($blocks) ?> فعال</span></div>
    <table><thead><tr><th>عضو مسدودشده</th><th>منبع</th><th>دلیل</th><th>زمان ایجاد</th><th>اقدام</th></tr></thead><tbody>
    <?php foreach ($blocks as $block): ?>
        <tr><td><?= e($block['blocked_name']) ?></td><td><span class="pill"><?= e(fa_label($block['source'])) ?></span></td><td><?= e($block['reason_text']) ?></td><td><?= e($block['created_at']) ?></td><td><form method="post" action="/safety/unblock" class="inline"><?= csrf_field() ?><input type="hidden" name="block_id" value="<?= (int)$block['id'] ?>"><button class="secondary">رفع مسدودی</button></form></td></tr>
    <?php endforeach; ?>
    </tbody></table>
    <?php if (!$blocks): ?><div class="empty-state"><h3>مسدودی فعالی ندارید</h3><p>اگر جایی احساس ناامنی کردید، از کارت معرفی یا ابزارهای رسیدگی می‌توانید مسدود کنید.</p></div><?php endif; ?>
</section>
