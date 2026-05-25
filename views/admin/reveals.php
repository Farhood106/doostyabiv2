<h1>مدیریت درخواست‌های نمایش</h1>
<p class="muted">مدیران نوع‌های مجاز نمایش اطلاعات و فعالیت درخواست‌ها را بررسی می‌کنند. مدیر نمی‌تواند در این MVP به‌جای عضو تأیید اجباری انجام دهد.</p>
<section class="card">
    <h2>نوع‌های نمایش</h2>
    <form method="post" action="/admin/reveals/types" class="grid six">
        <?= csrf_field() ?>
        <label>عنوان<input name="title" required></label>
        <label>شناسه متنی<input name="slug" required></label>
        <label>فیلد امن<select name="reveal_field_key"><option value="display_name">نام نمایشی</option><option value="city">شهر</option><option value="selected_goals_summary">خلاصه هدف‌ها</option></select></label>
        <label>حساسیت<select name="privacy_level"><option value="low">کم</option><option value="medium" selected>متوسط</option><option value="high">زیاد</option></select></label>
        <label>ترتیب<input type="number" name="sort_order" value="0"></label>
        <label class="check"><input type="checkbox" name="is_active" value="1" checked> فعال</label>
        <label class="check"><input type="checkbox" name="requires_mutual_approval" value="1" checked> نیازمند تأیید دوطرفه</label>
        <label class="full">توضیح<textarea name="description" rows="2"></textarea></label>
        <button class="button">افزودن نوع نمایش</button>
    </form>
    <div class="table-wrap"><table><thead><tr><th>شناسه</th><th>عنوان</th><th>شناسه متنی</th><th>فیلد</th><th>حساسیت</th><th>تأیید</th><th>فعال</th><th>ویرایش</th></tr></thead><tbody>
    <?php foreach ($types as $type): ?>
    <tr><td><?= (int)$type['id'] ?></td><td><?= e($type['title']) ?></td><td><?= e($type['slug']) ?></td><td><?= e(fa_label($type['reveal_field_key'])) ?></td><td><?= e(fa_label($type['privacy_level'])) ?></td><td><?= (int)$type['requires_mutual_approval'] ? 'بله' : 'خیر' ?></td><td><?= (int)$type['is_active'] ? 'بله' : 'خیر' ?></td><td><form method="post" action="/admin/reveals/types" class="grid two"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$type['id'] ?>"><input name="title" value="<?= e($type['title']) ?>"><input name="slug" value="<?= e($type['slug']) ?>"><select name="reveal_field_key"><option value="display_name" <?= $type['reveal_field_key']==='display_name'?'selected':'' ?>>نام نمایشی</option><option value="city" <?= $type['reveal_field_key']==='city'?'selected':'' ?>>شهر</option><option value="selected_goals_summary" <?= $type['reveal_field_key']==='selected_goals_summary'?'selected':'' ?>>خلاصه هدف‌ها</option></select><select name="privacy_level"><option value="low" <?= $type['privacy_level']==='low'?'selected':'' ?>>کم</option><option value="medium" <?= $type['privacy_level']==='medium'?'selected':'' ?>>متوسط</option><option value="high" <?= $type['privacy_level']==='high'?'selected':'' ?>>زیاد</option></select><input type="number" name="sort_order" value="<?= (int)$type['sort_order'] ?>"><label class="check"><input type="checkbox" name="is_active" value="1" <?= (int)$type['is_active']?'checked':'' ?>> فعال</label><label class="check"><input type="checkbox" name="requires_mutual_approval" value="1" <?= (int)$type['requires_mutual_approval']?'checked':'' ?>> نیازمند تأیید</label><textarea class="full" name="description" rows="2"><?= e($type['description']) ?></textarea><button>ذخیره</button></form></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<section class="card">
    <h2>درخواست‌های نمایش</h2>
    <form method="get" action="/admin/reveals" class="grid four">
        <label>وضعیت<select name="status"><option value="">همه</option><?php foreach (['pending','approved','rejected','cancelled','expired'] as $status): ?><option value="<?= e($status) ?>" <?= ($filters['status'] ?? '')===$status?'selected':'' ?>><?= e(fa_label($status)) ?></option><?php endforeach; ?></select></label>
        <label>نوع<select name="type_id"><option value="">همه</option><?php foreach ($types as $type): ?><option value="<?= (int)$type['id'] ?>" <?= (int)($filters['type_id'] ?? 0)===(int)$type['id']?'selected':'' ?>><?= e($type['title']) ?></option><?php endforeach; ?></select></label>
        <label>عضو<select name="user_id"><option value="">همه</option><?php foreach ($users as $member): ?><option value="<?= (int)$member['id'] ?>" <?= (int)($filters['user_id'] ?? 0)===(int)$member['id']?'selected':'' ?>><?= e($member['first_name'].' '.$member['last_name']) ?></option><?php endforeach; ?></select></label>
        <button>فیلتر</button>
    </form>
    <div class="table-wrap"><table><thead><tr><th>شناسه</th><th>نوع</th><th>وضعیت</th><th>درخواست‌دهنده</th><th>طرف مقابل</th><th>پیام</th><th>پاسخ</th><th>زمان درخواست</th><th>زمان پاسخ</th></tr></thead><tbody>
    <?php foreach ($requests as $request): ?>
    <tr><td><?= (int)$request['id'] ?></td><td><?= e($request['title']) ?></td><td><?= e(fa_label($request['status'])) ?></td><td><?= e($request['requester_name']) ?></td><td><?= e($request['target_name']) ?></td><td><?= e($request['request_message']) ?></td><td><?= e($request['response_note']) ?></td><td><?= e($request['requested_at']) ?></td><td><?= e($request['responded_at']) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
