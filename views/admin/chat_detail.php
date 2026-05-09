<h1>Chat #<?= (int)$chat['id'] ?></h1>
<section class="card">
    <p><strong>Participants:</strong> <?= e($chat['user_one_name']) ?> ↔ <?= e($chat['user_two_name']) ?></p>
    <p><strong>Chat status:</strong> <?= e($chat['status']) ?> · <strong>Match status:</strong> <?= e($chat['match_status']) ?></p>
    <?php if (!empty($chat['closed_reason'])): ?><p><strong>Closed reason:</strong> <?= e($chat['closed_reason']) ?></p><?php endif; ?>
</section>
<section class="card chat-thread">
<?php foreach ($messages as $message): ?>
    <article class="message">
        <small><?= e($message['sender_name']) ?> · <?= e($message['created_at']) ?> · <?= e($message['moderation_status']) ?><?php if ((int)$message['flag_count'] > 0): ?> · <?= (int)$message['flag_count'] ?> flag(s)<?php endif; ?></small>
        <div class="message-body"><?= nl2br(e($message['body'])) ?></div>
        <?php if (!empty($message['flag_reasons'])): ?><p class="warning"><strong>Flag reasons:</strong> <?= e($message['flag_reasons']) ?></p><?php endif; ?>
    </article>
<?php endforeach; ?>
<?php if (!$messages): ?><p>No messages yet.</p><?php endif; ?>
</section>
<?php if ($chat['status'] !== 'closed'): ?>
<section class="card">
    <h2>Close chat</h2>
    <form method="post" action="/admin/chats/<?= (int)$chat['id'] ?>/close">
        <?= csrf_field() ?>
        <label>Reason<textarea name="reason" rows="3" required placeholder="Explain why this chat is being closed for moderation records."></textarea></label>
        <button onclick="return confirm('Close this chat?')">Close chat</button>
    </form>
</section>
<?php endif; ?>
