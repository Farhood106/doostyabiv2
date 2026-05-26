<?php
$minimumRequired = (int)($settings['minimum_required_answers_before_matching'] ?? 3);
$requiredAnswered = (int)($readiness['required_answered'] ?? 0);
$matchableAnswered = (int)($readiness['matchable_answered'] ?? 0);
$availability = $cardAvailability ?? [];
?>
<section class="section-title"><div><span class="eyebrow">معرفی‌های ناشناس</span><h1>کارت‌های معرفی شما</h1><p class="muted">نام، ایمیل، اطلاعات تماس و پاسخ‌های خصوصی یا ویژه مدیران پنهان می‌ماند. با آرامش می‌توانید تمایل نشان دهید، فعلاً رد کنید، مسدود کنید یا گزارش بدهید.</p></div><a class="button secondary" href="/safety">امنیت و حریم خصوصی</a></section>
<?php if (!$cards): ?>
<section class="card empty-state">
    <?php if ($requiredAnswered < $minimumRequired): ?>
        <h2>برای معرفی دقیق‌تر، چند پاسخ ضروری دیگر لازم است</h2><p>شناخت‌نامه شما هنوز جای کامل‌تر شدن دارد. با چند پاسخ بیشتر، معرفی‌ها دقیق‌تر و آرام‌تر ساخته می‌شوند.</p><p class="help">تا اینجا <?= $requiredAnswered ?> پاسخ ضروری و <?= $matchableAnswered ?> پاسخ قابل استفاده در سازگاری دارید.</p><a class="button" href="/onboarding">ادامه شناخت‌نامه</a>
    <?php elseif ((int)($availability['hidden_cards'] ?? 0) > 0): ?>
        <h2>چند کارت فعلاً در استراحت کوتاه هستند</h2><p>برای جلوگیری از تکرار خسته‌کننده، بعضی معرفی‌های دیده‌شده موقتاً کنار گذاشته شده‌اند. کمی بعد دوباره پیشنهادهای تازه‌تر را بررسی می‌کنیم.</p><a class="button" href="/onboarding">افزودن چند پاسخ تازه</a>
    <?php elseif ((int)($availability['passed_cards'] ?? 0) > 0): ?>
        <h2>فعلاً کارت تازه‌ای بعد از انتخاب‌های قبلی ندارید</h2><p>انتخاب «فعلاً نه» محترم است. سامانه به‌جای تکرار همان کارت‌ها، منتظر داده یا گزینه مناسب‌تر می‌ماند.</p><a class="button" href="/onboarding">به‌روزرسانی شناخت‌نامه</a>
    <?php elseif ((int)($availability['mutual_cards'] ?? 0) > 0): ?>
        <h2>در انتظار قدم بعدی آرام</h2><p>اگر علاقه دوطرفه شکل گرفته باشد، می‌توانید گفت‌وگو را با احترام و بدون عجله ادامه دهید.</p><a class="button" href="/chats">رفتن به گفت‌وگوها</a>
    <?php else: ?>
        <h2>هنوز معرفی‌ای ندارید</h2><p>اگر هنوز کارتی نمی‌بینید، یعنی سامانه در حال پیدا کردن معرفی‌های امن‌تر و معنادارتر است. کامل‌تر کردن چند پاسخ و هدف ارتباطی می‌تواند به پیشنهادهای آرام‌تر و دقیق‌تر کمک کند.</p><?php if ((float)($quality['profile_quality_score'] ?? 0) < 55): ?><p class="help">شناخت‌نامه شما هنوز جای کامل‌تر شدن دارد. چند پاسخ معنادار می‌تواند کیفیت معرفی‌ها را بهتر کند.</p><?php endif; ?><a class="button" href="/onboarding">مرور شناخت‌نامه</a>
    <?php endif; ?>
</section>
<?php endif; ?>
<div class="grid two match-grid">
<?php foreach ($cards as $card): ?>
<?php $status = $card['match_status']; $viewerAction = $card['viewer_action'] ?? ''; $payload = json_decode((string)($card['generated_payload_json'] ?? '{}'), true) ?: []; $tone = $payload['tone'] ?? 'hopeful'; $lowConfidence = (float)$card['confidence_score'] < 45; ?>
<section class="card match-card status-<?= e($status) ?> tone-<?= e($tone) ?>">
    <div class="section-title"><div><span class="eyebrow">معرفی ناشناس</span><h2><?= e($card['title']) ?></h2></div><span class="pill compatibility-pill"><?= e($card['compatibility_label']) ?></span></div>
    <div class="match-score"><span><?= (int)round((float)$card['compatibility_score']) ?>٪</span><small><?= $lowConfidence ? 'برداشت خیلی اولیه، با داده محدود' : 'برداشت اولیه، نه قضاوت قطعی' ?></small></div>
    <p class="match-summary"><?= e($card['summary']) ?></p>
    <?php if ($lowConfidence): ?><p class="help">این کارت کسی را بد یا نامناسب نشان نمی‌دهد؛ فقط یعنی شناخت‌نامه‌ها هنوز برای توضیح دقیق‌تر به چند پاسخ بیشتر نیاز دارند. <a href="/onboarding">تکمیل چند پاسخ</a></p><?php endif; ?>
    <div class="grid two insight-grid"><div class="insight positive"><h3>نشانه‌های دلگرم‌کننده</h3><p><?= nl2br(e($card['strengths_text'])) ?></p></div><div class="insight caution"><h3>با آرامش بیشتر</h3><p><?= nl2br(e($card['cautions_text'])) ?></p></div></div>
    <div class="status-row"><span class="pill">وضعیت معرفی: <?= e(fa_label($status)) ?></span><?php if ($viewerAction): ?><span class="pill">انتخاب شما: <?= e(fa_label($viewerAction)) ?></span><?php endif; ?></div>
    <form method="post" action="/matches/action" class="checks action-row">
        <?= csrf_field() ?><input type="hidden" name="match_id" value="<?= (int)$card['match_id'] ?>">
        <button name="action" value="interested" <?= $viewerAction === 'interested' ? 'disabled' : '' ?>>مایلم بیشتر بدانم</button>
        <button class="secondary" name="action" value="pass" <?= $viewerAction === 'pass' ? 'disabled' : '' ?>>فعلاً نه</button>
        <button class="secondary quiet-danger" name="action" value="block" onclick="return confirm('این معرفی ناشناس مسدود شود؟')">مسدود کردن</button>
    </form>
    <?php if ($status === 'mutual'): ?><p class="success-inline">علاقه دوطرفه ثبت شده است؛ می‌توانید گفت‌وگو را آرام و محترمانه شروع کنید. <a href="/chats">رفتن به گفت‌وگوها</a>.</p><?php endif; ?>
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
