<section class="section-title"><div><span class="eyebrow">گفت‌وگوی فقط دوطرفه</span><h1>گفت‌وگوها</h1><p class="muted">گفت‌وگو فقط پس از علاقه دوطرفه باز می‌شود. تا زمانی که درخواست نمایش تأیید نشود، گفت‌وگو ناشناس می‌ماند.</p></div><a class="button secondary" href="/safety">امنیت و حریم خصوصی</a></section>
<?php if (!$chats): ?><section class="card empty-state"><h2>هنوز گفت‌وگویی ندارید</h2><p>وقتی هر دو نفر گزینه «مایلم بیشتر بدانم» را انتخاب کنند، گفت‌وگوی خصوصی اینجا ساخته می‌شود. تا آن زمان معرفی‌ها ناشناس می‌مانند.</p><a class="button" href="/matches">دیدن معرفی‌ها</a></section><?php endif; ?>
<div class="grid two chat-list">
<?php foreach ($chats as $chat): ?>
<section class="card chat-preview">
    <div class="section-title"><div><span class="eyebrow">گفت‌وگوی ناشناس</span><h2><?= e($chat['anonymous_title'] ?: 'معرفی دوطرفه') ?></h2></div><span class="pill status-pill"><?= e(fa_label($chat['status'])) ?></span></div>
    <p><strong>وضعیت معرفی:</strong> <?= e(fa_label($chat['match_status'])) ?><?php if (!empty($chat['compatibility_label'])): ?> · <?= e($chat['compatibility_label']) ?><?php endif; ?></p>
    <?php if (!empty($chat['last_message'])): ?><p class="last-message"><?= e($chat['last_message']) ?></p><small>آخرین پیام: <?= e($chat['last_message_at']) ?></small><?php else: ?><p class="muted">هنوز پیامی نیست. هر وقت آماده بودید، یک سلام محترمانه و امن بفرستید.</p><?php endif; ?>
    <?php if ($chat['status'] === 'closed' || $chat['match_status'] === 'blocked'): ?><p class="warning">این گفت‌وگو فقط خواندنی است.</p><?php endif; ?>
    <p><a class="button" href="/chats/<?= (int)$chat['id'] ?>">باز کردن گفت‌وگو</a></p>
</section>
<?php endforeach; ?>
</div>
