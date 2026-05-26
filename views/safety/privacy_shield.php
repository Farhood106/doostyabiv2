<h1>سپر حریم خصوصی</h1>
<p class="muted">افرادی که ترجیح می‌دهید هرگز به شما معرفی نشوند را اینجا ثبت کنید. اطلاعات به‌صورت محافظت‌شده ذخیره می‌شود و به کسی اطلاع‌رسانی نمی‌شود.</p>
<section class="card">
<form method="post" action="/safety/privacy-shield">
<?= csrf_field() ?>
<label>نوع شناسه
<select name="type" required>
<option value="phone">شماره تلفن</option>
<option value="email">ایمیل</option>
<option value="full_name">نام کامل</option>
<option value="username">نام کاربری</option>
</select>
</label>
<label>مقدار<input name="value" maxlength="255" required></label>
<label>یادداشت (اختیاری)<input name="note" maxlength="255"></label>
<button class="button">افزودن به سپر حریم خصوصی</button>
</form>
</section>
<section class="card table-wrap"><h2>فهرست شما</h2>
<table><thead><tr><th>نوع</th><th>یادداشت</th><th>تاریخ</th><th></th></tr></thead><tbody>
<?php foreach (($items ?? []) as $item): ?>
<tr>
<td><?= e(fa_label($item['type'])) ?></td>
<td><?= e($item['note'] ?? '—') ?></td>
<td><?= e($item['created_at']) ?></td>
<td><form method="post" action="/safety/privacy-shield/delete" onsubmit="return confirm('این مورد حذف شود؟');"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><button class="button secondary">حذف</button></form></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php if (empty($items)): ?><p class="muted">هنوز موردی ثبت نشده است.</p><?php endif; ?>
</section>
