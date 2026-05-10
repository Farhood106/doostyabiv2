<h1>گزارش #<?= (int)$report['id'] ?></h1>
<section class="card grid two">
    <div><h2>گزارش</h2><p><strong>وضعیت:</strong> <?= e(fa_label($report['status'])) ?> · <strong>اولویت:</strong> <?= e(fa_label($report['priority'])) ?></p><p><strong>نوع:</strong> <?= e(fa_label($report['report_type'])) ?> · <strong>دلیل:</strong> <?= e(fa_label($report['report_reason'])) ?></p><p><?= nl2br(e($report['description'])) ?></p><small>ایجاد شده در <?= e($report['created_at']) ?></small></div>
    <div><h2>افراد مرتبط</h2><p><strong>گزارش‌دهنده:</strong> <?= e($report['reporter_name']) ?> (<?= e($report['reporter_email']) ?>)</p><p><strong>عضو گزارش‌شده:</strong> <?= e($report['reported_name']) ?><?php if (!empty($report['reported_email'])): ?> (<?= e($report['reported_email']) ?>)<?php endif; ?></p><p><strong>مسئول رسیدگی:</strong> <?= e($report['assigned_admin_name']) ?></p></div>
</section>
<section class="card">
    <h2>زمینه مرتبط</h2>
    <p><strong>معرفی:</strong> #<?= (int)$report['match_id'] ?> <?= e(fa_label($report['match_status'])) ?> <?php if ($report['compatibility_score'] !== null): ?><?= (int)round((float)$report['compatibility_score']) ?>٪<?php endif; ?></p>
    <p><strong>گفت‌وگو:</strong> #<?= (int)$report['chat_id'] ?> <?= e(fa_label($report['chat_status'])) ?></p>
    <?php if (!empty($report['message_id'])): ?><div class="builder-section"><strong>پیام گزارش‌شده #<?= (int)$report['message_id'] ?></strong><p><?= nl2br(e($report['message_body'])) ?></p><small><?= e($report['message_created_at']) ?></small></div><?php endif; ?>
</section>
<section class="card grid two">
    <form method="post" action="/admin/moderation/<?= (int)$report['id'] ?>">
        <?= csrf_field() ?>
        <h2>به‌روزرسانی رسیدگی</h2>
        <label>وضعیت<select name="status"><?php foreach (['open','reviewing','resolved','dismissed'] as $status): ?><option value="<?= e($status) ?>" <?= $report['status']===$status?'selected':'' ?>><?= e(fa_label($status)) ?></option><?php endforeach; ?></select></label>
        <label>اولویت<select name="priority"><?php foreach (['low','normal','high','urgent'] as $priority): ?><option value="<?= e($priority) ?>" <?= $report['priority']===$priority?'selected':'' ?>><?= e(fa_label($priority)) ?></option><?php endforeach; ?></select></label>
        <label class="check"><input type="checkbox" name="assign_to_me" value="1"> رسیدگی به من سپرده شود</label>
        <label>یادداشت رسیدگی<textarea name="admin_resolution_note" rows="4"><?= e($report['admin_resolution_note']) ?></textarea></label>
        <button>ذخیره گزارش</button>
    </form>
    <form method="post" action="/admin/moderation/<?= (int)$report['id'] ?>/block">
        <?= csrf_field() ?>
        <h2>اقدام امنیتی</h2>
        <p class="muted">عضو گزارش‌شده را برای گزارش‌دهنده مسدود می‌کند و گفت‌وگوی معرفی مرتبط را غیرفعال می‌کند. این کار جزئیات خصوصی را برای هیچ‌کدام آشکار نمی‌کند.</p>
        <button class="secondary" onclick="return confirm('عضو گزارش‌شده برای گزارش‌دهنده مسدود شود؟')">مسدود کردن عضو گزارش‌شده</button>
    </form>
</section>
