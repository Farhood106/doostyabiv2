<h1>Chats</h1>
<p class="muted">Chats open only after mutual interest. Conversations stay anonymous: no email, contact, reveal request, or public profile data is shown.</p>
<?php if (!$chats): ?><section class="card"><h2>No chats yet</h2><p>When a match becomes mutual, a private chat will appear here automatically.</p></section><?php endif; ?>
<div class="grid two">
<?php foreach ($chats as $chat): ?>
<section class="card">
    <div class="section-title"><h2><?= e($chat['anonymous_title'] ?: 'Anonymous mutual match') ?></h2><span class="pill"><?= e($chat['status']) ?></span></div>
    <p><strong>Match:</strong> <?= e($chat['match_status']) ?><?php if (!empty($chat['compatibility_label'])): ?> · <?= e($chat['compatibility_label']) ?><?php endif; ?></p>
    <?php if (!empty($chat['last_message'])): ?><p><?= e($chat['last_message']) ?></p><small>Last message: <?= e($chat['last_message_at']) ?></small><?php else: ?><p class="muted">No messages yet.</p><?php endif; ?>
    <?php if ($chat['status'] === 'closed' || $chat['match_status'] === 'blocked'): ?><p class="warning">This chat is disabled.</p><?php endif; ?>
    <p><a class="button" href="/chats/<?= (int)$chat['id'] ?>">Open chat</a></p>
</section>
<?php endforeach; ?>
</div>
