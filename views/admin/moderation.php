<h1>Moderation reports</h1>
<p class="muted">Review reports about matches, chats, messages, and users. Admin actions here do not expose member private data to reporters.</p>
<section class="card">
    <form method="get" action="/admin/moderation" class="grid four">
        <label>Status<select name="status"><option value="">Any</option><?php foreach (['open','reviewing','resolved','dismissed'] as $status): ?><option value="<?= e($status) ?>" <?= ($filters['status'] ?? '')===$status?'selected':'' ?>><?= e($status) ?></option><?php endforeach; ?></select></label>
        <label>Type<select name="report_type"><option value="">Any</option><?php foreach (['match','chat','message','user','spam','harassment','safety','other'] as $type): ?><option value="<?= e($type) ?>" <?= ($filters['report_type'] ?? '')===$type?'selected':'' ?>><?= e($type) ?></option><?php endforeach; ?></select></label>
        <label>Priority<select name="priority"><option value="">Any</option><?php foreach (['low','normal','high','urgent'] as $priority): ?><option value="<?= e($priority) ?>" <?= ($filters['priority'] ?? '')===$priority?'selected':'' ?>><?= e($priority) ?></option><?php endforeach; ?></select></label>
        <button>Filter</button>
    </form>
</section>
<section class="card table-wrap">
<table><thead><tr><th>ID</th><th>Status</th><th>Priority</th><th>Type</th><th>Reason</th><th>Reporter</th><th>Reported</th><th>Assigned</th><th>Created</th><th>Action</th></tr></thead><tbody>
<?php foreach ($reports as $report): ?>
<tr><td><?= (int)$report['id'] ?></td><td><?= e($report['status']) ?></td><td><?= e($report['priority']) ?></td><td><?= e($report['report_type']) ?></td><td><?= e($report['report_reason']) ?></td><td><?= e($report['reporter_name']) ?></td><td><?= e($report['reported_name']) ?></td><td><?= e($report['assigned_admin_name']) ?></td><td><?= e($report['created_at']) ?></td><td><a href="/admin/moderation/<?= (int)$report['id'] ?>">Review</a></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php if (!$reports): ?><p>No reports match the selected filters.</p><?php endif; ?>
</section>
