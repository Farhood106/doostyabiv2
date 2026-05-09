<section class="section-title"><div><span class="eyebrow">Mutual-only conversations</span><h1>Chats</h1><p class="muted">Chats open only after mutual interest. Conversations stay anonymous until a reveal request is approved.</p></div><a class="button secondary" href="/safety">Safety Center</a></section>
<?php if (!$chats): ?><section class="card empty-state"><h2>No chats yet</h2><p>When both people choose Interested, a private chat appears here automatically. Until then, your matches remain anonymous cards.</p><a class="button" href="/matches">View matches</a></section><?php endif; ?>
<div class="grid two chat-list">
<?php foreach ($chats as $chat): ?>
<section class="card chat-preview">
    <div class="section-title"><div><span class="eyebrow">Anonymous chat</span><h2><?= e($chat['anonymous_title'] ?: 'Mutual match') ?></h2></div><span class="pill status-pill"><?= e($chat['status']) ?></span></div>
    <p><strong>Match:</strong> <?= e($chat['match_status']) ?><?php if (!empty($chat['compatibility_label'])): ?> · <?= e($chat['compatibility_label']) ?><?php endif; ?></p>
    <?php if (!empty($chat['last_message'])): ?><p class="last-message"><?= e($chat['last_message']) ?></p><small>Last message: <?= e($chat['last_message_at']) ?></small><?php else: ?><p class="muted">No messages yet. Send a privacy-safe hello when you are ready.</p><?php endif; ?>
    <?php if ($chat['status'] === 'closed' || $chat['match_status'] === 'blocked'): ?><p class="warning">This chat is read-only.</p><?php endif; ?>
    <p><a class="button" href="/chats/<?= (int)$chat['id'] ?>">Open conversation</a></p>
</section>
<?php endforeach; ?>
</div>
