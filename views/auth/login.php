<div class="auth-shell">
    <section class="card auth-card">
        <span class="eyebrow">Welcome back</span>
        <h1>Log in to your private space</h1>
        <p class="muted">Continue onboarding, review anonymous matches, or return to a mutual chat. Your contact details are never shown automatically.</p>
        <form method="post" action="/login">
            <?= csrf_field() ?>
            <label>Email <input type="email" name="email" autocomplete="email" required placeholder="you@example.com"></label>
            <label>Password <input type="password" name="password" autocomplete="current-password" required placeholder="Your password"></label>
            <button class="button large">Log in safely</button>
        </form>
        <p class="auth-switch">New here? <a href="/register">Create a private account</a></p>
    </section>
    <aside class="card auth-side"><h2>Privacy-first by design</h2><ul class="soft-list"><li>No public profile directory.</li><li>Anonymous match cards.</li><li>Mutual consent before chat and reveal.</li></ul></aside>
</div>
