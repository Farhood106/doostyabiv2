<section class="chat-header card"><div><span class="eyebrow">گفت‌وگوی ناشناس دوطرفه</span><h1><?= e($chat['anonymous_title'] ?: 'معرفی ناشناس دوطرفه') ?></h1><p class="muted">یادآوری حریم خصوصی: شماره تلفن، ایمیل، نشانی، شبکه اجتماعی، اطلاعات پرداخت یا جزئیات خصوصی دیگر را در پیام‌ها ننویسید.</p></div><a class="button secondary" href="/safety">امنیت و حریم خصوصی</a></section>
<?php if (!empty($chat['anonymous_summary'])): ?><section class="card"><p><?= e($chat['anonymous_summary']) ?></p><span class="pill"><?= e($chat['compatibility_label']) ?></span></section><?php endif; ?>
<?php if (!$canSend): ?><div class="alert warning">این گفت‌وگو فقط خواندنی است؛ یا بسته شده یا معرفی دیگر دوطرفه نیست.</div><?php endif; ?>
<section class="card chat-thread" aria-label="پیام‌های گفت‌وگو">
<?php if (!$messages): ?><p class="muted">هنوز پیامی نیست. یک سلام محترمانه و امن بفرستید.</p><?php endif; ?>
<?php foreach ($messages as $message): ?>
    <article class="message <?= (int)$message['sent_by_me'] === 1 ? 'mine' : 'theirs' ?>">
        <div class="message-body"><?= nl2br(e($message['body'])) ?></div>
        <small><?= (int)$message['sent_by_me'] === 1 ? 'شما' : 'معرفی ناشناس' ?> · <?= e($message['created_at']) ?><?php if ($message['moderation_status'] === 'flagged'): ?> · نیازمند بررسی<?php endif; ?></small>
        <?php if ((int)$message['sent_by_me'] !== 1): ?>
            <form method="post" action="/reports/message" class="inline flag-form">
                <?= csrf_field() ?>
                <input type="hidden" name="chat_id" value="<?= (int)$chat['id'] ?>">
                <input type="hidden" name="message_id" value="<?= (int)$message['id'] ?>">
                <input type="hidden" name="report_type" value="message">
                <select name="report_reason" <?= (int)$message['flagged_by_me'] === 1 ? 'disabled' : '' ?>><option value="harassment">آزار یا فشار</option><option value="spam">هرزنامه</option><option value="inappropriate_content">محتوای نامناسب</option><option value="unsafe_behavior">رفتار ناامن</option><option value="other">مورد دیگر</option></select>
                <input type="text" name="description" placeholder="توضیح اختیاری" maxlength="500" <?= (int)$message['flagged_by_me'] === 1 ? 'disabled' : '' ?>>
                <button class="secondary" <?= (int)$message['flagged_by_me'] === 1 ? 'disabled' : '' ?>><?= (int)$message['flagged_by_me'] === 1 ? 'گزارش شده' : 'گزارش پیام' ?></button>
            </form>
        <?php endif; ?>
    </article>
<?php endforeach; ?>
</section>

