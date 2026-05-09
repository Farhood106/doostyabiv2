<?php
$user = \App\Core\Auth::user();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isAdmin = $user && (($user['role_name'] ?? '') === 'admin');
function nav_active(string $path, string $currentPath): string { return strpos($currentPath, $path) === 0 ? 'active' : ''; }
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>دوستیابی</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="<?= $isAdmin ? 'admin-shell' : '' ?>">
<header class="topbar">
    <a class="brand" href="/">دوستیابی</a>
    <nav aria-label="ناوبری اصلی">
        <?php if ($user): ?>
            <?php if (!$isAdmin): ?><a href="/onboarding">تکمیل شناخت</a><a href="/matches">معرفی‌ها</a><a href="/chats">گفت‌وگوها</a><a href="/safety">امنیت و حریم خصوصی</a><?php endif; ?>
            <form method="post" action="/logout" class="inline"><?= csrf_field() ?><button>خروج</button></form>
        <?php else: ?>
            <a href="/login">ورود</a><a href="/register">ثبت‌نام</a>
        <?php endif; ?>
    </nav>
</header>
<?php if ($isAdmin): ?>
<aside class="admin-sidebar" aria-label="منوی مدیریت">
    <div class="nav-group"><span>نمای کلی</span><a class="<?= $currentPath === '/admin' ? 'active' : '' ?>" href="/admin">داشبورد</a></div>
    <div class="nav-group"><span>اعضا و ارتباط‌ها</span><a class="<?= nav_active('/admin/users', $currentPath) ?>" href="/admin/users">اعضا</a><a class="<?= nav_active('/admin/matches', $currentPath) ?>" href="/admin/matches">معرفی‌ها</a><a class="<?= nav_active('/admin/chats', $currentPath) ?>" href="/admin/chats">گفت‌وگوها</a><a class="<?= nav_active('/admin/reveals', $currentPath) ?>" href="/admin/reveals">درخواست‌های نمایش</a><a class="<?= nav_active('/admin/moderation', $currentPath) ?>" href="/admin/moderation">گزارش‌ها و رسیدگی</a></div>
    <div class="nav-group"><span>فهرست‌ها</span><a class="<?= nav_active('/admin/catalogs', $currentPath) ?>" href="/admin/catalogs">هدف‌ها، استان‌ها، شهرها</a></div>
    <div class="nav-group"><span>فرم‌ساز</span><a class="<?= nav_active('/admin/forms', $currentPath) ?>" href="/admin/forms">مرحله‌ها، گروه‌ها، پرسش‌ها</a></div>
    <div class="nav-group"><span>سامانه</span><a class="<?= nav_active('/admin/health', $currentPath) ?>" href="/admin/health">سلامت سامانه</a><a class="<?= nav_active('/admin/settings', $currentPath) ?>" href="/admin/settings">تنظیمات</a></div>
</aside>
<?php endif; ?>
<main class="container <?= $isAdmin ? 'admin-main' : '' ?>">
    <?php if ($msg = flash('success')): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('error')): ?><div class="alert error"><?= e($msg) ?></div><?php endif; ?>
    <?= $content ?>
</main>
</body>
</html>
