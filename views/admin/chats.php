<h1>رسیدگی گفت‌وگوها</h1>
<p class="muted">مدیران می‌توانند گفت‌وگوهای دوطرفه، پیام‌های گزارش‌شده و گفت‌وگوهای بسته را بررسی کنند. اطلاعات تماس و داده‌های خصوصی نباید در رابط اعضا نمایش داده شود.</p>
<section class="card table-wrap">
<table><thead><tr><th>شناسه</th><th>معرفی</th><th>وضعیت</th><th>وضعیت معرفی</th><th>پیام‌ها</th><th>گزارش‌ها</th><th>به‌روزرسانی</th><th>اقدام‌ها</th></tr></thead><tbody>
<?php foreach ($chats as $chat): ?>
<tr>
    <td><?= (int)$chat['id'] ?></td>
    <td><?= e($chat['user_one_name']) ?> ↔ <?= e($chat['user_two_name']) ?></td>
    <td><?= e(fa_label($chat['status'])) ?></td>
    <td><?= e(fa_label($chat['match_status'])) ?></td>
    <td><?= (int)$chat['message_count'] ?></td>
    <td><?= (int)$chat['flag_count'] ?></td>
    <td><?= e($chat['updated_at']) ?></td>
    <td><a href="/admin/chats/<?= (int)$chat['id'] ?>">مشاهده پیام‌ها</a></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php if (!$chats): ?><p>هنوز گفت‌وگویی ساخته نشده است.</p><?php endif; ?>
</section>
