<h1>Report #<?= (int)$report['id'] ?></h1>
<section class="card grid two">
    <div><h2>Report</h2><p><strong>Status:</strong> <?= e($report['status']) ?> · <strong>Priority:</strong> <?= e($report['priority']) ?></p><p><strong>Type:</strong> <?= e($report['report_type']) ?> · <strong>Reason:</strong> <?= e($report['report_reason']) ?></p><p><?= nl2br(e($report['description'])) ?></p><small>Created <?= e($report['created_at']) ?></small></div>
    <div><h2>People</h2><p><strong>Reporter:</strong> <?= e($report['reporter_name']) ?> (<?= e($report['reporter_email']) ?>)</p><p><strong>Reported:</strong> <?= e($report['reported_name']) ?><?php if (!empty($report['reported_email'])): ?> (<?= e($report['reported_email']) ?>)<?php endif; ?></p><p><strong>Assigned:</strong> <?= e($report['assigned_admin_name']) ?></p></div>
</section>
<section class="card">
    <h2>Related context</h2>
    <p><strong>Match:</strong> #<?= (int)$report['match_id'] ?> <?= e($report['match_status']) ?> <?php if ($report['compatibility_score'] !== null): ?><?= (int)round((float)$report['compatibility_score']) ?>%<?php endif; ?></p>
    <p><strong>Chat:</strong> #<?= (int)$report['chat_id'] ?> <?= e($report['chat_status']) ?></p>
    <?php if (!empty($report['message_id'])): ?><div class="builder-section"><strong>Reported message #<?= (int)$report['message_id'] ?></strong><p><?= nl2br(e($report['message_body'])) ?></p><small><?= e($report['message_created_at']) ?></small></div><?php endif; ?>
</section>
<section class="card grid two">
    <form method="post" action="/admin/moderation/<?= (int)$report['id'] ?>">
        <?= csrf_field() ?>
        <h2>Moderation update</h2>
        <label>Status<select name="status"><?php foreach (['open','reviewing','resolved','dismissed'] as $status): ?><option value="<?= e($status) ?>" <?= $report['status']===$status?'selected':'' ?>><?= e($status) ?></option><?php endforeach; ?></select></label>
        <label>Priority<select name="priority"><?php foreach (['low','normal','high','urgent'] as $priority): ?><option value="<?= e($priority) ?>" <?= $report['priority']===$priority?'selected':'' ?>><?= e($priority) ?></option><?php endforeach; ?></select></label>
        <label class="check"><input type="checkbox" name="assign_to_me" value="1"> Assign to me</label>
        <label>Resolution note<textarea name="admin_resolution_note" rows="4"><?= e($report['admin_resolution_note']) ?></textarea></label>
        <button>Save report</button>
    </form>
    <form method="post" action="/admin/moderation/<?= (int)$report['id'] ?>/block">
        <?= csrf_field() ?>
        <h2>Safety action</h2>
        <p class="muted">Blocks the reported user for the reporter and disables any related match chat. This does not reveal private details to either member.</p>
        <button class="secondary" onclick="return confirm('Block the reported user for the reporter?')">Block reported user</button>
    </form>
</section>
