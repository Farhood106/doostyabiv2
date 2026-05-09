<h1>Safety center</h1>
<section class="card">
    <h2>Privacy, consent, and reporting</h2>
    <ul>
        <li>Match cards and chats stay privacy-first: no emails, contact details, public profiles, or private/admin-only answers are shown automatically.</li>
        <li>Reveal requests require mutual-match chat context and target approval before limited mapped information is visible.</li>
        <li>You can report a match, chat, or message for moderation review. Reports are sent to admins without exposing extra private data to other members.</li>
        <li>Blocking a member hides the match and disables related chat. You can unblock members below if you blocked them by mistake.</li>
    </ul>
</section>
<section class="card table-wrap">
    <h2>Your active blocks</h2>
    <table><thead><tr><th>Blocked member</th><th>Source</th><th>Reason</th><th>Created</th><th>Action</th></tr></thead><tbody>
    <?php foreach ($blocks as $block): ?>
        <tr><td><?= e($block['blocked_name']) ?></td><td><?= e($block['source']) ?></td><td><?= e($block['reason_text']) ?></td><td><?= e($block['created_at']) ?></td><td><form method="post" action="/safety/unblock" class="inline"><?= csrf_field() ?><input type="hidden" name="block_id" value="<?= (int)$block['id'] ?>"><button class="secondary">Unblock</button></form></td></tr>
    <?php endforeach; ?>
    </tbody></table>
    <?php if (!$blocks): ?><p>No active blocks.</p><?php endif; ?>
</section>
