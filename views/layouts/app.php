<?php $user = \App\Core\Auth::user(); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Doostyabi</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="/">Doostyabi</a>
    <nav>
        <?php if ($user): ?>
            <?php if (($user['role_name'] ?? '') === 'admin'): ?><a href="/admin">Admin</a><a href="/admin/forms">Forms</a><a href="/admin/users">Users</a><?php else: ?><a href="/onboarding">Onboarding</a><?php endif; ?>
            <form method="post" action="/logout" class="inline"><?= csrf_field() ?><button>Logout</button></form>
        <?php else: ?>
            <a href="/login">Login</a><a href="/register">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
    <?php if ($msg = flash('success')): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('error')): ?><div class="alert error"><?= e($msg) ?></div><?php endif; ?>
    <?= $content ?>
</main>
</body>
</html>
