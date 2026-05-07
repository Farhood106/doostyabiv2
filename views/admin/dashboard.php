<section class="section-title"><div><h1>Admin dashboard</h1><p class="muted">Manage the Phase 2 foundation before matching features are added.</p></div><span class="pill"><?= e($settings['site_status'] ?? 'active') ?></span></section>
<div class="grid six">
    <?php foreach ([['Users','users','/admin/users'],['Goals','goals','/admin/catalogs'],['Cities','cities','/admin/catalogs'],['Form steps','steps','/admin/forms'],['Questions','questions','/admin/forms'],['Completed','completed_onboardings','/admin/users'],['Matches','matches','/admin/matches']] as $card): ?>
        <a class="card stat-card" href="<?= e($card[2]) ?>"><strong><?= e($card[0]) ?></strong><p class="metric"><?= (int)($stats[$card[1]] ?? 0) ?></p></a>
    <?php endforeach; ?>
</div>
<section class="card"><h2>Quick actions</h2><div class="grid three quick-links"><a href="/admin/catalogs">Manage goals, provinces, and cities</a><a href="/admin/forms">Build onboarding form</a><a href="/admin/users">Review users and answers</a><a href="/admin/health">Open system health</a><a href="/admin/settings">Site settings</a><a href="/onboarding">Preview member onboarding</a></div></section>
<section class="card warning"><strong>Phase 2 focus:</strong> Admin usability and dynamic forms only. Matching, chat, reveal, and reports are intentionally not enabled yet.</section>
