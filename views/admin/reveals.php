<h1>Reveal oversight</h1>
<p class="muted">Admins manage reveal type catalog entries and review request activity. Admins cannot force approval in this MVP.</p>
<section class="card">
    <h2>Reveal types</h2>
    <form method="post" action="/admin/reveals/types" class="grid six">
        <?= csrf_field() ?>
        <label>Title<input name="title" required></label>
        <label>Slug<input name="slug" required></label>
        <label>Field<select name="reveal_field_key"><option value="display_name">display_name</option><option value="city">city</option><option value="selected_goals_summary">selected_goals_summary</option></select></label>
        <label>Privacy<select name="privacy_level"><option>low</option><option selected>medium</option><option>high</option></select></label>
        <label>Sort<input type="number" name="sort_order" value="0"></label>
        <label class="check"><input type="checkbox" name="is_active" value="1" checked> Active</label>
        <label class="check"><input type="checkbox" name="requires_mutual_approval" value="1" checked> Mutual approval required</label>
        <label class="full">Description<textarea name="description" rows="2"></textarea></label>
        <button class="button">Add type</button>
    </form>
    <div class="table-wrap"><table><thead><tr><th>ID</th><th>Title</th><th>Slug</th><th>Field</th><th>Privacy</th><th>Approval</th><th>Active</th><th>Edit</th></tr></thead><tbody>
    <?php foreach ($types as $type): ?>
    <tr><td><?= (int)$type['id'] ?></td><td><?= e($type['title']) ?></td><td><?= e($type['slug']) ?></td><td><?= e($type['reveal_field_key']) ?></td><td><?= e($type['privacy_level']) ?></td><td><?= (int)$type['requires_mutual_approval'] ? 'yes' : 'no' ?></td><td><?= (int)$type['is_active'] ? 'yes' : 'no' ?></td><td><form method="post" action="/admin/reveals/types" class="grid two"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$type['id'] ?>"><input name="title" value="<?= e($type['title']) ?>"><input name="slug" value="<?= e($type['slug']) ?>"><select name="reveal_field_key"><option value="display_name" <?= $type['reveal_field_key']==='display_name'?'selected':'' ?>>display_name</option><option value="city" <?= $type['reveal_field_key']==='city'?'selected':'' ?>>city</option><option value="selected_goals_summary" <?= $type['reveal_field_key']==='selected_goals_summary'?'selected':'' ?>>selected_goals_summary</option></select><select name="privacy_level"><option <?= $type['privacy_level']==='low'?'selected':'' ?>>low</option><option <?= $type['privacy_level']==='medium'?'selected':'' ?>>medium</option><option <?= $type['privacy_level']==='high'?'selected':'' ?>>high</option></select><input type="number" name="sort_order" value="<?= (int)$type['sort_order'] ?>"><label class="check"><input type="checkbox" name="is_active" value="1" <?= (int)$type['is_active']?'checked':'' ?>> active</label><label class="check"><input type="checkbox" name="requires_mutual_approval" value="1" <?= (int)$type['requires_mutual_approval']?'checked':'' ?>> approval</label><textarea class="full" name="description" rows="2"><?= e($type['description']) ?></textarea><button>Save</button></form></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<section class="card">
    <h2>Reveal requests</h2>
    <form method="get" action="/admin/reveals" class="grid four">
        <label>Status<select name="status"><option value="">Any</option><?php foreach (['pending','approved','rejected','cancelled','expired'] as $status): ?><option value="<?= e($status) ?>" <?= ($filters['status'] ?? '')===$status?'selected':'' ?>><?= e($status) ?></option><?php endforeach; ?></select></label>
        <label>Type<select name="type_id"><option value="">Any</option><?php foreach ($types as $type): ?><option value="<?= (int)$type['id'] ?>" <?= (int)($filters['type_id'] ?? 0)===(int)$type['id']?'selected':'' ?>><?= e($type['title']) ?></option><?php endforeach; ?></select></label>
        <label>User<select name="user_id"><option value="">Any</option><?php foreach ($users as $member): ?><option value="<?= (int)$member['id'] ?>" <?= (int)($filters['user_id'] ?? 0)===(int)$member['id']?'selected':'' ?>><?= e($member['first_name'].' '.$member['last_name']) ?></option><?php endforeach; ?></select></label>
        <button>Filter</button>
    </form>
    <div class="table-wrap"><table><thead><tr><th>ID</th><th>Type</th><th>Status</th><th>Requester</th><th>Target</th><th>Message</th><th>Response</th><th>Requested</th><th>Responded</th></tr></thead><tbody>
    <?php foreach ($requests as $request): ?>
    <tr><td><?= (int)$request['id'] ?></td><td><?= e($request['title']) ?></td><td><?= e($request['status']) ?></td><td><?= e($request['requester_name']) ?></td><td><?= e($request['target_name']) ?></td><td><?= e($request['request_message']) ?></td><td><?= e($request['response_note']) ?></td><td><?= e($request['requested_at']) ?></td><td><?= e($request['responded_at']) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
