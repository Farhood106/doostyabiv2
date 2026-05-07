<h1><?= e($chat['anonymous_title'] ?: 'Anonymous mutual match') ?></h1>
<p class="muted">Privacy reminder: do not share phone numbers, email addresses, addresses, social handles, payment details, or other contact/private profile data.</p>
<?php if (!empty($chat['anonymous_summary'])): ?><section class="card"><p><?= e($chat['anonymous_summary']) ?></p><span class="pill"><?= e($chat['compatibility_label']) ?></span></section><?php endif; ?>
<?php if (!$canSend): ?><div class="alert warning">This chat is read-only because it is closed or the match is no longer mutual.</div><?php endif; ?>
<section class="card chat-thread">
<?php if (!$messages): ?><p class="muted">No messages yet. Send a privacy-safe hello.</p><?php endif; ?>
<?php foreach ($messages as $message): ?>
    <article class="message <?= (int)$message['sent_by_me'] === 1 ? 'mine' : 'theirs' ?>">
        <div class="message-body"><?= nl2br(e($message['body'])) ?></div>
        <small><?= (int)$message['sent_by_me'] === 1 ? 'You' : 'Anonymous match' ?> · <?= e($message['created_at']) ?><?php if ($message['moderation_status'] === 'flagged'): ?> · flagged<?php endif; ?></small>
        <?php if ((int)$message['sent_by_me'] !== 1): ?>
            <form method="post" action="/chats/messages/flag" class="inline flag-form">
                <?= csrf_field() ?>
                <input type="hidden" name="chat_id" value="<?= (int)$chat['id'] ?>">
                <input type="hidden" name="message_id" value="<?= (int)$message['id'] ?>">
                <input type="text" name="reason" placeholder="Optional reason" maxlength="255" <?= (int)$message['flagged_by_me'] === 1 ? 'disabled' : '' ?>>
                <button class="secondary" <?= (int)$message['flagged_by_me'] === 1 ? 'disabled' : '' ?>><?= (int)$message['flagged_by_me'] === 1 ? 'Flagged' : 'Flag message' ?></button>
            </form>
        <?php endif; ?>
    </article>
<?php endforeach; ?>
</section>
<?php if ($canSend): ?>
<section class="card">
    <h2>Send message</h2>
    <form method="post" action="/chats/<?= (int)$chat['id'] ?>/messages">
        <?= csrf_field() ?>
        <label>Message<textarea name="body" rows="4" maxlength="2000" required placeholder="Write a respectful, privacy-safe message..."></textarea></label>
        <button class="button">Send</button>
    </form>
</section>
<?php endif; ?>
