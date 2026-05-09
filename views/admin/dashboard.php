<section class="section-title"><div><h1>داشبورد مدیریت</h1><p class="muted">اعضا، معرفی‌ها، گفت‌وگوها، درخواست‌های نمایش، گزارش‌ها و رسیدگی‌ها را پایش کنید.</p></div><span class="pill"><?= e(fa_label($settings['site_status'] ?? 'active')) ?></span></section>
<div class="grid six">
    <?php foreach ([['اعضا','users','/admin/users'],['هدف‌ها','goals','/admin/catalogs'],['شهرها','cities','/admin/catalogs'],['مرحله‌های فرم','steps','/admin/forms'],['پرسش‌ها','questions','/admin/forms'],['تکمیل‌شده','completed_onboardings','/admin/users'],['معرفی‌ها','matches','/admin/matches'],['گزارش‌های باز','open_reports','/admin/moderation'],['پیام‌های گزارش‌شده','flagged_messages','/admin/moderation?report_type=message'],['جفت‌های مسدود','blocked_pairs','/admin/matches']] as $card): ?>
        <a class="card stat-card" href="<?= e($card[2]) ?>"><strong><?= e($card[0]) ?></strong><p class="metric"><?= (int)($stats[$card[1]] ?? 0) ?></p></a>
    <?php endforeach; ?>
</div>
<section class="card"><h2>اقدام‌های سریع</h2><div class="grid three quick-links"><a href="/admin/catalogs">مدیریت هدف‌ها، استان‌ها و شهرها</a><a href="/admin/forms">ساخت فرم شناخت</a><a href="/admin/users">بررسی اعضا و پاسخ‌ها</a><a href="/admin/health">سلامت سامانه</a><a href="/admin/settings">تنظیمات سایت</a><a href="/onboarding">پیش‌نمایش شناخت عضو</a></div></section>
<section class="card warning"><strong>یادداشت امنیتی:</strong> ابزارهای معرفی، گفت‌وگو، نمایش اطلاعات، گزارش و رسیدگی برای آزمون مرحله‌ای آماده‌اند.</section>