<section class="card">
    <span class="eyebrow">کنترل‌های رضایت</span><h2>درخواست‌های نمایش اطلاعات</h2>
    <p class="muted">نمایش اطلاعات اختیاری به رضایت نیاز دارد و فقط از فیلدهای امن استفاده می‌کند: نام نمایشی، شهر یا خلاصه هدف‌های انتخاب‌شده. ایمیل، اطلاعات تماس، پاسخ‌های خصوصی یا ویژه مدیران، پروفایل عمومی و نمایش خودکار در این فرایند وجود ندارد.</p>
    <?php if ($revealedSnapshots): ?>
        <h3>اطلاعات تأییدشده که برای شما قابل مشاهده است</h3>
        <div class="grid two">
        <?php foreach ($revealedSnapshots as $snapshot): ?>
            <div class="builder-section"><strong><?= e($snapshot['title']) ?></strong><p><?= e($snapshot['revealed_value']) ?></p><small>تأیید شده در <?= e($snapshot['visible_at']) ?></small></div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($canSend && $revealTypes): ?>
        <h3>درخواست نمایش</h3>
        <div class="grid three">
        <?php foreach ($revealTypes as $type): ?>
            <form method="post" action="/chats/<?= (int)$chat['id'] ?>/reveal-requests" class="builder-section">
                <?= csrf_field() ?>
                <input type="hidden" name="reveal_type_id" value="<?= (int)$type['id'] ?>">
                <strong><?= e($type['title']) ?></strong> <span class="pill"><?= e(fa_label($type['privacy_level'])) ?></span>
                <p><?= e($type['description']) ?></p>
                <label>یادداشت اختیاری<textarea name="request_message" rows="2" maxlength="500" placeholder="به‌آرامی توضیح دهید چرا مایل به دیدن این مورد هستید."></textarea></label>
                <button>ارسال درخواست</button>
            </form>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <h3>درخواست‌های نمایش</h3>
    <?php if (!$revealRequests): ?><p class="muted">هنوز درخواستی ثبت نشده است.</p><?php endif; ?>
    <?php foreach ($revealRequests as $request): ?>
        <div class="builder-section reveal-request-card">
            <div class="section-title"><strong><?= e($request['title']) ?></strong><span class="pill"><?= e(fa_label($request['status'])) ?> · <?= e(fa_label($request['direction'])) ?></span></div>
            <?php if (!empty($request['request_message'])): ?><p><?= e($request['request_message']) ?></p><?php endif; ?>
            <?php if (!empty($request['response_note'])): ?><p><strong>پاسخ:</strong> <?= e($request['response_note']) ?></p><?php endif; ?>
            <small>درخواست در <?= e($request['requested_at']) ?><?php if (!empty($request['expires_at'])): ?> · انقضا در <?= e($request['expires_at']) ?><?php endif; ?></small>
            <?php if ($request['direction'] === 'incoming' && $request['status'] === 'pending'): ?>
                <form method="post" action="/chats/reveal-requests/respond" class="checks">
                    <?= csrf_field() ?>
                    <input type="hidden" name="chat_id" value="<?= (int)$chat['id'] ?>">
                    <input type="hidden" name="request_id" value="<?= (int)$request['id'] ?>">
                    <input type="text" name="response_note" placeholder="یادداشت اختیاری برای پاسخ" maxlength="500">
                    <button name="status" value="approved">تأیید می‌کنم</button>
                    <button class="secondary" name="status" value="rejected">ترجیح می‌دهم نمایش ندهم</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</section>

<section class="card safety-panel">
    <span class="eyebrow">امنیت</span><h2>گزارش این گفت‌وگو</h2>
    <details><summary>باز کردن فرم گزارش</summary>
        <form method="post" action="/reports/chat" class="grid two">
            <?= csrf_field() ?><input type="hidden" name="chat_id" value="<?= (int)$chat['id'] ?>"><input type="hidden" name="report_type" value="chat">
            <label>دلیل<select name="report_reason"><option value="harassment">آزار یا فشار</option><option value="spam">هرزنامه یا کلاهبرداری</option><option value="unsafe_behavior">رفتار ناامن</option><option value="privacy">نگرانی حریم خصوصی</option><option value="other">مورد دیگر</option></select></label>
            <label class="full">توضیح<textarea name="description" rows="2" maxlength="1000"></textarea></label>
            <button class="secondary">ثبت گزارش گفت‌وگو</button>
        </form>
    </details>
</section>

<?php if ($canSend): ?>
<section class="card composer-card">
    <h2>ارسال پیام</h2>
    <form method="post" action="/chats/<?= (int)$chat['id'] ?>/messages">
        <?= csrf_field() ?>
        <label>پیام<textarea name="body" rows="4" maxlength="2000" required placeholder="یک پیام محترمانه و امن از نظر حریم خصوصی بنویسید..."></textarea></label>
        <button class="button">ارسال</button>
    </form>
</section>
<?php endif; ?>
