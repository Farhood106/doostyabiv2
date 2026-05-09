<h1>Your anonymous matches</h1>
<p class="muted">Cards are anonymous. Names, email, contact info, and private/admin-only answers are not shown.</p>
<?php if (!$cards): ?><section class="card"><h2>No match cards yet</h2><p>Check back later after matching runs, or ask an admin to run matching for your profile.</p></section><?php endif; ?>
<div class="grid two">
<?php foreach ($cards as $card): ?>
<section class="card">
    <div class="section-title"><h2><?= e($card['title']) ?></h2><span class="pill"><?= e($card['compatibility_label']) ?></span></div>
    <p class="metric"><?= (int)round((float)$card['compatibility_score']) ?>%</p>
    <p><?= e($card['summary']) ?></p>
    <h3>Strengths</h3><p><?= nl2br(e($card['strengths_text'])) ?></p>
    <h3>Cautions</h3><p><?= nl2br(e($card['cautions_text'])) ?></p>
    <form method="post" action="/matches/action" class="checks">
        <?= csrf_field() ?><input type="hidden" name="match_id" value="<?= (int)$card['match_id'] ?>">
        <button name="action" value="interested" <?= ($card['viewer_action'] ?? '') === 'interested' ? 'disabled' : '' ?>>Interested</button>
        <button class="secondary" name="action" value="pass" <?= ($card['viewer_action'] ?? '') === 'pass' ? 'disabled' : '' ?>>Pass</button>
        <button class="secondary" name="action" value="block" onclick="return confirm('Block this anonymous profile?')">Block</button>
    </form>
    <?php if (($card['viewer_action'] ?? '') === 'interested'): ?><p class="pill">You marked Interested.</p><?php endif; ?>
    <?php if ($card['match_status'] === 'mutual'): ?><p class="pill">Mutual interest saved. <a href="/chats">Open chats</a>.</p><?php endif; ?>

    <details class="report-box"><summary>Report this match</summary>
        <form method="post" action="/reports/match" class="grid two">
            <?= csrf_field() ?><input type="hidden" name="match_id" value="<?= (int)$card['match_id'] ?>"><input type="hidden" name="report_type" value="match">
            <label>Reason<select name="report_reason"><option value="harassment">Harassment</option><option value="spam">Spam or scam</option><option value="fake_profile">Fake profile</option><option value="unsafe_behavior">Unsafe behavior</option><option value="privacy">Privacy concern</option><option value="other">Other</option></select></label>
            <label class="full">Description<textarea name="description" rows="2" maxlength="1000" placeholder="Share only what admins need to review this safely."></textarea></label>
            <button class="secondary">Submit report</button>
        </form>
    </details>
</section>
<?php endforeach; ?>
</div>
