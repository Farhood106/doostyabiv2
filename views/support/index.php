<h1>مرکز پشتیبانی انسانی</h1>
<p class="muted">اگر درباره امنیت، شروع گفت‌وگو یا روند استفاده از سامانه نیاز به همراهی دارید، اینجا با تیم پشتیبانی در ارتباط می‌شوید.</p>
<section class="card">
<form method="post" action="/support">
<?= csrf_field() ?>
<label>دسته‌بندی
<select name="category"><?php foreach (($categories ?? []) as $cat): ?><option value="<?= e($cat) ?>"><?= e(fa_label($cat)) ?></option><?php endforeach; ?></select>
</label>
<label>موضوع<input name="subject" maxlength="180" required></label>
<label>پیام<textarea name="message" rows="4" maxlength="3000" required></textarea></label>
<button class="button">ثبت گفتگو با پشتیبانی</button>
</form>
</section>
<section class="card table-wrap"><h2>گفتگوهای پشتیبانی شما</h2><table><thead><tr><th>موضوع</th><th>دسته</th><th>وضعیت</th><th>آخرین به‌روزرسانی</th><th></th></tr></thead><tbody><?php foreach (($conversations ?? []) as $c): ?><tr><td><?= e($c['subject']) ?></td><td><?= e(fa_label($c['category'])) ?></td><td><span class="pill"><?= e(fa_label($c['status'])) ?></span></td><td><?= e($c['updated_at']) ?></td><td><a href="/support/<?= (int)$c['id'] ?>">مشاهده</a></td></tr><?php endforeach; ?></tbody></table><?php if (empty($conversations)): ?><p class="muted">هنوز گفتگویی ثبت نشده است.</p><?php endif; ?></section>
