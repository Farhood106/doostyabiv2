<h1>گزارش‌ها و رسیدگی</h1>
<p class="muted">گزارش‌های مربوط به معرفی‌ها، گفت‌وگوها، پیام‌ها و اعضا را بررسی کنید. اقدام‌های مدیر نباید داده خصوصی اعضا را برای گزارش‌دهنده آشکار کند.</p>
<section class="card">
    <form method="get" action="/admin/moderation" class="grid four">
        <label>وضعیت<select name="status"><option value="">همه</option><?php foreach (['open','reviewing','resolved','dismissed'] as $status): ?><option value="<?= e($status) ?>" <?= ($filters['status'] ?? '')===$status?'selected':'' ?>><?= e(fa_label($status)) ?></option><?php endforeach; ?></select></label>
        <label>نوع<select name="report_type"><option value="">همه</option><?php foreach (['match','chat','message','user','spam','harassment','safety','other'] as $type): ?><option value="<?= e($type) ?>" <?= ($filters['report_type'] ?? '')===$type?'selected':'' ?>><?= e(fa_label($type)) ?></option><?php endforeach; ?></select></label>
        <label>اولویت<select name="priority"><option value="">همه</option><?php foreach (['low','normal','high','urgent'] as $priority): ?><option value="<?= e($priority) ?>" <?= ($filters['priority'] ?? '')===$priority?'selected':'' ?>><?= e(fa_label($priority)) ?></option><?php endforeach; ?></select></label>
        <button>فیلتر</button>
    </form>
</section>
<section class="card table-wrap">
<table><thead><tr><th>شناسه</th><th>وضعیت</th><th>اولویت</th><th>نوع</th><th>دلیل</th><th>گزارش‌دهنده</th><th>عضو گزارش‌شده</th><th>مسئول رسیدگی</th><th>زمان ایجاد</th><th>اقدام</th></tr></thead><tbody>
<?php foreach ($reports as $report): ?>
<tr><td><?= (int)$report['id'] ?></td><td><?= e(fa_label($report['status'])) ?></td><td><?= e(fa_label($report['priority'])) ?></td><td><?= e(fa_label($report['report_type'])) ?></td><td><?= e(fa_label($report['report_reason'])) ?></td><td><?= e($report['reporter_name']) ?></td><td><?= e($report['reported_name']) ?></td><td><?= e($report['assigned_admin_name']) ?></td><td><?= e($report['created_at']) ?></td><td><a href="/admin/moderation/<?= (int)$report['id'] ?>">بررسی</a></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php if (!$reports): ?><p>هیچ گزارشی با فیلترهای انتخاب‌شده پیدا نشد.</p><?php endif; ?>
</section>
