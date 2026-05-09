<h1>Chat moderation</h1>
<p class="muted">Admins can review mutual-match chats, flagged messages, and closed conversations. Contact/private profile data should not appear in the member chat UI.</p>
<section class="card table-wrap">
<table><thead><tr><th>ID</th><th>Match</th><th>Status</th><th>Match status</th><th>Messages</th><th>Flags</th><th>Updated</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($chats as $chat): ?>
<tr>
    <td><?= (int)$chat['id'] ?></td>
    <td><?= e($chat['user_one_name']) ?> ↔ <?= e($chat['user_two_name']) ?></td>
    <td><?= e($chat['status']) ?></td>
    <td><?= e($chat['match_status']) ?></td>
    <td><?= (int)$chat['message_count'] ?></td>
    <td><?= (int)$chat['flag_count'] ?></td>
    <td><?= e($chat['updated_at']) ?></td>
    <td><a href="/admin/chats/<?= (int)$chat['id'] ?>">View messages</a></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php if (!$chats): ?><p>No chats have been created yet.</p><?php endif; ?>
</section>
