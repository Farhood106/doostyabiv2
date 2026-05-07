<?php
$user = \App\Core\Auth::user();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isAdmin = $user && (($user['role_name'] ?? '') === 'admin');
function nav_active(string $path, string $currentPath): string { return strpos($currentPath, $path) === 0 ? 'active' : ''; }
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Doostyabi</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="<?= $isAdmin ? 'admin-shell' : '' ?>">
<header class="topbar">
    <a class="brand" href="/">Doostyabi</a>
    <nav>
        <?php if ($user): ?>
            <?php if (!$isAdmin): ?><a href="/onboarding">Onboarding</a><a href="/matches">Matches</a><?php endif; ?>
            <form method="post" action="/logout" class="inline"><?= csrf_field() ?><button>Logout</button></form>
        <?php else: ?>
            <a href="/login">Login</a><a href="/register">Register</a>
        <?php endif; ?>
    </nav>
</header>
<?php if ($isAdmin): ?>
<aside class="admin-sidebar">
    <div class="nav-group"><span>Overview</span><a class="<?= $currentPath === '/admin' ? 'active' : '' ?>" href="/admin">Dashboard</a></div>
    <div class="nav-group"><span>People</span><a class="<?= nav_active('/admin/users', $currentPath) ?>" href="/admin/users">Users</a><a class="<?= nav_active('/admin/matches', $currentPath) ?>" href="/admin/matches">Matches</a></div>
    <div class="nav-group"><span>Catalogs</span><a class="<?= nav_active('/admin/catalogs', $currentPath) ?>" href="/admin/catalogs">Goals, Provinces, Cities</a></div>
    <div class="nav-group"><span>Form Builder</span><a class="<?= nav_active('/admin/forms', $currentPath) ?>" href="/admin/forms">Steps, Groups, Questions</a></div>
    <div class="nav-group"><span>System</span><a class="<?= nav_active('/admin/health', $currentPath) ?>" href="/admin/health">Health</a><a class="<?= nav_active('/admin/settings', $currentPath) ?>" href="/admin/settings">Settings</a></div>
</aside>
<?php endif; ?>
<main class="container <?= $isAdmin ? 'admin-main' : '' ?>">
    <?php if ($msg = flash('success')): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('error')): ?><div class="alert error"><?= e($msg) ?></div><?php endif; ?>
    <?= $content ?>
</main>
</body>
</html>
