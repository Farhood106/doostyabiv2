<h1>مرکز پشتیبانی</h1>
<section class="card">
<form method="get" action="/admin/support" class="inline-form">
<label>وضعیت <input name="status" value="<?= e($filters['status'] ?? '') ?>"></label>
<label>دسته <input name="category" value="<?= e($filters['category'] ?? '') ?>"></label>
<button class="button secondary">فیلتر</button>
</form>
</section>
<section class="card table-wrap">
<table><thead><tr><th>شناسه</th><th>کاربر</th><th>موضوع</th><th>دسته</th><th>وضعیت</th><th>آخرین بروزرسانی</th><th>عملیات</th></tr></thead><tbody>
<?php foreach (($conversations ?? []) as $c): ?>
<tr>
<td>#<?= (int)$c['id'] ?></td>
<td><?= e(trim(($c['first_name'] ?? '').' '.($c['last_name'] ?? ''))) ?></td>
<td><?= e($c['subject']) ?></td>
<td><?= e(fa_label($c['category'])) ?></td>
<td><?= e(fa_label($c['status'])) ?></td>
<td><?= e($c['updated_at']) ?></td>
<td>
<form method="post" action="/admin/support/assign"><?= csrf_field() ?><input type="hidden" name="conversation_id" value="<?= (int)$c['id'] ?>"><select name="assigned_user_id"><?php foreach (($staff ?? []) as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e(($s['first_name'] ?? '').' '.($s['last_name'] ?? '')) ?></option><?php endforeach; ?></select><select name="assigned_role"><option value="support_agent">کارشناس پشتیبانی</option><option value="advisor">همراه/مشاور</option></select><button class="button secondary">ارجاع</button></form>
<form method="post" action="/admin/support/close"><?= csrf_field() ?><input type="hidden" name="conversation_id" value="<?= (int)$c['id'] ?>"><button class="button">بستن</button></form>
</td>
</tr>
<?php endforeach; ?>
</tbody></table>
</section>
<section class="card"><h2>پاسخ‌های آماده</h2>
<form method="post" action="/admin/support/quick-replies"><?= csrf_field() ?><label>دسته<input name="category" required></label><label>عنوان<input name="title" required></label><label>متن<textarea name="body" rows="3" required></textarea></label><button class="button">ذخیره پاسخ آماده</button></form>
<ul><?php foreach (($quickReplies ?? []) as $q): ?><li><strong><?= e($q['title']) ?>:</strong> <?= e($q['body']) ?></li><?php endforeach; ?></ul>
</section>
