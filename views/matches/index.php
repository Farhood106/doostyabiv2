<section class="section-title"><div><span class="eyebrow">معرفی‌های ناشناس</span><h1>کارت‌های معرفی شما</h1><p class="muted">نام، ایمیل، اطلاعات تماس و پاسخ‌های خصوصی یا ویژه مدیران پنهان می‌ماند. با آرامش می‌توانید تمایل نشان دهید، فعلاً رد کنید، مسدود کنید یا گزارش بدهید.</p></div><a class="button secondary" href="/safety">امنیت و حریم خصوصی</a></section>
<?php if (!$cards): ?><section class="card empty-state"><h2>هنوز معرفی‌ای ندارید</h2><p>اگر هنوز کارتی نمی‌بینید، یعنی سامانه در حال پیدا کردن معرفی‌های امن‌تر و معنادارتر است. کامل‌تر کردن چند پاسخ و هدف ارتباطی می‌تواند به پیشنهادهای آرام‌تر و دقیق‌تر کمک کند.</p><?php if ((float)($quality['profile_quality_score'] ?? 0) < 55): ?><p class="help">شناخت‌نامه شما هنوز جای کامل‌تر شدن دارد. چند پاسخ معنادار می‌تواند کیفیت معرفی‌ها را بهتر کند.</p><?php endif; ?><a class="button" href="/onboarding">مرور شناخت‌نامه</a></section><?php endif; ?>
<div class="grid two match-grid">
<?php foreach ($cards as $card): ?>
<?php $status = $card['match_status']; $viewerAction = $card['viewer_action'] ?? ''; ?>
<section class="card match-card status-<?= e($status) ?>">
    <div class="section-title"><div><span class="eyebrow">معرفی ناشناس</span><h2><?= e($card['title']) ?></h2></div><span class="pill compatibility-pill"><?= e($card['compatibility_label']) ?></span></div>
    <div class="match-score"><span><?= (int)round((float)$card['compatibility_score']) ?>٪</span><small>برآورد اولیه، نه قضاوت قطعی</small></div>
    <p class="match-summary"><?= e($card['summary']) ?></p>
    <div class="grid two insight-grid"><div class="insight positive"><h3>نقاط قوت</h3><p><?= nl2br(e($card['strengths_text'])) ?></p></div><div class="insight caution"><h3>برای شناخت بیشتر</h3><p><?= nl2br(e($card['cautions_text'])) ?></p></div></div>
    <div class="status-row"><span class="pill">وضعیت معرفی: <?= e(fa_label($status)) ?></span><?php if ($viewerAction): ?><span class="pill">انتخاب شما: <?= e(fa_label($viewerAction)) ?></span><?php endif; ?></div>
    <form method="post" action="/matches/action" class="checks action-row">
        <?= csrf_field() ?><input type="hidden" name="match_id" value="<?= (int)$card['match_id'] ?>">
        <button name="action" value="interested" <?= $viewerAction === 'interested' ? 'disabled' : '' ?>>مایلم بیشتر بدانم</button>
        <button class="secondary" name="action" value="pass" <?= $viewerAction === 'pass' ? 'disabled' : '' ?>>فعلاً نه</button>
        <button class="secondary quiet-danger" name="action" value="block" onclick="return confirm('این معرفی ناشناس مسدود شود؟')">مسدود کردن</button>
    </form>
    <?php if ($status === 'mutual'): ?><p class="success-inline">علاقه دوطرفه ثبت شده است. <a href="/chats">رفتن به گفت‌وگوها</a>.</p><?php endif; ?>
    <details class="report-box"><summary>گزارش نگرانی امنیتی</summary>
        <form method="post" action="/reports/match" class="grid two compact-form">
            <?= csrf_field() ?><input type="hidden" name="match_id" value="<?= (int)$card['match_id'] ?>"><input type="hidden" name="report_type" value="match">
            <label>دلیل<select name="report_reason"><option value="harassment">آزار یا فشار</option><option value="spam">هرزنامه یا کلاهبرداری</option><option value="fake_profile">اطلاعات غیرواقعی</option><option value="unsafe_behavior">رفتار ناامن</option><option value="privacy">نگرانی حریم خصوصی</option><option value="other">مورد دیگر</option></select></label>
            <label class="full">توضیح<textarea name="description" rows="2" maxlength="1000" placeholder="فقط اطلاعات لازم برای بررسی امن مدیران را بنویسید."></textarea></label>
            <button class="secondary">ثبت گزارش</button>
        </form>
    </details>
</section>
<?php endforeach; ?>
</div>
