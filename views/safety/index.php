<section class="hero safety-hero card">
    <div><span class="eyebrow">Safety Center</span><h1>Your privacy and comfort come first.</h1><p class="lead">Use this page to understand how Doostyabi protects anonymity, how consent-based reveals work, and how to manage blocks.</p></div>
</section>
<section class="grid three feature-grid">
    <article class="card feature-card"><span class="feature-icon">B</span><h2>Block</h2><p>Blocking hides the match and disables related chat. You can unblock below if you change your mind.</p></article>
    <article class="card feature-card"><span class="feature-icon">R</span><h2>Report</h2><p>Report a match, chat, or message for admin review without exposing extra private information to the other member.</p></article>
    <article class="card feature-card"><span class="feature-icon">✓</span><h2>Consent reveals</h2><p>Limited details such as display name, city, or goals summary appear only after you approve a request.</p></article>
</section>
<section class="card promise-card"><h2>Privacy reminders</h2><ul class="soft-list"><li>No public profiles or searchable member directory.</li><li>Anonymous match cards before mutual interest.</li><li>No automatic email, phone, social handle, or address sharing.</li><li>Admins review reports for safety and moderation readiness.</li></ul></section>
<section class="card table-wrap">
    <div class="section-title"><div><h2>Your active blocks</h2><p class="muted">Blocked members cannot continue the related match/chat while the block is active.</p></div><span class="pill"><?= count($blocks) ?> active</span></div>
    <table><thead><tr><th>Blocked member</th><th>Source</th><th>Reason</th><th>Created</th><th>Action</th></tr></thead><tbody>
    <?php foreach ($blocks as $block): ?>
        <tr><td><?= e($block['blocked_name']) ?></td><td><span class="pill"><?= e($block['source']) ?></span></td><td><?= e($block['reason_text']) ?></td><td><?= e($block['created_at']) ?></td><td><form method="post" action="/safety/unblock" class="inline"><?= csrf_field() ?><input type="hidden" name="block_id" value="<?= (int)$block['id'] ?>"><button class="secondary">Unblock</button></form></td></tr>
    <?php endforeach; ?>
    </tbody></table>
    <?php if (!$blocks): ?><div class="empty-state"><h3>No active blocks</h3><p>You can block from match cards or moderation actions when something feels unsafe.</p></div><?php endif; ?>
</section>
