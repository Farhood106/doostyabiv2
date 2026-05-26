<h1>مرور سپرهای حریم خصوصی</h1>
<p class="muted">در این بخش فقط آمار و فراداده نمایش داده می‌شود. مقادیر حساس تماس قابل مشاهده نیستند.</p>
<section class="card table-wrap">
<table>
<thead><tr><th>کاربر</th><th>نوع</th><th>یادداشت</th><th>تاریخ</th></tr></thead>
<tbody>
<?php foreach (($items ?? []) as $item): ?>
<tr>
<td><?= e(trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''))) ?> <small class="muted">#<?= (int)$item['user_id'] ?></small></td>
<td><?= e(fa_label($item['type'])) ?></td>
<td><?= e($item['note'] ?? '—') ?></td>
<td><?= e($item['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php if (empty($items)): ?><p class="muted">داده‌ای برای نمایش وجود ندارد.</p><?php endif; ?>
</section>
