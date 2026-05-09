<h1>گفت‌وگو #<?= (int)$chat['id'] ?></h1>
<section class="card">
    <p><strong>شرکت‌کنندگان:</strong> <?= e($chat['user_one_name']) ?> ↔ <?= e($chat['user_two_name']) ?></p>
    <p><strong>وضعیت گفت‌وگو:</strong> <?= e(fa_label($chat['status'])) ?> · <strong>وضعیت معرفی:</strong> <?= e(fa_label($chat['match_status'])) ?></p>
    <?php if (!empty($chat['closed_reason'])): ?><p><strong>دلیل بسته‌شدن:</strong> <?= e($chat['closed_reason']) ?></p><?php endif; ?>
</section>
<section class="card chat-thread">
<?php foreach ($messages as $message): ?>
    <article class="message">
        <small><?= e($message['sender_name']) ?> · <?= e($message['created_at']) ?> · <?= e(fa_label($message['moderation_status'])) ?><?php if ((int)$message['flag_count'] > 0): ?> · <?= (int)$message['flag_count'] ?> گزارش<?php endif; ?></small>
        <div class="message-body"><?= nl2br(e($message['body'])) ?></div>
        <?php if (!empty($message['flag_reasons'])): ?><p class="warning"><strong>دلایل گزارش:</strong> <?= e($message['flag_reasons']) ?></p><?php endif; ?>
    </article>
<?php endforeach; ?>
<?php if (!$messages): ?><p>هنوز پیامی ثبت نشده است.</p><?php endif; ?>
</section>
<?php if ($chat['status'] !== 'closed'): ?>
<section class="card">
    <h2>بستن گفت‌وگو</h2>
    <form method="post" action="/admin/chats/<?= (int)$chat['id'] ?>/close">
        <?= csrf_field() ?>
        <label>دلیل<textarea name="reason" rows="3" required placeholder="برای سوابق رسیدگی، دلیل بسته‌شدن گفت‌وگو را کوتاه و روشن بنویسید."></textarea></label>
        <button onclick="return confirm('این گفت‌وگو بسته شود؟')">بستن گفت‌وگو</button>
    </form>
</section>
<?php endif; ?>
