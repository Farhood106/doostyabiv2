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
            <form method="post" action="/reports/message" class="inline flag-form">
                <?= csrf_field() ?>
                <input type="hidden" name="chat_id" value="<?= (int)$chat['id'] ?>">
                <input type="hidden" name="message_id" value="<?= (int)$message['id'] ?>">
                <input type="hidden" name="report_type" value="message">
                <select name="report_reason" <?= (int)$message['flagged_by_me'] === 1 ? 'disabled' : '' ?>><option value="harassment">Harassment</option><option value="spam">Spam</option><option value="inappropriate_content">Inappropriate</option><option value="unsafe_behavior">Unsafe</option><option value="other">Other</option></select>
                <input type="text" name="description" placeholder="Optional details" maxlength="500" <?= (int)$message['flagged_by_me'] === 1 ? 'disabled' : '' ?>>
                <button class="secondary" <?= (int)$message['flagged_by_me'] === 1 ? 'disabled' : '' ?>><?= (int)$message['flagged_by_me'] === 1 ? 'Reported' : 'Report message' ?></button>
            </form>
        <?php endif; ?>
    </article>
<?php endforeach; ?>
</section>

<section class="card">
    <h2>Consent-based reveals</h2>
    <p class="muted">Optional reveal steps require consent and only use safe mapped fields: display name, city, or selected goals summary. Email, contact info, private/admin-only answers, public profiles, and automatic reveals are not included.</p>
    <?php if ($revealedSnapshots): ?>
        <h3>Approved info visible to you</h3>
        <div class="grid two">
        <?php foreach ($revealedSnapshots as $snapshot): ?>
            <div class="builder-section"><strong><?= e($snapshot['title']) ?></strong><p><?= e($snapshot['revealed_value']) ?></p><small>Approved <?= e($snapshot['visible_at']) ?></small></div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($canSend && $revealTypes): ?>
        <h3>Request a reveal</h3>
        <div class="grid three">
        <?php foreach ($revealTypes as $type): ?>
            <form method="post" action="/chats/<?= (int)$chat['id'] ?>/reveal-requests" class="builder-section">
                <?= csrf_field() ?>
                <input type="hidden" name="reveal_type_id" value="<?= (int)$type['id'] ?>">
                <strong><?= e($type['title']) ?></strong> <span class="pill"><?= e($type['privacy_level']) ?></span>
                <p><?= e($type['description']) ?></p>
                <label>Optional note<textarea name="request_message" rows="2" maxlength="500" placeholder="Why would you like to see this?"></textarea></label>
                <button>Request</button>
            </form>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <h3>Reveal requests</h3>
    <?php if (!$revealRequests): ?><p class="muted">No reveal requests yet.</p><?php endif; ?>
    <?php foreach ($revealRequests as $request): ?>
        <div class="builder-section">
            <div class="section-title"><strong><?= e($request['title']) ?></strong><span class="pill"><?= e($request['status']) ?> · <?= e($request['direction']) ?></span></div>
            <?php if (!empty($request['request_message'])): ?><p><?= e($request['request_message']) ?></p><?php endif; ?>
            <?php if (!empty($request['response_note'])): ?><p><strong>Response:</strong> <?= e($request['response_note']) ?></p><?php endif; ?>
            <small>Requested <?= e($request['requested_at']) ?><?php if (!empty($request['expires_at'])): ?> · expires <?= e($request['expires_at']) ?><?php endif; ?></small>
            <?php if ($request['direction'] === 'incoming' && $request['status'] === 'pending'): ?>
                <form method="post" action="/chats/reveal-requests/respond" class="checks">
                    <?= csrf_field() ?>
                    <input type="hidden" name="chat_id" value="<?= (int)$chat['id'] ?>">
                    <input type="hidden" name="request_id" value="<?= (int)$request['id'] ?>">
                    <input type="text" name="response_note" placeholder="Optional response note" maxlength="500">
                    <button name="status" value="approved">Approve</button>
                    <button class="secondary" name="status" value="rejected">Reject</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</section>


<section class="card">
    <h2>Report this chat</h2>
    <details><summary>Open report form</summary>
        <form method="post" action="/reports/chat" class="grid two">
            <?= csrf_field() ?><input type="hidden" name="chat_id" value="<?= (int)$chat['id'] ?>"><input type="hidden" name="report_type" value="chat">
            <label>Reason<select name="report_reason"><option value="harassment">Harassment</option><option value="spam">Spam or scam</option><option value="unsafe_behavior">Unsafe behavior</option><option value="privacy">Privacy concern</option><option value="other">Other</option></select></label>
            <label class="full">Description<textarea name="description" rows="2" maxlength="1000"></textarea></label>
            <button class="secondary">Submit chat report</button>
        </form>
    </details>
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
