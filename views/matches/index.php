<section class="section-title"><div><span class="eyebrow">Anonymous introductions</span><h1>Your match cards</h1><p class="muted">Names, email, contact info, and private/admin-only answers stay hidden. Use interest, pass, block, or report controls at your pace.</p></div><a class="button secondary" href="/safety">Safety Center</a></section>
<?php if (!$cards): ?><section class="card empty-state"><h2>No matches yet</h2><p>Once matching runs for your profile, anonymous cards will appear here with compatibility context, strengths, and cautions.</p><a class="button" href="/onboarding">Review onboarding</a></section><?php endif; ?>
<div class="grid two match-grid">
<?php foreach ($cards as $card): ?>
<?php $status = $card['match_status']; $viewerAction = $card['viewer_action'] ?? ''; ?>
<section class="card match-card status-<?= e($status) ?>">
    <div class="section-title"><div><span class="eyebrow">Anonymous match</span><h2><?= e($card['title']) ?></h2></div><span class="pill compatibility-pill"><?= e($card['compatibility_label']) ?></span></div>
    <div class="match-score"><span><?= (int)round((float)$card['compatibility_score']) ?>%</span><small>compatibility estimate</small></div>
    <p class="match-summary"><?= e($card['summary']) ?></p>
    <div class="grid two insight-grid"><div class="insight positive"><h3>Strengths</h3><p><?= nl2br(e($card['strengths_text'])) ?></p></div><div class="insight caution"><h3>Cautions</h3><p><?= nl2br(e($card['cautions_text'])) ?></p></div></div>
    <div class="status-row"><span class="pill">Match: <?= e($status) ?></span><?php if ($viewerAction): ?><span class="pill">Your action: <?= e($viewerAction) ?></span><?php endif; ?></div>
    <form method="post" action="/matches/action" class="checks action-row">
        <?= csrf_field() ?><input type="hidden" name="match_id" value="<?= (int)$card['match_id'] ?>">
        <button name="action" value="interested" <?= $viewerAction === 'interested' ? 'disabled' : '' ?>>Interested</button>
        <button class="secondary" name="action" value="pass" <?= $viewerAction === 'pass' ? 'disabled' : '' ?>>Pass</button>
        <button class="secondary quiet-danger" name="action" value="block" onclick="return confirm('Block this anonymous profile?')">Block</button>
    </form>
    <?php if ($status === 'mutual'): ?><p class="success-inline">Mutual interest saved. <a href="/chats">Open chats</a>.</p><?php endif; ?>
    <details class="report-box"><summary>Report a safety concern</summary>
        <form method="post" action="/reports/match" class="grid two compact-form">
            <?= csrf_field() ?><input type="hidden" name="match_id" value="<?= (int)$card['match_id'] ?>"><input type="hidden" name="report_type" value="match">
            <label>Reason<select name="report_reason"><option value="harassment">Harassment</option><option value="spam">Spam or scam</option><option value="fake_profile">Fake profile</option><option value="unsafe_behavior">Unsafe behavior</option><option value="privacy">Privacy concern</option><option value="other">Other</option></select></label>
            <label class="full">Description<textarea name="description" rows="2" maxlength="1000" placeholder="Share only what admins need to review this safely."></textarea></label>
            <button class="secondary">Submit report</button>
        </form>
    </details>
</section>
<?php endforeach; ?>
</div>
